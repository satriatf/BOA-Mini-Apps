<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return redirect('/admin');
});

// Route untuk preview dan download file attachments - Optimized
Route::get('/storage/mtc_attachments/{filename}', function ($filename) {
    // Set execution time limit untuk file access
    set_time_limit(60);
    
    // Cek file di storage
    $filePath = storage_path('app/public/mtc_attachments/' . $filename);
    
    if (!file_exists($filePath)) {
        // Fallback: cek di public storage
        $publicPath = public_path('storage/mtc_attachments/' . $filename);
        if (file_exists($publicPath)) {
            $filePath = $publicPath;
        } else {
            abort(404, 'File not found: ' . $filename);
        }
    }
    
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    // MIME types sederhana dan cepat
    $mimeTypes = [
        'pdf' => 'application/pdf',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'bmp' => 'image/bmp',
        'svg' => 'image/svg+xml',
        'txt' => 'text/plain',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'ppt' => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    ];
    
    $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';
    
    // File yang bisa preview
    $previewableTypes = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'txt'];
    
    if (in_array($extension, $previewableTypes)) {
        $contentDisposition = 'inline; filename="' . $filename . '"';
    } else {
        $contentDisposition = 'attachment; filename="' . $filename . '"';
    }
    
    // Headers optimasi untuk performa lebih baik
    $fileSize = filesize($filePath);
    
    return response()->file($filePath, [
        'Content-Type' => $mimeType,
        'Content-Disposition' => $contentDisposition,
        'Cache-Control' => 'public, max-age=86400', // Cache 24 jam
        'Content-Length' => $fileSize,
        'Accept-Ranges' => 'bytes',
        'X-Accel-Buffering' => 'no' // Disable buffering untuk streaming
    ]);
})->where('filename', '.*');

// Route khusus untuk download file attachments (forced download) - Optimized
Route::get('/storage/mtc_attachments/download/{filename}', function ($filename) {
    // Set execution time limit untuk file access
    set_time_limit(60);
    
    $filePath = storage_path('app/public/mtc_attachments/' . $filename);
    
    if (!file_exists($filePath)) {
        // Fallback: cek di public storage
        $publicPath = public_path('storage/mtc_attachments/' . $filename);
        if (file_exists($publicPath)) {
            $filePath = $publicPath;
        } else {
            abort(404, 'File not found: ' . $filename);
        }
    }
    
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    // MIME types sederhana dan cepat
    $mimeTypes = [
        'pdf' => 'application/pdf',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'bmp' => 'image/bmp',
        'svg' => 'image/svg+xml',
        'txt' => 'text/plain',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'ppt' => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    ];
    
    $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';
    
    // Headers optimasi untuk performa lebih baik
    $fileSize = filesize($filePath);
    
    return response()->file($filePath, [
        'Content-Type' => $mimeType,
        'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        'Cache-Control' => 'public, max-age=86400', // Cache 24 jam
        'Content-Length' => $fileSize,
        'Accept-Ranges' => 'bytes',
        'X-Accel-Buffering' => 'no' // Disable buffering untuk streaming
    ]);
})->where('filename', '.*');
