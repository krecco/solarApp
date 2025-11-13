<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SolarPlant;
use App\Models\Investment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ProjectController extends Controller
{
    /**
     * Get all projects for public showcase
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'nullable|in:active,funded,operational,completed',
            'min_power' => 'nullable|numeric|min:0',
            'max_power' => 'nullable|numeric|min:0',
            'location' => 'nullable|string',
            'per_page' => 'nullable|integer|min:1|max:50',
            'sort_by' => 'nullable|in:nominal_power,total_cost,created_at',
            'sort_order' => 'nullable|in:asc,desc',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $query = SolarPlant::where('rs', 0)
            ->whereIn('status', ['active', 'funded', 'operational', 'completed']);

        // Status filter
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Power range filters
        if ($request->has('min_power')) {
            $query->where('nominal_power', '>=', $request->min_power);
        }

        if ($request->has('max_power')) {
            $query->where('nominal_power', '<=', $request->max_power);
        }

        // Location filter
        if ($request->has('location')) {
            $query->where('location', 'ilike', "%{$request->location}%");
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->input('per_page', 12);
        $projects = $query->paginate($perPage);

        // Calculate funding progress for each project
        $projects->getCollection()->transform(function ($project) {
            $totalInvested = Investment::where('solar_plant_id', $project->id)
                ->where('rs', 0)
                ->sum('amount');

            $fundingProgress = $project->investment_needed > 0
                ? min(100, ($totalInvested / $project->investment_needed) * 100)
                : 100;

            $investorCount = Investment::where('solar_plant_id', $project->id)
                ->where('rs', 0)
                ->distinct('user_id')
                ->count('user_id');

            return [
                'id' => $project->id,
                'title' => $project->title,
                'description' => $project->description,
                'location' => $project->location,
                'address' => $project->address,
                'nominal_power' => $project->nominal_power,
                'annual_production' => $project->annual_production,
                'total_cost' => $project->total_cost,
                'investment_needed' => $project->investment_needed,
                'kwh_price' => $project->kwh_price,
                'contract_duration_years' => $project->contract_duration_years,
                'interest_rate' => $project->interest_rate,
                'status' => $project->status,
                'start_date' => $project->start_date,
                'operational_date' => $project->operational_date,
                'funding_progress' => round($fundingProgress, 2),
                'total_invested' => $totalInvested,
                'remaining_needed' => max(0, $project->investment_needed - $totalInvested),
                'investor_count' => $investorCount,
                'created_at' => $project->created_at,
            ];
        });

        return response()->json($projects);
    }

    /**
     * Get single project details
     */
    public function show(SolarPlant $project): JsonResponse
    {
        if ($project->rs !== 0) {
            return response()->json([
                'message' => 'Project not found',
            ], 404);
        }

        // Only show public projects
        if (!in_array($project->status, ['active', 'funded', 'operational', 'completed'])) {
            return response()->json([
                'message' => 'Project not available',
            ], 404);
        }

        // Calculate detailed funding information
        $investments = Investment::where('solar_plant_id', $project->id)
            ->where('rs', 0)
            ->get();

        $totalInvested = $investments->sum('amount');
        $investorCount = $investments->unique('user_id')->count();

        $fundingProgress = $project->investment_needed > 0
            ? min(100, ($totalInvested / $project->investment_needed) * 100)
            : 100;

        // Calculate expected returns
        $averageInvestment = $investorCount > 0 ? $totalInvested / $investorCount : 0;
        $monthlyInvestments = $investments->where('duration_months', '>', 0);
        $averageDuration = $monthlyInvestments->count() > 0
            ? $monthlyInvestments->avg('duration_months')
            : 0;

        $projectData = [
            'id' => $project->id,
            'title' => $project->title,
            'description' => $project->description,
            'location' => $project->location,
            'address' => $project->address,
            'nominal_power' => $project->nominal_power,
            'annual_production' => $project->annual_production,
            'consumption' => $project->consumption,
            'total_cost' => $project->total_cost,
            'investment_needed' => $project->investment_needed,
            'kwh_price' => $project->kwh_price,
            'contract_duration_years' => $project->contract_duration_years,
            'interest_rate' => $project->interest_rate,
            'status' => $project->status,
            'start_date' => $project->start_date,
            'operational_date' => $project->operational_date,
            'end_date' => $project->end_date,
            'funding' => [
                'total_invested' => $totalInvested,
                'remaining_needed' => max(0, $project->investment_needed - $totalInvested),
                'funding_progress' => round($fundingProgress, 2),
                'investor_count' => $investorCount,
                'average_investment' => round($averageInvestment, 2),
                'average_duration_months' => round($averageDuration, 0),
            ],
            'technical' => [
                'nominal_power_kwp' => $project->nominal_power,
                'annual_production_kwh' => $project->annual_production,
                'consumption_kwh' => $project->consumption,
                'efficiency' => $project->nominal_power > 0
                    ? round(($project->annual_production / ($project->nominal_power * 8760)) * 100, 2)
                    : 0,
            ],
            'created_at' => $project->created_at,
            'updated_at' => $project->updated_at,
        ];

        return response()->json([
            'data' => $projectData,
        ]);
    }

    /**
     * Get project statistics
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total_projects' => SolarPlant::where('rs', 0)
                ->whereIn('status', ['active', 'funded', 'operational', 'completed'])
                ->count(),
            'active_projects' => SolarPlant::where('rs', 0)
                ->where('status', 'active')
                ->count(),
            'operational_projects' => SolarPlant::where('rs', 0)
                ->where('status', 'operational')
                ->count(),
            'completed_projects' => SolarPlant::where('rs', 0)
                ->where('status', 'completed')
                ->count(),
            'total_capacity_kwp' => SolarPlant::where('rs', 0)
                ->whereIn('status', ['active', 'funded', 'operational', 'completed'])
                ->sum('nominal_power'),
            'total_annual_production_kwh' => SolarPlant::where('rs', 0)
                ->whereIn('status', ['operational', 'completed'])
                ->sum('annual_production'),
            'total_investors' => Investment::whereHas('solarPlant', function ($query) {
                $query->where('rs', 0)
                    ->whereIn('status', ['active', 'funded', 'operational', 'completed']);
            })
                ->where('rs', 0)
                ->distinct('user_id')
                ->count('user_id'),
            'total_invested' => Investment::whereHas('solarPlant', function ($query) {
                $query->where('rs', 0)
                    ->whereIn('status', ['active', 'funded', 'operational', 'completed']);
            })
                ->where('rs', 0)
                ->sum('amount'),
        ];

        return response()->json([
            'data' => $stats,
        ]);
    }

    /**
     * Get featured/highlighted projects
     */
    public function featured(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 6);

        $projects = SolarPlant::where('rs', 0)
            ->whereIn('status', ['active', 'funded', 'operational'])
            ->orderBy('nominal_power', 'desc')
            ->limit($limit)
            ->get();

        // Add funding info
        $projectsData = $projects->map(function ($project) {
            $totalInvested = Investment::where('solar_plant_id', $project->id)
                ->where('rs', 0)
                ->sum('amount');

            $fundingProgress = $project->investment_needed > 0
                ? min(100, ($totalInvested / $project->investment_needed) * 100)
                : 100;

            return [
                'id' => $project->id,
                'title' => $project->title,
                'description' => $project->description,
                'location' => $project->location,
                'nominal_power' => $project->nominal_power,
                'annual_production' => $project->annual_production,
                'investment_needed' => $project->investment_needed,
                'status' => $project->status,
                'funding_progress' => round($fundingProgress, 2),
                'total_invested' => $totalInvested,
            ];
        });

        return response()->json([
            'data' => $projectsData,
        ]);
    }

    /**
     * Get projects by location/region
     */
    public function byLocation(): JsonResponse
    {
        $projectsByLocation = SolarPlant::where('rs', 0)
            ->whereIn('status', ['active', 'funded', 'operational', 'completed'])
            ->whereNotNull('location')
            ->selectRaw('location, COUNT(*) as project_count, SUM(nominal_power) as total_power')
            ->groupBy('location')
            ->orderBy('project_count', 'desc')
            ->get();

        return response()->json([
            'data' => $projectsByLocation,
        ]);
    }

    /**
     * Get investment opportunities (projects needing funding)
     */
    public function opportunities(Request $request): JsonResponse
    {
        $projects = SolarPlant::where('rs', 0)
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->limit($request->input('limit', 10))
            ->get();

        $opportunitiesData = $projects->map(function ($project) {
            $totalInvested = Investment::where('solar_plant_id', $project->id)
                ->where('rs', 0)
                ->sum('amount');

            $fundingProgress = $project->investment_needed > 0
                ? min(100, ($totalInvested / $project->investment_needed) * 100)
                : 100;

            $remainingNeeded = max(0, $project->investment_needed - $totalInvested);

            // Only include projects that still need funding
            if ($remainingNeeded > 0) {
                return [
                    'id' => $project->id,
                    'title' => $project->title,
                    'description' => $project->description,
                    'location' => $project->location,
                    'nominal_power' => $project->nominal_power,
                    'interest_rate' => $project->interest_rate,
                    'contract_duration_years' => $project->contract_duration_years,
                    'investment_needed' => $project->investment_needed,
                    'funding_progress' => round($fundingProgress, 2),
                    'remaining_needed' => $remainingNeeded,
                    'created_at' => $project->created_at,
                ];
            }

            return null;
        })->filter()->values();

        return response()->json([
            'data' => $opportunitiesData,
        ]);
    }
}
