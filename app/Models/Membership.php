<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Membership extends Model
{
    protected $table = 'memberships';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'user_id',
        'membership_number',
        'status',
        'start_date',
        'expiry_date',
        'renewal_due_date',
        'current_plan_id',
        'approval_status',
        'approved_by',
        'approved_at',
        'joined_at',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'current_plan_id' => 'integer',
            'start_date' => 'date',
            'expiry_date' => 'date',
            'renewal_due_date' => 'date',
            'approved_at' => 'datetime',
            'joined_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(MembershipPlan::class, 'current_plan_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(MembershipPayment::class);
    }

    public function history(): HasMany
    {
        return $this->hasMany(MembershipHistory::class);
    }
}
