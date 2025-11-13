<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WebInfo;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class WebInfoController extends Controller
{
    /**
     * Get all web info items (admin)
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => 'nullable|in:news,info,page,announcement',
            'is_published' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'category' => 'nullable|string',
            'search' => 'nullable|string|max:255',
            'per_page' => 'nullable|integer|min:1|max:100',
            'sort_by' => 'nullable|in:title,published_at,view_count,created_at',
            'sort_order' => 'nullable|in:asc,desc',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $query = WebInfo::with('author:id,firstname,lastname,email')
            ->where('rs', 0);

        // Type filter
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Published filter
        if ($request->has('is_published')) {
            $query->where('is_published', $request->boolean('is_published'));
        }

        // Featured filter
        if ($request->has('is_featured')) {
            $query->where('is_featured', $request->boolean('is_featured'));
        }

        // Category filter
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'ilike', "%{$search}%")
                    ->orWhere('excerpt', 'ilike', "%{$search}%")
                    ->orWhere('content', 'ilike', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->input('per_page', 15);
        $webInfos = $query->paginate($perPage);

        return response()->json($webInfos);
    }

    /**
     * Get published web info items (public)
     */
    public function published(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => 'nullable|in:news,info,page,announcement',
            'category' => 'nullable|string',
            'featured' => 'nullable|boolean',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $query = WebInfo::with('author:id,firstname,lastname')
            ->published();

        // Type filter
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Category filter
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Featured filter
        if ($request->has('featured') && $request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        $query->orderBy('published_at', 'desc');

        $perPage = $request->input('per_page', 10);
        $webInfos = $query->paginate($perPage);

        return response()->json($webInfos);
    }

    /**
     * Get featured items (public)
     */
    public function featured(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 5);

        $webInfos = WebInfo::with('author:id,firstname,lastname')
            ->published()
            ->featured()
            ->orderBy('published_at', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'data' => $webInfos,
        ]);
    }

    /**
     * Get single web info by ID (admin)
     */
    public function show(WebInfo $webInfo): JsonResponse
    {
        if ($webInfo->rs !== 0) {
            return response()->json([
                'message' => 'Content not found',
            ], 404);
        }

        $webInfo->load('author:id,firstname,lastname,email');

        return response()->json([
            'data' => $webInfo,
        ]);
    }

    /**
     * Get single web info by slug (public)
     */
    public function bySlug(string $slug): JsonResponse
    {
        $webInfo = WebInfo::with('author:id,firstname,lastname')
            ->where('slug', $slug)
            ->where('rs', 0)
            ->first();

        if (!$webInfo) {
            return response()->json([
                'message' => 'Content not found',
            ], 404);
        }

        // Only allow viewing published content or require authentication
        if (!$webInfo->is_published) {
            return response()->json([
                'message' => 'Content not published',
            ], 403);
        }

        // Increment view count
        $webInfo->incrementViews();

        return response()->json([
            'data' => $webInfo,
        ]);
    }

    /**
     * Get categories list
     */
    public function categories(): JsonResponse
    {
        $categories = WebInfo::where('rs', 0)
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category');

        return response()->json([
            'data' => $categories,
        ]);
    }

    /**
     * Get statistics
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total_items' => WebInfo::where('rs', 0)->count(),
            'published_items' => WebInfo::where('rs', 0)
                ->where('is_published', true)
                ->count(),
            'draft_items' => WebInfo::where('rs', 0)
                ->where('is_published', false)
                ->count(),
            'featured_items' => WebInfo::where('rs', 0)
                ->where('is_featured', true)
                ->count(),
            'by_type' => WebInfo::where('rs', 0)
                ->selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->get()
                ->pluck('count', 'type'),
            'total_views' => WebInfo::where('rs', 0)->sum('view_count'),
            'most_viewed' => WebInfo::where('rs', 0)
                ->orderBy('view_count', 'desc')
                ->limit(5)
                ->get(['id', 'title', 'slug', 'view_count']),
        ];

        return response()->json([
            'data' => $stats,
        ]);
    }

    /**
     * Create new web info (admin only)
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:web_infos,slug',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'type' => 'required|in:news,info,page,announcement',
            'is_published' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'published_at' => 'nullable|date',
            'featured_image' => 'nullable|string',
            'meta' => 'nullable|array',
            'tags' => 'nullable|array',
            'category' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Generate slug if not provided
        $slug = $request->slug ?? Str::slug($request->title);

        // Ensure slug is unique
        $originalSlug = $slug;
        $counter = 1;
        while (WebInfo::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $webInfo = WebInfo::create([
            'title' => $request->title,
            'slug' => $slug,
            'excerpt' => $request->excerpt,
            'content' => $request->content,
            'type' => $request->type,
            'is_published' => $request->input('is_published', false),
            'is_featured' => $request->input('is_featured', false),
            'published_at' => $request->published_at ?? ($request->input('is_published', false) ? now() : null),
            'author_id' => $request->user()->id,
            'featured_image' => $request->featured_image,
            'meta' => $request->meta,
            'tags' => $request->tags,
            'category' => $request->category,
        ]);

        // Log activity
        activity()
            ->performedOn($webInfo)
            ->causedBy($request->user())
            ->log('created web info');

        return response()->json([
            'message' => 'Content created successfully',
            'data' => $webInfo,
        ], 201);
    }

    /**
     * Update web info (admin only)
     */
    public function update(Request $request, WebInfo $webInfo): JsonResponse
    {
        if ($webInfo->rs !== 0) {
            return response()->json([
                'message' => 'Content not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255|unique:web_infos,slug,' . $webInfo->id,
            'excerpt' => 'nullable|string',
            'content' => 'nullable|string',
            'type' => 'nullable|in:news,info,page,announcement',
            'is_published' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'published_at' => 'nullable|date',
            'featured_image' => 'nullable|string',
            'meta' => 'nullable|array',
            'tags' => 'nullable|array',
            'category' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $oldData = $webInfo->toArray();

        // If title changed, regenerate slug if slug not explicitly provided
        if ($request->has('title') && !$request->has('slug')) {
            $slug = Str::slug($request->title);
            $originalSlug = $slug;
            $counter = 1;
            while (WebInfo::where('slug', $slug)->where('id', '!=', $webInfo->id)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
            $request->merge(['slug' => $slug]);
        }

        // Auto-set published_at if publishing for the first time
        if ($request->has('is_published') && $request->boolean('is_published') && !$webInfo->published_at) {
            $request->merge(['published_at' => now()]);
        }

        $webInfo->update($request->only([
            'title',
            'slug',
            'excerpt',
            'content',
            'type',
            'is_published',
            'is_featured',
            'published_at',
            'featured_image',
            'meta',
            'tags',
            'category',
        ]));

        // Log activity
        activity()
            ->performedOn($webInfo)
            ->causedBy($request->user())
            ->withProperties([
                'old' => $oldData,
                'new' => $webInfo->toArray(),
            ])
            ->log('updated web info');

        return response()->json([
            'message' => 'Content updated successfully',
            'data' => $webInfo->fresh(),
        ]);
    }

    /**
     * Delete web info (admin only - soft delete)
     */
    public function destroy(Request $request, WebInfo $webInfo): JsonResponse
    {
        if ($webInfo->rs !== 0) {
            return response()->json([
                'message' => 'Content not found',
            ], 404);
        }

        $webInfo->rs = 99;
        $webInfo->save();
        $webInfo->delete();

        // Log activity
        activity()
            ->performedOn($webInfo)
            ->causedBy($request->user())
            ->log('deleted web info');

        return response()->json([
            'message' => 'Content deleted successfully',
        ]);
    }

    /**
     * Publish/unpublish web info (admin only)
     */
    public function togglePublish(Request $request, WebInfo $webInfo): JsonResponse
    {
        if ($webInfo->rs !== 0) {
            return response()->json([
                'message' => 'Content not found',
            ], 404);
        }

        $newStatus = !$webInfo->is_published;

        $webInfo->update([
            'is_published' => $newStatus,
            'published_at' => $newStatus && !$webInfo->published_at ? now() : $webInfo->published_at,
        ]);

        // Log activity
        activity()
            ->performedOn($webInfo)
            ->causedBy($request->user())
            ->withProperties([
                'action' => $newStatus ? 'published' : 'unpublished',
            ])
            ->log($newStatus ? 'published web info' : 'unpublished web info');

        return response()->json([
            'message' => $newStatus ? 'Content published successfully' : 'Content unpublished successfully',
            'data' => $webInfo->fresh(),
        ]);
    }
}
