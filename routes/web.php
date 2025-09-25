<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return redirect('/admin');
});

// Route untuk preview dan download file attachments
Route::get('/storage/mtc_attachments/{filename}', function ($filename) {
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
    
    // Set MIME types yang benar untuk preview
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
    
    // File types yang bisa di-preview inline di browser
    $previewableTypes = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'txt'];
    
    // Tentukan Content-Disposition berdasarkan kemampuan browser untuk preview
    if (in_array($extension, $previewableTypes)) {
        $contentDisposition = 'inline; filename="' . $filename . '"';
    } else {
        // Untuk Office files yang tidak bisa di-preview, gunakan attachment
        $contentDisposition = 'attachment; filename="' . $filename . '"';
    }
    
    return response()->file($filePath, [
        'Content-Type' => $mimeType,
        'Content-Disposition' => $contentDisposition,
        'Cache-Control' => 'public, max-age=3600',
        'X-Content-Type-Options' => 'nosniff'
    ]);
})->where('filename', '.*');

// Route khusus untuk download file attachments (forced download)
Route::get('/storage/mtc_attachments/download/{filename}', function ($filename) {
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
    
    // Set MIME types yang benar untuk download
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
    
    return response()->file($filePath, [
        'Content-Type' => $mimeType,
        'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        'Cache-Control' => 'public, max-age=3600',
        'X-Content-Type-Options' => 'nosniff'
    ]);
})->where('filename', '.*');
