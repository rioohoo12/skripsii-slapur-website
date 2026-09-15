<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $table = 'payments';

    protected $fillable = [
        'student_id',
        'order_id',
        'amount',
        'payment_type',
        'payment_status',
        'payment_method',
        'transaction_status',
        'proof_file',
        'verified_at',
        'verified_by',
        'paid_at',
        'midtrans_transaction_data',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'verified_at' => 'datetime',
        'paid_at' => 'datetime',
        'midtrans_transaction_data' => 'array',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
