<?php

namespace App\Services;

use App\Models\Investment;
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
    /**
     * Generate investment contract PDF
     *
     * @param Investment $investment
     * @param array $options Additional options for contract generation
     * @return string Path to generated PDF
     */
    public function generateInvestmentContract(Investment $investment, array $options = []): string
    {
        // Load relationships
        $investment->load(['user', 'solarPlant', 'repayments']);

        // Prepare contract data
        $data = $this->prepareContractData($investment, $options);

        // Generate PDF
        $pdf = Pdf::loadView('pdfs.investment-contract', $data);

        // Set paper size and orientation
        $pdf->setPaper('a4', 'portrait');

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
        activity()
            ->performedOn($investment)
            ->withProperties(['contract_path' => $path])
            ->log('generated investment contract');

        return $path;
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

            activity()
                ->performedOn($investment)
                ->withProperties(['archived_contract' => $archivePath])
                ->log('archived old contract');
        }

        // Generate new contract
        return $this->generateInvestmentContract($investment, $options);
    }

    /**
     * Generate repayment schedule PDF
     *
     * @param Investment $investment
     * @return string Path to generated PDF
     */
    public function generateRepaymentSchedule(Investment $investment): string
    {
        $investment->load(['user', 'solarPlant', 'repayments']);

        $data = [
            'investment' => $investment,
            'investor' => $investment->user,
            'solarPlant' => $investment->solarPlant,
            'repayments' => $investment->repayments->sortBy('due_date'),
            'generatedAt' => Carbon::now(),
        ];

        $pdf = Pdf::loadView('pdfs.repayment-schedule', $data);
        $pdf->setPaper('a4', 'portrait');

        $filename = sprintf(
            'repayment_schedule_%s_%s.pdf',
            strtoupper(substr($investment->id, 0, 8)),
            Carbon::now()->format('YmdHis')
        );

        $path = "schedules/{$investment->id}/{$filename}";
        Storage::disk('private')->put($path, $pdf->output());

        return $path;
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
}
