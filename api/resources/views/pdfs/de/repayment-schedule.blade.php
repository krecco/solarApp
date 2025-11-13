@extends('pdfs.layouts.base', ['locale' => 'de'])

@section('title', 'Rückzahlungsplan')

@section('header-info')
    Investitions-Rückzahlungsübersicht
@endsection

@section('document-reference', 'REP-' . $investment->id)

@section('content')
    <div class="document-title">{{ trans('contracts.repayment_schedule', [], 'de') }}</div>
    <div class="document-subtitle">Investitionsvertrag: INV-{{ $investment->id }}</div>

    <div class="document-meta">
        <div class="document-meta-row">
            <span class="meta-label">Investor:</span>
            <span class="meta-value">{{ $investment->user->full_name_with_titles ?? $investment->user->name }}</span>
        </div>
        <div class="document-meta-row">
            <span class="meta-label">Gesamtinvestition:</span>
            <span class="meta-value amount">{{ number_format($investment->amount, 2, ',', '.') }} €</span>
        </div>
        <div class="document-meta-row">
            <span class="meta-label">Zinssatz:</span>
            <span class="meta-value">{{ number_format($investment->interest_rate, 2, ',', '.') }}% pro Jahr</span>
        </div>
        <div class="document-meta-row">
            <span class="meta-label">Gesamtrendite:</span>
            <span class="meta-value amount">{{ number_format($investment->total_repayment, 2, ',', '.') }} €</span>
        </div>
    </div>

    <div class="section">
        <h2 class="section-title">{{ trans('contracts.payment_schedule', [], 'de') }}</h2>
        <table>
            <thead>
                <tr>
                    <th style="width: 8%;">#</th>
                    <th style="width: 20%;">{{ trans('contracts.payment_date', [], 'de') }}</th>
                    <th style="width: 18%;">{{ trans('contracts.principal_amount', [], 'de') }}</th>
                    <th style="width: 18%;">{{ trans('contracts.interest_amount', [], 'de') }}</th>
                    <th style="width: 18%;">{{ trans('contracts.total_payment', [], 'de') }}</th>
                    <th style="width: 18%;">{{ trans('contracts.remaining_balance', [], 'de') }}</th>
                </tr>
            </thead>
            <tbody>
                @php $remainingBalance = $investment->total_repayment; @endphp
                @foreach($repayments as $index => $repayment)
                    @php $remainingBalance -= $repayment->total_amount; @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $repayment->due_date->format('d.m.Y') }}</td>
                        <td class="text-right">{{ number_format($repayment->principal_amount, 2, ',', '.') }} €</td>
                        <td class="text-right">{{ number_format($repayment->interest_amount, 2, ',', '.') }} €</td>
                        <td class="text-right"><strong>{{ number_format($repayment->total_amount, 2, ',', '.') }} €</strong></td>
                        <td class="text-right">{{ number_format(max(0, $remainingBalance), 2, ',', '.') }} €</td>
                    </tr>
                @endforeach
                <tr class="table-total">
                    <td colspan="2" class="text-right"><strong>GESAMT:</strong></td>
                    <td class="text-right"><strong>{{ number_format($repayments->sum('principal_amount'), 2, ',', '.') }} €</strong></td>
                    <td class="text-right"><strong>{{ number_format($repayments->sum('interest_amount'), 2, ',', '.') }} €</strong></td>
                    <td class="text-right"><strong>{{ number_format($repayments->sum('total_amount'), 2, ',', '.') }} €</strong></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2 class="section-title">Zahlungsinformationen</h2>
        <div class="info-box">
            <p class="mb-10"><strong>Zahlungshäufigkeit:</strong> {{ ucfirst($investment->repayment_interval ?? 'monatlich') }}</p>
            <p class="mb-10"><strong>Anzahl der Zahlungen:</strong> {{ $repayments->count() }}</p>
            <p class="mb-10"><strong>Erste Zahlung:</strong> {{ $repayments->first()->due_date->format('d.m.Y') }}</p>
            <p><strong>Letzte Zahlung:</strong> {{ $repayments->last()->due_date->format('d.m.Y') }}</p>
        </div>
    </div>

    <div class="section">
        <h2 class="section-title">Wichtige Hinweise</h2>
        <div class="warning-box">
            <p class="mb-10">• Alle Zahlungen erfolgen auf das in Ihrem Investorenprofil registrierte Bankkonto.</p>
            <p class="mb-10">• Zahlungstermine können je nach Bankbearbeitungszeit um ±3 Werktage variieren.</p>
            <p class="mb-10">• Bei vorzeitiger Beendigung wird der Zeitplan entsprechend angepasst.</p>
            <p>• Bei Fragen zu Zahlungen wenden Sie sich bitte an unser Support-Team.</p>
        </div>
    </div>
@endsection
