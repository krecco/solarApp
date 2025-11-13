@extends('pdfs.layouts.base', ['locale' => 'si'])

@section('title', 'Urnik Odplačil')

@section('header-info')
    Pregled Odplačil Naložbe
@endsection

@section('document-reference', 'REP-' . $investment->id)

@section('content')
    <div class="document-title">{{ trans('contracts.repayment_schedule', [], 'si') }}</div>
    <div class="document-subtitle">Naložbena Pogodba: INV-{{ $investment->id }}</div>

    <div class="document-meta">
        <div class="document-meta-row">
            <span class="meta-label">Vlagatelj:</span>
            <span class="meta-value">{{ $investment->user->full_name_with_titles ?? $investment->user->name }}</span>
        </div>
        <div class="document-meta-row">
            <span class="meta-label">Skupna Naložba:</span>
            <span class="meta-value amount">{{ number_format($investment->amount, 2, ',', '.') }} €</span>
        </div>
        <div class="document-meta-row">
            <span class="meta-label">Obrestna Mera:</span>
            <span class="meta-value">{{ number_format($investment->interest_rate, 2, ',', '.') }}% letno</span>
        </div>
        <div class="document-meta-row">
            <span class="meta-label">Skupni Donos:</span>
            <span class="meta-value amount">{{ number_format($investment->total_repayment, 2, ',', '.') }} €</span>
        </div>
    </div>

    <div class="section">
        <h2 class="section-title">{{ trans('contracts.payment_schedule', [], 'si') }}</h2>
        <table>
            <thead>
                <tr>
                    <th style="width: 8%;">#</th>
                    <th style="width: 20%;">{{ trans('contracts.payment_date', [], 'si') }}</th>
                    <th style="width: 18%;">{{ trans('contracts.principal_amount', [], 'si') }}</th>
                    <th style="width: 18%;">{{ trans('contracts.interest_amount', [], 'si') }}</th>
                    <th style="width: 18%;">{{ trans('contracts.total_payment', [], 'si') }}</th>
                    <th style="width: 18%;">{{ trans('contracts.remaining_balance', [], 'si') }}</th>
                </tr>
            </thead>
            <tbody>
                @php $remainingBalance = $investment->total_repayment; @endphp
                @foreach($repayments as $index => $repayment)
                    @php $remainingBalance -= $repayment->total_amount; @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $repayment->due_date->format('d. m. Y') }}</td>
                        <td class="text-right">{{ number_format($repayment->principal_amount, 2, ',', '.') }} €</td>
                        <td class="text-right">{{ number_format($repayment->interest_amount, 2, ',', '.') }} €</td>
                        <td class="text-right"><strong>{{ number_format($repayment->total_amount, 2, ',', '.') }} €</strong></td>
                        <td class="text-right">{{ number_format(max(0, $remainingBalance), 2, ',', '.') }} €</td>
                    </tr>
                @endforeach
                <tr class="table-total">
                    <td colspan="2" class="text-right"><strong>SKUPAJ:</strong></td>
                    <td class="text-right"><strong>{{ number_format($repayments->sum('principal_amount'), 2, ',', '.') }} €</strong></td>
                    <td class="text-right"><strong>{{ number_format($repayments->sum('interest_amount'), 2, ',', '.') }} €</strong></td>
                    <td class="text-right"><strong>{{ number_format($repayments->sum('total_amount'), 2, ',', '.') }} €</strong></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2 class="section-title">Informacije o Plačilu</h2>
        <div class="info-box">
            <p class="mb-10"><strong>Pogostost Plačil:</strong> {{ ucfirst($investment->repayment_interval ?? 'mesečno') }}</p>
            <p class="mb-10"><strong>Število Plačil:</strong> {{ $repayments->count() }}</p>
            <p class="mb-10"><strong>Prvo Plačilo:</strong> {{ $repayments->first()->due_date->format('d. m. Y') }}</p>
            <p><strong>Zadnje Plačilo:</strong> {{ $repayments->last()->due_date->format('d. m. Y') }}</p>
        </div>
    </div>

    <div class="section">
        <h2 class="section-title">Pomembne Opombe</h2>
        <div class="warning-box">
            <p class="mb-10">• Vsa plačila bodo izvedena na bančni račun, registriran v vašem profilu vlagatelja.</p>
            <p class="mb-10">• Datumi plačil se lahko razlikujejo za ±3 delovne dni glede na čase bančnega procesiranja.</p>
            <p class="mb-10">• V primeru predčasne prekinitve bo urnik ustrezno prilagojen.</p>
            <p>• Za vprašanja v zvezi s plačili se obrnite na našo podporno ekipo.</p>
        </div>
    </div>
@endsection
