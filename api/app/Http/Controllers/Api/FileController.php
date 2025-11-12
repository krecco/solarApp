<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\FileContainer;
use App\Models\Investment;
use App\Models\SolarPlant;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class FileController extends Controller
{
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
                'filename' => $uploadedFile->getClientOriginalName(),
                'stored_filename' => $filename,
                'path' => $path,
                'mime_type' => $uploadedFile->getMimeType(),
                'size' => $uploadedFile->getSize(),
                'file_type' => $fileType,
                'description' => $request->description,
                'uploaded_by' => $request->user()->id,
                'verified' => false,
            ]);

            // Log activity
            activity()
                ->performedOn($file)
                ->causedBy($request->user())
                ->withProperties([
                    'container_type' => $containerType,
                    'container_id' => $containerId,
                    'file_type' => $fileType,
                    'filename' => $file->filename,
                ])
                ->log('uploaded file');

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
        activity()
            ->performedOn($file)
            ->causedBy($request->user())
            ->log('downloaded file');

        return Storage::disk('private')->download(
            $file->path,
            $file->filename,
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

        // Get file container
        $fileContainer = FileContainer::where('containerable_type', $this->getContainerableType($containerType))
            ->where('containerable_id', $containerId)
            ->first();

        if (!$fileContainer) {
            return response()->json([
                'data' => [],
                'message' => 'No files found',
            ]);
        }

        $files = $fileContainer->files()
            ->with('uploadedBy')
            ->where('rs', 0)
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
        if (!$request->user()->hasRole('admin') && $file->uploaded_by !== $request->user()->id) {
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
            $file->rs = 99;
            $file->save();
            $file->delete();

            // Log activity
            activity()
                ->performedOn($file)
                ->causedBy($request->user())
                ->log('deleted file');

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
            'verified' => true,
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
        ]);

        // Log activity
        activity()
            ->performedOn($file)
            ->causedBy($request->user())
            ->log('verified file');

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
        $containerableType = $this->getContainerableType($containerType);

        return FileContainer::firstOrCreate([
            'containerable_type' => $containerableType,
            'containerable_id' => $containerId,
        ]);
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
        if ($user->hasRole('admin')) {
            return true;
        }

        switch ($containerType) {
            case 'investment':
                $investment = Investment::find($containerId);
                if (!$investment) return false;

                // Investor can access their own investments
                if ($user->id === $investment->user_id) return true;

                // Manager can access investments for their managed plants
                if ($user->hasRole('manager') && $investment->solarPlant->manager_id === $user->id) {
                    return true;
                }
                break;

            case 'solar_plant':
                $plant = SolarPlant::find($containerId);
                if (!$plant) return false;

                // Owner can access their own plants
                if ($user->id === $plant->user_id) return true;

                // Manager can access managed plants
                if ($user->hasRole('manager') && $plant->manager_id === $user->id) {
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
        if ($user->hasRole('admin')) {
            return true;
        }

        // Get the container
        $container = $file->fileContainer;
        if (!$container) return false;

        $containerableType = class_basename($container->containerable_type);
        $containerId = $container->containerable_id;

        return $this->verifyContainerAccess($user, strtolower($containerableType), $containerId);
    }
}
