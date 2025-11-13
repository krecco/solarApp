@extends('pdfs.layouts.base', ['locale' => 'de'])

@section('title', 'Investitionsvertrag')

@section('header-info')
    Solarenergie-Investitionsplattform
@endsection

@section('document-reference', 'INV-' . $investment->id)

@section('content')
    <div class="document-title">
        {{ trans('contracts.investment_contract', [], 'de') }}
    </div>
    <div class="document-subtitle">
        {{ trans('contracts.contract_number', [], 'de') }}: INV-{{ $investment->id }}
    </div>

    <!-- Contract Metadata -->
    <div class="document-meta">
        <div class="document-meta-row">
            <span class="meta-label">{{ trans('contracts.contract_date', [], 'de') }}:</span>
            <span class="meta-value">{{ $investment->created_at->format('d.m.Y') }}</span>
        </div>
        <div class="document-meta-row">
            <span class="meta-label">Vertragsbeginn:</span>
            <span class="meta-value">{{ $investment->start_date->format('d.m.Y') }}</span>
        </div>
        <div class="document-meta-row">
            <span class="meta-label">{{ trans('contracts.effective_date', [], 'de') }}:</span>
            <span class="meta-value">{{ $investment->end_date->format('d.m.Y') }}</span>
        </div>
        @if($investment->verified)
        <div class="document-meta-row">
            <span class="meta-label">{{ trans('common.status', [], 'de') }}:</span>
            <span class="meta-value">{{ trans('contracts.active', [], 'de') }}</span>
        </div>
        @endif
    </div>

    <!-- Contracting Parties -->
    <div class="section">
        <h2 class="section-title">{{ trans('contracts.contracting_parties', [], 'de') }}</h2>

        <div class="two-columns">
            <div class="column-50">
                <div class="party-info">
                    <h3>{{ trans('contracts.party_a', [], 'de') }} - Investor</h3>
                    <p><strong>{{ trans('common.name', [], 'de') }}:</strong> {{ $investment->user->full_name_with_titles ?? $investment->user->name }}</p>
                    <p><strong>{{ trans('common.email', [], 'de') }}:</strong> {{ $investment->user->email }}</p>
                    @if($investment->user->phone_nr)
                    <p><strong>{{ trans('common.phone', [], 'de') }}:</strong> {{ $investment->user->phone_nr }}</p>
                    @endif
                    @if($investment->user->customer_no)
                    <p><strong>Kundennummer:</strong> {{ $investment->user->customer_no }}</p>
                    @endif
                </div>
            </div>

            <div class="column-50">
                <div class="party-info">
                    <h3>{{ trans('contracts.party_b', [], 'de') }} - Plattform</h3>
                    <p><strong>{{ trans('common.name', [], 'de') }}:</strong> {{ config('app.name') }}</p>
                    <p><strong>Projekt:</strong> {{ $investment->solarPlant->plant_name ?? 'N/A' }}</p>
                    @if($investment->solarPlant)
                    <p><strong>Standort:</strong> {{ $investment->solarPlant->location ?? 'N/A' }}</p>
                    <p><strong>Kapazität:</strong> {{ $investment->solarPlant->capacity_kwp ?? 'N/A' }} kWp</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Investment Details -->
    <div class="section">
        <h2 class="section-title">{{ trans('contracts.investment_details', [], 'de') }}</h2>

        <div class="info-box">
            <table>
                <tr>
                    <td style="width: 60%;"><strong>{{ trans('contracts.investment_amount', [], 'de') }}:</strong></td>
                    <td class="text-right"><span class="amount-large">{{ number_format($investment->amount, 2, ',', '.') }} €</span></td>
                </tr>
                <tr>
                    <td><strong>{{ trans('contracts.investment_term', [], 'de') }}:</strong></td>
                    <td class="text-right">{{ $investment->duration_months }} Monate</td>
                </tr>
                <tr>
                    <td><strong>{{ trans('contracts.interest_rate', [], 'de') }}:</strong></td>
                    <td class="text-right">{{ number_format($investment->interest_rate, 2, ',', '.') }}% pro Jahr</td>
                </tr>
                <tr>
                    <td><strong>Gesamtzinsen:</strong></td>
                    <td class="text-right"><span class="amount">{{ number_format($investment->total_interest, 2, ',', '.') }} €</span></td>
                </tr>
                <tr class="table-total">
                    <td><strong>{{ trans('contracts.total_return', [], 'de') }}:</strong></td>
                    <td class="text-right"><span class="amount-large">{{ number_format($investment->total_repayment, 2, ',', '.') }} €</span></td>
                </tr>
            </table>
        </div>

        <p class="text-muted text-small mb-10">
            Rückzahlungsintervall: {{ ucfirst($investment->repayment_interval ?? 'monatlich') }}
        </p>
    </div>

    <!-- Investment Terms -->
    <div class="section no-break">
        <h2 class="section-title">{{ trans('contracts.investment_terms', [], 'de') }}</h2>

        <div class="section-content">
            <p class="mb-10"><strong>1. Investitionszweck</strong></p>
            <p class="mb-20">
                Der Investor verpflichtet sich, den angegebenen Betrag in das von {{ config('app.name') }} verwaltete
                Solarenergieprojekt zu investieren. Diese Investition wird für die Entwicklung, Installation und den
                Betrieb von Photovoltaik-Solaranlagen verwendet.
            </p>

            <p class="mb-10"><strong>2. Renditen und Zahlungen</strong></p>
            <p class="mb-20">
                Die Plattform verpflichtet sich, dem Investor eine Rendite zu zahlen, die mit {{ number_format($investment->interest_rate, 2, ',', '.') }}%
                pro Jahr auf den investierten Betrag berechnet wird. Die Zahlungen erfolgen gemäß dem {{ $investment->repayment_interval ?? 'monatlichen' }}
                Rückzahlungsplan, der diesem Vertrag beigefügt ist.
            </p>

            <p class="mb-10"><strong>3. Investitionszeitraum</strong></p>
            <p class="mb-20">
                Der Investitionszeitraum beginnt am {{ $investment->start_date->format('d.m.Y') }} und endet am
                {{ $investment->end_date->format('d.m.Y') }}, sofern er nicht früher gemäß diesen Bedingungen beendet wird.
            </p>

            <p class="mb-10"><strong>4. Risikoaufklärung</strong></p>
            <p class="mb-20">
                Der Investor erkennt an, dass alle Investitionen mit Risiken verbunden sind. Während {{ config('app.name') }}
                sich bemüht, stabile Renditen zu gewährleisten, kann die tatsächliche Performance aufgrund von Energieproduktion,
                Marktbedingungen und anderen Faktoren außerhalb der Kontrolle der Plattform variieren.
            </p>

            <p class="mb-10"><strong>5. Vorzeitige Beendigung</strong></p>
            <p class="mb-20">
                Jede Partei kann diese Vereinbarung vorzeitig beenden, indem sie eine schriftliche Kündigungsfrist von 30 Tagen
                einreicht. In solchen Fällen gibt die Plattform den Kapitalbetrag zuzüglich aufgelaufener Zinsen bis zum
                Kündigungsdatum zurück, vorbehaltlich geltender Geschäftsbedingungen.
            </p>

            <p class="mb-10"><strong>6. Anwendbares Recht</strong></p>
            <p class="mb-10">
                Dieser Vertrag unterliegt den Gesetzen der Gerichtsbarkeit, in der die Plattform registriert ist, und ist
                entsprechend auszulegen. Alle aus diesem Vertrag resultierenden Streitigkeiten werden durch Schiedsverfahren
                oder zuständige Gerichte beigelegt.
            </p>
        </div>
    </div>

    <!-- Signatures -->
    <div class="signatures no-break">
        <div class="signature-block">
            <div class="signature-line">
                <div class="signature-label">Unterschrift Investor</div>
                <div class="text-bold">{{ $investment->user->full_name_with_titles ?? $investment->user->name }}</div>
            </div>
        </div>

        <div class="signature-block">
            <div class="signature-line">
                <div class="signature-label">Plattform-Vertreter</div>
                <div class="text-bold">{{ config('app.name') }}</div>
            </div>
        </div>
    </div>

    @if($investment->notes)
    <div class="section mt-20">
        <h2 class="section-title">Zusätzliche Hinweise</h2>
        <p>{{ $investment->notes }}</p>
    </div>
    @endif
@endsection

@section('footer')
    <div class="footer-row text-small">
        Dieses Dokument ist ein rechtlich bindender Vertrag. Bitte bewahren Sie es an einem sicheren Ort auf.
    </div>
@endsection
