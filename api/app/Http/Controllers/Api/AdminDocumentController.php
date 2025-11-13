<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\DocumentVerificationStatusEmail;
use App\Models\File;
use App\Models\User;
use App\Services\DocumentRequirementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

/**
 * Admin Document Controller
 *
 * Handles admin/manager document verification workflows
 */
class AdminDocumentController extends Controller
{
    protected DocumentRequirementService $documentService;

    public function __construct(DocumentRequirementService $documentService)
    {
        $this->documentService = $documentService;
        $this->middleware('role:admin|manager');
    }

    /**
     * List all pending documents requiring verification
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function pendingDocuments(Request $request): JsonResponse
    {
        $query = File::with(['uploadedBy', 'fileContainer.containable'])
            ->required()
            ->pendingVerification()
            ->orderBy('created_at', 'asc');

        // Optional filter by document type
        if ($request->has('document_type')) {
            $query->ofDocumentType($request->input('document_type'));
        }

        // Optional filter by user
        if ($request->has('user_id')) {
            $query->whereHas('fileContainer', function ($q) use ($request) {
                $q->where('containable_type', User::class)
                    ->where('containable_id', $request->input('user_id'));
            });
        }

        $files = $query->paginate($request->input('per_page', 20));

        return response()->json([
            'data' => $files->map(function ($file) {
                return $this->formatFileForAdmin($file);
            }),
            'meta' => [
                'current_page' => $files->currentPage(),
                'last_page' => $files->lastPage(),
                'per_page' => $files->perPage(),
                'total' => $files->total(),
            ],
        ]);
    }

    /**
     * List all rejected documents
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function rejectedDocuments(Request $request): JsonResponse
    {
        $query = File::with(['uploadedBy', 'fileContainer.containable', 'verifiedBy'])
            ->rejected()
            ->orderBy('updated_at', 'desc');

        // Optional filter by user
        if ($request->has('user_id')) {
            $query->whereHas('fileContainer', function ($q) use ($request) {
                $q->where('containable_type', User::class)
                    ->where('containable_id', $request->input('user_id'));
            });
        }

        $files = $query->paginate($request->input('per_page', 20));

        return response()->json([
            'data' => $files->map(function ($file) {
                return $this->formatFileForAdmin($file);
            }),
            'meta' => [
                'current_page' => $files->currentPage(),
                'last_page' => $files->lastPage(),
                'per_page' => $files->perPage(),
                'total' => $files->total(),
            ],
        ]);
    }

    /**
     * Verify a document
     *
     * @param Request $request
     * @param File $file
     * @return JsonResponse
     */
    public function verify(Request $request, File $file): JsonResponse
    {
        if ($file->is_verified) {
            return response()->json([
                'error' => 'Already Verified',
                'message' => 'This document is already verified.',
            ], 400);
        }

        $file->update([
            'is_verified' => true,
            'verified_at' => now(),
            'verified_by_id' => $request->user()->id,
            'rejection_reason' => null, // Clear any previous rejection
        ]);

        // Log activity
        activity()
            ->performedOn($file)
            ->causedBy($request->user())
            ->withProperties([
                'document_type' => $file->document_type,
                'file_name' => $file->original_name,
            ])
            ->log('verified customer document');

        // Check if user now has all required documents
        $container = $file->fileContainer;
        if ($container && $container->containable_type === User::class) {
            $user = $container->containable;
            if ($user && $user->hasAllRequiredDocuments()) {
                // Update user verification status
                if (!$user->user_files_verified) {
                    $user->update([
                        'user_files_verified' => true,
                        'user_verified_at' => now(),
                    ]);

                    activity()
                        ->performedOn($user)
                        ->causedBy($request->user())
                        ->log('completed document verification');
                }
            }
        }

        // Send verification email
        $container = $file->fileContainer;
        if ($container && $container->containable_type === User::class) {
            $user = $container->containable;
            if ($user) {
                try {
                    $locale = $user->preferences['language'] ?? 'en';
                    Mail::to($user->email)->send(new DocumentVerificationStatusEmail(
                        $user,
                        $file,
                        'verified',
                        null,
                        $locale
                    ));
                } catch (\Exception $e) {
                    \Log::warning('Document verification email failed: ' . $e->getMessage());
                }
            }
        }

        return response()->json([
            'data' => $this->formatFileForAdmin($file->fresh(['verifiedBy', 'uploadedBy', 'fileContainer.containable'])),
            'meta' => [
                'status' => 'success',
                'message' => 'Document verified successfully.',
            ],
        ]);
    }

