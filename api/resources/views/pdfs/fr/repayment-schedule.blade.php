@extends('pdfs.layouts.base', ['locale' => 'fr'])

@section('title', 'Échéancier de Remboursement')

@section('header-info')
    Aperçu des Remboursements d'Investissement
@endsection

@section('document-reference', 'REP-' . $investment->id)

@section('content')
    <div class="document-title">{{ trans('contracts.repayment_schedule', [], 'fr') }}</div>
    <div class="document-subtitle">Contrat d'Investissement: INV-{{ $investment->id }}</div>

    <div class="document-meta">
        <div class="document-meta-row">
            <span class="meta-label">Investisseur:</span>
            <span class="meta-value">{{ $investment->user->full_name_with_titles ?? $investment->user->name }}</span>
        </div>
        <div class="document-meta-row">
            <span class="meta-label">Investissement Total:</span>
            <span class="meta-value amount">{{ number_format($investment->amount, 2, ',', ' ') }} €</span>
        </div>
        <div class="document-meta-row">
            <span class="meta-label">Taux d'Intérêt:</span>
            <span class="meta-value">{{ number_format($investment->interest_rate, 2, ',', ' ') }}% par an</span>
        </div>
        <div class="document-meta-row">
            <span class="meta-label">Rendement Total:</span>
            <span class="meta-value amount">{{ number_format($investment->total_repayment, 2, ',', ' ') }} €</span>
        </div>
    </div>

    <div class="section">
        <h2 class="section-title">{{ trans('contracts.payment_schedule', [], 'fr') }}</h2>
        <table>
            <thead>
                <tr>
                    <th style="width: 8%;">#</th>
                    <th style="width: 20%;">{{ trans('contracts.payment_date', [], 'fr') }}</th>
                    <th style="width: 18%;">{{ trans('contracts.principal_amount', [], 'fr') }}</th>
                    <th style="width: 18%;">{{ trans('contracts.interest_amount', [], 'fr') }}</th>
                    <th style="width: 18%;">{{ trans('contracts.total_payment', [], 'fr') }}</th>
                    <th style="width: 18%;">{{ trans('contracts.remaining_balance', [], 'fr') }}</th>
                </tr>
            </thead>
            <tbody>
                @php $remainingBalance = $investment->total_repayment; @endphp
                @foreach($repayments as $index => $repayment)
                    @php $remainingBalance -= $repayment->total_amount; @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $repayment->due_date->format('d/m/Y') }}</td>
                        <td class="text-right">{{ number_format($repayment->principal_amount, 2, ',', ' ') }} €</td>
                        <td class="text-right">{{ number_format($repayment->interest_amount, 2, ',', ' ') }} €</td>
                        <td class="text-right"><strong>{{ number_format($repayment->total_amount, 2, ',', ' ') }} €</strong></td>
                        <td class="text-right">{{ number_format(max(0, $remainingBalance), 2, ',', ' ') }} €</td>
                    </tr>
                @endforeach
                <tr class="table-total">
                    <td colspan="2" class="text-right"><strong>TOTAL:</strong></td>
                    <td class="text-right"><strong>{{ number_format($repayments->sum('principal_amount'), 2, ',', ' ') }} €</strong></td>
                    <td class="text-right"><strong>{{ number_format($repayments->sum('interest_amount'), 2, ',', ' ') }} €</strong></td>
                    <td class="text-right"><strong>{{ number_format($repayments->sum('total_amount'), 2, ',', ' ') }} €</strong></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2 class="section-title">Informations de Paiement</h2>
        <div class="info-box">
            <p class="mb-10"><strong>Fréquence de Paiement:</strong> {{ ucfirst($investment->repayment_interval ?? 'mensuel') }}</p>
            <p class="mb-10"><strong>Nombre de Paiements:</strong> {{ $repayments->count() }}</p>
            <p class="mb-10"><strong>Premier Paiement:</strong> {{ $repayments->first()->due_date->format('d/m/Y') }}</p>
            <p><strong>Dernier Paiement:</strong> {{ $repayments->last()->due_date->format('d/m/Y') }}</p>
        </div>
    </div>

    <div class="section">
        <h2 class="section-title">Notes Importantes</h2>
        <div class="warning-box">
            <p class="mb-10">• Tous les paiements seront effectués sur le compte bancaire enregistré dans votre profil d'investisseur.</p>
            <p class="mb-10">• Les dates de paiement peuvent varier de ±3 jours ouvrables selon les délais de traitement bancaire.</p>
            <p class="mb-10">• En cas de résiliation anticipée, le calendrier sera ajusté en conséquence.</p>
            <p>• Pour toute question concernant les paiements, veuillez contacter notre équipe d'assistance.</p>
        </div>
    </div>
@endsection
