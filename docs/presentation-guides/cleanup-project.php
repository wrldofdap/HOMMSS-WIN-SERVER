<?php
/**
 * HOMMSS Project Cleanup Script
 * 
 * This script removes unnecessary files and prepares the project for presentation.
 * Run this before your PBL presentation to clean up the project.
 */

echo "🧹 HOMMSS Project Cleanup Script\n";
echo "================================\n\n";

$cleaned = 0;
$errors = 0;

// Files to remove (unnecessary for presentation)
$filesToRemove = [
    // Temporary and development files
    'backup-config-aws.php',
    'fix-deployment-issues.php',
    'fix-deployment.bat',
    'migrate-rename.php',
    'test-sql-injection-manual.php',
    'composer.phar',
    
    // Backup scripts (keep the main ones)
    'backup-db.sh',
    'secure-backup-db.sh',
    'secure-restore-db.sh',
    'restore-database.bat',
    'security-scan.sh',
    
    // Old README
    'README-backup.md',
];

// Directories to clean (remove contents but keep directory)
$directoriesToClean = [
    'storage/logs' => ['*.log'],
    'storage/framework/cache' => ['*'],
    'storage/framework/sessions' => ['*'],
    'storage/framework/views' => ['*'],
    'bootstrap/cache' => ['*.php'],
];

// Clean individual files
echo "1. Removing unnecessary files...\n";
foreach ($filesToRemove as $file) {
    if (file_exists($file)) {
        if (unlink($file)) {
            echo "   ✅ Removed: $file\n";
            $cleaned++;
        } else {
            echo "   ❌ Failed to remove: $file\n";
            $errors++;
        }
    } else {
        echo "   ℹ️ Not found: $file\n";
    }
}

// Clean directories
echo "\n2. Cleaning cache directories...\n";
foreach ($directoriesToClean as $dir => $patterns) {
    if (is_dir($dir)) {
        foreach ($patterns as $pattern) {
            $files = glob("$dir/$pattern");
            foreach ($files as $file) {
                if (is_file($file)) {
                    if (unlink($file)) {
                        echo "   ✅ Cleaned: $file\n";
                        $cleaned++;
                    } else {
                        echo "   ❌ Failed to clean: $file\n";
                        $errors++;
                    }
                }
            }
        }
    }
}

// Clean old backup files (keep only recent ones)
echo "\n3. Cleaning old backup files...\n";
$backupDirs = [
    'storage/app/backups',
    'storage/app/private/backups'
];

