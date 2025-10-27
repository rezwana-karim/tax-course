<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Content extends Model
{
    protected $fillable = [
        'module_id',
        'parent_id',
        'title',
        'body',
        'type',
        'order',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Content::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Content::class, 'parent_id')->orderBy('order');
    }
}
