<?php
// Script to rename migration files from 2025 to 2024

$migrationDir = __DIR__ . '/database/migrations';
$backupDir = $migrationDir . '/backup';

// Create backup directory if it doesn't exist
if (!file_exists($backupDir)) {
    mkdir($backupDir, 0755, true);
}

// Get all migration files with 2025 prefix
$files = glob($migrationDir . '/2025_*.php');

foreach ($files as $file) {
    $filename = basename($file);
    
    // Replace 2025 with 2024
    $newFilename = str_replace('2025_', '2024_', $filename);
    $newFilePath = $migrationDir . '/' . $newFilename;
    
    // Backup the original file
    copy($file, $backupDir . '/' . $filename);
    
    // Rename the file
    rename($file, $newFilePath);
    
    echo "Renamed: $filename -> $newFilename\n";
}

echo "Migration rename complete. Originals backed up to migrations/backup/\n";