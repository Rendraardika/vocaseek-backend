<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class AdminInvitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'role',
        'token_hash',
        'expires_at',
        'used_at',
        'cancelled_at',
        'invited_by',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by', 'user_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at instanceof Carbon
            ? $this->expires_at->isPast()
            : false;
    }

    public function isUsed(): bool
    {
        return !is_null($this->used_at);
    }

    public function isCancelled(): bool
    {
        return !is_null($this->cancelled_at);
    }

    public function isPending(): bool
    {
        return !$this->isUsed() && !$this->isCancelled() && !$this->isExpired();
    }

    public function resolveState(): string
    {
        if ($this->isUsed()) {
            return 'used';
        }

        if ($this->isCancelled()) {
            return 'cancelled';
        }

        if ($this->isExpired()) {
            return 'expired';
        }

        return 'pending';
    }
}
