<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use ZipArchive;

class BackupDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:backup-database
                            {--filename= : Custom filename for the backup}
                            {--password= : Password for encrypting the backup (will prompt if not provided)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a secure, encrypted backup of the database';

    /**
     * Execute the console command.
     * 
     * @return int
     */
    public function handle(): int
    {
        $this->info('Starting database backup process...');

        try {
            // Get database configuration
            $connection = config('database.default');
            $config = config("database.connections.{$connection}");

            if (!isset($config['dump']['dump_binary_path'])) {
                $this->error('MySQL dump binary path not configured in config/database.php');
                return Command::FAILURE;
            }

            // Create backup directory if it doesn't exist
            $backupDir = storage_path('app/Laravel');
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
            $filename = $customFilename ?: "hommss-db-backup-{$timestamp}";

            // Get encryption password
            $password = $this->getBackupPassword();

            // SQL file path
            $sqlFile = "{$tempDir}/{$config['database']}.sql";

            // Dump database using environment variables instead of command line arguments
            $this->dumpDatabase($config, $sqlFile);

            $this->info('Database dumped successfully.');

            // Encrypt the SQL file
            $this->info('Encrypting database dump...');
            $sqlContent = File::get($sqlFile);
            $encryptedContent = $this->encryptContent($sqlContent, $password);
            $encryptedFile = "{$tempDir}/{$config['database']}.enc";
            File::put($encryptedFile, $encryptedContent);

            // Generate hash for verification
            $fileHash = hash_file('sha256', $sqlFile);

            // Generate a password verification hash (different from the encryption key)
            $passwordVerificationHash = hash('sha256', $password);

            // Create metadata file with timestamp, database info, and hash
            $metadata = [
                'timestamp' => Carbon::now()->toIso8601String(),
                'database' => $config['database'],
                'version' => '1.0',
                'encrypted' => true,
                'hash' => $fileHash,
                'hash_algorithm' => 'sha256',
                'password_hash' => $passwordVerificationHash // Store password hash for verification
            ];

            $metadataFile = "{$tempDir}/backup-metadata.json";
            File::put($metadataFile, json_encode($metadata, JSON_PRETTY_PRINT));

            // Create ZIP file
            $zipFile = "{$backupDir}/{$filename}.zip";
            $this->createBackupZip($zipFile, $encryptedFile, $metadataFile, $tempDir, $config);

            // Delete temporary files
            File::delete($sqlFile);
            File::delete($encryptedFile);
            File::delete($metadataFile);
            File::delete("{$tempDir}/README.txt");

            $this->info('Secure backup completed successfully!');
            $this->info('Backup file: ' . $zipFile);
            $this->info('The backup is encrypted and password-protected.');
            $this->info('Keep your backup password safe - you will need it to restore the backup.');

            // Send email notification for successful backup
            $this->sendEmailNotification(true);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Backup failed: ' . $e->getMessage());
            // Send email notification for failed backup
            $this->sendEmailNotification(false, $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Get backup password securely
     * 
     * @return string
     */
    protected function getBackupPassword(): string
    {
        $password = $this->option('password');

        // If no password provided, check if we're running in a console
        if (empty($password)) {
            $defaultPassword = env('BACKUP_PASSWORD');

            // Check if we're running in interactive mode (with a console)
            if ($this->input->isInteractive()) {
                $password = $this->secret('Enter a password for encrypting the backup (leave empty for default):');
                if (empty($password)) {
                    if (empty($defaultPassword)) {
                        $this->error('No default backup password found in environment and no password provided.');
                        $password = $this->secret('Password is required. Please enter a password:');

                        if (empty($password)) {
                            throw new \Exception('Backup requires a password for encryption.');
                        }
                    } else {
                        $password = $defaultPassword;
                        $this->info('Using default password from environment.');
                    }
                }
            } else {
                // We're running from scheduler or non-interactive environment
                if (empty($defaultPassword)) {
                    throw new \Exception('No backup password found in environment for non-interactive mode.');
                }

                $password = $defaultPassword;
                $this->info('Running in non-interactive mode. Using default password from environment.');
            }
        }

        return $password;
    }

    /**
     * Dump database to file
     * 
     * @param array $config
     * @param string $sqlFile
     * @return void
     */
    protected function dumpDatabase(array $config, string $sqlFile): void
    {
        $this->info('Dumping database...');

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

        // Execute mysqldump command
        $returnVar = null;
        $output = [];
        exec($dumpCommand, $output, $returnVar);

        // Remove temporary credentials file
        unlink($tmpCnf);

        if ($returnVar !== 0) {
            throw new \Exception("Database dump failed. Error code: {$returnVar}");
        }
    }

    /**
     * Create backup ZIP file
     * 
     * @param string $zipFile
     * @param string $encryptedFile
     * @param string $metadataFile
     * @param string $tempDir
     * @param array $config
     * @return void
     */
    protected function createBackupZip(string $zipFile, string $encryptedFile, string $metadataFile, string $tempDir, array $config): void
    {
        $this->info('Creating ZIP archive: ' . $zipFile);

        $zip = new ZipArchive();
        $result = $zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        if ($result !== true) {
            throw new \Exception('Failed to create ZIP file. Error code: ' . $result);
        }

        // Add encrypted SQL file to ZIP
        $zip->addFile($encryptedFile, basename($encryptedFile));
        $zip->setCompressionName(basename($encryptedFile), ZipArchive::CM_STORE, 0);

        // Add metadata file to ZIP
        $zip->addFile($metadataFile, basename($metadataFile));
        $zip->setCompressionName(basename($metadataFile), ZipArchive::CM_STORE, 0);

        // Add README file to ZIP
        $readmeContent = "SECURE DATABASE BACKUP\n\n";
        $readmeContent .= "Created: " . Carbon::now()->format('Y-m-d H:i:s') . "\n";
        $readmeContent .= "Database: " . $config['database'] . "\n";
        $readmeContent .= "Encrypted: Yes\n";
        $readmeContent .= "Hash Algorithm: SHA-256\n\n";
        $readmeContent .= "This backup is encrypted and requires a password to restore.\n\n";
        $readmeContent .= "To restore this backup, use the command:\n";
        $readmeContent .= "php artisan app:restore-database --backup=" . basename($zipFile) . "\n\n";
        $readmeContent .= "Or use the restore-database script.\n";

        $readmeFile = "{$tempDir}/README.txt";
        File::put($readmeFile, $readmeContent);
        $zip->addFile($readmeFile, "README.txt");

        // Close ZIP file
        $zip->close();
    }

    /**
     * Send email notification about backup status
     * 
     * @param bool $success
     * @param string $message
     * @return void
     */
    protected function sendEmailNotification(bool $success, string $message = ''): void
    {
        // Only send emails for manual backups if not running from scheduler
        if (!$this->laravel->runningInConsole() || $this->input->isInteractive()) {
            $adminEmail = env('ADMIN_EMAIL');

            if (empty($adminEmail)) {
                $this->warn('Admin email not configured. Email notification not sent.');
                return;
            }

            $subject = $success ?
                'Database Backup Completed Successfully' :
                'Database Backup Failed';

            // Ensure we use Manila timezone for the timestamp
            $timestamp = Carbon::now()->setTimezone('Asia/Manila')->format('Y-m-d H:i:s');

            $content = $success ?
                "A database backup was completed successfully at " . $timestamp . " (Manila time).\n\n" .
                "This is an automated notification." :
                "A database backup failed at " . $timestamp . " (Manila time).\n\n" .
                "Error: " . $message . "\n\n" .
                "Please check the system and resolve the issue.";

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

    /**
     * Encrypt content with password
     * 
     * @param string $content
     * @param string $password
     * @return string
     */
    protected function encryptContent(string $content, string $password): string
    {
        // Generate a random initialization vector
        $iv = openssl_random_pseudo_bytes(16);

        // Add a salt to the password (using part of the IV as salt)
        $salt = substr(bin2hex($iv), 0, 16);
        $saltedPassword = $password . $salt;

        // Create a key from the salted password
        $key = hash('sha256', $saltedPassword, true);

        // Encrypt the content
        $encrypted = openssl_encrypt($content, 'AES-256-CBC', $key, 0, $iv);

        // Combine the IV, salt, and encrypted content
        return base64_encode(json_encode([
            'iv' => base64_encode($iv),
            'salt' => $salt,
            'data' => $encrypted,
            'method' => 'AES-256-CBC'
        ]));
    }
}
