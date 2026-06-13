<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Report extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'reports';

    protected $fillable = [
        'user_id',
        'title',
        'type',
        'location',
        'images',      // ganti dari image ke images
        'description',
        'status'       // tambahkan status (untuk fitur selesai)
    ];

    // WAJIB untuk array MongoDB
    protected $casts = [
        'images' => 'array',
    ];

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}