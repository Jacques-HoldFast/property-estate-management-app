<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceRequest extends Model
{
    use HasFactory;
    protected $fillable = [
        'property_id',
        'resident_id',
        'title',
        'description',
        'category',
        'priority',
        'status',
        'estimated_cost',
        'actual_cost',
        'assigned_to',
        'notes',
        'reported_at',
        'assigned_at',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'reported_at' => 'datetime',
        'assigned_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'yellow',
            'assigned' => 'blue',
            'in_progress' => 'indigo',
            'on_hold' => 'orange',
            'completed' => 'green',
            'cancelled' => 'red',
            default => 'gray',
        };
    }

    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            'low' => 'green',
            'medium' => 'yellow',
            'high' => 'orange',
            'urgent' => 'red',
            default => 'gray',
        };
    }
}
