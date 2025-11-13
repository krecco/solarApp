<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\DocumentRequirementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

/**
 * Admin User Detail Controller
 *
 * Provides comprehensive user information for admin user detail views.
 * Aggregates data from multiple sources: account, files, investments, plants, SEPA, activity.
 */
class AdminUserDetailController extends Controller
{
    protected DocumentRequirementService $documentService;

    public function __construct(DocumentRequirementService $documentService)
    {
        $this->documentService = $documentService;
        $this->middleware('role:admin|manager');
    }

    /**
     * Get comprehensive user overview
     *
     * Returns all tabs data in one endpoint for efficiency
     *
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    public function overview(Request $request, User $user): JsonResponse
    {
        $user->load([
            'roles',
            'addresses',
            'sepaPermissions',
            'investments.solarPlant',
            'solarPlants',
        ]);

        return response()->json([
            'data' => [
                'account' => $this->getAccountInfo($user),
                'documents_summary' => $this->getDocumentsSummary($user),
                'investments_summary' => $this->getInvestmentsSummary($user),
                'plants_summary' => $this->getPlantsSummary($user),
                'billing_summary' => $this->getBillingSummary($user),
                'activity_summary' => $this->getActivitySummary($user),
            ],
        ]);
    }

    /**
     * Get account information tab data
     *
     * @param User $user
     * @return JsonResponse
     */
    public function accountInfo(User $user): JsonResponse
    {
        $user->load(['roles', 'addresses']);

        return response()->json([
            'data' => $this->getAccountInfo($user),
        ]);
    }

    /**
     * Get user documents tab data
     *
     * @param User $user
     * @return JsonResponse
     */
    public function documents(User $user): JsonResponse
    {
        $requirements = $this->documentService->getDocumentRequirementsStatus($user);
        $summary = $this->documentService->getDocumentVerificationSummary($user);

        $container = $user->fileContainer;
        $allFiles = $container ? $container->files()
            ->with(['verifiedBy'])
            ->orderBy('created_at', 'desc')
            ->get()
            : collect();

        return response()->json([
            'data' => [
                'summary' => $summary,
                'requirements' => $requirements,
                'all_files' => $allFiles->map(function ($file) {
                    $docType = $file->getDocumentTypeEnum();
                    return [
                        'id' => $file->id,
                        'name' => $file->name,
                        'original_name' => $file->original_name,
                        'document_type' => $file->document_type,
                        'document_type_label' => $docType?->label(),
                        'is_required' => $file->is_required,
                        'is_verified' => $file->is_verified,
                        'verified_at' => $file->verified_at,
                        'verified_by' => $file->verifiedBy ? [
                            'id' => $file->verifiedBy->id,
                            'name' => $file->verifiedBy->name,
                        ] : null,
                        'rejection_reason' => $file->rejection_reason,
                        'size' => $file->formatted_size,
                        'uploaded_at' => $file->created_at,
                    ];
                }),
            ],
        ]);
    }

    /**
     * Get user investments tab data
     *
     * @param User $user
     * @return JsonResponse
     */
    public function investments(User $user): JsonResponse
    {
        $investments = $user->investments()
            ->with(['solarPlant', 'repayments'])
            ->orderBy('created_at', 'desc')
            ->get();

        $stats = [
            'total_investments' => $investments->count(),
            'total_invested' => $investments->sum('amount'),
            'total_active' => $investments->where('status', 'active')->count(),
            'total_completed' => $investments->where('status', 'completed')->count(),
            'total_pending' => $investments->where('verified', false)->count(),
        ];

        return response()->json([
            'data' => [
                'statistics' => $stats,
                'investments' => $investments->map(function ($investment) {
                    return [
                        'id' => $investment->id,
                        'amount' => (float) $investment->amount,
                        'duration_months' => $investment->duration_months,
                        'interest_rate' => (float) $investment->interest_rate,
                        'status' => $investment->status,
                        'verified' => $investment->verified,
                        'verified_at' => $investment->verified_at,
                        'start_date' => $investment->start_date,
                        'end_date' => $investment->end_date,
                        'total_repayment' => (float) $investment->total_repayment,
                        'paid_amount' => (float) $investment->paid_amount,
                        'remaining_balance' => $investment->remaining_balance,
                        'completion_percentage' => $investment->completion_percentage,
                        'solar_plant' => $investment->solarPlant ? [
                            'id' => $investment->solarPlant->id,
                            'plant_name' => $investment->solarPlant->plant_name,
                            'location' => $investment->solarPlant->location,
                        ] : null,
                        'repayments_count' => $investment->repayments->count(),
                        'created_at' => $investment->created_at,
                    ];
                }),
            ],
        ]);
    }

