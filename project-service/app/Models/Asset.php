<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasFactory;

    protected $table = 'assets';

    protected $fillable = [
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'category',
        'tags',
        'version',
        'share_token',
    ];
}