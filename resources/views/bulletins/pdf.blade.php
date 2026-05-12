<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bulletin trimestriel</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #0f172a;
            font-size: 12px;
            margin: 26px;
            background: #ffffff;
        }

        .header {
            border: 1px solid #cbd5e1;
            border-top: 6px solid #7d221b;
            border-radius: 18px;
            padding: 18px 20px;
            margin-bottom: 18px;
            background: linear-gradient(180deg, #fffdfb 0%, #ffffff 100%);
        }

        .header-table,
        .meta-table,
        .results-table {
            width: 100%;
            border-collapse: collapse;
        }

        .logo-cell {
            width: 90px;
            vertical-align: top;
        }

        .logo {
            width: 72px;
            height: auto;
        }

        .title {
            font-size: 25px;
            font-weight: 700;
            margin: 0 0 6px 0;
            color: #7d221b;
        }

        .institution {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: #0f4d6a;
            margin-bottom: 6px;
        }

        .subtitle {
            margin: 0;
            color: #475569;
            line-height: 1.5;
        }

        .box {
            border: 1px solid #cbd5e1;
            border-radius: 14px;
            padding: 14px 16px;
            margin-bottom: 16px;
            background: #ffffff;
        }

        .box-title {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #7d221b;
            margin-bottom: 10px;
        }

        .meta-table td {
            padding: 6px 8px;
            vertical-align: top;
        }

        .meta-label {
            font-weight: 700;
            color: #334155;
            width: 130px;
        }

        .summary-table {
            width: 100%;
            margin-bottom: 16px;
            border-collapse: separate;
            border-spacing: 10px 0;
        }

        .summary-card {
            border: 1px solid #cbd5e1;
            border-radius: 14px;
            padding: 12px 14px;
            background: #f8fafc;
            min-height: 74px;
        }

        .summary-label {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #64748b;
        }

        .summary-value {
            margin-top: 8px;
            font-size: 20px;
            font-weight: 700;
            color: #0f4d6a;
        }

        .results-table th {
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-size: 10px;
            text-align: left;
            color: #475569;
            border-bottom: 1px solid #94a3b8;
            padding: 10px 8px;
            background: #f8fafc;
        }

        .results-table td {
            border-bottom: 1px solid #e2e8f0;
            padding: 10px 8px;
        }

        .results-table tbody tr:nth-child(even) td {
            background: #fcfcfd;
        }

        .results-table tfoot td {
            border-top: 1px solid #94a3b8;
            border-bottom: none;
            font-weight: 700;
            background: #f8fafc;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border: 1px solid #a7f3d0;
            border-radius: 999px;
            background: #ecfdf5;
            color: #047857;
            font-weight: 700;
        }

        .footer-grid {
            width: 100%;
            margin-top: 20px;
            border-collapse: separate;
            border-spacing: 14px 0;
        }

        .signature-box {
            border: 1px solid #cbd5e1;
            border-radius: 14px;
            padding: 14px 16px 32px 16px;
            vertical-align: top;
        }

        .signature-title {
            font-weight: 700;
            color: #334155;
            margin-bottom: 38px;
        }

        .signature-line {
            border-top: 1px solid #94a3b8;
            padding-top: 6px;
            color: #64748b;
            font-size: 11px;
        }

        .document-note {
            margin-top: 18px;
            font-size: 10px;
            line-height: 1.6;
            color: #64748b;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header">
        <table class="header-table">
            <tr>
                <td class="logo-cell">
                    <img src="{{ public_path('images/logo_i3p.jpg') }}" alt="Logo I3P" class="logo">
                </td>
                <td>
                    <div class="institution">Institut I3P</div>
                    <div class="title">Bulletin trimestriel</div>
                    <p class="subtitle">
                        Institut I3P - Bulletin scolaire de demonstration etabli pour
                        <strong>{{ $eleve->nom }} {{ $eleve->prenoms }}</strong>.
                    </p>
                </td>
            </tr>
        </table>
    </div>

    <div class="box">
        <div class="box-title">Identite scolaire</div>
        <table class="meta-table">
            <tr>
                <td class="meta-label">Matricule</td>
                <td>{{ $eleve->matricule }}</td>
                <td class="meta-label">Annee scolaire</td>
                <td>{{ $annee?->libelle ?? 'Non definie' }}</td>
            </tr>
            <tr>
                <td class="meta-label">Classe</td>
                <td>{{ $classe?->code }} - {{ $classe?->nom }}</td>
                <td class="meta-label">Trimestre</td>
                <td>{{ $trimestre->libelle }}</td>
            </tr>
            <tr>
                <td class="meta-label">Filiere</td>
                <td>{{ $classe?->filiere?->nom ?? 'Non definie' }}</td>
                <td class="meta-label">Date emission</td>
                <td>{{ $dateEmission->format('d/m/Y H:i') }}</td>
            </tr>
        </table>
    </div>

    <table class="summary-table">
        <tr>
            <td>
                <div class="summary-card">
                    <div class="summary-label">Moyenne generale</div>
                    <div class="summary-value">{{ $synthese['moyenne_generale'] !== null ? number_format($synthese['moyenne_generale'], 2, ',', ' ') : 'N/D' }}</div>
                </div>
            </td>
            <td>
                <div class="summary-card">
                    <div class="summary-label">Total points</div>
                    <div class="summary-value">{{ number_format($synthese['total_points'], 2, ',', ' ') }}</div>
                </div>
            </td>
            <td>
                <div class="summary-card">
                    <div class="summary-label">Rang</div>
                    <div class="summary-value">{{ $synthese['rang'] ?? 'N/D' }}</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="box">
        <div class="box-title">Resultats par matiere</div>
        <table class="results-table">
            <thead>
                <tr>
                    <th>Matiere</th>
                    <th>Coef.</th>
                    <th>Moy. devoirs</th>
                    <th>Composition</th>
                    <th>Moyenne matiere</th>
                    <th>Points</th>
                    <th>Rang</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($resultats as $resultat)
                    <tr>
                        <td><strong>{{ $resultat->matiere?->libelle }}</strong></td>
                        <td>{{ rtrim(rtrim(number_format((float) $resultat->coefficient, 2, '.', ''), '0'), '.') }}</td>
                        <td>{{ $resultat->moyenne_devoirs !== null ? number_format((float) $resultat->moyenne_devoirs, 2, ',', ' ') : 'N/D' }}</td>
                        <td>{{ $resultat->composition !== null ? number_format((float) $resultat->composition, 2, ',', ' ') : 'N/D' }}</td>
                        <td>
                            @if ($resultat->moyenne_matiere !== null)
                                <span class="badge">{{ number_format((float) $resultat->moyenne_matiere, 2, ',', ' ') }}</span>
                            @else
                                N/D
                            @endif
                        </td>
                        <td>{{ $resultat->points !== null ? number_format((float) $resultat->points, 2, ',', ' ') : 'N/D' }}</td>
                        <td>{{ $resultat->rang ?? 'N/D' }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td>Totaux</td>
                    <td>{{ rtrim(rtrim(number_format((float) $synthese['total_coefficients'], 2, '.', ''), '0'), '.') }}</td>
                    <td></td>
                    <td></td>
                    <td>{{ $synthese['moyenne_generale'] !== null ? number_format($synthese['moyenne_generale'], 2, ',', ' ') : 'N/D' }}</td>
                    <td>{{ number_format($synthese['total_points'], 2, ',', ' ') }}</td>
                    <td>{{ $synthese['rang'] ?? 'N/D' }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <table class="footer-grid">
        <tr>
            <td class="signature-box">
                <div class="box-title">Appreciation generale</div>
                <div style="color:#475569; line-height:1.7;">
                    Bulletin de demonstration I3P. Cette zone pourra accueillir l appreciation du conseil de classe
                    et les observations pedagogiques.
                </div>
            </td>
            <td class="signature-box">
                <div class="box-title">Visa administratif</div>
                <div class="signature-title">Direction</div>
                <div class="signature-line">Cachet / Signature</div>
            </td>
        </tr>
    </table>

    <div class="document-note">
        Emission : {{ $dateEmission->format('d/m/Y H:i') }} - Generation automatique I3P
    </div>
</body>
</html>
