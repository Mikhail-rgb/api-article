<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'tag',
        'article_ids'
    ];

    protected $casts = [
        'article_ids' => 'array'
    ];

}
