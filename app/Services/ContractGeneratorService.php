<?php

namespace App\Services;

use App\Models\Investment;
use App\Models\Language;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class ContractGeneratorService
{
    /**
     * Generate investment contract PDF
     */
    public function generateInvestmentContract(Investment $investment, array $options = [])
    {
        $investment->load(['user', 'solarPlant']);
        $language = $this->determineDocumentLanguage($investment, $options);
        $templatePath = "pdfs.{$language}.investment-contract";

        $pdf = Pdf::loadView($templatePath, [
            'investment' => $investment,
            'locale' => $language,
        ]);

        $pdf->setPaper('a4', 'portrait');
        return $this->handlePdfOutput($pdf, $investment, 'investment-contract', $language, $options);
    }

    /**
     * Generate repayment schedule PDF
     */
    public function generateRepaymentSchedule(Investment $investment, array $options = [])
    {
        $investment->load(['user', 'solarPlant', 'repayments' => function ($query) {
            $query->orderBy('due_date');
        }]);

        $language = $this->determineDocumentLanguage($investment, $options);
        $templatePath = "pdfs.{$language}.repayment-schedule";

        $pdf = Pdf::loadView($templatePath, [
            'investment' => $investment,
            'repayments' => $investment->repayments,
            'locale' => $language,
        ]);

        $pdf->setPaper('a4', 'portrait');
        return $this->handlePdfOutput($pdf, $investment, 'repayment-schedule', $language, $options);
    }

    /**
     * Determine the language to use for the document
     * Priority:
     * 1. Explicit language parameter
     * 2. User's document language preference
     * 3. User's general language preference
     * 4. System default (English)
     */
    protected function determineDocumentLanguage(Investment $investment, array $options): string
    {
        if (isset($options['language']) && Language::isValidCode($options['language'])) {
            return $options['language'];
        }

        if ($investment->user) {
            $userDocLang = $investment->user->getDocumentLanguage();
            if (Language::isValidCode($userDocLang)) {
                return $userDocLang;
            }

            $userLang = $investment->user->getLanguage();
            if (Language::isValidCode($userLang)) {
                return $userLang;
            }
        }

        return Language::getDefaultCode();
    }

    /**
     * Handle PDF output based on options
     */
    protected function handlePdfOutput($pdf, Investment $investment, string $documentType, string $language, array $options)
    {
        $shouldSave = $options['save'] ?? false;
        $returnPdf = $options['return_pdf'] ?? false;

        $filename = $this->generateFilename($investment, $documentType, $language);

        if ($shouldSave) {
            $path = "documents/{$investment->user_id}/investments/{$investment->id}/{$filename}";
            Storage::put($path, $pdf->output());

            if ($returnPdf) {
                return $pdf;
            }
            return $path;
        }

        return $pdf;
    }

    /**
     * Generate filename for PDF document
     */
    protected function generateFilename(Investment $investment, string $documentType, string $language): string
    {
        $date = now()->format('Y-m-d');
        $investmentId = substr($investment->id, 0, 8);
        return "{$documentType}_{$investmentId}_{$language}_{$date}.pdf";
    }

    /**
     * Download PDF directly
     */
    public function downloadPdf($pdf, string $filename)
    {
        return $pdf->download($filename);
    }

    /**
     * Stream PDF to browser
     */
    public function streamPdf($pdf)
    {
        return $pdf->stream();
    }

    /**
     * Get all available document languages
     */
    public function getAvailableLanguages(): array
    {
        return Language::active()->ordered()->pluck('code')->toArray();
    }

    /**
     * Check if template exists
     */
    public function templateExists(string $documentType, string $language): bool
    {
        $viewPath = "pdfs.{$language}.{$documentType}";
        return view()->exists($viewPath);
    }
}
