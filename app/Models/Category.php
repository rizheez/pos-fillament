<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description'];
    protected $casts = [
        'slug' => 'string',
        'description' => 'string',
    ];
    public function products()
    {
        return $this->hasMany(Product::class);
    }

}
