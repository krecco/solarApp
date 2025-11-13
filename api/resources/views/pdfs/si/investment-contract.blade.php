@extends('pdfs.layouts.base', ['locale' => 'si'])

@section('title', 'Naložbena Pogodba')

@section('header-info')
    Platforma za Naložbe v Sončno Energijo
@endsection

@section('document-reference', 'INV-' . $investment->id)

@section('content')
    <div class="document-title">
        {{ trans('contracts.investment_contract', [], 'si') }}
    </div>
    <div class="document-subtitle">
        {{ trans('contracts.contract_number', [], 'si') }}: INV-{{ $investment->id }}
    </div>

    <div class="document-meta">
        <div class="document-meta-row">
            <span class="meta-label">{{ trans('contracts.contract_date', [], 'si') }}:</span>
            <span class="meta-value">{{ $investment->created_at->format('d. m. Y') }}</span>
        </div>
        <div class="document-meta-row">
            <span class="meta-label">Začetni datum:</span>
            <span class="meta-value">{{ $investment->start_date->format('d. m. Y') }}</span>
        </div>
        <div class="document-meta-row">
            <span class="meta-label">{{ trans('contracts.effective_date', [], 'si') }}:</span>
            <span class="meta-value">{{ $investment->end_date->format('d. m. Y') }}</span>
        </div>
        @if($investment->verified)
        <div class="document-meta-row">
            <span class="meta-label">{{ trans('common.status', [], 'si') }}:</span>
            <span class="meta-value">{{ trans('contracts.active', [], 'si') }}</span>
        </div>
        @endif
    </div>

    <div class="section">
        <h2 class="section-title">{{ trans('contracts.contracting_parties', [], 'si') }}</h2>
        <div class="two-columns">
            <div class="column-50">
                <div class="party-info">
                    <h3>{{ trans('contracts.party_a', [], 'si') }} - Vlagatelj</h3>
                    <p><strong>{{ trans('common.name', [], 'si') }}:</strong> {{ $investment->user->full_name_with_titles ?? $investment->user->name }}</p>
                    <p><strong>{{ trans('common.email', [], 'si') }}:</strong> {{ $investment->user->email }}</p>
                    @if($investment->user->phone_nr)
                    <p><strong>{{ trans('common.phone', [], 'si') }}:</strong> {{ $investment->user->phone_nr }}</p>
                    @endif
                    @if($investment->user->customer_no)
                    <p><strong>Številka stranke:</strong> {{ $investment->user->customer_no }}</p>
                    @endif
                </div>
            </div>
            <div class="column-50">
                <div class="party-info">
                    <h3>{{ trans('contracts.party_b', [], 'si') }} - Platforma</h3>
                    <p><strong>{{ trans('common.name', [], 'si') }}:</strong> {{ config('app.name') }}</p>
                    <p><strong>Projekt:</strong> {{ $investment->solarPlant->plant_name ?? 'N/A' }}</p>
                    @if($investment->solarPlant)
                    <p><strong>Lokacija:</strong> {{ $investment->solarPlant->location ?? 'N/A' }}</p>
                    <p><strong>Kapaciteta:</strong> {{ $investment->solarPlant->capacity_kwp ?? 'N/A' }} kWp</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="section">
        <h2 class="section-title">{{ trans('contracts.investment_details', [], 'si') }}</h2>
        <div class="info-box">
            <table>
                <tr>
                    <td style="width: 60%;"><strong>{{ trans('contracts.investment_amount', [], 'si') }}:</strong></td>
                    <td class="text-right"><span class="amount-large">{{ number_format($investment->amount, 2, ',', '.') }} €</span></td>
                </tr>
                <tr>
                    <td><strong>{{ trans('contracts.investment_term', [], 'si') }}:</strong></td>
                    <td class="text-right">{{ $investment->duration_months }} mesecev</td>
                </tr>
                <tr>
                    <td><strong>{{ trans('contracts.interest_rate', [], 'si') }}:</strong></td>
                    <td class="text-right">{{ number_format($investment->interest_rate, 2, ',', '.') }}% letno</td>
                </tr>
                <tr>
                    <td><strong>Skupne obresti:</strong></td>
                    <td class="text-right"><span class="amount">{{ number_format($investment->total_interest, 2, ',', '.') }} €</span></td>
                </tr>
                <tr class="table-total">
                    <td><strong>{{ trans('contracts.total_return', [], 'si') }}:</strong></td>
                    <td class="text-right"><span class="amount-large">{{ number_format($investment->total_repayment, 2, ',', '.') }} €</span></td>
                </tr>
            </table>
        </div>
        <p class="text-muted text-small mb-10">
            Interval odplačil: {{ ucfirst($investment->repayment_interval ?? 'mesečno') }}
        </p>
    </div>

    <div class="section no-break">
        <h2 class="section-title">{{ trans('contracts.investment_terms', [], 'si') }}</h2>
        <div class="section-content">
            <p class="mb-10"><strong>1. Namen Naložbe</strong></p>
            <p class="mb-20">
                Vlagatelj se strinja, da bo naložil določen znesek v projekt sončne energije, ki ga upravlja {{ config('app.name') }}.
                Ta naložba bo uporabljena za razvoj, namestitev in delovanje fotovoltaičnih sončnih sistemov.
            </p>
            <p class="mb-10"><strong>2. Donosi in Plačila</strong></p>
            <p class="mb-20">
                Platforma se zavezuje, da bo vlagatelju izplačala donos, izračunan po stopnji {{ number_format($investment->interest_rate, 2, ',', '.') }}%
                letno od vloženega zneska. Plačila bodo izvedena v skladu z {{ $investment->repayment_interval ?? 'mesečnim' }} razporedom odplačil.
            </p>
            <p class="mb-10"><strong>3. Obdobje Naložbe</strong></p>
            <p class="mb-20">
                Obdobje naložbe se začne dne {{ $investment->start_date->format('d. m. Y') }} in se konča dne
                {{ $investment->end_date->format('d. m. Y') }}.
            </p>
            <p class="mb-10"><strong>4. Razkritje Tveganj</strong></p>
            <p class="mb-20">
                Vlagatelj priznava, da vse naložbe nosijo tveganja. Dejanska uspešnost se lahko razlikuje glede na
                proizvodnjo energije, tržne razmere in druge dejavnike.
            </p>
            <p class="mb-10"><strong>5. Predčasna Prekinitev</strong></p>
            <p class="mb-20">
                Katera koli stranka lahko predčasno prekine ta dogovor s predložitvijo 30-dnevnega pisnega obvestila.
            </p>
            <p class="mb-10"><strong>6. Veljavno Pravo</strong></p>
            <p class="mb-10">
                Ta pogodba se ureja v skladu z zakoni jurisdikcije, v kateri je registrirana platforma.
            </p>
        </div>
    </div>

    <div class="signatures no-break">
        <div class="signature-block">
            <div class="signature-line">
                <div class="signature-label">Podpis Vlagatelja</div>
                <div class="text-bold">{{ $investment->user->full_name_with_titles ?? $investment->user->name }}</div>
            </div>
        </div>
        <div class="signature-block">
            <div class="signature-line">
                <div class="signature-label">Predstavnik Platforme</div>
                <div class="text-bold">{{ config('app.name') }}</div>
            </div>
        </div>
    </div>

    @if($investment->notes)
    <div class="section mt-20">
        <h2 class="section-title">Dodatne Opombe</h2>
        <p>{{ $investment->notes }}</p>
    </div>
    @endif
@endsection

@section('footer')
    <div class="footer-row text-small">
        Ta dokument je pravno zavezujoča pogodba. Prosimo, da ga hranite na varnem mestu.
    </div>
@endsection
