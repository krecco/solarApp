<?php

namespace App\Services;

use App\Enums\DocumentType;
use App\Models\File;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Document Requirement Service
 *
 * Handles document requirement tracking and verification status for customers.
 * Determines which documents are required based on customer type and business status.
 */
class DocumentRequirementService
{
    /**
     * Get all required documents for a user
     *
     * @param User $user
     * @return Collection<DocumentType>
     */
    public function getRequiredDocuments(User $user): Collection
    {
        $customerType = $user->customer_type ?? 'investor';
        $isBusiness = $user->is_business ?? false;

        $required = DocumentType::getRequiredDocuments($customerType, $isBusiness);

        return collect($required);
    }

    /**
     * Get optional documents for a user
     *
     * @param User $user
     * @return Collection<DocumentType>
     */
    public function getOptionalDocuments(User $user): Collection
    {
        $customerType = $user->customer_type ?? 'investor';
        $isBusiness = $user->is_business ?? false;

        $optional = DocumentType::getOptionalDocuments($customerType, $isBusiness);

        return collect($optional);
    }

    /**
     * Get document requirements with their status for a user
     *
     * Returns an array of document types with their fulfillment status
     *
     * @param User $user
     * @return array
     */
    public function getDocumentRequirementsStatus(User $user): array
    {
        $required = $this->getRequiredDocuments($user);
        $optional = $this->getOptionalDocuments($user);

        // Get user's file container
        $container = $user->fileContainer;

        $status = [];

        // Process required documents
        foreach ($required as $docType) {
            $status[] = $this->buildDocumentStatus($docType, $container, true);
        }

        // Process optional documents
        foreach ($optional as $docType) {
            $status[] = $this->buildDocumentStatus($docType, $container, false);
        }

        return $status;
    }

    /**
     * Build document status information
     *
     * @param DocumentType $docType
     * @param mixed $container
     * @param bool $isRequired
     * @return array
     */
    protected function buildDocumentStatus(DocumentType $docType, $container, bool $isRequired): array
    {
        $files = $container
            ? $container->files()->ofDocumentType($docType)->get()
            : collect();

        $verifiedFile = $files->firstWhere('is_verified', true);
        $pendingFile = $files->where('is_verified', false)->where('rejection_reason', null)->first();
        $rejectedFiles = $files->where('is_verified', false)->whereNotNull('rejection_reason');

        return [
            'document_type' => $docType->value,
            'label' => $docType->label(),
            'description' => $docType->description(),
            'is_required' => $isRequired,
            'status' => $this->determineDocumentStatus($verifiedFile, $pendingFile, $rejectedFiles, $isRequired),
            'uploaded_files' => $files->map(function ($file) {
                return [
                    'id' => $file->id,
                    'name' => $file->name,
                    'original_name' => $file->original_name,
                    'size' => $file->size,
                    'formatted_size' => $file->formatted_size,
                    'uploaded_at' => $file->created_at,
                    'is_verified' => $file->is_verified,
                    'verified_at' => $file->verified_at,
                    'rejection_reason' => $file->rejection_reason,
                ];
            })->values()->all(),
            'verified_file_id' => $verifiedFile?->id,
            'pending_file_id' => $pendingFile?->id,
            'has_rejected_files' => $rejectedFiles->isNotEmpty(),
            'rejection_reasons' => $rejectedFiles->pluck('rejection_reason')->filter()->values()->all(),
        ];
    }

    /**
     * Determine the overall status of a document requirement
     *
     * @param mixed $verifiedFile
     * @param mixed $pendingFile
     * @param Collection $rejectedFiles
     * @param bool $isRequired
     * @return string
     */
    protected function determineDocumentStatus($verifiedFile, $pendingFile, $rejectedFiles, bool $isRequired): string
    {
        if ($verifiedFile) {
            return 'verified';
        }

        if ($pendingFile) {
            return 'pending';
        }

        if ($rejectedFiles->isNotEmpty()) {
            return 'rejected';
        }

        if ($isRequired) {
            return 'required';
        }

        return 'optional';
    }

