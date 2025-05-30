<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'facture_id',
        'amount',
        'type',
        'payment_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'datetime:Y-m-d',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $dates = [
        'payment_date',
        'created_at',
        'updated_at',
    ];

    // Constants for payment types
    public const PAYMENT_TYPES = [
        'cash' => 'Cash',
        'bank_transfer' => 'Bank Transfer',
        'credit_card' => 'Credit Card',
        'check' => 'Check',
        'mobile_payment' => 'Mobile Payment',
        'other' => 'Other',
    ];

    /**
     * Get the facture that owns the payment.
     */
    public function facture(): BelongsTo
    {
        return $this->belongsTo(Facture::class);
    }

    /**
     * Scope to filter by payment type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter by payment status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('payment_date', [$startDate, $endDate]);
    }

    /**
     * Scope to get completed payments only
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to get payments for a specific facture
     */
    public function scopeForFacture($query, int $factureId)
    {
        return $query->where('facture_id', $factureId);
    }

    /**
     * Get formatted payment type
     */
    public function getFormattedTypeAttribute(): string
    {
        return self::PAYMENT_TYPES[$this->type] ?? ucfirst($this->type);
    }
}