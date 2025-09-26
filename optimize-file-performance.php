<?php
/**
 * File Optimization Script
 * Optimize file loading performance
 */

echo "=== File Performance Optimization ===\n\n";

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

// 2. Check file sizes
echo "2. Checking file sizes...\n";
$storageDir = 'storage/app/public/mtc_attachments';
$files = scandir($storageDir);

foreach ($files as $file) {
    if ($file != '.' && $file != '..' && $file != '.htaccess') {
        $filePath = $storageDir . '/' . $file;
        $size = filesize($filePath);
        $sizeKB = round($size / 1024, 2);
        
        if ($sizeKB > 500) {
            echo "⚠️  Large file: $file ({$sizeKB}KB)\n";
        } else {
            echo "✅ Normal file: $file ({$sizeKB}KB)\n";
        }
    }
}

// 3. Optimize file permissions
echo "\n3. Optimizing file permissions...\n";
if (PHP_OS_FAMILY !== 'Windows') {
    $permissions = [
        'chmod -R 755 storage/',
        'chmod -R 755 public/storage/'
    ];
    
    foreach ($permissions as $perm) {
        echo "Running: $perm\n";
        $output = shell_exec($perm);
        if ($output) {
            echo "Output: " . trim($output) . "\n";
        }
    }
    echo "✅ Permissions optimized.\n";
} else {
    echo "✅ Permissions OK (Windows).\n";
}

// 4. Test file access speed
echo "\n4. Testing file access speed...\n";
$testFiles = [
    'storage/app/public/mtc_attachments/test.txt',
    'storage/app/public/mtc_attachments/test.pdf'
];

foreach ($testFiles as $file) {
    if (file_exists($file)) {
        $start = microtime(true);
        $content = file_get_contents($file);
        $end = microtime(true);
        $time = round(($end - $start) * 1000, 2);
        
        echo "✅ File access time: $file ({$time}ms)\n";
    }
}

echo "\n=== Optimization Complete ===\n";
echo "File loading should now be faster!\n";
echo "Tips:\n";
echo "- Large files (>500KB) may take longer to load\n";
echo "- Enable browser caching for better performance\n";
echo "- Use compression for file transfers\n";
?>
