@extends('pdfs.layouts.base', ['locale' => 'fr'])

@section('title', 'Contrat d\'Investissement')

@section('header-info')
    Plateforme d'Investissement en Énergie Solaire
@endsection

@section('document-reference', 'INV-' . $investment->id)

@section('content')
    <div class="document-title">
        {{ trans('contracts.investment_contract', [], 'fr') }}
    </div>
    <div class="document-subtitle">
        {{ trans('contracts.contract_number', [], 'fr') }}: INV-{{ $investment->id }}
    </div>

    <div class="document-meta">
        <div class="document-meta-row">
            <span class="meta-label">{{ trans('contracts.contract_date', [], 'fr') }}:</span>
            <span class="meta-value">{{ $investment->created_at->format('d/m/Y') }}</span>
        </div>
        <div class="document-meta-row">
            <span class="meta-label">Date de début:</span>
            <span class="meta-value">{{ $investment->start_date->format('d/m/Y') }}</span>
        </div>
        <div class="document-meta-row">
            <span class="meta-label">{{ trans('contracts.effective_date', [], 'fr') }}:</span>
            <span class="meta-value">{{ $investment->end_date->format('d/m/Y') }}</span>
        </div>
        @if($investment->verified)
        <div class="document-meta-row">
            <span class="meta-label">{{ trans('common.status', [], 'fr') }}:</span>
            <span class="meta-value">{{ trans('contracts.active', [], 'fr') }}</span>
        </div>
        @endif
    </div>

    <div class="section">
        <h2 class="section-title">{{ trans('contracts.contracting_parties', [], 'fr') }}</h2>
        <div class="two-columns">
            <div class="column-50">
                <div class="party-info">
                    <h3>{{ trans('contracts.party_a', [], 'fr') }} - Investisseur</h3>
                    <p><strong>{{ trans('common.name', [], 'fr') }}:</strong> {{ $investment->user->full_name_with_titles ?? $investment->user->name }}</p>
                    <p><strong>{{ trans('common.email', [], 'fr') }}:</strong> {{ $investment->user->email }}</p>
                    @if($investment->user->phone_nr)
                    <p><strong>{{ trans('common.phone', [], 'fr') }}:</strong> {{ $investment->user->phone_nr }}</p>
                    @endif
                    @if($investment->user->customer_no)
                    <p><strong>N° Client:</strong> {{ $investment->user->customer_no }}</p>
                    @endif
                </div>
            </div>
            <div class="column-50">
                <div class="party-info">
                    <h3>{{ trans('contracts.party_b', [], 'fr') }} - Plateforme</h3>
                    <p><strong>{{ trans('common.name', [], 'fr') }}:</strong> {{ config('app.name') }}</p>
                    <p><strong>Projet:</strong> {{ $investment->solarPlant->plant_name ?? 'N/A' }}</p>
                    @if($investment->solarPlant)
                    <p><strong>Emplacement:</strong> {{ $investment->solarPlant->location ?? 'N/A' }}</p>
                    <p><strong>Capacité:</strong> {{ $investment->solarPlant->capacity_kwp ?? 'N/A' }} kWp</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="section">
        <h2 class="section-title">{{ trans('contracts.investment_details', [], 'fr') }}</h2>
        <div class="info-box">
            <table>
                <tr>
                    <td style="width: 60%;"><strong>{{ trans('contracts.investment_amount', [], 'fr') }}:</strong></td>
                    <td class="text-right"><span class="amount-large">{{ number_format($investment->amount, 2, ',', ' ') }} €</span></td>
                </tr>
                <tr>
                    <td><strong>{{ trans('contracts.investment_term', [], 'fr') }}:</strong></td>
                    <td class="text-right">{{ $investment->duration_months }} mois</td>
                </tr>
                <tr>
                    <td><strong>{{ trans('contracts.interest_rate', [], 'fr') }}:</strong></td>
                    <td class="text-right">{{ number_format($investment->interest_rate, 2, ',', ' ') }}% par an</td>
                </tr>
                <tr>
                    <td><strong>Intérêts totaux:</strong></td>
                    <td class="text-right"><span class="amount">{{ number_format($investment->total_interest, 2, ',', ' ') }} €</span></td>
                </tr>
                <tr class="table-total">
                    <td><strong>{{ trans('contracts.total_return', [], 'fr') }}:</strong></td>
                    <td class="text-right"><span class="amount-large">{{ number_format($investment->total_repayment, 2, ',', ' ') }} €</span></td>
                </tr>
            </table>
        </div>
        <p class="text-muted text-small mb-10">
            Intervalle de remboursement: {{ ucfirst($investment->repayment_interval ?? 'mensuel') }}
        </p>
    </div>

    <div class="section no-break">
        <h2 class="section-title">{{ trans('contracts.investment_terms', [], 'fr') }}</h2>
        <div class="section-content">
            <p class="mb-10"><strong>1. Objet de l'Investissement</strong></p>
            <p class="mb-20">
                L'Investisseur accepte d'investir le montant spécifié dans le projet d'énergie solaire géré par {{ config('app.name') }}.
                Cet investissement sera utilisé pour le développement, l'installation et l'exploitation de systèmes solaires photovoltaïques.
            </p>
            <p class="mb-10"><strong>2. Rendements et Paiements</strong></p>
            <p class="mb-20">
                La Plateforme s'engage à verser à l'Investisseur un rendement calculé à {{ number_format($investment->interest_rate, 2, ',', ' ') }}%
                par an sur le montant investi. Les paiements seront effectués selon le calendrier de remboursement {{ $investment->repayment_interval ?? 'mensuel' }}.
            </p>
            <p class="mb-10"><strong>3. Période d'Investissement</strong></p>
            <p class="mb-20">
                La période d'investissement commence le {{ $investment->start_date->format('d/m/Y') }} et se termine le
                {{ $investment->end_date->format('d/m/Y') }}.
            </p>
            <p class="mb-10"><strong>4. Divulgation des Risques</strong></p>
            <p class="mb-20">
                L'Investisseur reconnaît que tous les investissements comportent des risques. La performance réelle peut
                varier en fonction de la production d'énergie, des conditions du marché et d'autres facteurs.
            </p>
            <p class="mb-10"><strong>5. Résiliation Anticipée</strong></p>
            <p class="mb-20">
                Chaque partie peut résilier cet accord de manière anticipée en fournissant un préavis écrit de 30 jours.
            </p>
            <p class="mb-10"><strong>6. Droit Applicable</strong></p>
            <p class="mb-10">
                Ce contrat sera régi par les lois de la juridiction où la Plateforme est enregistrée.
            </p>
        </div>
    </div>

    <div class="signatures no-break">
        <div class="signature-block">
            <div class="signature-line">
                <div class="signature-label">Signature de l'Investisseur</div>
                <div class="text-bold">{{ $investment->user->full_name_with_titles ?? $investment->user->name }}</div>
            </div>
        </div>
        <div class="signature-block">
            <div class="signature-line">
                <div class="signature-label">Représentant de la Plateforme</div>
                <div class="text-bold">{{ config('app.name') }}</div>
            </div>
        </div>
    </div>

    @if($investment->notes)
    <div class="section mt-20">
        <h2 class="section-title">Notes Supplémentaires</h2>
        <p>{{ $investment->notes }}</p>
    </div>
    @endif
@endsection

@section('footer')
    <div class="footer-row text-small">
        Ce document est un contrat juridiquement contraignant. Veuillez le conserver dans un endroit sûr.
    </div>
@endsection
