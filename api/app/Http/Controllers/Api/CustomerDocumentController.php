<?php

namespace App\Http\Controllers\Api;

use App\Enums\DocumentType;
use App\Http\Controllers\Controller;
use App\Models\File;
use App\Services\DocumentRequirementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Enum;

/**
 * Customer Document Controller
 *
 * Handles customer document upload, retrieval, and requirement tracking.
 * Focuses on customer-facing document management workflows.
 */
class CustomerDocumentController extends Controller
{
    protected DocumentRequirementService $documentService;

    public function __construct(DocumentRequirementService $documentService)
    {
        $this->documentService = $documentService;
    }

    /**
     * Get document requirements for authenticated user
     *
     * Returns list of required and optional documents with their status
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function requirements(Request $request): JsonResponse
    {
        $user = $request->user();
        $requirements = $this->documentService->getDocumentRequirementsStatus($user);
        $summary = $this->documentService->getDocumentVerificationSummary($user);

        return response()->json([
            'data' => [
                'requirements' => $requirements,
                'summary' => $summary,
            ],
        ]);
    }

    /**
     * Get verification summary for authenticated user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();
        $summary = $this->documentService->getDocumentVerificationSummary($user);

        return response()->json([
            'data' => $summary,
        ]);
    }

    /**
     * Get all uploaded documents for authenticated user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $container = $user->fileContainer;

        if (!$container) {
            return response()->json([
                'data' => [],
                'meta' => [
                    'total' => 0,
                    'message' => 'No documents uploaded yet',
                ],
            ]);
        }

        $files = $container->files()
            ->with(['verifiedBy'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $files->map(function ($file) {
                return $this->formatFileResponse($file);
            }),
            'meta' => [
                'total' => $files->count(),
            ],
        ]);
    }

    /**
     * Upload a document
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function upload(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'file' => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx',
            'document_type' => ['required', new Enum(DocumentType::class)],
            'description' => 'nullable|string|max:500',
        ]);

        // Validate document type
        $documentType = DocumentType::from($validated['document_type']);

        // Check if document is required for this user
        $isRequired = $this->documentService->isDocumentRequired($user, $documentType);

        // Get or create file container
        $container = $user->getOrCreateFileContainer();

        // Upload file
        $uploadedFile = $request->file('file');
        $originalName = $uploadedFile->getClientOriginalName();
        $extension = $uploadedFile->getClientOriginalExtension();
        $size = $uploadedFile->getSize();
        $mimeType = $uploadedFile->getMimeType();

        // Generate unique filename
        $filename = sprintf(
            '%s_%s_%s.%s',
            $user->id,
            $documentType->value,
            time(),
            $extension
        );

        // Store file in private disk
        $path = $uploadedFile->storeAs(
            "user-documents/{$user->id}",
            $filename,
            'private'
        );

        // Create file record
        $file = $container->files()->create([
            'name' => $documentType->label(),
            'original_name' => $originalName,
            'path' => $path,
            'mime_type' => $mimeType,
            'size' => $size,
            'extension' => $extension,
            'uploaded_by_type' => get_class($user),
            'uploaded_by_id' => $user->id,
            'document_type' => $documentType->value,
            'is_required' => $isRequired,
            'is_verified' => false,
        ]);

        // Log activity
        activity()
            ->performedOn($file)
            ->causedBy($user)
            ->withProperties([
                'document_type' => $documentType->value,
                'file_name' => $originalName,
            ])
            ->log('uploaded customer document');

        return response()->json([
            'data' => $this->formatFileResponse($file),
            'meta' => [
                'status' => 'success',
                'message' => 'Document uploaded successfully. It will be verified by our team.',
            ],
        ], 201);
    }

    /**
     * Get a specific document
     *
     * @param Request $request
     * @param File $file
     * @return JsonResponse
     */
    public function show(Request $request, File $file): JsonResponse
    {
        $user = $request->user();

        // Check if user owns this file
        if (!$this->userOwnsFile($user, $file)) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'You do not have permission to view this document.',
            ], 403);
        }

        return response()->json([
            'data' => $this->formatFileResponse($file),
        ]);
    }

    /**
     * Download a document
     *
     * @param Request $request
     * @param File $file
     * @return \Symfony\Component\HttpFoundation\StreamedResponse|JsonResponse
     */
    public function download(Request $request, File $file)
    {
        $user = $request->user();

        // Check if user owns this file
        if (!$this->userOwnsFile($user, $file)) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'You do not have permission to download this document.',
            ], 403);
        }

        // Check if file exists
        if (!Storage::disk('private')->exists($file->path)) {
            return response()->json([
                'error' => 'Not Found',
                'message' => 'File not found on server.',
            ], 404);
        }

        // Log activity
        activity()
            ->performedOn($file)
            ->causedBy($user)
            ->log('downloaded customer document');

        return Storage::disk('private')->download(
            $file->path,
            $file->original_name,
            [
                'Content-Type' => $file->mime_type,
            ]
        );
    }

    /**
     * Delete a document
     *
     * Only allows deletion if not yet verified
     *
     * @param Request $request
     * @param File $file
     * @return JsonResponse
     */
    public function destroy(Request $request, File $file): JsonResponse
    {
        $user = $request->user();

        // Check if user owns this file
        if (!$this->userOwnsFile($user, $file)) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'You do not have permission to delete this document.',
            ], 403);
        }

        // Prevent deletion of verified documents
        if ($file->is_verified) {
            return response()->json([
                'error' => 'Cannot Delete',
                'message' => 'Cannot delete a verified document. Please contact support.',
            ], 400);
        }

        // Delete physical file
        if (Storage::disk('private')->exists($file->path)) {
            Storage::disk('private')->delete($file->path);
        }

        // Log activity before deletion
        activity()
            ->performedOn($file)
            ->causedBy($user)
            ->withProperties([
                'document_type' => $file->document_type,
                'file_name' => $file->original_name,
            ])
            ->log('deleted customer document');

        // Delete database record
        $file->delete();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Document deleted successfully.',
            ],
        ]);
    }

    /**
     * Get available document types with metadata
     *
     * @return JsonResponse
     */
    public function documentTypes(): JsonResponse
    {
        $types = $this->documentService->getAllDocumentTypes();

        return response()->json([
            'data' => $types,
        ]);
    }

    /**
     * Format file for API response
     *
     * @param File $file
     * @return array
     */
    protected function formatFileResponse(File $file): array
    {
        $docType = $file->getDocumentTypeEnum();

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
            'size' => $file->size,
            'formatted_size' => $file->formatted_size,
            'mime_type' => $file->mime_type,
            'extension' => $file->extension,
            'uploaded_at' => $file->created_at,
            'can_delete' => !$file->is_verified,
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

    /**
     * Check if user owns the file
     *
     * @param \App\Models\User $user
     * @param File $file
     * @return bool
     */
    protected function userOwnsFile($user, File $file): bool
    {
        // Check if file belongs to user's container
        $container = $user->fileContainer;

        if (!$container) {
            return false;
        }

        return $file->file_container_id === $container->id;
    }
}
