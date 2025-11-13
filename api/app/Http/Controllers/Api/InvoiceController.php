<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\InvoiceEmail;
use App\Mail\RepaymentReminderEmail;
use App\Models\Invoice;
use App\Models\InvestmentRepayment;
use App\Models\RepaymentReminder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class InvoiceController extends Controller
{
    /**
     * Get all invoices (with filtering)
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => 'nullable|in:repayment,plant_billing,service,other',
            'status' => 'nullable|in:draft,sent,paid,overdue,cancelled',
            'user_id' => 'nullable|uuid',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'search' => 'nullable|string|max:255',
            'per_page' => 'nullable|integer|min:1|max:100',
            'sort_by' => 'nullable|in:invoice_number,invoice_date,due_date,total_amount,created_at',
            'sort_order' => 'nullable|in:asc,desc',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        $query = Invoice::with(['user', 'investment', 'repayment'])
            ->where('rs', 0);

        // Non-admin users can only see their own invoices
        if (!$user->hasRole('admin') && !$user->hasRole('manager')) {
            $query->where('user_id', $user->id);
        } elseif ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Type filter
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Status filter
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Date range filter
        if ($request->has('date_from')) {
            $query->where('invoice_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('invoice_date', '<=', $request->date_to);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'ilike', "%{$search}%")
                    ->orWhere('description', 'ilike', "%{$search}%")
                    ->orWhere('payment_reference', 'ilike', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'invoice_date');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->input('per_page', 15);
        $invoices = $query->paginate($perPage);

        return response()->json($invoices);
    }

    /**
     * Get single invoice
     */
    public function show(Request $request, Invoice $invoice): JsonResponse
    {
        if ($invoice->rs !== 0) {
            return response()->json([
                'message' => 'Invoice not found',
            ], 404);
        }

        $user = $request->user();

        // Check authorization
        if (!$user->hasRole('admin') && !$user->hasRole('manager') && $invoice->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized to view this invoice',
            ], 403);
        }

        $invoice->load(['user', 'investment', 'repayment']);

        return response()->json([
            'data' => $invoice,
        ]);
    }

    /**
     * Get invoice statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Invoice::where('rs', 0);

        // Filter by user if not admin
        if (!$user->hasRole('admin') && !$user->hasRole('manager')) {
            $query->where('user_id', $user->id);
        }

        $stats = [
            'total_invoices' => (clone $query)->count(),
            'draft_invoices' => (clone $query)->where('status', 'draft')->count(),
            'sent_invoices' => (clone $query)->where('status', 'sent')->count(),
            'paid_invoices' => (clone $query)->where('status', 'paid')->count(),
            'overdue_invoices' => (clone $query)->where('status', 'overdue')->count(),
            'total_amount' => (clone $query)->sum('total_amount'),
            'paid_amount' => (clone $query)->where('status', 'paid')->sum('total_amount'),
            'outstanding_amount' => (clone $query)->whereIn('status', ['draft', 'sent', 'overdue'])->sum('total_amount'),
            'overdue_amount' => (clone $query)->where('status', 'overdue')->sum('total_amount'),
            'by_type' => (clone $query)->selectRaw('type, COUNT(*) as count, SUM(total_amount) as total')
                ->groupBy('type')
                ->get(),
        ];

        return response()->json([
            'data' => $stats,
        ]);
    }

    /**
     * Get overdue invoices
     */
    public function overdue(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Invoice::with(['user', 'investment', 'repayment'])
            ->overdue();

        // Filter by user if not admin
        if (!$user->hasRole('admin') && !$user->hasRole('manager')) {
            $query->where('user_id', $user->id);
        }

        $invoices = $query->orderBy('due_date', 'asc')
            ->paginate($request->input('per_page', 15));

        return response()->json($invoices);
    }

    /**
     * Create invoice (admin only)
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|uuid|exists:users,id',
            'investment_id' => 'nullable|uuid|exists:investments,id',
            'repayment_id' => 'nullable|uuid|exists:investment_repayments,id',
            'type' => 'required|in:repayment,plant_billing,service,other',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'subtotal' => 'required|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'description' => 'nullable|string',
            'line_items' => 'nullable|array',
            'payment_info' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $invoice = Invoice::create([
            'user_id' => $request->user_id,
            'investment_id' => $request->investment_id,
            'repayment_id' => $request->repayment_id,
            'type' => $request->type,
            'status' => 'draft',
            'invoice_date' => $request->invoice_date,
            'due_date' => $request->due_date,
            'subtotal' => $request->subtotal,
            'tax_amount' => $request->input('tax_amount', 0),
            'total_amount' => $request->total_amount,
            'currency' => $request->input('currency', 'EUR'),
            'description' => $request->description,
            'line_items' => $request->line_items,
            'payment_info' => $request->payment_info,
            'notes' => $request->notes,
        ]);

        // Log activity
        activity()
            ->performedOn($invoice)
            ->causedBy($request->user())
            ->log('created invoice');

        return response()->json([
            'message' => 'Invoice created successfully',
            'data' => $invoice->load(['user', 'investment', 'repayment']),
        ], 201);
    }

    /**
     * Generate invoice from repayment (admin only)
     */
    public function generateFromRepayment(Request $request, InvestmentRepayment $repayment): JsonResponse
    {
        // Check if invoice already exists for this repayment
        $existingInvoice = Invoice::where('repayment_id', $repayment->id)
            ->where('rs', 0)
            ->first();

        if ($existingInvoice) {
            return response()->json([
                'message' => 'Invoice already exists for this repayment',
                'data' => $existingInvoice,
            ], 409);
        }

        $investment = $repayment->investment;
        $user = $investment->user;

        $invoice = Invoice::create([
            'user_id' => $user->id,
            'investment_id' => $investment->id,
            'repayment_id' => $repayment->id,
            'type' => 'repayment',
            'status' => 'draft',
            'invoice_date' => Carbon::now(),
            'due_date' => $repayment->due_date,
            'subtotal' => $repayment->amount,
            'tax_amount' => 0,
            'total_amount' => $repayment->amount,
            'currency' => 'EUR',
            'description' => "Repayment for investment {$investment->id}",
            'line_items' => [
                [
                    'description' => "Investment Repayment - Due " . $repayment->due_date->format('Y-m-d'),
                    'quantity' => 1,
                    'unit_price' => $repayment->amount,
                    'total' => $repayment->amount,
                ],
            ],
        ]);

        // Log activity
        activity()
            ->performedOn($invoice)
            ->causedBy($request->user())
            ->withProperties([
                'repayment_id' => $repayment->id,
            ])
            ->log('generated invoice from repayment');

        return response()->json([
            'message' => 'Invoice generated successfully',
            'data' => $invoice->load(['user', 'investment', 'repayment']),
        ], 201);
    }

    /**
     * Update invoice (admin only)
     */
    public function update(Request $request, Invoice $invoice): JsonResponse
    {
        if ($invoice->rs !== 0) {
            return response()->json([
                'message' => 'Invoice not found',
            ], 404);
        }

        // Cannot update paid invoices
        if ($invoice->status === 'paid') {
            return response()->json([
                'message' => 'Cannot update paid invoices',
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'due_date' => 'nullable|date',
            'subtotal' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'total_amount' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'line_items' => 'nullable|array',
            'payment_info' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $oldData = $invoice->toArray();
        $invoice->update($request->only([
            'due_date',
            'subtotal',
            'tax_amount',
            'total_amount',
            'description',
            'line_items',
            'payment_info',
            'notes',
        ]));

        // Log activity
        activity()
            ->performedOn($invoice)
            ->causedBy($request->user())
            ->withProperties([
                'old' => $oldData,
                'new' => $invoice->toArray(),
            ])
            ->log('updated invoice');

        return response()->json([
            'message' => 'Invoice updated successfully',
            'data' => $invoice->fresh(['user', 'investment', 'repayment']),
        ]);
    }

    /**
     * Mark invoice as paid (admin only)
     */
    public function markAsPaid(Request $request, Invoice $invoice): JsonResponse
    {
        if ($invoice->rs !== 0) {
            return response()->json([
                'message' => 'Invoice not found',
            ], 404);
        }

        if ($invoice->status === 'paid') {
            return response()->json([
                'message' => 'Invoice is already paid',
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'payment_method' => 'nullable|string|max:100',
            'payment_reference' => 'nullable|string|max:255',
            'paid_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $invoice->update([
            'status' => 'paid',
            'paid_date' => $request->input('paid_date', Carbon::now()),
            'payment_method' => $request->payment_method,
            'payment_reference' => $request->payment_reference,
        ]);

        // If linked to repayment, mark repayment as paid too
        if ($invoice->repayment) {
            $invoice->repayment->update([
                'status' => 'paid',
                'paid_at' => $invoice->paid_date,
            ]);
        }

        // Log activity
        activity()
            ->performedOn($invoice)
            ->causedBy($request->user())
            ->withProperties([
                'payment_method' => $request->payment_method,
                'payment_reference' => $request->payment_reference,
            ])
            ->log('marked invoice as paid');

        return response()->json([
            'message' => 'Invoice marked as paid successfully',
            'data' => $invoice->fresh(['user', 'investment', 'repayment']),
        ]);
    }

    /**
     * Send invoice (admin only)
     */
    public function send(Request $request, Invoice $invoice): JsonResponse
    {
        if ($invoice->rs !== 0) {
            return response()->json([
                'message' => 'Invoice not found',
            ], 404);
        }

        if ($invoice->status === 'paid') {
            return response()->json([
                'message' => 'Cannot send paid invoices',
            ], 400);
        }

        $invoice->markAsSent();

        // Send invoice email to user
        $user = $invoice->user;
        if ($user) {
            try {
                $locale = $user->preferences['language'] ?? 'en';
                Mail::to($user->email)->send(new InvoiceEmail($user, $invoice, $locale));
            } catch (\Exception $e) {
                \Log::warning('Invoice email failed: ' . $e->getMessage());
            }
        }

        // Log activity
        activity()
            ->performedOn($invoice)
            ->causedBy($request->user())
            ->log('sent invoice');

        return response()->json([
            'message' => 'Invoice sent successfully',
            'data' => $invoice->fresh(['user', 'investment', 'repayment']),
        ]);
    }

    /**
     * Cancel invoice (admin only)
     */
    public function cancel(Request $request, Invoice $invoice): JsonResponse
    {
        if ($invoice->rs !== 0) {
            return response()->json([
                'message' => 'Invoice not found',
            ], 404);
        }

        if ($invoice->status === 'paid') {
            return response()->json([
                'message' => 'Cannot cancel paid invoices',
            ], 400);
        }

        $invoice->update(['status' => 'cancelled']);

        // Log activity
        activity()
            ->performedOn($invoice)
            ->causedBy($request->user())
            ->log('cancelled invoice');

        return response()->json([
            'message' => 'Invoice cancelled successfully',
            'data' => $invoice->fresh(['user', 'investment', 'repayment']),
        ]);
    }

    /**
     * Delete invoice (admin only - soft delete)
     */
    public function destroy(Request $request, Invoice $invoice): JsonResponse
    {
        if ($invoice->rs !== 0) {
            return response()->json([
                'message' => 'Invoice not found',
            ], 404);
        }

        if ($invoice->status === 'paid') {
            return response()->json([
                'message' => 'Cannot delete paid invoices',
            ], 400);
        }

        $invoice->rs = 99;
        $invoice->save();
        $invoice->delete();

        // Log activity
        activity()
            ->performedOn($invoice)
            ->causedBy($request->user())
            ->log('deleted invoice');

        return response()->json([
            'message' => 'Invoice deleted successfully',
        ]);
    }

    /**
     * Send repayment reminder (admin only)
     */
    public function sendReminder(Request $request, InvestmentRepayment $repayment): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:upcoming,overdue,final_notice',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $repayment->investment->user;
        $dueDate = $repayment->due_date;
        $now = Carbon::now();

        $daysBeforeDue = $dueDate->isFuture() ? $now->diffInDays($dueDate) : null;
        $daysOverdue = $dueDate->isPast() ? $dueDate->diffInDays($now) : null;

        $reminder = RepaymentReminder::create([
            'repayment_id' => $repayment->id,
            'user_id' => $user->id,
            'type' => $request->type,
            'days_before_due' => $daysBeforeDue,
            'days_overdue' => $daysOverdue,
            'sent_at' => now(),
            'sent_via' => 'email',
            'recipient_email' => $user->email,
            'message_content' => $this->generateReminderMessage($repayment, $request->type),
        ]);

        // Send repayment reminder email
        if ($user) {
            try {
                $locale = $user->preferences['language'] ?? 'en';
                Mail::to($user->email)->send(new RepaymentReminderEmail(
                    $user,
                    $repayment,
                    $request->type,
                    $locale
                ));
            } catch (\Exception $e) {
                \Log::warning('Repayment reminder email failed: ' . $e->getMessage());
            }
        }

        // Log activity
        activity()
            ->performedOn($reminder)
            ->causedBy($request->user())
            ->withProperties([
                'repayment_id' => $repayment->id,
                'type' => $request->type,
            ])
            ->log('sent repayment reminder');

        return response()->json([
            'message' => 'Reminder sent successfully',
            'data' => $reminder,
        ], 201);
    }

    /**
     * Get reminders for repayment
     */
    public function reminders(Request $request, InvestmentRepayment $repayment): JsonResponse
    {
        $reminders = RepaymentReminder::where('repayment_id', $repayment->id)
            ->where('rs', 0)
            ->orderBy('sent_at', 'desc')
            ->get();

        return response()->json([
            'data' => $reminders,
        ]);
    }

    /**
     * Generate reminder message
     */
    private function generateReminderMessage(InvestmentRepayment $repayment, string $type): string
    {
        $amount = $repayment->amount;
        $dueDate = $repayment->due_date->format('Y-m-d');

        switch ($type) {
            case 'upcoming':
                return "Your repayment of {$amount} EUR is due on {$dueDate}. Please ensure payment is made on time.";
            case 'overdue':
                return "Your repayment of {$amount} EUR was due on {$dueDate} and is now overdue. Please make payment as soon as possible.";
            case 'final_notice':
                return "FINAL NOTICE: Your repayment of {$amount} EUR is significantly overdue (due date: {$dueDate}). Immediate payment is required.";
            default:
                return "Repayment reminder for {$amount} EUR due on {$dueDate}.";
        }
    }
}
