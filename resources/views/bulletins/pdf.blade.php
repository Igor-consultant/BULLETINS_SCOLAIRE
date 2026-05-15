<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bulletin trimestriel</title>
    @php
        $logoPath = public_path('images/logo_i3p.jpg');
        $logoSrc = file_exists($logoPath)
            ? 'data:image/jpeg;base64,'.base64_encode(file_get_contents($logoPath))
            : '';
    @endphp
    <style>
        @page {
            margin: 18px 22px 22px 22px;
        }

        body {
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            color: #111827;
            font-size: 11px;
            margin: 0;
            background: #ffffff;
        }

        .header-table,
        .identity-table,
        .results-table,
        .summary-table,
        .signatures-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: top;
        }

        .header-left {
            width: 8%;
        }

        .header-middle {
            width: 56%;
            text-align: left;
        }

        .header-right {
            width: 36%;
            text-align: right;
        }

        .top-title {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .top-line {
            margin-top: 2px;
            line-height: 1.45;
        }

        .logo {
            width: 46px;
            height: auto;
            margin: 0;
            display: block;
        }

        .bulletin-title {
            margin-top: 16px;
            text-align: center;
        }

        .bulletin-title h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: 0.04em;
            color: #17763c;
            text-transform: uppercase;
        }

        .bulletin-title p {
            margin: 4px 0 0 0;
            font-size: 12px;
        }

        .identity-table {
            margin-top: 14px;
            font-size: 11px;
        }

        .identity-table td {
            padding: 4px 6px;
            border-bottom: 1px solid #d1d5db;
        }

        .identity-label {
            width: 18%;
            font-weight: 700;
        }

        .identity-value {
            width: 32%;
            color: #9a1f1f;
            font-weight: 700;
        }

        .identity-context {
            font-weight: 700;
            color: #17763c;
        }

        .results-table {
            margin-top: 14px;
            border: 1px solid #111827;
            font-size: 10px;
        }

        .results-table th,
        .results-table td {
            border: 1px solid #111827;
            padding: 4px 5px;
        }

        .results-table th {
            text-transform: uppercase;
            font-size: 9px;
            line-height: 1.25;
            text-align: center;
            background: #f3f4f6;
        }

        .results-table td {
            vertical-align: middle;
        }

        .text-left {
            text-align: left;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .nowrap {
            white-space: nowrap;
        }

        .totals-row td {
            font-weight: 700;
            background: #f9fafb;
        }

        .summary-table {
            margin-top: 10px;
            font-size: 11px;
        }

        .summary-table td {
            padding: 4px 6px;
        }

        .summary-label {
            font-weight: 700;
        }

        .summary-accent {
            color: #9a1f1f;
            font-weight: 700;
        }

        .appreciation-box {
            margin-top: 8px;
            border: 1px solid #111827;
            padding: 8px 10px;
            min-height: 44px;
        }

        .signatures-table {
            margin-top: 14px;
        }

        .signatures-table td {
            width: 50%;
            padding-top: 24px;
            vertical-align: top;
        }

        .signature-line {
            margin-top: 42px;
            border-top: 1px solid #111827;
            padding-top: 4px;
            width: 75%;
        }

        .footer-note {
            margin-top: 12px;
            font-size: 10px;
            color: #4b5563;
            text-align: right;
        }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td class="header-left">
                <img src="{{ $logoSrc }}" alt="Logo I3P" class="logo">
            </td>
            <td class="header-middle">
                <div class="top-title">{{ $header['institution_name'] }}</div>
                <div class="top-line">{{ $header['contact_line'] }}</div>
                <div class="top-line">{{ $header['email_line'] }}</div>
            </td>
            <td class="header-right">
                <div class="top-title">{{ $header['republic_title'] }}</div>
                <div class="top-line">{{ $header['republic_subtitle'] }}</div>
            </td>
        </tr>
    </table>

    <div class="bulletin-title">
        <h1>Bulletin de notes</h1>
        <p>Du {{ $trimestre->libelle }} - {{ $annee?->libelle ?? 'Annee non definie' }}</p>
    </div>

    <table class="identity-table">
        <tr>
            <td class="identity-label">De l'eleve :</td>
            <td class="identity-value">{{ $eleve->nom }} {{ $eleve->prenoms }}</td>
            <td class="identity-label">Matricule</td>
            <td>{{ $eleve->matricule }}</td>
        </tr>
        <tr>
            <td colspan="2" class="identity-context">{{ $classe?->filiere?->nom ?? 'Filiere non definie' }}</td>
            <td colspan="2" class="identity-context">{{ $classe?->code }} {{ $classe?->nom }}</td>
        </tr>
        <tr>
            <td class="identity-label">Date d'emission</td>
            <td>{{ $dateEmission->format('d/m/Y') }}</td>
            <td class="identity-label">Effectif</td>
            <td>{{ $synthese['effectif'] }} eleve(s)</td>
        </tr>
    </table>

    <table class="results-table">
        <thead>
            <tr>
                <th class="text-left">Disciplines</th>
                <th>Note de classe</th>
                <th>Compo.</th>
                <th>Moyenne<br>sur 20</th>
                <th>Coef.</th>
                <th>Moy. x coef</th>
                <th>Rang</th>
                <th class="text-left">Nom professeur</th>
                <th class="text-left">Appreciation</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($lignes as $ligne)
                <tr>
                    <td class="text-left">{{ $ligne['matiere'] }}</td>
                    <td class="text-center nowrap">{{ $ligne['moyenne_devoirs'] !== null ? number_format((float) $ligne['moyenne_devoirs'], 2, ',', ' ') : '--' }}</td>
                    <td class="text-center nowrap">{{ $ligne['composition'] !== null ? number_format((float) $ligne['composition'], 2, ',', ' ') : '--' }}</td>
                    <td class="text-center nowrap">{{ $ligne['moyenne_matiere'] !== null ? number_format((float) $ligne['moyenne_matiere'], 2, ',', ' ') : '--' }}</td>
                    <td class="text-center">{{ rtrim(rtrim(number_format((float) $ligne['coefficient'], 2, '.', ''), '0'), '.') }}</td>
                    <td class="text-center nowrap">{{ $ligne['points'] !== null ? number_format((float) $ligne['points'], 2, ',', ' ') : '--' }}</td>
                    <td class="text-center">{{ $ligne['rang'] ?? '--' }}</td>
                    <td class="text-left">{{ $ligne['professeur'] ?? '--' }}</td>
                    <td class="text-left">{{ $ligne['appreciation'] ?? '--' }}</td>
                </tr>
            @endforeach
            <tr class="totals-row">
                <td class="text-left">Totaux</td>
                <td></td>
                <td></td>
                <td class="text-center">{{ $synthese['moyenne_generale'] !== null ? number_format($synthese['moyenne_generale'], 2, ',', ' ') : '--' }}</td>
                <td class="text-center">{{ rtrim(rtrim(number_format((float) $synthese['total_coefficients'], 2, '.', ''), '0'), '.') }}</td>
                <td class="text-center">{{ number_format($synthese['total_points'], 2, ',', ' ') }}</td>
                <td class="text-center">{{ $synthese['rang'] ?? '--' }}</td>
                <td colspan="2"></td>
            </tr>
        </tbody>
    </table>

    <table class="summary-table">
        <tr>
            <td class="summary-label">Premier:</td>
            <td>{{ $synthese['premier'] !== null ? number_format($synthese['premier'], 2, ',', ' ') : '--' }}</td>
            <td class="summary-label">Dernier:</td>
            <td>{{ $synthese['dernier'] !== null ? number_format($synthese['dernier'], 2, ',', ' ') : '--' }}</td>
        </tr>
        <tr>
            <td class="summary-label">Moyenne:</td>
            <td class="summary-accent">{{ $synthese['moyenne_generale'] !== null ? number_format($synthese['moyenne_generale'], 2, ',', ' ') : '--' }}</td>
            <td class="summary-label">Rang:</td>
            <td><span class="summary-accent">{{ $synthese['rang'] ?? '--' }}</span> sur {{ $synthese['effectif'] }}</td>
        </tr>
        <tr>
            <td class="summary-label">Appreciation:</td>
            <td colspan="3" class="summary-accent">{{ $synthese['appreciation_generale'] }}</td>
        </tr>
        <tr>
            <td class="summary-label">Sanction:</td>
            <td colspan="3">{{ $synthese['sanction'] }}</td>
        </tr>
    </table>

    <div class="appreciation-box">
        {{ $synthese['appreciation_generale'] }}
    </div>

    <table class="signatures-table">
        <tr>
            <td></td>
            <td>
                <div class="summary-label">Direction</div>
                <div class="signature-line">Cachet / Signature</div>
            </td>
        </tr>
    </table>

    <div class="footer-note">
        Emission automatique I3P - {{ $dateEmission->format('d/m/Y H:i') }}
    </div>
</body>
</html>
