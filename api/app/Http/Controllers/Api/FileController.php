<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\FileContainer;
use App\Models\Investment;
use App\Models\SolarPlant;
use App\Services\ActivityService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class FileController extends Controller
{
    protected ActivityService $activityService;

    public function __construct(ActivityService $activityService)
    {
        $this->activityService = $activityService;
    }
    /**
     * Upload file to a file container
     */
    public function upload(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:10240', // 10MB max
            'container_type' => 'required|string|in:investment,solar_plant,user',
            'container_id' => 'required',
            'file_type' => 'required|string|in:contract,invoice,identity,verification,other',
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $uploadedFile = $request->file('file');
        $containerType = $request->container_type;
        $containerId = $request->container_id;
        $fileType = $request->file_type;

        // Verify container exists and user has access
        if (!$this->verifyContainerAccess($request->user(), $containerType, $containerId)) {
            return response()->json([
                'message' => 'Unauthorized to upload to this container',
            ], 403);
        }

        try {
            // Get or create file container
            $fileContainer = $this->getOrCreateFileContainer($containerType, $containerId);

            // Generate unique filename
            $extension = $uploadedFile->getClientOriginalExtension();
            $filename = Str::uuid() . '.' . $extension;

            // Store file
            $path = $uploadedFile->storeAs(
                "uploads/{$containerType}/{$containerId}",
                $filename,
                'private'
            );

            // Create file record
            $file = File::create([
                'file_container_id' => $fileContainer->id,
                'name' => $filename,
                'original_name' => $uploadedFile->getClientOriginalName(),
                'path' => $path,
                'mime_type' => $uploadedFile->getMimeType(),
                'size' => $uploadedFile->getSize(),
                'extension' => $extension,
                'uploaded_by_type' => User::class,
                'uploaded_by_id' => $request->user()->id,
                'document_type' => $fileType,
                'is_verified' => false,
            ]);

            // Log activity
            $this->activityService->log('uploaded file', $file, $request->user(), [
                'container_type' => $containerType,
                'container_id' => $containerId,
                'file_type' => $fileType,
                'filename' => $file->original_name,
            ]);

            return response()->json([
                'message' => 'File uploaded successfully',
                'data' => $file,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'File upload failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download a file
     */
    public function download(Request $request, File $file)
    {
        // Verify user has access to this file
        if (!$this->verifyFileAccess($request->user(), $file)) {
            return response()->json([
                'message' => 'Unauthorized to download this file',
            ], 403);
        }

        if (!Storage::disk('private')->exists($file->path)) {
            return response()->json([
                'message' => 'File not found',
            ], 404);
        }

        // Log activity
        $this->activityService->log('downloaded file', $file, $request->user());

        return Storage::disk('private')->download(
            $file->path,
            $file->original_name,
            [
                'Content-Type' => $file->mime_type,
            ]
        );
    }

    /**
     * List files in a container
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'container_type' => 'required|string|in:investment,solar_plant,user',
            'container_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $containerType = $request->container_type;
        $containerId = $request->container_id;

        // Verify access
        if (!$this->verifyContainerAccess($request->user(), $containerType, $containerId)) {
            return response()->json([
                'message' => 'Unauthorized to view files in this container',
            ], 403);
        }

        // Get the file container ID from the parent model
        $fileContainerId = null;

        switch ($containerType) {
            case 'investment':
                $entity = Investment::find($containerId);
                $fileContainerId = $entity?->file_container_id;
                break;
            case 'solar_plant':
                $entity = SolarPlant::find($containerId);
                $fileContainerId = $entity?->file_container_id;
                break;
            case 'user':
                return response()->json([
                    'message' => 'User file listing not yet implemented',
                ], 501);
        }

        if (!$fileContainerId) {
            return response()->json([
                'data' => [],
                'message' => 'No file container found',
            ]);
        }

        // Get file container
        $fileContainer = FileContainer::find($fileContainerId);

        if (!$fileContainer) {
            return response()->json([
                'data' => [],
                'message' => 'No files found',
            ]);
        }

        $files = $fileContainer->files()
            ->with('uploadedBy')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $files,
        ]);
    }

    /**
     * Delete a file
     */
    public function destroy(Request $request, File $file): JsonResponse
    {
        // Only admin or file uploader can delete
        if (!$request->user()->hasRole('admin', 'sanctum') && $file->uploaded_by !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized to delete this file',
            ], 403);
        }

        try {
            // Delete physical file
            if (Storage::disk('private')->exists($file->path)) {
                Storage::disk('private')->delete($file->path);
            }

            // Soft delete record
            $file->delete();

            // Log activity
            $this->activityService->log('deleted file', $file, $request->user());

            return response()->json([
                'message' => 'File deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'File deletion failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verify a file (admin/manager only)
     */
    public function verify(Request $request, File $file): JsonResponse
    {
        if (!$request->user()->hasRole(['admin', 'manager'])) {
            return response()->json([
                'message' => 'Unauthorized to verify files',
            ], 403);
        }

        $file->update([
            'is_verified' => true,
            'verified_by_id' => $request->user()->id,
            'verified_at' => now(),
        ]);

        // Log activity
        $this->activityService->log('verified file', $file, $request->user());

        return response()->json([
            'message' => 'File verified successfully',
            'data' => $file->load('verifiedBy'),
        ]);
    }

    /**
     * Get or create file container
     */
    protected function getOrCreateFileContainer(string $containerType, string $containerId): FileContainer
    {
        // Get the file container ID from the parent model
        $fileContainerId = null;

        switch ($containerType) {
            case 'investment':
                $entity = Investment::find($containerId);
                $fileContainerId = $entity?->file_container_id;

                // If entity exists but has no file container, create one
                if ($entity && !$fileContainerId) {
                    $fileContainer = FileContainer::create([
                        'name' => "Investment {$containerId} Documents",
                        'type' => 'investment_docs',
                        'description' => "Document container for investment {$containerId}",
                    ]);
                    $entity->file_container_id = $fileContainer->id;
                    $entity->save();
                    return $fileContainer;
                }
                break;

            case 'solar_plant':
                $entity = SolarPlant::find($containerId);
                $fileContainerId = $entity?->file_container_id;

                // If entity exists but has no file container, create one
                if ($entity && !$fileContainerId) {
                    $fileContainer = FileContainer::create([
                        'name' => "Solar Plant {$containerId} Documents",
                        'type' => 'plant_docs',
                        'description' => "Document container for solar plant {$containerId}",
                    ]);
                    $entity->file_container_id = $fileContainer->id;
                    $entity->save();
                    return $fileContainer;
                }
                break;

            case 'user':
                throw new \Exception('User file containers not yet implemented');
        }

        if ($fileContainerId) {
            $fileContainer = FileContainer::find($fileContainerId);
            if ($fileContainer) {
                return $fileContainer;
            }
        }

        throw new \Exception('Unable to get or create file container');
    }

    /**
     * Get containerable type class name
     */
    protected function getContainerableType(string $containerType): string
    {
        $types = [
            'investment' => Investment::class,
            'solar_plant' => SolarPlant::class,
            'user' => \App\Models\User::class,
        ];

        return $types[$containerType] ?? Investment::class;
    }

    /**
     * Verify container access
     */
    protected function verifyContainerAccess($user, string $containerType, string $containerId): bool
    {
        // Admin has access to everything
        if ($user->hasRole('admin', 'sanctum')) {
            return true;
        }

        switch ($containerType) {
            case 'investment':
                $investment = Investment::find($containerId);
                if (!$investment) return false;

                // Investor can access their own investments
                if ($user->id === $investment->user_id) return true;

                // Manager can access investments for their managed plants
                if ($user->hasRole('manager', 'sanctum') && $investment->solarPlant->manager_id === $user->id) {
                    return true;
                }
                break;

            case 'solar_plant':
                $plant = SolarPlant::find($containerId);
                if (!$plant) return false;

                // Owner can access their own plants
                if ($user->id === $plant->user_id) return true;

                // Manager can access managed plants
                if ($user->hasRole('manager', 'sanctum') && $plant->manager_id === $user->id) {
                    return true;
                }
                break;

            case 'user':
                // Users can only access their own files
                return $user->id === (int)$containerId;
        }

        return false;
    }

    /**
     * Verify file access
     */
    protected function verifyFileAccess($user, File $file): bool
    {
        // Admin has access to everything
        if ($user->hasRole('admin', 'sanctum')) {
            return true;
        }

        // Get the container
        $container = $file->fileContainer;
        if (!$container) return false;

        $containerableType = class_basename($container->containerable_type);
        $containerId = $container->containerable_id;

        return $this->verifyContainerAccess($user, strtolower($containerableType), $containerId);
    }

    /**
     * Download all files from a container as a ZIP
     */
    public function bulkDownload(Request $request): JsonResponse|\Symfony\Component\HttpFoundation\StreamedResponse
    {
        $validator = Validator::make($request->all(), [
            'container_type' => 'required|string|in:investment,solar_plant,user',
            'container_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $containerType = $request->container_type;
        $containerId = $request->container_id;

        // Verify access
        if (!$this->verifyContainerAccess($request->user(), $containerType, $containerId)) {
            return response()->json([
                'message' => 'Unauthorized to download files from this container',
            ], 403);
        }

        // Get the file container ID from the parent model
        $fileContainerId = null;

        switch ($containerType) {
            case 'investment':
                $entity = Investment::find($containerId);
                $fileContainerId = $entity?->file_container_id;
                break;
            case 'solar_plant':
                $entity = SolarPlant::find($containerId);
                $fileContainerId = $entity?->file_container_id;
                break;
            case 'user':
                // Users don't have file_container_id, need different approach
                // For now, return error
                return response()->json([
                    'message' => 'User file downloads not yet implemented',
                ], 501);
        }

        if (!$fileContainerId) {
            return response()->json([
                'message' => 'No file container found for this entity',
            ], 404);
        }

        // Get file container
        $fileContainer = FileContainer::find($fileContainerId);

        if (!$fileContainer) {
            return response()->json([
                'message' => 'File container not found',
            ], 404);
        }

        $files = $fileContainer->files()->get();

        if ($files->isEmpty()) {
            return response()->json([
                'message' => 'No files found in this container',
            ], 404);
        }

        // Create a temporary ZIP file
        $zipFileName = "documents_{$containerType}_{$containerId}_" . date('Y-m-d_His') . '.zip';
        $zipPath = storage_path('app/temp/' . $zipFileName);

        // Ensure temp directory exists
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return response()->json([
                'message' => 'Could not create ZIP file',
            ], 500);
        }

        // Add files to ZIP
        foreach ($files as $file) {
            $filePath = storage_path('app/private/' . $file->path);
            if (file_exists($filePath)) {
                $zip->addFile($filePath, $file->original_name);
            }
        }

        $zip->close();

        // Log activity
        $this->activityService->log('bulk downloaded files', $fileContainer, $request->user(), [
            'container_type' => $containerType,
            'container_id' => $containerId,
            'files_count' => $files->count(),
        ]);

        // Return the ZIP file and delete it after sending
        return response()->download($zipPath, $zipFileName, [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }
}
