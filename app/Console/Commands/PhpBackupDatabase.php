<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use ZipArchive;

class PhpBackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:php-backup-database 
                            {--filename= : Custom filename for the backup}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a database backup using pure PHP (no mysqldump required)';

    /**
     * Execute the console command.
     * 
     * @return int
     */
    public function handle(): int
    {
        $this->info('Starting PHP database backup process...');

        try {
            // Create backup directory if it doesn't exist
            $backupDir = storage_path('app/backups');
            if (!File::exists($backupDir)) {
                File::makeDirectory($backupDir, 0755, true);
            }

            // Create temporary directory
            $tempDir = storage_path('app/backup-temp');
            if (!File::exists($tempDir)) {
                File::makeDirectory($tempDir, 0755, true);
            }

            // Get database configuration
            $config = config('database.connections.' . config('database.default'));

            // Set filename
            $customFilename = $this->option('filename');
            $timestamp = Carbon::now()->format('Y-m-d-H-i-s');
            $filename = $customFilename ?: "php-backup-{$timestamp}";

            // SQL file path
            $sqlFile = "{$tempDir}/{$config['database']}.sql";

            $this->info('Generating database dump using PHP...');

            // Generate SQL dump using PHP
            $this->generateSqlDump($config, $sqlFile);

            $this->info('Database dumped successfully.');

            // Create ZIP file
            $zipFile = "{$backupDir}/{$filename}.zip";
            $this->info('Creating ZIP archive: ' . $zipFile);

            $zip = new ZipArchive();
            $result = $zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);

            if ($result !== true) {
                $this->error('Failed to create ZIP file. Error code: ' . $result);
                return Command::FAILURE;
            }

            // Add SQL file to ZIP
            $zip->addFile($sqlFile, basename($sqlFile));

            // Set compression method to STORE (no compression) for compatibility
            $zip->setCompressionName(basename($sqlFile), ZipArchive::CM_STORE, 0);

            // Add README file with backup info
            $readmeContent = "PHP DATABASE BACKUP\n\n";
            $readmeContent .= "Created: " . Carbon::now()->format('Y-m-d H:i:s') . "\n";
            $readmeContent .= "Database: " . $config['database'] . "\n";
            $readmeContent .= "Method: Pure PHP (no mysqldump)\n\n";
            $readmeContent .= "This backup was created using PHP database queries.\n";
            $readmeContent .= "To restore, import the SQL file into your database.\n";

            $readmeFile = "{$tempDir}/README.txt";
            File::put($readmeFile, $readmeContent);
            $zip->addFile($readmeFile, "README.txt");

            // Close ZIP file
            $zip->close();

            // Delete temporary files
            File::delete($sqlFile);
            File::delete($readmeFile);

            $this->info('Backup completed successfully!');
            $this->info('Backup file: ' . $zipFile);

            // Send email notification for successful backup
            $this->sendEmailNotification(true, $zipFile);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Backup failed: ' . $e->getMessage());
            
            // Send email notification for failed backup
            $this->sendEmailNotification(false, '', $e->getMessage());
            
            return Command::FAILURE;
        }
    }

    /**
     * Generate SQL dump using PHP database queries
     * 
     * @param array $config
     * @param string $sqlFile
     * @return void
     */
    protected function generateSqlDump(array $config, string $sqlFile): void
    {
        $sql = "-- PHP Database Backup\n";
        $sql .= "-- Generated on: " . Carbon::now()->format('Y-m-d H:i:s') . "\n";
        $sql .= "-- Database: {$config['database']}\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n";
        $sql .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
        $sql .= "SET AUTOCOMMIT = 0;\n";
        $sql .= "START TRANSACTION;\n\n";

        // Get all tables
        $tables = DB::select('SHOW TABLES');
        $tableKey = 'Tables_in_' . $config['database'];

        foreach ($tables as $table) {
            $tableName = $table->$tableKey;
            $this->info("Processing table: {$tableName}");

            // Get table structure
            $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`")[0];
            $sql .= "-- Table structure for table `{$tableName}`\n";
            $sql .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
            $sql .= $createTable->{'Create Table'} . ";\n\n";

            // Get table data
            $rows = DB::table($tableName)->get();
            
            if ($rows->count() > 0) {
                $sql .= "-- Dumping data for table `{$tableName}`\n";
                $sql .= "INSERT INTO `{$tableName}` VALUES\n";

                $values = [];
                foreach ($rows as $row) {
                    $rowData = [];
                    foreach ($row as $value) {
                        if (is_null($value)) {
                            $rowData[] = 'NULL';
                        } else {
                            $rowData[] = "'" . addslashes($value) . "'";
                        }
                    }
                    $values[] = '(' . implode(',', $rowData) . ')';
                }
                
                $sql .= implode(",\n", $values) . ";\n\n";
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
        $sql .= "COMMIT;\n";

        // Write to file
        File::put($sqlFile, $sql);
    }

    /**
     * Send email notification about backup status
     * 
     * @param bool $success
     * @param string $backupFile
     * @param string $errorMessage
     * @return void
     */
    protected function sendEmailNotification(bool $success, string $backupFile = '', string $errorMessage = ''): void
    {
        $adminEmail = env('ADMIN_EMAIL');

        if (empty($adminEmail)) {
            $this->warn('Admin email not configured. Email notification not sent.');
            return;
        }

        $subject = $success ?
            'PHP Database Backup Completed Successfully' :
            'PHP Database Backup Failed';

        // Ensure we use Manila timezone for the timestamp
        $timestamp = Carbon::now()->setTimezone('Asia/Manila')->format('Y-m-d H:i:s');

        if ($success) {
            $content = "A PHP database backup was completed successfully at " . $timestamp . " (Manila time).\n\n";
            $content .= "Backup file: " . basename($backupFile) . "\n";
            $content .= "Method: Pure PHP (no mysqldump required)\n\n";
            $content .= "This backup was created using PHP database queries and works on any system.\n\n";
            $content .= "This is an automated notification from your HOMMSS application.";
        } else {
            $content = "A PHP database backup failed at " . $timestamp . " (Manila time).\n\n";
            $content .= "Error: " . $errorMessage . "\n\n";
            $content .= "Please check the system and resolve the issue.\n\n";
            $content .= "This is an automated notification from your HOMMSS application.";
        }

        try {
            Mail::raw($content, function ($mail) use ($adminEmail, $subject) {
                $mail->to($adminEmail)
                    ->subject($subject);
            });

            $this->info('Email notification sent to: ' . $adminEmail);
        } catch (\Exception $e) {
            $this->warn('Failed to send email notification: ' . $e->getMessage());
        }
    }
}