    /**
     * Reject a document with reason
     *
     * @param Request $request
     * @param File $file
     * @return JsonResponse
     */
    public function reject(Request $request, File $file): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|min:10|max:500',
        ]);

        if ($file->is_verified) {
            return response()->json([
                'error' => 'Already Verified',
                'message' => 'Cannot reject a verified document. Please contact technical support.',
            ], 400);
        }

        $file->update([
            'is_verified' => false,
            'verified_at' => null,
            'verified_by_id' => $request->user()->id,
            'rejection_reason' => $validated['reason'],
        ]);

        // Log activity
        activity()
            ->performedOn($file)
            ->causedBy($request->user())
            ->withProperties([
                'document_type' => $file->document_type,
                'file_name' => $file->original_name,
                'rejection_reason' => $validated['reason'],
            ])
            ->log('rejected customer document');

        // Send rejection email
        $container = $file->fileContainer;
        if ($container && $container->containable_type === User::class) {
            $user = $container->containable;
            if ($user) {
                try {
                    $locale = $user->preferences['language'] ?? 'en';
                    Mail::to($user->email)->send(new DocumentVerificationStatusEmail(
                        $user,
                        $file,
                        'rejected',
                        $validated['reason'],
                        $locale
                    ));
                } catch (\Exception $e) {
                    \Log::warning('Document rejection email failed: ' . $e->getMessage());
                }
            }
        }

        return response()->json([
            'data' => $this->formatFileForAdmin($file->fresh(['verifiedBy', 'uploadedBy', 'fileContainer.containable'])),
            'meta' => [
                'status' => 'success',
                'message' => 'Document rejected. User will be notified.',
            ],
        ]);
    }

    /**
     * Get document verification statistics
     *
     * @return JsonResponse
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'pending_count' => File::required()->pendingVerification()->count(),
            'verified_count' => File::required()->verified()->count(),
            'rejected_count' => File::required()->rejected()->count(),
            'users_pending_verification' => $this->getUsersPendingVerification(),
            'users_fully_verified' => $this->getUsersFullyVerified(),
        ];

        return response()->json([
            'data' => $stats,
        ]);
    }

    /**
     * Get verification status for a specific user
     *
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    public function userVerificationStatus(Request $request, User $user): JsonResponse
    {
        $requirements = $this->documentService->getDocumentRequirementsStatus($user);
        $summary = $this->documentService->getDocumentVerificationSummary($user);

        $container = $user->fileContainer;
        $allFiles = $container ? $container->files()
            ->with(['verifiedBy'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($f) => $this->formatFileForAdmin($f))
            : collect();

        return response()->json([
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'customer_type' => $user->customer_type,
                    'is_business' => $user->is_business,
                    'user_files_verified' => $user->user_files_verified,
                    'user_verified_at' => $user->user_verified_at,
                ],
                'requirements' => $requirements,
                'summary' => $summary,
                'all_files' => $allFiles,
            ],
        ]);
    }

    /**
     * Get users with pending document verification
     *
     * @return int
     */
    protected function getUsersPendingVerification(): int
    {
        return User::whereHas('fileContainer.files', function ($query) {
            $query->required()->pendingVerification();
        })->count();
    }

    /**
     * Get users with all documents verified
     *
     * @return int
     */
    protected function getUsersFullyVerified(): int
    {
        return User::where('user_files_verified', true)->count();
    }

    /**
     * Format file for admin view
     *
     * @param File $file
     * @return array
     */
    protected function formatFileForAdmin(File $file): array
    {
        $docType = $file->getDocumentTypeEnum();
        $container = $file->fileContainer;
        $user = null;

        if ($container && $container->containable_type === User::class) {
            $user = $container->containable;
        }

        return [
            'id' => $file->id,
            'name' => $file->name,
            'original_name' => $file->original_name,
            'document_type' => $file->document_type,
            'document_type_label' => $docType?->label(),
            'document_type_description' => $docType?->description(),
            'is_required' => $file->is_required,
            'is_verified' => $file->is_verified,
            'verified_at' => $file->verified_at,
            'verified_by' => $file->verifiedBy ? [
                'id' => $file->verifiedBy->id,
                'name' => $file->verifiedBy->name,
            ] : null,
            'rejection_reason' => $file->rejection_reason,
            'status' => $this->getFileStatus($file),
            'uploaded_by' => $file->uploadedBy ? [
                'id' => $file->uploadedBy->id,
                'name' => $file->uploadedBy->name ?? 'Unknown',
                'email' => $file->uploadedBy->email ?? null,
            ] : null,
            'user' => $user ? [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'customer_type' => $user->customer_type,
            ] : null,
            'size' => $file->size,
            'formatted_size' => $file->formatted_size,
            'mime_type' => $file->mime_type,
            'extension' => $file->extension,
            'uploaded_at' => $file->created_at,
            'updated_at' => $file->updated_at,
        ];
    }

    /**
     * Get file status string
     *
     * @param File $file
     * @return string
     */
    protected function getFileStatus(File $file): string
    {
        if ($file->is_verified) {
            return 'verified';
        }

        if ($file->rejection_reason) {
            return 'rejected';
        }

        return 'pending';
    }
}
