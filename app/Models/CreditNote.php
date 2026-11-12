<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CreditNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'credit_note_number',
        'order_id',
        'order_cancellation_id',
        'original_invoice_number',
        'invoice_date',
        'client_name',
        'client_email',
        'client_phone',
        'client_address',
        'cancelled_items',
        'taxable_amount',
        'gst_amount',
        'total_credit_amount',
        'reason',
        'issued_at',
    ];

    protected function casts(): array
    {
        return [
            'client_address' => 'array',
            'cancelled_items' => 'array',
            'taxable_amount' => 'decimal:2',
            'gst_amount' => 'decimal:2',
            'total_credit_amount' => 'decimal:2',
            'invoice_date' => 'date',
            'issued_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (CreditNote $note): void {
            if (empty($note->credit_note_number)) {
                $year = date('Y');
                $count = static::whereYear('created_at', $year)->count() + 1;
                $formattedNumber = str_pad((string) $count, 5, '0', STR_PAD_LEFT);
                $candidate = "VRZ-CN-{$year}-{$formattedNumber}";

                while (static::where('credit_note_number', $candidate)->exists()) {
                    $count++;
                    $formattedNumber = str_pad((string) $count, 5, '0', STR_PAD_LEFT);
                    $candidate = "VRZ-CN-{$year}-{$formattedNumber}";
                }

                $note->credit_note_number = $candidate;
            }
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function cancellation(): BelongsTo
    {
        return $this->belongsTo(OrderCancellation::class, 'order_cancellation_id');
    }
}
