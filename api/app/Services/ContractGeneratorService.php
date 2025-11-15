<?php

namespace App\Services;

use App\Models\Investment;
use App\Models\Language;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * Contract Generator Service
 *
 * NOTE: This service requires the barryvdh/laravel-dompdf package.
 * Install with: composer require barryvdh/laravel-dompdf
 */
class ContractGeneratorService
{
    protected ActivityService $activityService;

    public function __construct(ActivityService $activityService)
    {
        $this->activityService = $activityService;
    }
    /**
     * Generate investment contract PDF
     *
     * @param Investment $investment
     * @param array $options Additional options for contract generation
     * @return string Path to generated PDF
     */
    public function generateInvestmentContract(Investment $investment, array $options = [])
    {
        // Load relationships
        $investment->load(['user', 'solarPlant', 'repayments']);

        // Determine language for document
        $language = $this->determineDocumentLanguage($investment, $options);

        // Prepare contract data
        $data = $this->prepareContractData($investment, $options);
        $data['locale'] = $language;

        // Generate PDF with language-specific template
        $templatePath = "pdfs.{$language}.investment-contract";
        $pdf = Pdf::loadView($templatePath, $data);

        // Set paper size and orientation
        $pdf->setPaper('a4', 'portrait');

        // If save option is true, save to storage
        if (isset($options['save']) && $options['save']) {
            // Generate filename
            $filename = $this->generateFilename($investment);

            // Save PDF to storage
            $path = "contracts/{$investment->id}/{$filename}";
            Storage::disk('private')->put($path, $pdf->output());

            // Update investment with contract path
            $investment->update([
                'contract_path' => $path,
                'contract_status' => 'generated',
                'contract_generated_at' => now(),
            ]);

            // Log activity
            $this->activityService->log('generated investment contract', $investment, null, ['contract_path' => $path]);

            return $path;
        }

        // Return the PDF object for streaming/download
        return $pdf;
    }

    /**
     * Prepare contract data for PDF generation
     *
     * @param Investment $investment
     * @param array $options
     * @return array
     */
    protected function prepareContractData(Investment $investment, array $options = []): array
    {
        return [
            'investment' => $investment,
            'investor' => $investment->user,
            'solarPlant' => $investment->solarPlant,
            'plantOwner' => $investment->solarPlant->owner,
            'repayments' => $investment->repayments,
            'contractDate' => $options['contract_date'] ?? Carbon::now(),
            'contractNumber' => $this->generateContractNumber($investment),
            'companyInfo' => $this->getCompanyInfo(),
            'termsAndConditions' => $this->getTermsAndConditions(),
            'totalRepayments' => $investment->repayments->count(),
            'monthlyPayment' => $investment->repayment_interval === 'monthly'
                ? $investment->total_repayment / $investment->repayments->count()
                : 0,
        ];
    }

    /**
     * Generate contract number
     *
     * @param Investment $investment
     * @return string
     */
    protected function generateContractNumber(Investment $investment): string
    {
        $year = Carbon::now()->format('Y');
        $month = Carbon::now()->format('m');

        // Format: INV-YYYY-MM-{investment_id_first_8_chars}
        return sprintf(
            'INV-%s-%s-%s',
            $year,
            $month,
            strtoupper(substr($investment->id, 0, 8))
        );
    }

    /**
     * Generate filename for contract PDF
     *
     * @param Investment $investment
     * @return string
     */
    protected function generateFilename(Investment $investment): string
    {
        $contractNumber = $this->generateContractNumber($investment);
        $timestamp = Carbon::now()->format('YmdHis');

        return sprintf(
            'contract_%s_%s.pdf',
            $contractNumber,
            $timestamp
        );
    }

    /**
     * Get company information
     *
     * @return array
     */
    protected function getCompanyInfo(): array
    {
        return [
            'name' => config('app.company_name', 'Solar Planning GmbH'),
            'address' => config('app.company_address', 'Musterstraße 123, 12345 Berlin'),
            'phone' => config('app.company_phone', '+49 30 12345678'),
            'email' => config('app.company_email', 'info@solarplanning.de'),
            'registration' => config('app.company_registration', 'HRB 12345'),
            'tax_id' => config('app.company_tax_id', 'DE123456789'),
            'bank_account' => config('app.company_bank_account', 'DE89 3704 0044 0532 0130 00'),
            'bank_name' => config('app.company_bank_name', 'Deutsche Bank'),
        ];
    }

    /**
     * Get terms and conditions
     *
     * @return array
     */
    protected function getTermsAndConditions(): array
    {
        return [
            'This investment contract is governed by German law.',
            'The investor commits to providing the agreed investment amount.',
            'Repayments will be made according to the agreed schedule.',
            'Early termination is subject to the conditions outlined in the general terms.',
            'The solar plant owner commits to maintaining the plant in operational condition.',
            'All parties agree to the terms outlined in this contract.',
        ];
    }

