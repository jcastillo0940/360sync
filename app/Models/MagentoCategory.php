<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MagentoCategory extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'parent_id',
        'level',
        'path',
        'is_active',
        'position',
        'product_count',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'level' => 'integer',
        'position' => 'integer',
        'product_count' => 'integer',
    ];

    public function parent()
    {
        return $this->belongsTo(MagentoCategory::class, 'parent_id', 'category_id');
    }

    public function children()
    {
        return $this->hasMany(MagentoCategory::class, 'parent_id', 'category_id');
    }
}