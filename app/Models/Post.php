<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;



class Post extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'slug', 'body', 'is_published',
        'published_date', 'meta_description',
        'tags', 'keywords'
    ];

    protected $casts = [
        'is_published'      => 'boolean',
        'published_date'    => 'datetime',
        'tags'              => 'array',
        'keywords'          => 'array'
    ];
}
