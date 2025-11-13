<!DOCTYPE html>
<html lang="{{ $locale ?? 'en' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Document')</title>
    <style>
        @page {
            margin: 2cm 1.5cm;
            @bottom-right {
                content: counter(page) " / " counter(pages);
            }
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.6;
            color: #333;
        }

        .container {
            width: 100%;
            max-width: 100%;
        }

        /* Header */
        .header {
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #3B82F6;
        }

        .header-logo {
            font-size: 24pt;
            font-weight: bold;
            color: #3B82F6;
            margin-bottom: 10px;
        }

        .header-info {
            font-size: 9pt;
            color: #666;
        }

        /* Document Title */
        .document-title {
            font-size: 18pt;
            font-weight: bold;
            color: #1F2937;
            margin: 20px 0;
            text-align: center;
        }

        .document-subtitle {
            font-size: 12pt;
            color: #666;
            text-align: center;
            margin-bottom: 30px;
        }

        /* Document Metadata */
        .document-meta {
            background-color: #F3F4F6;
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 4px;
        }

        .document-meta-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .document-meta-row:last-child {
            margin-bottom: 0;
        }

        .meta-label {
            font-weight: bold;
            color: #4B5563;
        }

        .meta-value {
            color: #1F2937;
        }

        /* Content Sections */
        .section {
            margin-bottom: 25px;
        }

        .section-title {
            font-size: 14pt;
            font-weight: bold;
            color: #1F2937;
            margin-bottom: 12px;
            padding-bottom: 5px;
            border-bottom: 1px solid #E5E7EB;
        }

        .section-content {
            margin-bottom: 15px;
        }

        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table th {
            background-color: #3B82F6;
            color: white;
            padding: 10px;
            text-align: left;
            font-weight: bold;
            font-size: 10pt;
        }

        table td {
            padding: 8px 10px;
            border-bottom: 1px solid #E5E7EB;
            font-size: 10pt;
        }

        table tr:last-child td {
            border-bottom: none;
        }

        table tr:nth-child(even) {
            background-color: #F9FAFB;
        }

        .table-total {
            font-weight: bold;
            background-color: #EFF6FF !important;
        }

        /* Info Boxes */
        .info-box {
            background-color: #EFF6FF;
            border-left: 4px solid #3B82F6;
            padding: 15px;
            margin-bottom: 20px;
        }

        .warning-box {
            background-color: #FEF3C7;
            border-left: 4px solid #F59E0B;
            padding: 15px;
            margin-bottom: 20px;
        }

        /* Two Column Layout */
        .two-columns {
            display: flex;
            justify-content: space-between;
            gap: 20px;
        }

        .column {
            flex: 1;
        }

        .column-50 {
            width: 48%;
        }

        /* Party Information */
        .party-info {
            background-color: #F9FAFB;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .party-info h3 {
            font-size: 12pt;
            font-weight: bold;
            color: #3B82F6;
            margin-bottom: 10px;
        }

        .party-info p {
            margin-bottom: 5px;
            line-height: 1.5;
        }

        /* Signatures */
        .signatures {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }

        .signature-block {
            width: 45%;
        }

        .signature-line {
            border-top: 1px solid #333;
            margin-top: 60px;
            padding-top: 5px;
            text-align: center;
        }

        .signature-label {
            font-size: 9pt;
            color: #666;
        }

        /* Footer */
        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 1px solid #E5E7EB;
            font-size: 8pt;
            color: #666;
            text-align: center;
        }

        .footer-row {
            margin-bottom: 5px;
        }

        /* Text Utilities */
        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-bold {
            font-weight: bold;
        }

        .text-muted {
            color: #6B7280;
        }

        .text-small {
            font-size: 9pt;
        }

        .mb-10 {
            margin-bottom: 10px;
        }

        .mb-20 {
            margin-bottom: 20px;
        }

        .mt-20 {
            margin-top: 20px;
        }

        /* Amount/Currency Formatting */
        .amount {
            font-weight: bold;
            color: #059669;
        }

        .amount-large {
            font-size: 14pt;
            font-weight: bold;
            color: #059669;
        }

        /* Page Break Control */
        .page-break {
            page-break-after: always;
        }

        .no-break {
            page-break-inside: avoid;
        }
    </style>
    @yield('additional-styles')
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-logo">
                {{ config('app.name', 'SolarApp') }}
            </div>
            <div class="header-info">
                @yield('header-info')
            </div>
        </div>

        <!-- Main Content -->
        @yield('content')

        <!-- Footer -->
        <div class="footer">
            @yield('footer', '')
            <div class="footer-row">
                {{ config('app.name') }} | {{ trans('contracts.generated_on', [], $locale ?? 'en') }}: {{ now()->format('d.m.Y H:i') }}
            </div>
            <div class="footer-row">
                {{ trans('contracts.document_reference', [], $locale ?? 'en') }}: @yield('document-reference', 'N/A')
            </div>
        </div>
    </div>
</body>
</html>
