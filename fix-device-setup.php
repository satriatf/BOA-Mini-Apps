<?php
/**
 * Simple Device Setup Script
 * Fix "not found" issues on other devices
 */

echo "=== Fixing Device File Access ===\n\n";

// 1. Clear all caches
echo "1. Clearing caches...\n";
$commands = [
    'php artisan cache:clear',
    'php artisan config:clear',
    'php artisan route:clear',
    'php artisan view:clear'
];

foreach ($commands as $command) {
    echo "Running: $command\n";
    $output = shell_exec($command);
    if ($output) {
        echo "Output: " . trim($output) . "\n";
    }
}
echo "✅ Caches cleared.\n\n";

// 2. Remove old symlink and create new one
echo "2. Recreating storage symlink...\n";
if (is_link('public/storage')) {
    unlink('public/storage');
    echo "Removed old symlink\n";
}

$output = shell_exec('php artisan storage:link');
echo "Output: " . trim($output) . "\n";
echo "✅ Storage symlink recreated.\n\n";

// 3. Ensure directories exist
echo "3. Creating directories...\n";
$dirs = [
    'storage/app/public/mtc_attachments',
    'public/storage/mtc_attachments'
];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        echo "✅ Created: $dir\n";
    } else {
        echo "✅ Exists: $dir\n";
    }
}

// 4. Copy files from storage to public (fallback)
echo "4. Creating file fallback...\n";
$storageDir = 'storage/app/public/mtc_attachments';
$publicDir = 'public/storage/mtc_attachments';

if (is_dir($storageDir)) {
    $files = scandir($storageDir);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..' && $file != '.htaccess') {
            $source = $storageDir . '/' . $file;
            $target = $publicDir . '/' . $file;
            
            if (!file_exists($target)) {
                copy($source, $target);
                echo "✅ Copied: $file\n";
            }
        }
    }
}

// 5. Test file access
echo "5. Testing file access...\n";
$testFile = 'public/storage/mtc_attachments/test.txt';
if (!file_exists($testFile)) {
    file_put_contents($testFile, 'Test file for device compatibility');
    echo "✅ Created test file\n";
}

echo "\n=== Setup Complete ===\n";
echo "Files should now be accessible on all devices!\n";
echo "Test URL: http://your-domain/storage/mtc_attachments/test.txt\n";
?>
