<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return redirect('/admin');
});

// Route khusus untuk mtc attachments
Route::get('/storage/mtc_attachments/{filename}', function ($filename) {
    $filePath = storage_path('app/public/mtc_attachments/' . $filename);
    
    if (!file_exists($filePath)) {
        abort(404, 'File not found: ' . $filename);
    }
    
    return response()->file($filePath);
})->name('mtc.attachment')->middleware('web');

// Route general untuk storage files
Route::get('/storage/{path}', function ($path) {
    $filePath = storage_path('app/public/' . $path);
    
    if (!file_exists($filePath)) {
        abort(404, 'File not found: ' . $path);
    }
    
    return response()->file($filePath);
})->where('path', '.*')->name('storage.local')->middleware('web');
