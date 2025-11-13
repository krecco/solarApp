@extends('pdfs.layouts.base', ['locale' => 'en'])

@section('title', 'Investment Contract')

@section('header-info')
    Solar Energy Investment Platform
@endsection

@section('document-reference', 'INV-' . $investment->id)

@section('content')
    <div class="document-title">
        {{ trans('contracts.investment_contract', [], 'en') }}
    </div>
    <div class="document-subtitle">
        {{ trans('contracts.contract_number', [], 'en') }}: INV-{{ $investment->id }}
    </div>

    <!-- Contract Metadata -->
    <div class="document-meta">
        <div class="document-meta-row">
            <span class="meta-label">{{ trans('contracts.contract_date', [], 'en') }}:</span>
            <span class="meta-value">{{ $investment->created_at->format('F d, Y') }}</span>
        </div>
        <div class="document-meta-row">
            <span class="meta-label">{{ trans('contracts.start_date', [], 'en') }}:</span>
            <span class="meta-value">{{ $investment->start_date->format('F d, Y') }}</span>
        </div>
        <div class="document-meta-row">
            <span class="meta-label">{{ trans('contracts.effective_date', [], 'en') }}:</span>
            <span class="meta-value">{{ $investment->end_date->format('F d, Y') }}</span>
        </div>
        @if($investment->verified)
        <div class="document-meta-row">
            <span class="meta-label">{{ trans('common.status', [], 'en') }}:</span>
            <span class="meta-value">{{ trans('contracts.active', [], 'en') }}</span>
        </div>
        @endif
    </div>

    <!-- Contracting Parties -->
    <div class="section">
        <h2 class="section-title">{{ trans('contracts.contracting_parties', [], 'en') }}</h2>

        <div class="two-columns">
            <div class="column-50">
                <div class="party-info">
                    <h3>{{ trans('contracts.party_a', [], 'en') }} - Investor</h3>
                    <p><strong>{{ trans('common.name', [], 'en') }}:</strong> {{ $investment->user->full_name_with_titles ?? $investment->user->name }}</p>
                    <p><strong>{{ trans('common.email', [], 'en') }}:</strong> {{ $investment->user->email }}</p>
                    @if($investment->user->phone_nr)
                    <p><strong>{{ trans('common.phone', [], 'en') }}:</strong> {{ $investment->user->phone_nr }}</p>
                    @endif
                    @if($investment->user->customer_no)
                    <p><strong>Customer No:</strong> {{ $investment->user->customer_no }}</p>
                    @endif
                </div>
            </div>

            <div class="column-50">
                <div class="party-info">
                    <h3>{{ trans('contracts.party_b', [], 'en') }} - Platform</h3>
                    <p><strong>{{ trans('common.name', [], 'en') }}:</strong> {{ config('app.name') }}</p>
                    <p><strong>Project:</strong> {{ $investment->solarPlant->plant_name ?? 'N/A' }}</p>
                    @if($investment->solarPlant)
                    <p><strong>Location:</strong> {{ $investment->solarPlant->location ?? 'N/A' }}</p>
                    <p><strong>Capacity:</strong> {{ $investment->solarPlant->capacity_kwp ?? 'N/A' }} kWp</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Investment Details -->
    <div class="section">
        <h2 class="section-title">{{ trans('contracts.investment_details', [], 'en') }}</h2>

        <div class="info-box">
            <table>
                <tr>
                    <td style="width: 60%;"><strong>{{ trans('contracts.investment_amount', [], 'en') }}:</strong></td>
                    <td class="text-right"><span class="amount-large">€{{ number_format($investment->amount, 2, '.', ',') }}</span></td>
                </tr>
                <tr>
                    <td><strong>{{ trans('contracts.investment_term', [], 'en') }}:</strong></td>
                    <td class="text-right">{{ $investment->duration_months }} months</td>
                </tr>
                <tr>
                    <td><strong>{{ trans('contracts.interest_rate', [], 'en') }}:</strong></td>
                    <td class="text-right">{{ number_format($investment->interest_rate, 2) }}% per annum</td>
                </tr>
                <tr>
                    <td><strong>{{ trans('contracts.total_interest', [], 'en') }}:</strong></td>
                    <td class="text-right"><span class="amount">€{{ number_format($investment->total_interest, 2, '.', ',') }}</span></td>
                </tr>
                <tr class="table-total">
                    <td><strong>{{ trans('contracts.total_return', [], 'en') }}:</strong></td>
                    <td class="text-right"><span class="amount-large">€{{ number_format($investment->total_repayment, 2, '.', ',') }}</span></td>
                </tr>
            </table>
        </div>

        <p class="text-muted text-small mb-10">
            Repayment Interval: {{ ucfirst($investment->repayment_interval ?? 'monthly') }}
        </p>
    </div>

    <!-- Investment Terms -->
    <div class="section no-break">
        <h2 class="section-title">{{ trans('contracts.investment_terms', [], 'en') }}</h2>

        <div class="section-content">
            <p class="mb-10"><strong>1. Investment Purpose</strong></p>
            <p class="mb-20">
                The Investor agrees to invest the specified amount in the solar energy project managed by {{ config('app.name') }}.
                This investment will be used for the development, installation, and operation of solar photovoltaic systems.
            </p>

            <p class="mb-10"><strong>2. Returns and Payments</strong></p>
            <p class="mb-20">
                The Platform agrees to pay the Investor a return calculated at {{ number_format($investment->interest_rate, 2) }}% per annum
                on the invested amount. Payments will be made according to the {{ $investment->repayment_interval ?? 'monthly' }} repayment schedule
                attached to this contract.
            </p>

            <p class="mb-10"><strong>3. Investment Period</strong></p>
            <p class="mb-20">
                The investment period commences on {{ $investment->start_date->format('F d, Y') }} and concludes on
                {{ $investment->end_date->format('F d, Y') }}, unless terminated earlier in accordance with these terms.
            </p>

            <p class="mb-10"><strong>4. Risk Disclosure</strong></p>
            <p class="mb-20">
                The Investor acknowledges that all investments carry risk. While {{ config('app.name') }} makes every effort
                to ensure stable returns, the actual performance may vary based on energy production, market conditions,
                and other factors beyond the Platform's control.
            </p>

            <p class="mb-10"><strong>5. Early Termination</strong></p>
            <p class="mb-20">
                Either party may terminate this agreement early by providing 30 days written notice. In such cases,
                the Platform shall return the principal amount plus any accrued interest up to the termination date,
                subject to applicable terms and conditions.
            </p>

            <p class="mb-10"><strong>6. Governing Law</strong></p>
            <p class="mb-10">
                This contract shall be governed by and construed in accordance with the laws of the jurisdiction
                where the Platform is registered. Any disputes arising from this contract shall be resolved through
                arbitration or competent courts.
            </p>
        </div>
    </div>

    <!-- Signatures -->
    <div class="signatures no-break">
        <div class="signature-block">
            <div class="signature-line">
                <div class="signature-label">Investor Signature</div>
                <div class="text-bold">{{ $investment->user->full_name_with_titles ?? $investment->user->name }}</div>
            </div>
        </div>

        <div class="signature-block">
            <div class="signature-line">
                <div class="signature-label">Platform Representative</div>
                <div class="text-bold">{{ config('app.name') }}</div>
            </div>
        </div>
    </div>

    @if($investment->notes)
    <div class="section mt-20">
        <h2 class="section-title">Additional Notes</h2>
        <p>{{ $investment->notes }}</p>
    </div>
    @endif
@endsection

@section('footer')
    <div class="footer-row text-small">
        This document is a legally binding contract. Please keep it in a safe place.
    </div>
@endsection
