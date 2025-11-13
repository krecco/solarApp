<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Invoice extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = [
        'invoice_number',
        'user_id',
        'investment_id',
        'repayment_id',
        'type',
        'status',
        'invoice_date',
        'due_date',
        'paid_date',
        'subtotal',
        'tax_amount',
        'total_amount',
        'currency',
        'description',
        'line_items',
        'payment_info',
        'payment_method',
        'payment_reference',
        'notes',
        'pdf_path',
        'rs',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'paid_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'line_items' => 'array',
        'payment_info' => 'array',
    ];

    /**
     * Boot method to auto-generate invoice number
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = static::generateInvoiceNumber();
            }
        });
    }

    /**
     * Generate unique invoice number
     */
    public static function generateInvoiceNumber(): string
    {
        $year = Carbon::now()->format('Y');
        $month = Carbon::now()->format('m');

        // Get last invoice number for this month
        $lastInvoice = static::where('invoice_number', 'like', "INV-{$year}{$month}-%")
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($lastInvoice) {
            // Extract sequence number and increment
            $parts = explode('-', $lastInvoice->invoice_number);
            $sequence = intval($parts[2] ?? 0) + 1;
        } else {
            $sequence = 1;
        }

        return sprintf('INV-%s%s-%04d', $year, $month, $sequence);
    }

    /**
     * Get the user that owns the invoice
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the investment associated with the invoice
     */
    public function investment(): BelongsTo
    {
        return $this->belongsTo(Investment::class);
    }

    /**
     * Get the repayment associated with the invoice
     */
    public function repayment(): BelongsTo
    {
        return $this->belongsTo(InvestmentRepayment::class, 'repayment_id');
    }

    /**
     * Scope to get overdue invoices
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'paid')
            ->where('status', '!=', 'cancelled')
            ->where('due_date', '<', Carbon::now())
            ->where('rs', 0);
    }

    /**
     * Scope to get unpaid invoices
     */
    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', ['draft', 'sent', 'overdue'])
            ->where('rs', 0);
    }

    /**
     * Scope to get paid invoices
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid')
            ->where('rs', 0);
    }

    /**
     * Check if invoice is overdue
     */
    public function isOverdue(): bool
    {
        return $this->status !== 'paid'
            && $this->status !== 'cancelled'
            && $this->due_date < Carbon::now();
    }

    /**
     * Get days overdue
     */
    public function daysOverdue(): int
    {
        if (!$this->isOverdue()) {
            return 0;
        }

        return Carbon::now()->diffInDays($this->due_date);
    }

    /**
     * Mark invoice as paid
     */
    public function markAsPaid(string $paymentMethod = null, string $paymentReference = null): void
    {
        $this->update([
            'status' => 'paid',
            'paid_date' => Carbon::now(),
            'payment_method' => $paymentMethod,
            'payment_reference' => $paymentReference,
        ]);
    }

    /**
     * Mark invoice as sent
     */
    public function markAsSent(): void
    {
        if ($this->status === 'draft') {
            $this->update(['status' => 'sent']);
        }
    }

    /**
     * Update status based on due date
     */
    public function updateStatus(): void
    {
        if ($this->status !== 'paid' && $this->status !== 'cancelled') {
            if ($this->isOverdue()) {
                $this->update(['status' => 'overdue']);
            }
        }
    }
}
