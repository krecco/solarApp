@extends('pdfs.layouts.base', ['locale' => 'es'])

@section('title', 'Contrato de Inversión')

@section('header-info')
    Plataforma de Inversión en Energía Solar
@endsection

@section('document-reference', 'INV-' . $investment->id)

@section('content')
    <div class="document-title">
        {{ trans('contracts.investment_contract', [], 'es') }}
    </div>
    <div class="document-subtitle">
        {{ trans('contracts.contract_number', [], 'es') }}: INV-{{ $investment->id }}
    </div>

    <div class="document-meta">
        <div class="document-meta-row">
            <span class="meta-label">{{ trans('contracts.contract_date', [], 'es') }}:</span>
            <span class="meta-value">{{ $investment->created_at->format('d/m/Y') }}</span>
        </div>
        <div class="document-meta-row">
            <span class="meta-label">Fecha de inicio:</span>
            <span class="meta-value">{{ $investment->start_date->format('d/m/Y') }}</span>
        </div>
        <div class="document-meta-row">
            <span class="meta-label">{{ trans('contracts.effective_date', [], 'es') }}:</span>
            <span class="meta-value">{{ $investment->end_date->format('d/m/Y') }}</span>
        </div>
        @if($investment->verified)
        <div class="document-meta-row">
            <span class="meta-label">{{ trans('common.status', [], 'es') }}:</span>
            <span class="meta-value">{{ trans('contracts.active', [], 'es') }}</span>
        </div>
        @endif
    </div>

    <div class="section">
        <h2 class="section-title">{{ trans('contracts.contracting_parties', [], 'es') }}</h2>
        <div class="two-columns">
            <div class="column-50">
                <div class="party-info">
                    <h3>{{ trans('contracts.party_a', [], 'es') }} - Inversor</h3>
                    <p><strong>{{ trans('common.name', [], 'es') }}:</strong> {{ $investment->user->full_name_with_titles ?? $investment->user->name }}</p>
                    <p><strong>{{ trans('common.email', [], 'es') }}:</strong> {{ $investment->user->email }}</p>
                    @if($investment->user->phone_nr)
                    <p><strong>{{ trans('common.phone', [], 'es') }}:</strong> {{ $investment->user->phone_nr }}</p>
                    @endif
                    @if($investment->user->customer_no)
                    <p><strong>N° Cliente:</strong> {{ $investment->user->customer_no }}</p>
                    @endif
                </div>
            </div>
            <div class="column-50">
                <div class="party-info">
                    <h3>{{ trans('contracts.party_b', [], 'es') }} - Plataforma</h3>
                    <p><strong>{{ trans('common.name', [], 'es') }}:</strong> {{ config('app.name') }}</p>
                    <p><strong>Proyecto:</strong> {{ $investment->solarPlant->plant_name ?? 'N/A' }}</p>
                    @if($investment->solarPlant)
                    <p><strong>Ubicación:</strong> {{ $investment->solarPlant->location ?? 'N/A' }}</p>
                    <p><strong>Capacidad:</strong> {{ $investment->solarPlant->capacity_kwp ?? 'N/A' }} kWp</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="section">
        <h2 class="section-title">{{ trans('contracts.investment_details', [], 'es') }}</h2>
        <div class="info-box">
            <table>
                <tr>
                    <td style="width: 60%;"><strong>{{ trans('contracts.investment_amount', [], 'es') }}:</strong></td>
                    <td class="text-right"><span class="amount-large">{{ number_format($investment->amount, 2, ',', '.') }} €</span></td>
                </tr>
                <tr>
                    <td><strong>{{ trans('contracts.investment_term', [], 'es') }}:</strong></td>
                    <td class="text-right">{{ $investment->duration_months }} meses</td>
                </tr>
                <tr>
                    <td><strong>{{ trans('contracts.interest_rate', [], 'es') }}:</strong></td>
                    <td class="text-right">{{ number_format($investment->interest_rate, 2, ',', '.') }}% anual</td>
                </tr>
                <tr>
                    <td><strong>Intereses totales:</strong></td>
                    <td class="text-right"><span class="amount">{{ number_format($investment->total_interest, 2, ',', '.') }} €</span></td>
                </tr>
                <tr class="table-total">
                    <td><strong>{{ trans('contracts.total_return', [], 'es') }}:</strong></td>
                    <td class="text-right"><span class="amount-large">{{ number_format($investment->total_repayment, 2, ',', '.') }} €</span></td>
                </tr>
            </table>
        </div>
        <p class="text-muted text-small mb-10">
            Intervalo de reembolso: {{ ucfirst($investment->repayment_interval ?? 'mensual') }}
        </p>
    </div>

    <div class="section no-break">
        <h2 class="section-title">{{ trans('contracts.investment_terms', [], 'es') }}</h2>
        <div class="section-content">
            <p class="mb-10"><strong>1. Propósito de la Inversión</strong></p>
            <p class="mb-20">
                El Inversor acepta invertir el monto especificado en el proyecto de energía solar gestionado por {{ config('app.name') }}.
                Esta inversión se utilizará para el desarrollo, instalación y operación de sistemas solares fotovoltaicos.
            </p>
            <p class="mb-10"><strong>2. Rendimientos y Pagos</strong></p>
            <p class="mb-20">
                La Plataforma se compromete a pagar al Inversor un rendimiento calculado al {{ number_format($investment->interest_rate, 2, ',', '.') }}%
                anual sobre el monto invertido. Los pagos se realizarán según el calendario de reembolso {{ $investment->repayment_interval ?? 'mensual' }}.
            </p>
            <p class="mb-10"><strong>3. Período de Inversión</strong></p>
            <p class="mb-20">
                El período de inversión comienza el {{ $investment->start_date->format('d/m/Y') }} y concluye el
                {{ $investment->end_date->format('d/m/Y') }}.
            </p>
            <p class="mb-10"><strong>4. Divulgación de Riesgos</strong></p>
            <p class="mb-20">
                El Inversor reconoce que todas las inversiones conllevan riesgos. El rendimiento real puede variar según
                la producción de energía, las condiciones del mercado y otros factores.
            </p>
            <p class="mb-10"><strong>5. Terminación Anticipada</strong></p>
            <p class="mb-20">
                Cualquiera de las partes puede rescindir este acuerdo anticipadamente proporcionando un aviso por escrito de 30 días.
            </p>
            <p class="mb-10"><strong>6. Ley Aplicable</strong></p>
            <p class="mb-10">
                Este contrato se regirá por las leyes de la jurisdicción donde está registrada la Plataforma.
            </p>
        </div>
    </div>

    <div class="signatures no-break">
        <div class="signature-block">
            <div class="signature-line">
                <div class="signature-label">Firma del Inversor</div>
                <div class="text-bold">{{ $investment->user->full_name_with_titles ?? $investment->user->name }}</div>
            </div>
        </div>
        <div class="signature-block">
            <div class="signature-line">
                <div class="signature-label">Representante de la Plataforma</div>
                <div class="text-bold">{{ config('app.name') }}</div>
            </div>
        </div>
    </div>

    @if($investment->notes)
    <div class="section mt-20">
        <h2 class="section-title">Notas Adicionales</h2>
        <p>{{ $investment->notes }}</p>
    </div>
    @endif
@endsection

@section('footer')
    <div class="footer-row text-small">
        Este documento es un contrato legalmente vinculante. Por favor, guárdelo en un lugar seguro.
    </div>
@endsection
