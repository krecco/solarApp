@extends('pdfs.layouts.base', ['locale' => 'es'])

@section('title', 'Calendario de Reembolsos')

@section('header-info')
    Resumen de Reembolsos de Inversión
@endsection

@section('document-reference', 'REP-' . $investment->id)

@section('content')
    <div class="document-title">{{ trans('contracts.repayment_schedule', [], 'es') }}</div>
    <div class="document-subtitle">Contrato de Inversión: INV-{{ $investment->id }}</div>

    <div class="document-meta">
        <div class="document-meta-row">
            <span class="meta-label">Inversor:</span>
            <span class="meta-value">{{ $investment->user->full_name_with_titles ?? $investment->user->name }}</span>
        </div>
        <div class="document-meta-row">
            <span class="meta-label">Inversión Total:</span>
            <span class="meta-value amount">{{ number_format($investment->amount, 2, ',', '.') }} €</span>
        </div>
        <div class="document-meta-row">
            <span class="meta-label">Tasa de Interés:</span>
            <span class="meta-value">{{ number_format($investment->interest_rate, 2, ',', '.') }}% anual</span>
        </div>
        <div class="document-meta-row">
            <span class="meta-label">Rendimiento Total:</span>
            <span class="meta-value amount">{{ number_format($investment->total_repayment, 2, ',', '.') }} €</span>
        </div>
    </div>

    <div class="section">
        <h2 class="section-title">{{ trans('contracts.payment_schedule', [], 'es') }}</h2>
        <table>
            <thead>
                <tr>
                    <th style="width: 8%;">#</th>
                    <th style="width: 20%;">{{ trans('contracts.payment_date', [], 'es') }}</th>
                    <th style="width: 18%;">{{ trans('contracts.principal_amount', [], 'es') }}</th>
                    <th style="width: 18%;">{{ trans('contracts.interest_amount', [], 'es') }}</th>
                    <th style="width: 18%;">{{ trans('contracts.total_payment', [], 'es') }}</th>
                    <th style="width: 18%;">{{ trans('contracts.remaining_balance', [], 'es') }}</th>
                </tr>
            </thead>
            <tbody>
                @php $remainingBalance = $investment->total_repayment; @endphp
                @foreach($repayments as $index => $repayment)
                    @php $remainingBalance -= $repayment->total_amount; @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $repayment->due_date->format('d/m/Y') }}</td>
                        <td class="text-right">{{ number_format($repayment->principal_amount, 2, ',', '.') }} €</td>
                        <td class="text-right">{{ number_format($repayment->interest_amount, 2, ',', '.') }} €</td>
                        <td class="text-right"><strong>{{ number_format($repayment->total_amount, 2, ',', '.') }} €</strong></td>
                        <td class="text-right">{{ number_format(max(0, $remainingBalance), 2, ',', '.') }} €</td>
                    </tr>
                @endforeach
                <tr class="table-total">
                    <td colspan="2" class="text-right"><strong>TOTAL:</strong></td>
                    <td class="text-right"><strong>{{ number_format($repayments->sum('principal_amount'), 2, ',', '.') }} €</strong></td>
                    <td class="text-right"><strong>{{ number_format($repayments->sum('interest_amount'), 2, ',', '.') }} €</strong></td>
                    <td class="text-right"><strong>{{ number_format($repayments->sum('total_amount'), 2, ',', '.') }} €</strong></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2 class="section-title">Información de Pago</h2>
        <div class="info-box">
            <p class="mb-10"><strong>Frecuencia de Pago:</strong> {{ ucfirst($investment->repayment_interval ?? 'mensual') }}</p>
            <p class="mb-10"><strong>Número de Pagos:</strong> {{ $repayments->count() }}</p>
            <p class="mb-10"><strong>Primer Pago:</strong> {{ $repayments->first()->due_date->format('d/m/Y') }}</p>
            <p><strong>Pago Final:</strong> {{ $repayments->last()->due_date->format('d/m/Y') }}</p>
        </div>
    </div>

    <div class="section">
        <h2 class="section-title">Notas Importantes</h2>
        <div class="warning-box">
            <p class="mb-10">• Todos los pagos se realizarán a la cuenta bancaria registrada en su perfil de inversor.</p>
            <p class="mb-10">• Las fechas de pago pueden variar ±3 días hábiles según los tiempos de procesamiento bancario.</p>
            <p class="mb-10">• En caso de terminación anticipada, el calendario se ajustará en consecuencia.</p>
            <p>• Para cualquier pregunta sobre pagos, comuníquese con nuestro equipo de soporte.</p>
        </div>
    </div>
@endsection
