<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Resident extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'id_number',
        'date_of_birth',
        'gender',
        'occupation',
        'emergency_contact_name',
        'emergency_contact_phone',
        'lease_start_date',
        'lease_end_date',
        'monthly_rent',
        'deposit_amount',
        'status',
        'notes',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'lease_start_date' => 'date',
        'lease_end_date' => 'date',
        'monthly_rent' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
    ];

    protected $appends = ['full_name'];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}
