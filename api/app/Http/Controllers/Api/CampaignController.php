<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class CampaignController extends Controller
{
    /**
     * Get all campaigns (with filtering)
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => 'nullable|in:referral,seasonal,bonus,promotional',
            'is_active' => 'nullable|boolean',
            'status' => 'nullable|in:upcoming,active,expired,all',
            'search' => 'nullable|string|max:255',
            'per_page' => 'nullable|integer|min:1|max:100',
            'sort_by' => 'nullable|in:name,start_date,end_date,created_at',
            'sort_order' => 'nullable|in:asc,desc',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $query = Campaign::where('rs', 0);

        // Type filter
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Active filter
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Status filter (upcoming, active, expired)
        if ($request->has('status') && $request->status !== 'all') {
            $now = now();
            switch ($request->status) {
                case 'upcoming':
                    $query->where('start_date', '>', $now);
                    break;
                case 'active':
                    $query->where('start_date', '<=', $now)
                        ->where(function ($q) use ($now) {
                            $q->whereNull('end_date')
                                ->orWhere('end_date', '>=', $now);
                        });
                    break;
                case 'expired':
                    $query->where('end_date', '<', $now);
                    break;
            }
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('description', 'ilike', "%{$search}%")
                    ->orWhere('code', 'ilike', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->input('per_page', 15);
        $campaigns = $query->paginate($perPage);

        return response()->json($campaigns);
    }

    /**
     * Get active campaigns (public access)
     */
    public function active(): JsonResponse
    {
        $now = now();

        $campaigns = Campaign::where('rs', 0)
            ->where('is_active', true)
            ->where('start_date', '<=', $now)
            ->where(function ($q) use ($now) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', $now);
            })
            ->where(function ($q) {
                $q->whereNull('max_uses')
                    ->orWhereRaw('current_uses < max_uses');
            })
            ->orderBy('start_date', 'desc')
            ->get();

        return response()->json([
            'data' => $campaigns,
        ]);
    }

    /**
     * Get single campaign
     */
    public function show(Campaign $campaign): JsonResponse
    {
        if ($campaign->rs !== 0) {
            return response()->json([
                'message' => 'Campaign not found',
            ], 404);
        }

        return response()->json([
            'data' => $campaign,
        ]);
    }

    /**
     * Validate campaign code
     */
    public function validateCode(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string',
            'investment_amount' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $campaign = Campaign::where('code', $request->code)
            ->where('rs', 0)
            ->first();

        if (!$campaign) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid campaign code',
            ], 404);
        }

        $isValid = $campaign->isValid();

        if (!$isValid) {
            return response()->json([
                'valid' => false,
                'message' => 'Campaign is not currently active or has reached maximum uses',
            ]);
        }

        // Check investment amount if provided
        if ($request->has('investment_amount')) {
            if (!$campaign->qualifiesForAmount($request->investment_amount)) {
                return response()->json([
                    'valid' => false,
                    'message' => "Minimum investment amount of {$campaign->min_investment_amount} required",
                ]);
            }
        }

        return response()->json([
            'valid' => true,
            'data' => [
                'id' => $campaign->id,
                'name' => $campaign->name,
                'type' => $campaign->type,
                'bonus_amount' => $campaign->bonus_amount,
                'min_investment_amount' => $campaign->min_investment_amount,
                'terms' => $campaign->terms,
            ],
        ]);
    }

    /**
     * Get campaign statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $stats = [
            'total_campaigns' => Campaign::where('rs', 0)->count(),
            'active_campaigns' => Campaign::where('rs', 0)
                ->where('is_active', true)
                ->whereRaw("start_date <= NOW()")
                ->whereRaw("(end_date IS NULL OR end_date >= NOW())")
                ->count(),
            'expired_campaigns' => Campaign::where('rs', 0)
                ->whereNotNull('end_date')
                ->whereRaw("end_date < NOW()")
                ->count(),
            'upcoming_campaigns' => Campaign::where('rs', 0)
                ->whereRaw("start_date > NOW()")
                ->count(),
            'by_type' => Campaign::where('rs', 0)
                ->selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->get()
                ->pluck('count', 'type'),
            'total_uses' => Campaign::where('rs', 0)->sum('current_uses'),
        ];

        return response()->json([
            'data' => $stats,
        ]);
    }

    /**
     * Create new campaign (admin only)
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:referral,seasonal,bonus,promotional',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'bonus_amount' => 'nullable|numeric|min:0',
            'min_investment_amount' => 'nullable|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'is_active' => 'nullable|boolean',
            'conditions' => 'nullable|array',
            'terms' => 'nullable|string',
            'code' => 'nullable|string|max:50|unique:campaigns,code',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $campaign = Campaign::create([
            'name' => $request->name,
            'description' => $request->description,
            'type' => $request->type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'bonus_amount' => $request->input('bonus_amount', 0),
            'min_investment_amount' => $request->min_investment_amount,
            'max_uses' => $request->max_uses,
            'is_active' => $request->input('is_active', true),
            'conditions' => $request->conditions,
            'terms' => $request->terms,
            'code' => $request->code,
        ]);

        // Log activity
        activity()
            ->performedOn($campaign)
            ->causedBy($request->user())
            ->log('created campaign');

        return response()->json([
            'message' => 'Campaign created successfully',
            'data' => $campaign,
        ], 201);
    }

    /**
     * Update campaign (admin only)
     */
    public function update(Request $request, Campaign $campaign): JsonResponse
    {
        if ($campaign->rs !== 0) {
            return response()->json([
                'message' => 'Campaign not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'type' => 'nullable|in:referral,seasonal,bonus,promotional',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'bonus_amount' => 'nullable|numeric|min:0',
            'min_investment_amount' => 'nullable|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'is_active' => 'nullable|boolean',
            'conditions' => 'nullable|array',
            'terms' => 'nullable|string',
            'code' => 'nullable|string|max:50|unique:campaigns,code,' . $campaign->id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $oldData = $campaign->toArray();
        $campaign->update($request->only([
            'name',
            'description',
            'type',
            'start_date',
            'end_date',
            'bonus_amount',
            'min_investment_amount',
            'max_uses',
            'is_active',
            'conditions',
            'terms',
            'code',
        ]));

        // Log activity
        activity()
            ->performedOn($campaign)
            ->causedBy($request->user())
            ->withProperties([
                'old' => $oldData,
                'new' => $campaign->toArray(),
            ])
            ->log('updated campaign');

        return response()->json([
            'message' => 'Campaign updated successfully',
            'data' => $campaign->fresh(),
        ]);
    }

    /**
     * Delete campaign (admin only - soft delete)
     */
    public function destroy(Request $request, Campaign $campaign): JsonResponse
    {
        if ($campaign->rs !== 0) {
            return response()->json([
                'message' => 'Campaign not found',
            ], 404);
        }

        $campaign->rs = 99;
        $campaign->save();
        $campaign->delete();

        // Log activity
        activity()
            ->performedOn($campaign)
            ->causedBy($request->user())
            ->log('deleted campaign');

        return response()->json([
            'message' => 'Campaign deleted successfully',
        ]);
    }

    /**
     * Apply campaign to investment (increment usage counter)
     */
    public function apply(Request $request, Campaign $campaign): JsonResponse
    {
        if ($campaign->rs !== 0) {
            return response()->json([
                'message' => 'Campaign not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'investment_amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        if (!$campaign->isValid()) {
            return response()->json([
                'message' => 'Campaign is not currently active or has reached maximum uses',
            ], 400);
        }

        if (!$campaign->qualifiesForAmount($request->investment_amount)) {
            return response()->json([
                'message' => "Investment amount does not meet minimum requirement of {$campaign->min_investment_amount}",
            ], 400);
        }

        $campaign->incrementUses();

        // Log activity
        activity()
            ->performedOn($campaign)
            ->causedBy($request->user())
            ->withProperties([
                'investment_amount' => $request->investment_amount,
            ])
            ->log('applied campaign to investment');

        return response()->json([
            'message' => 'Campaign applied successfully',
            'data' => [
                'bonus_amount' => $campaign->bonus_amount,
                'remaining_uses' => $campaign->max_uses ? ($campaign->max_uses - $campaign->current_uses) : null,
            ],
        ]);
    }
}
