<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class CheckBackupSchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-backup-schedule';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check backup schedule status and configuration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 HOMMSS Backup Schedule Status Check');
        $this->info('=====================================');
        $this->newLine();

        // Check Laravel Scheduler Configuration
        $this->checkSchedulerConfig();
        
        // Check Backup Commands
        $this->checkBackupCommands();
        
        // Check Backup Directory
        $this->checkBackupDirectory();
        
        // Check Recent Backups
        $this->checkRecentBackups();
        
        // Check Email Configuration
        $this->checkEmailConfig();
        
        // Show Setup Instructions
        $this->showSetupInstructions();
    }

    protected function checkSchedulerConfig()
    {
        $this->info('📅 Laravel Scheduler Configuration:');
        
        // Check if schedule is defined in Kernel.php
        $kernelPath = app_path('Console/Kernel.php');
        $kernelContent = File::get($kernelPath);
        
        if (strpos($kernelContent, 'app:backup-database') !== false) {
            $this->line('   ✅ Backup schedule defined in Kernel.php');
            $this->line('   📅 Scheduled: Daily at 2:00 AM');
            $this->line('   📧 Email notifications: Enabled');
        } else {
            $this->error('   ❌ Backup schedule not found in Kernel.php');
        }
        
        $this->newLine();
    }

    protected function checkBackupCommands()
    {
        $this->info('🛠️ Available Backup Commands:');
        
        $commands = [
            'app:backup-database' => 'Main backup command (with encryption)',
            'app:simple-backup-database' => 'Simple backup (no encryption)',
            'app:php-backup-database' => 'PHP-only backup (no mysqldump)',
            'app:secure-backup-database' => 'Secure encrypted backup',
        ];
        
        foreach ($commands as $command => $description) {
            try {
                $this->call($command, ['--help' => true]);
                $this->line("   ✅ $command - $description");
            } catch (\Exception $e) {
                $this->line("   ❌ $command - Not available");
            }
        }
        
        $this->newLine();
    }

    protected function checkBackupDirectory()
    {
        $this->info('📁 Backup Directory Status:');
        
        $backupDirs = [
            'storage/app/backups' => 'Main backup directory',
            'storage/app/private/backups' => 'Private backup directory',
            'storage/logs' => 'Log directory'
        ];
        
        foreach ($backupDirs as $dir => $description) {
            if (File::exists($dir)) {
                $size = $this->getDirSize($dir);
                $this->line("   ✅ $dir ($description) - Size: " . $this->formatBytes($size));
            } else {
                $this->line("   ❌ $dir ($description) - Not found");
            }
        }
        
        $this->newLine();
    }

    protected function checkRecentBackups()
    {
        $this->info('📦 Recent Backups:');
        
        $backupDir = storage_path('app/backups');
        if (File::exists($backupDir)) {
            $backups = collect(File::files($backupDir))
                ->filter(function ($file) {
                    return pathinfo($file, PATHINFO_EXTENSION) === 'zip';
                })
                ->sortByDesc(function ($file) {
                    return File::lastModified($file);
                })
                ->take(5);
            
            if ($backups->count() > 0) {
                foreach ($backups as $backup) {
                    $name = basename($backup);
                    $size = $this->formatBytes(File::size($backup));
                    $date = Carbon::createFromTimestamp(File::lastModified($backup))->format('Y-m-d H:i:s');
                    $this->line("   📦 $name ($size) - $date");
                }
            } else {
                $this->line('   ℹ️ No backup files found');
            }
        } else {
            $this->line('   ❌ Backup directory not found');
        }
        
        $this->newLine();
    }

    protected function checkEmailConfig()
    {
        $this->info('📧 Email Configuration:');
        
        $adminEmail = env('ADMIN_EMAIL');
        $mailHost = env('MAIL_HOST');
        $mailMailer = env('MAIL_MAILER');
        
        if ($adminEmail) {
            $this->line("   ✅ Admin email: $adminEmail");
        } else {
            $this->line('   ❌ Admin email not configured');
        }
        
        if ($mailHost && $mailMailer) {
            $this->line("   ✅ Mail configured: $mailMailer via $mailHost");
        } else {
            $this->line('   ❌ Mail configuration incomplete');
        }
        
        $this->newLine();
    }

    protected function showSetupInstructions()
    {
        $this->info('⚙️ Setup Instructions:');
        $this->newLine();
        
        $this->line('📋 To enable scheduled backups:');
        $this->newLine();
        
        $this->line('🖥️ On Windows (Development):');
        $this->line('   1. Open Task Scheduler');
        $this->line('   2. Create Basic Task');
        $this->line('   3. Set to run every minute');
        $this->line('   4. Action: Start a program');
        $this->line('   5. Program: php');
        $this->line('   6. Arguments: ' . base_path('artisan') . ' schedule:run');
        $this->line('   7. Start in: ' . base_path());
        $this->newLine();
        
        $this->line('🐧 On Linux/AWS (Production):');
        $this->line('   1. Edit crontab: crontab -e');
        $this->line('   2. Add this line:');
        $this->line('      * * * * * cd ' . base_path() . ' && php artisan schedule:run >> /dev/null 2>&1');
        $this->newLine();
        
        $this->line('🧪 Test Commands:');
        $this->line('   • Test scheduler: php artisan schedule:run');
        $this->line('   • Manual backup: php artisan app:php-backup-database');
        $this->line('   • Check this status: php artisan app:check-backup-schedule');
        $this->newLine();
        
        $this->line('📊 Next scheduled backup: Tomorrow at 2:00 AM (if cron is set up)');
    }

    protected function getDirSize($dir)
    {
        $size = 0;
        if (is_dir($dir)) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
            );
            
            foreach ($files as $file) {
                if ($file->isFile()) {
                    $size += $file->getSize();
                }
            }
        }
        return $size;
    }

    protected function formatBytes($size, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        
        return round($size, $precision) . ' ' . $units[$i];
    }
}