    /**
     * Get user power plants tab data
     *
     * @param User $user
     * @return JsonResponse
     */
    public function powerPlants(User $user): JsonResponse
    {
        $plants = $user->solarPlants()
            ->with(['investments', 'extras'])
            ->withCount(['investments'])
            ->orderBy('created_at', 'desc')
            ->get();

        $stats = [
            'total_plants' => $plants->count(),
            'total_capacity' => $plants->sum('capacity_kwp'),
            'active_plants' => $plants->where('status', 'active')->count(),
            'total_investments_received' => $plants->sum(fn($p) => $p->investments->sum('amount')),
        ];

        return response()->json([
            'data' => [
                'statistics' => $stats,
                'plants' => $plants->map(function ($plant) {
                    return [
                        'id' => $plant->id,
                        'plant_name' => $plant->plant_name,
                        'location' => $plant->location,
                        'capacity_kwp' => (float) $plant->capacity_kwp,
                        'status' => $plant->status,
                        'installation_date' => $plant->installation_date,
                        'grid_connection_date' => $plant->grid_connection_date,
                        'investments_count' => $plant->investments_count,
                        'total_invested' => (float) $plant->investments->sum('amount'),
                        'extras_count' => $plant->extras->count(),
                        'created_at' => $plant->created_at,
                    ];
                }),
            ],
        ]);
    }

    /**
     * Get user billing & SEPA tab data
     *
     * @param User $user
     * @return JsonResponse
     */
    public function billing(User $user): JsonResponse
    {
        $user->load(['sepaPermissions', 'addresses']);

        $sepaPermissions = $user->sepaPermissions->map(function ($sepa) {
            return [
                'id' => $sepa->id,
                'iban' => $sepa->iban,
                'bic' => $sepa->bic,
                'account_holder' => $sepa->account_holder,
                'bank_name' => $sepa->bank_name,
                'mandate_reference' => $sepa->mandate_reference,
                'mandate_date' => $sepa->mandate_date,
                'is_active' => $sepa->is_active,
                'created_at' => $sepa->created_at,
            ];
        });

        // Get billing address
        $billingAddress = $user->addresses->firstWhere('type', 'billing')
            ?? $user->addresses->first();

        return response()->json([
            'data' => [
                'sepa_permissions' => $sepaPermissions,
                'billing_address' => $billingAddress ? [
                    'id' => $billingAddress->id,
                    'street' => $billingAddress->street,
                    'house_number' => $billingAddress->house_number,
                    'postal_code' => $billingAddress->postal_code,
                    'city' => $billingAddress->city,
                    'country' => $billingAddress->country,
                    'type' => $billingAddress->type,
                ] : null,
                'has_active_sepa' => $user->sepaPermissions->where('is_active', true)->isNotEmpty(),
            ],
        ]);
    }