foreach ($backupDirs as $backupDir) {
    if (is_dir($backupDir)) {
        $backupFiles = glob("$backupDir/*.zip");
        
        // Sort by modification time (newest first)
        usort($backupFiles, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        
        // Keep only the 3 most recent backups
        $filesToKeep = array_slice($backupFiles, 0, 3);
        $filesToDelete = array_slice($backupFiles, 3);
        
        foreach ($filesToDelete as $file) {
            if (unlink($file)) {
                echo "   ✅ Removed old backup: " . basename($file) . "\n";
                $cleaned++;
            } else {
                echo "   ❌ Failed to remove: " . basename($file) . "\n";
                $errors++;
            }
        }
        
        if (count($filesToKeep) > 0) {
            echo "   ℹ️ Kept " . count($filesToKeep) . " recent backups\n";
        }
    }
}

// Clean node_modules if it's too large (optional)
echo "\n4. Checking node_modules size...\n";
if (is_dir('node_modules')) {
    $size = getDirSize('node_modules');
    $sizeMB = round($size / 1024 / 1024, 2);
    echo "   ℹ️ node_modules size: {$sizeMB} MB\n";
    
    if ($sizeMB > 500) {
        echo "   ⚠️ node_modules is large. Consider running 'npm ci --production' to reduce size\n";
    }
}

// Optimize composer autoloader
echo "\n5. Optimizing composer autoloader...\n";
exec('composer dump-autoload --optimize 2>&1', $output, $return);
if ($return === 0) {
    echo "   ✅ Composer autoloader optimized\n";
    $cleaned++;
} else {
    echo "   ❌ Failed to optimize composer autoloader\n";
    $errors++;
}

// Clear and rebuild Laravel caches
echo "\n6. Rebuilding Laravel caches...\n";
$cacheCommands = [
    'config:clear' => 'Config cache cleared',
    'route:clear' => 'Route cache cleared',
    'view:clear' => 'View cache cleared',
    'config:cache' => 'Config cached',
    'route:cache' => 'Routes cached',
    'view:cache' => 'Views cached'
];

foreach ($cacheCommands as $command => $message) {
    exec("php artisan $command 2>&1", $output, $return);
    if ($return === 0) {
        echo "   ✅ $message\n";
        $cleaned++;
    } else {
        echo "   ❌ Failed: $command\n";
        $errors++;
    }
}

// Create presentation-ready backup
echo "\n7. Creating presentation backup...\n";
exec('php artisan app:php-backup-database --filename=presentation-ready 2>&1', $output, $return);
if ($return === 0) {
    echo "   ✅ Presentation backup created\n";
    $cleaned++;
} else {
    echo "   ❌ Failed to create presentation backup\n";
    $errors++;
}

// Generate project statistics
echo "\n8. Generating project statistics...\n";
$stats = generateProjectStats();
file_put_contents('PROJECT-STATISTICS.md', $stats);
echo "   ✅ Project statistics saved to PROJECT-STATISTICS.md\n";
$cleaned++;

// Summary
echo "\n" . str_repeat("=", 50) . "\n";
echo "🎯 CLEANUP SUMMARY\n";
echo str_repeat("=", 50) . "\n";
echo "✅ Items cleaned: $cleaned\n";
echo "❌ Errors: $errors\n";

if ($errors === 0) {
    echo "\n🎉 Project is ready for presentation!\n";
    echo "📋 Next steps:\n";
    echo "   1. Review PBL-PRESENTATION-GUIDE.md\n";
    echo "   2. Test the application: php artisan serve\n";
    echo "   3. Practice your demo\n";
    echo "   4. Check PROJECT-STATISTICS.md for metrics\n";
} else {
    echo "\n⚠️ Some cleanup tasks failed. Review errors above.\n";
}

echo "\n📖 Documentation files created:\n";
echo "   - PBL-PRESENTATION-GUIDE.md (Main presentation guide)\n";
echo "   - PROJECT-STATISTICS.md (Project metrics)\n";
echo "   - deployment-readiness-test.md (Technical testing)\n";

/**
 * Calculate directory size recursively
 */
function getDirSize($dir) {
    $size = 0;
    if (is_dir($dir)) {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($files as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }
    }
    return $size;
}

/**
 * Generate project statistics
 */
function generateProjectStats() {
    $stats = "# 📊 HOMMSS Project Statistics\n\n";
    
    // Count files by type
    $phpFiles = count(glob('app/**/*.php', GLOB_BRACE)) + count(glob('routes/*.php'));
    $bladeFiles = count(glob('resources/views/**/*.blade.php', GLOB_BRACE));
    $jsFiles = count(glob('public/js/*.js')) + count(glob('resources/js/*.js'));
    $cssFiles = count(glob('public/css/*.css')) + count(glob('resources/css/*.css'));
    
    $stats .= "## 📁 File Count\n";
    $stats .= "- **PHP Files:** $phpFiles\n";
    $stats .= "- **Blade Templates:** $bladeFiles\n";
    $stats .= "- **JavaScript Files:** $jsFiles\n";
    $stats .= "- **CSS Files:** $cssFiles\n\n";
    
    // Database tables
    $migrationFiles = count(glob('database/migrations/*.php'));
    $modelFiles = count(glob('app/Models/*.php'));
    
    $stats .= "## 🗄️ Database\n";
    $stats .= "- **Migration Files:** $migrationFiles\n";
    $stats .= "- **Model Files:** $modelFiles\n\n";
    
    // Features implemented
    $stats .= "## ✅ Features Implemented\n";
    $stats .= "- User Authentication with OTP\n";
    $stats .= "- Product Catalog Management\n";
    $stats .= "- Shopping Cart & Checkout\n";
    $stats .= "- Order Management System\n";
    $stats .= "- Admin Dashboard\n";
    $stats .= "- Email Notifications with TLS\n";
    $stats .= "- Database Backup System\n";
    $stats .= "- SQL Injection Protection\n";
    $stats .= "- Secure File Upload System\n";
    $stats .= "- Payment Gateway Integration\n\n";
    
    // Security features
    $stats .= "## 🛡️ Security Features\n";
    $stats .= "- OTP-based Authentication\n";
    $stats .= "- Session Encryption\n";
    $stats .= "- CSRF Protection\n";
    $stats .= "- SQL Injection Prevention\n";
    $stats .= "- File Upload Validation\n";
    $stats .= "- Email Security (TLS)\n";
    $stats .= "- Encrypted Database Backups\n\n";
    
    $stats .= "## 🚀 Deployment\n";
    $stats .= "- **Platform:** AWS EC2 Ubuntu\n";
    $stats .= "- **Web Server:** Apache\n";
    $stats .= "- **Database:** MySQL\n";
    $stats .= "- **PHP Version:** 8.3+\n";
    $stats .= "- **Framework:** Laravel 11\n\n";
    
    $stats .= "---\n";
    $stats .= "*Generated on: " . date('Y-m-d H:i:s') . "*\n";
    
    return $stats;
}
?>
