<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Property extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'type',
        'price',
        'bedrooms',
        'bathrooms',
        'parking_spaces',
        'size_sqm',
        'address',
        'city',
        'province',
        'postal_code',
        'latitude',
        'longitude',
        'status',
        'is_featured',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'size_sqm' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_featured' => 'boolean',
    ];

    public function residents(): HasMany
    {
        return $this->hasMany(Resident::class);
    }
}
