<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClaimApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'claim_id',
        'approved_by',
        'action',
        'previous_status',
        'new_status',
        'comments',
        'approved_amount',
    ];

    protected function casts(): array
    {
        return [
            'approved_amount' => 'decimal:2',
        ];
    }

    public function claim()
    {
        return $this->belongsTo(Claim::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
