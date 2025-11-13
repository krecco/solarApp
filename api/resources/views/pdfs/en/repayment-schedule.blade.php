@extends('pdfs.layouts.base', ['locale' => 'en'])

@section('title', 'Repayment Schedule')

@section('header-info')
    Investment Repayment Overview
@endsection

@section('document-reference', 'REP-' . $investment->id)

@section('content')
    <div class="document-title">
        {{ trans('contracts.repayment_schedule', [], 'en') }}
    </div>
    <div class="document-subtitle">
        Investment Contract: INV-{{ $investment->id }}
    </div>

    <!-- Investment Summary -->
    <div class="document-meta">
        <div class="document-meta-row">
            <span class="meta-label">Investor:</span>
            <span class="meta-value">{{ $investment->user->full_name_with_titles ?? $investment->user->name }}</span>
        </div>
        <div class="document-meta-row">
            <span class="meta-label">Total Investment:</span>
            <span class="meta-value amount">€{{ number_format($investment->amount, 2, '.', ',') }}</span>
        </div>
        <div class="document-meta-row">
            <span class="meta-label">Interest Rate:</span>
            <span class="meta-value">{{ number_format($investment->interest_rate, 2) }}% per annum</span>
        </div>
        <div class="document-meta-row">
            <span class="meta-label">Total Return:</span>
            <span class="meta-value amount">€{{ number_format($investment->total_repayment, 2, '.', ',') }}</span>
        </div>
    </div>

    <!-- Repayment Schedule Table -->
    <div class="section">
        <h2 class="section-title">{{ trans('contracts.payment_schedule', [], 'en') }}</h2>

        <table>
            <thead>
                <tr>
                    <th style="width: 8%;">#</th>
                    <th style="width: 20%;">{{ trans('contracts.payment_date', [], 'en') }}</th>
                    <th style="width: 18%;">{{ trans('contracts.principal_amount', [], 'en') }}</th>
                    <th style="width: 18%;">{{ trans('contracts.interest_amount', [], 'en') }}</th>
                    <th style="width: 18%;">{{ trans('contracts.total_payment', [], 'en') }}</th>
                    <th style="width: 18%;">{{ trans('contracts.remaining_balance', [], 'en') }}</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $remainingBalance = $investment->total_repayment;
                @endphp
                @foreach($repayments as $index => $repayment)
                    @php
                        $remainingBalance -= $repayment->total_amount;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $repayment->due_date->format('M d, Y') }}</td>
                        <td class="text-right">€{{ number_format($repayment->principal_amount, 2, '.', ',') }}</td>
                        <td class="text-right">€{{ number_format($repayment->interest_amount, 2, '.', ',') }}</td>
                        <td class="text-right"><strong>€{{ number_format($repayment->total_amount, 2, '.', ',') }}</strong></td>
                        <td class="text-right">€{{ number_format(max(0, $remainingBalance), 2, '.', ',') }}</td>
                    </tr>
                @endforeach
                <tr class="table-total">
                    <td colspan="2" class="text-right"><strong>TOTAL:</strong></td>
                    <td class="text-right"><strong>€{{ number_format($repayments->sum('principal_amount'), 2, '.', ',') }}</strong></td>
                    <td class="text-right"><strong>€{{ number_format($repayments->sum('interest_amount'), 2, '.', ',') }}</strong></td>
                    <td class="text-right"><strong>€{{ number_format($repayments->sum('total_amount'), 2, '.', ',') }}</strong></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Payment Information -->
    <div class="section">
        <h2 class="section-title">Payment Information</h2>
        <div class="info-box">
            <p class="mb-10"><strong>Payment Frequency:</strong> {{ ucfirst($investment->repayment_interval ?? 'monthly') }}</p>
            <p class="mb-10"><strong>Number of Payments:</strong> {{ $repayments->count() }}</p>
            <p class="mb-10"><strong>First Payment:</strong> {{ $repayments->first()->due_date->format('F d, Y') }}</p>
            <p><strong>Final Payment:</strong> {{ $repayments->last()->due_date->format('F d, Y') }}</p>
        </div>
    </div>

    <!-- Important Notes -->
    <div class="section">
        <h2 class="section-title">Important Notes</h2>
        <div class="warning-box">
            <p class="mb-10">• All payments will be made to the bank account registered in your investor profile.</p>
            <p class="mb-10">• Payment dates may vary by ±3 business days depending on banking processing times.</p>
            <p class="mb-10">• In case of early termination, the schedule will be adjusted accordingly.</p>
            <p>• For any questions regarding payments, please contact our support team.</p>
        </div>
    </div>
@endsection