    /**
     * Download contract PDF
     *
     * @param Investment $investment
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function downloadContract(Investment $investment)
    {
        if (!$investment->contract_path) {
            throw new \Exception('Contract has not been generated yet.');
        }

        if (!Storage::disk('private')->exists($investment->contract_path)) {
            throw new \Exception('Contract file not found.');
        }

        $filename = basename($investment->contract_path);

        return Storage::disk('private')->download(
            $investment->contract_path,
            $filename,
            [
                'Content-Type' => 'application/pdf',
            ]
        );
    }

    /**
     * Regenerate contract (if terms have changed)
     *
     * @param Investment $investment
     * @param array $options
     * @return string
     */
    public function regenerateContract(Investment $investment, array $options = []): string
    {
        // Archive old contract if exists
        if ($investment->contract_path && Storage::disk('private')->exists($investment->contract_path)) {
            $archivePath = str_replace('/contracts/', '/contracts/archive/', $investment->contract_path);
            Storage::disk('private')->move($investment->contract_path, $archivePath);

            $this->activityService->log('archived old contract', $investment, null, ['archived_contract' => $archivePath]);
        }

        // Generate new contract
        return $this->generateInvestmentContract($investment, $options);
    }

    /**
     * Generate repayment schedule PDF
     *
     * @param Investment $investment
     * @return mixed PDF object or path to generated PDF
     */
    public function generateRepaymentSchedule(Investment $investment, array $options = [])
    {
        $investment->load(['user', 'solarPlant', 'repayments']);

        // Determine language for document
        $language = $this->determineDocumentLanguage($investment, $options);

        $data = [
            'investment' => $investment,
            'investor' => $investment->user,
            'solarPlant' => $investment->solarPlant,
            'repayments' => $investment->repayments->sortBy('due_date'),
            'generatedAt' => Carbon::now(),
            'locale' => $language,
        ];

        // Generate PDF with language-specific template
        $templatePath = "pdfs.{$language}.repayment-schedule";
        $pdf = Pdf::loadView($templatePath, $data);
        $pdf->setPaper('a4', 'portrait');

        // If save option is true, save to storage
        if (isset($options['save']) && $options['save']) {
            $filename = sprintf(
                'repayment_schedule_%s_%s.pdf',
                strtoupper(substr($investment->id, 0, 8)),
                Carbon::now()->format('YmdHis')
            );

            $path = "schedules/{$investment->id}/{$filename}";
            Storage::disk('private')->put($path, $pdf->output());

            return $path;
        }

        // Return the PDF object for streaming/download
        return $pdf;
    }

    /**
     * Send contract via email
     *
     * @param Investment $investment
     * @return void
     */
    public function sendContractByEmail(Investment $investment): void
    {
        if (!$investment->contract_path) {
            throw new \Exception('Contract must be generated before sending.');
        }

        // TODO: Implement contract email
        // This would use a ContractReadyMail class similar to InvestmentVerifiedMail
        // Mail::to($investment->user->email)
        //     ->send(new ContractReadyMail($investment));
    }

    /**
     * Determine the language to use for the document
     *
     * Priority:
     * 1. Explicit language parameter in options
     * 2. Investment document_language field
     * 3. User's document language preference
     * 4. User's general language preference
     * 5. System default (English)
     *
     * @param Investment $investment
     * @param array $options
     * @return string
     */
    protected function determineDocumentLanguage(Investment $investment, array $options): string
    {
        // 1. Explicit parameter
        if (isset($options['language']) && Language::isValidCode($options['language'])) {
            return $options['language'];
        }

        // 2. Investment document_language field
        if ($investment->document_language && Language::isValidCode($investment->document_language)) {
            return $investment->document_language;
        }

        // 3. User's document language preference
        if ($investment->user) {
            $userDocLang = $investment->user->getDocumentLanguage();
            if (Language::isValidCode($userDocLang)) {
                return $userDocLang;
            }

            // 4. User's general language preference
            $userLang = $investment->user->getLanguage();
            if (Language::isValidCode($userLang)) {
                return $userLang;
            }
        }

        // 5. System default
        return Language::getDefaultCode();
    }

    /**
     * Get all available document languages
     */
    public function getAvailableLanguages(): array
    {
        return Language::active()->ordered()->pluck('code')->toArray();
    }

    /**
     * Check if a template exists for a given language and document type
     */
    public function templateExists(string $documentType, string $language): bool
    {
        $viewPath = "pdfs.{$language}.{$documentType}";
        return view()->exists($viewPath);
    }

    /**
     * Stream PDF to browser
     *
     * @param mixed $pdf PDF object or path to PDF file
     * @return \Illuminate\Http\Response
     */
    public function streamPdf($pdf)
    {
        // If it's a PDF object (Barryvdh\DomPDF\PDF), stream it directly
        if (is_object($pdf) && method_exists($pdf, 'stream')) {
            return $pdf->stream();
        }

        // Otherwise, treat it as a file path
        if (!Storage::disk('private')->exists($pdf)) {
            abort(404, 'PDF file not found');
        }

        return response()->file(
            Storage::disk('private')->path($pdf),
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline',
            ]
        );
    }

    /**
     * Download PDF file
     *
     * @param mixed $pdf PDF object or path to PDF file
     * @param string $filename Filename for download
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadPdf($pdf, string $filename)
    {
        // If it's a PDF object (Barryvdh\DomPDF\PDF), download it directly
        if (is_object($pdf) && method_exists($pdf, 'download')) {
            return $pdf->download($filename);
        }

        // Otherwise, treat it as a file path
        if (!Storage::disk('private')->exists($pdf)) {
            abort(404, 'PDF file not found');
        }

        return Storage::disk('private')->download($pdf, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