    /**
     * Get user activity timeline tab data
     *
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    public function activity(Request $request, User $user): JsonResponse
    {
        $perPage = $request->input('per_page', 20);

        // Get activities caused by or performed on the user
        $activities = Activity::query()
            ->where(function ($query) use ($user) {
                $query->where('causer_id', $user->id)
                    ->where('causer_type', get_class($user))
                    ->orWhere(function ($q) use ($user) {
                        $q->where('subject_id', $user->id)
                            ->where('subject_type', get_class($user));
                    });
            })
            ->with(['causer', 'subject'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'data' => $activities->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'description' => $activity->description,
                    'log_name' => $activity->log_name,
                    'causer' => $activity->causer ? [
                        'id' => $activity->causer->id,
                        'name' => $activity->causer->name ?? 'System',
                    ] : null,
                    'subject_type' => class_basename($activity->subject_type),
                    'subject_id' => $activity->subject_id,
                    'properties' => $activity->properties,
                    'created_at' => $activity->created_at,
                ];
            }),
            'meta' => [
                'current_page' => $activities->currentPage(),
                'last_page' => $activities->lastPage(),
                'per_page' => $activities->perPage(),
                'total' => $activities->total(),
            ],
        ]);
    }

    /**
     * Get account information summary
     *
     * @param User $user
     * @return array
     */
    protected function getAccountInfo(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'title_prefix' => $user->title_prefix,
            'title_suffix' => $user->title_suffix,
            'full_name_with_titles' => $user->full_name_with_titles,
            'phone_nr' => $user->phone_nr,
            'gender' => $user->gender,
            'is_business' => $user->is_business,
            'customer_type' => $user->customer_type,
            'customer_no' => $user->customer_no,
            'status' => $user->status,
            'avatar_url' => $user->avatar_url,
            'preferences' => $user->preferences,
            'email_verified_at' => $user->email_verified_at,
            'user_verified_at' => $user->user_verified_at,
            'user_files_verified' => $user->user_files_verified,
            'last_login_at' => $user->last_login_at,
            'roles' => $user->getRoleNames(),
            'addresses_count' => $user->addresses->count(),
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ];
    }

    /**
     * Get documents summary
     *
     * @param User $user
     * @return array
     */
    protected function getDocumentsSummary(User $user): array
    {
        return $this->documentService->getDocumentVerificationSummary($user);
    }

    /**
     * Get investments summary
     *
     * @param User $user
     * @return array
     */
    protected function getInvestmentsSummary(User $user): array
    {
        $investments = $user->investments;

        return [
            'total_count' => $investments->count(),
            'total_invested' => (float) $investments->sum('amount'),
            'active_count' => $investments->where('status', 'active')->count(),
            'pending_verification' => $investments->where('verified', false)->count(),
        ];
    }

    /**
     * Get power plants summary
     *
     * @param User $user
     * @return array
     */
    protected function getPlantsSummary(User $user): array
    {
        $plants = $user->solarPlants;

        return [
            'total_count' => $plants->count(),
            'total_capacity' => (float) $plants->sum('capacity_kwp'),
            'active_count' => $plants->where('status', 'active')->count(),
        ];
    }

    /**
     * Get billing summary
     *
     * @param User $user
     * @return array
     */
    protected function getBillingSummary(User $user): array
    {
        $sepaPermissions = $user->sepaPermissions;

        return [
            'has_sepa' => $sepaPermissions->isNotEmpty(),
            'active_sepa_count' => $sepaPermissions->where('is_active', true)->count(),
            'total_sepa_count' => $sepaPermissions->count(),
        ];
    }

    /**
     * Get activity summary
     *
     * @param User $user
     * @return array
     */
    protected function getActivitySummary(User $user): array
    {
        $recentCount = Activity::query()
            ->where(function ($query) use ($user) {
                $query->where('causer_id', $user->id)
                    ->where('causer_type', get_class($user))
                    ->orWhere(function ($q) use ($user) {
                        $q->where('subject_id', $user->id)
                            ->where('subject_type', get_class($user));
                    });
            })
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        return [
            'recent_activity_count' => $recentCount,
            'last_activity' => $user->updated_at,
        ];
    }
}
