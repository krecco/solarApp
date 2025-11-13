<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Investment;
use App\Services\ContractGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PdfController extends Controller
{
    protected ContractGeneratorService $contractService;

    public function __construct(ContractGeneratorService $contractService)
    {
        $this->contractService = $contractService;
    }

    /**
     * Generate investment contract PDF
     */
    public function generateInvestmentContract(Request $request, Investment $investment)
    {
        // Authorization check
        $user = $request->user();
        if ($investment->user_id !== $user->id && !$user->hasRole(['admin', 'manager'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'language' => 'sometimes|string|size:2',
            'action' => 'sometimes|in:download,view,save',
        ]);

        $language = $validated['language'] ?? null;
        $action = $validated['action'] ?? 'view';

        try {
            $pdf = $this->contractService->generateInvestmentContract($investment, [
                'language' => $language,
                'save' => ($action === 'save'),
            ]);

            $filename = "investment-contract-{$investment->id}.pdf";

            if ($action === 'save') {
                return response()->json([
                    'message' => 'Contract saved successfully',
                    'path' => $pdf,
                ]);
            }

            if ($action === 'download') {
                return $this->contractService->downloadPdf($pdf, $filename);
            }

            return $this->contractService->streamPdf($pdf);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to generate contract',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate repayment schedule PDF
     */
    public function generateRepaymentSchedule(Request $request, Investment $investment)
    {
        $user = $request->user();
        if ($investment->user_id !== $user->id && !$user->hasRole(['admin', 'manager'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'language' => 'sometimes|string|size:2',
            'action' => 'sometimes|in:download,view,save',
        ]);

        $language = $validated['language'] ?? null;
        $action = $validated['action'] ?? 'view';

        try {
            $pdf = $this->contractService->generateRepaymentSchedule($investment, [
                'language' => $language,
                'save' => ($action === 'save'),
            ]);

            $filename = "repayment-schedule-{$investment->id}.pdf";

            if ($action === 'save') {
                return response()->json([
                    'message' => 'Repayment schedule saved successfully',
                    'path' => $pdf,
                ]);
            }

            if ($action === 'download') {
                return $this->contractService->downloadPdf($pdf, $filename);
            }

            return $this->contractService->streamPdf($pdf);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to generate repayment schedule',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get available document languages
     */
    public function availableLanguages(): JsonResponse
    {
        return response()->json([
            'data' => $this->contractService->getAvailableLanguages(),
        ]);
    }
}
