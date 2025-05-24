<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;
use ZipArchive;

class SimpleBackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:simple-backup-database 
                            {--filename= : Custom filename for the backup}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a simple backup of the database without using Spatie';

    /**
     * Execute the console command.
     * 
     * @return int
     */
    public function handle(): int
    {
        $this->info('Starting simple database backup process...');

        try {
            // Get database configuration
            $connection = config('database.default');
            $config = config("database.connections.{$connection}");

            if (!isset($config['dump']['dump_binary_path'])) {
                $this->error('MySQL dump binary path not configured in config/database.php');
                return Command::FAILURE;
            }

            // Create backup directory if it doesn't exist
            $backupDir = storage_path('app/private/backups');
            if (!File::exists($backupDir)) {
                File::makeDirectory($backupDir, 0755, true);
            }

            // Create temporary directory
            $tempDir = storage_path('app/backup-temp');
            if (!File::exists($tempDir)) {
                File::makeDirectory($tempDir, 0755, true);
            }

            // Set filename
            $customFilename = $this->option('filename');
            $timestamp = Carbon::now()->format('Y-m-d-H-i-s');
            $filename = $customFilename ?: "db-backup-{$timestamp}";

            // SQL file path
            $sqlFile = "{$tempDir}/{$config['database']}.sql";

            // Create a temporary file with credentials
            $tmpCnf = tempnam(sys_get_temp_dir(), 'mysql');
            file_put_contents($tmpCnf, "[client]\nuser = \"{$config['username']}\"\npassword = \"{$config['password']}\"\nhost = \"{$config['host']}\"\nport = \"{$config['port']}\"\n");

            // Build command using the credentials file instead of command line parameters
            $dumpCommand = sprintf(
                '"%s%s" --defaults-extra-file="%s" "%s" > "%s"',
                $config['dump']['dump_binary_path'],
                'mysqldump',
                $tmpCnf,
                $config['database'],
                $sqlFile
            );

            $this->info('Dumping database...');

            // Execute mysqldump command
            $returnVar = null;
            $output = [];
            exec($dumpCommand, $output, $returnVar);

            // Remove temporary credentials file
            unlink($tmpCnf);

            if ($returnVar !== 0) {
                $this->error('Database dump failed. Error code: ' . $returnVar);
                return Command::FAILURE;
            }

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

            // Set compression method to STORE (no compression) for Windows compatibility
            $zip->setCompressionName(basename($sqlFile), ZipArchive::CM_STORE, 0);

            // Add README file with backup info
            $readmeContent = "DATABASE BACKUP\n\n";
            $readmeContent .= "Created: " . Carbon::now()->format('Y-m-d H:i:s') . "\n";
            $readmeContent .= "Database: " . $config['database'] . "\n\n";
            $readmeContent .= "This is an unencrypted database backup.\n";
            $readmeContent .= "To restore this backup, use the command:\n";
            $readmeContent .= "php artisan app:restore-database --backup=" . basename($zipFile) . "\n";

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

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Backup failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