    /**
     * Check if user has all required documents verified
     *
     * @param User $user
     * @return bool
     */
    public function hasAllRequiredDocuments(User $user): bool
    {
        $required = $this->getRequiredDocuments($user);
        $container = $user->fileContainer;

        if (!$container) {
            return false;
        }

        foreach ($required as $docType) {
            $verifiedFile = $container->files()
                ->ofDocumentType($docType)
                ->verified()
                ->exists();

            if (!$verifiedFile) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get missing required documents for a user
     *
     * @param User $user
     * @return Collection<DocumentType>
     */
    public function getMissingRequiredDocuments(User $user): Collection
    {
        $required = $this->getRequiredDocuments($user);
        $container = $user->fileContainer;

        if (!$container) {
            return $required;
        }

        return $required->filter(function (DocumentType $docType) use ($container) {
            return !$container->files()
                ->ofDocumentType($docType)
                ->verified()
                ->exists();
        });
    }

    /**
     * Get documents pending verification for a user
     *
     * @param User $user
     * @return Collection<File>
     */
    public function getPendingDocuments(User $user): Collection
    {
        $container = $user->fileContainer;

        if (!$container) {
            return collect();
        }

        return $container->files()
            ->required()
            ->pendingVerification()
            ->get();
    }

    /**
     * Get rejected documents for a user
     *
     * @param User $user
     * @return Collection<File>
     */
    public function getRejectedDocuments(User $user): Collection
    {
        $container = $user->fileContainer;

        if (!$container) {
            return collect();
        }

        return $container->files()
            ->required()
            ->rejected()
            ->get();
    }

    /**
     * Calculate document completion percentage
     *
     * @param User $user
     * @return float Percentage from 0 to 100
     */
    public function getDocumentCompletionPercentage(User $user): float
    {
        $required = $this->getRequiredDocuments($user);

        if ($required->isEmpty()) {
            return 100.0;
        }

        $container = $user->fileContainer;
        if (!$container) {
            return 0.0;
        }

        $verified = 0;
        foreach ($required as $docType) {
            if ($container->files()->ofDocumentType($docType)->verified()->exists()) {
                $verified++;
            }
        }

        return round(($verified / $required->count()) * 100, 2);
    }

    /**
     * Get document verification summary for a user
     *
     * @param User $user
     * @return array
     */
    public function getDocumentVerificationSummary(User $user): array
    {
        $required = $this->getRequiredDocuments($user);
        $missing = $this->getMissingRequiredDocuments($user);
        $pending = $this->getPendingDocuments($user);
        $rejected = $this->getRejectedDocuments($user);

        return [
            'total_required' => $required->count(),
            'verified_count' => $required->count() - $missing->count(),
            'missing_count' => $missing->count(),
            'pending_count' => $pending->count(),
            'rejected_count' => $rejected->count(),
            'completion_percentage' => $this->getDocumentCompletionPercentage($user),
            'is_complete' => $this->hasAllRequiredDocuments($user),
            'missing_documents' => $missing->map(fn($dt) => [
                'type' => $dt->value,
                'label' => $dt->label(),
                'description' => $dt->description(),
            ])->values()->all(),
        ];
    }

    /**
     * Check if a specific document type is required for the user
     *
     * @param User $user
     * @param DocumentType $documentType
     * @return bool
     */
    public function isDocumentRequired(User $user, DocumentType $documentType): bool
    {
        $required = $this->getRequiredDocuments($user);
        return $required->contains($documentType);
    }

    /**
     * Get all available document types with metadata
     *
     * @return array
     */
    public function getAllDocumentTypes(): array
    {
        return collect(DocumentType::cases())->map(function (DocumentType $docType) {
            return [
                'value' => $docType->value,
                'label' => $docType->label(),
                'description' => $docType->description(),
                'is_identity' => $docType->isIdentityDocument(),
                'is_address' => $docType->isAddressDocument(),
                'is_business' => $docType->isBusinessDocument(),
                'is_plant_owner' => $docType->isPlantOwnerDocument(),
            ];
        })->values()->all();
    }
}
