<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Temporary File Upload Configuration
    |--------------------------------------------------------------------------
    |
    | Livewire handles file uploads by temporarily storing them until the
    | actual form submission. Here we set a 100MB limit so large slide decks
    | and training PDFs can be uploaded smoothly.
    |
    */
    'temporary_file_upload' => [
        'disk' => null,
        'rules' => ['file', 'max:102400'], // 100MB max upload
        'directory' => null,
        'middleware' => null,
        'preview_mimes' => [
            'png', 'gif', 'bmp', 'svg', 'wav', 'mp4',
            'mov', 'avi', 'wmv', 'mp3', 'm4a',
            'jpg', 'jpeg', 'mpga', 'webp', 'wma', 'pdf',
        ],
        'max_upload_time' => 10,
        'cleanup' => true,
    ],
];
