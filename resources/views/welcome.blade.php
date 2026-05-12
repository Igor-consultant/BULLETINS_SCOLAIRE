<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name') }}</title>
        <style>
            :root {
                --bg: #f5efe5;
                --panel: rgba(255, 255, 255, 0.78);
                --panel-strong: #ffffff;
                --ink: #10233d;
                --muted: #5f6f82;
                --line: rgba(16, 35, 61, 0.1);
                --accent: #c96d2d;
                --accent-dark: #8e4518;
                --success: #2b7a4b;
                --shadow: 0 18px 50px rgba(30, 49, 74, 0.12);
            }

            * {
                box-sizing: border-box;
            }

            body {
                margin: 0;
                font-family: Georgia, "Times New Roman", serif;
                color: var(--ink);
                background:
                    radial-gradient(circle at top left, rgba(201, 109, 45, 0.18), transparent 30%),
                    radial-gradient(circle at top right, rgba(43, 122, 75, 0.16), transparent 28%),
                    linear-gradient(180deg, #f8f4ec 0%, #f3ecdf 100%);
                min-height: 100vh;
            }

            .page {
                width: min(1180px, calc(100% - 32px));
                margin: 0 auto;
                padding: 32px 0 48px;
            }

            .hero {
                background: linear-gradient(135deg, rgba(16, 35, 61, 0.95), rgba(29, 58, 88, 0.9));
                color: #fff8f0;
                border-radius: 28px;
                padding: 36px;
                box-shadow: var(--shadow);
                position: relative;
                overflow: hidden;
            }

            .hero::after {
                content: "";
                position: absolute;
                inset: auto -80px -80px auto;
                width: 240px;
                height: 240px;
                border-radius: 50%;
                background: rgba(201, 109, 45, 0.16);
            }

            .eyebrow {
                display: inline-block;
                padding: 8px 14px;
                border-radius: 999px;
                background: rgba(255, 255, 255, 0.1);
                color: #ffe7d6;
                font-size: 12px;
                letter-spacing: 0.1em;
                text-transform: uppercase;
            }

            h1 {
                margin: 18px 0 10px;
                font-size: clamp(2rem, 5vw, 4rem);
                line-height: 0.95;
                max-width: 760px;
            }

            .hero p {
                margin: 0;
                max-width: 720px;
                color: rgba(255, 248, 240, 0.84);
                font-size: 1.03rem;
                line-height: 1.65;
            }

            .hero-meta {
                margin-top: 22px;
                display: flex;
                flex-wrap: wrap;
                gap: 12px;
            }

            .chip {
                padding: 10px 14px;
                border-radius: 14px;
                background: rgba(255, 255, 255, 0.08);
                border: 1px solid rgba(255, 255, 255, 0.09);
                font-size: 0.95rem;
            }

            .grid {
                display: grid;
                grid-template-columns: 1.15fr 0.85fr;
                gap: 22px;
                margin-top: 22px;
            }

            .card {
                background: var(--panel);
                backdrop-filter: blur(10px);
                border: 1px solid var(--line);
                border-radius: 24px;
                box-shadow: var(--shadow);
                padding: 24px;
            }

            .card h2 {
                margin: 0 0 14px;
                font-size: 1.25rem;
            }

            .stats {
                display: grid;
                grid-template-columns: repeat(4, minmax(0, 1fr));
                gap: 14px;
            }

            .stat {
                background: var(--panel-strong);
                border: 1px solid var(--line);
                border-radius: 18px;
                padding: 18px;
            }

            .stat .label {
                font-size: 0.9rem;
                color: var(--muted);
            }

            .stat .value {
                margin-top: 8px;
                font-size: 2rem;
                font-weight: bold;
                color: var(--accent-dark);
            }

            .section-list {
                display: grid;
                gap: 12px;
            }

            .row {
                display: flex;
                justify-content: space-between;
                gap: 16px;
                padding: 15px 18px;
                border-radius: 16px;
                background: rgba(255, 255, 255, 0.72);
                border: 1px solid var(--line);
            }

            .row strong {
                display: block;
                margin-bottom: 4px;
            }

            .muted {
                color: var(--muted);
                font-size: 0.95rem;
            }

            .badge {
                align-self: start;
                padding: 8px 12px;
                border-radius: 999px;
                font-size: 0.82rem;
                background: rgba(43, 122, 75, 0.12);
                color: var(--success);
                border: 1px solid rgba(43, 122, 75, 0.16);
                white-space: nowrap;
            }

            .todo {
                margin: 0;
                padding-left: 18px;
                color: var(--muted);
                line-height: 1.7;
            }

            .footer-note {
                margin-top: 22px;
                padding: 18px 20px;
                border-radius: 18px;
                background: rgba(201, 109, 45, 0.1);
                border: 1px solid rgba(201, 109, 45, 0.18);
                color: #6f3d18;
            }

            @media (max-width: 900px) {
                .grid {
                    grid-template-columns: 1fr;
                }

                .stats {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }
            }

            @media (max-width: 560px) {
                .page {
                    width: min(100% - 20px, 1180px);
                    padding-top: 20px;
                }

                .hero,
                .card {
                    padding: 20px;
                    border-radius: 20px;
                }

                .stats {
                    grid-template-columns: 1fr;
                }

                .row {
                    flex-direction: column;
                }
            }
        </style>
    </head>
    <body>
        <div class="page">
            <section class="hero">
                <span class="eyebrow">Institut Polytechnique Pierre Prie</span>
                <h1>{{ config('app.name') }}</h1>
                <p>
                    Socle local de gestion des bulletins scolaires pour l'I3P. Cette premiere version
                    confirme l'installation Laravel, la connexion MariaDB et la presence des premiers
                    referentiels metier du projet.
                </p>

                <div class="hero-meta">
                    <div class="chip">URL de test : {{ config('app.url') }}</div>
                    <div class="chip">Locale : {{ app()->getLocale() }}</div>
                    <div class="chip">Fuseau : {{ config('app.timezone') }}</div>
                </div>
            </section>

            <section class="grid">
                <article class="card">
                    <h2>Etat du socle Lot 1</h2>

                    <div class="stats">
                        <div class="stat">
                            <div class="label">Annees scolaires</div>
                            <div class="value">{{ $stats['annees'] }}</div>
                        </div>
                        <div class="stat">
                            <div class="label">Trimestres</div>
                            <div class="value">{{ $stats['trimestres'] }}</div>
                        </div>
                        <div class="stat">
                            <div class="label">Filieres</div>
                            <div class="value">{{ $stats['filieres'] }}</div>
                        </div>
                        <div class="stat">
                            <div class="label">Classes</div>
                            <div class="value">{{ $stats['classes'] }}</div>
                        </div>
                    </div>

                    <div class="footer-note">
                        Base de donnees connectee sur MariaDB XAMPP et seed de demonstration injecte.
                    </div>
                </article>

                <article class="card">
                    <h2>Annee active</h2>

                    @if ($anneeActive)
                        <div class="row">
                            <div>
                                <strong>{{ $anneeActive->libelle }}</strong>
                                <div class="muted">
                                    Du {{ $anneeActive->date_debut?->format('d/m/Y') }} au
                                    {{ $anneeActive->date_fin?->format('d/m/Y') }}
                                </div>
                            </div>
                            <span class="badge">{{ ucfirst($anneeActive->statut) }}</span>
                        </div>
                    @else
                        <div class="row">
                            <div>
                                <strong>Aucune annee active</strong>
                                <div class="muted">Le referentiel n'a pas encore ete renseigne.</div>
                            </div>
                        </div>
                    @endif

                    <h2 style="margin-top: 22px;">Prochaines briques</h2>
                    <ol class="todo">
                        <li>Roles et permissions</li>
                        <li>Tableau de bord d'administration</li>
                        <li>Gestion des matieres et coefficients</li>
                        <li>Dossiers eleves et inscriptions</li>
                    </ol>
                </article>
            </section>

            <section class="grid">
                <article class="card">
                    <h2>Trimestres de reference</h2>
                    <div class="section-list">
                        @forelse ($anneeActive?->trimestres?->sortBy('ordre') ?? [] as $trimestre)
                            <div class="row">
                                <div>
                                    <strong>{{ $trimestre->libelle }}</strong>
                                    <div class="muted">
                                        Ordre {{ $trimestre->ordre }}
                                        @if ($trimestre->date_debut && $trimestre->date_fin)
                                            · du {{ $trimestre->date_debut->format('d/m/Y') }} au
                                            {{ $trimestre->date_fin->format('d/m/Y') }}
                                        @endif
                                    </div>
                                </div>
                                <span class="badge">{{ ucfirst($trimestre->statut) }}</span>
                            </div>
                        @empty
                            <div class="row">
                                <div>
                                    <strong>Aucun trimestre</strong>
                                    <div class="muted">Les periodes scolaires ne sont pas encore configurees.</div>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </article>

                <article class="card">
                    <h2>Classes initialisees</h2>
                    <div class="section-list">
                        @forelse ($anneeActive?->classes?->sortBy('code') ?? [] as $classe)
                            <div class="row">
                                <div>
                                    <strong>{{ $classe->code }} - {{ $classe->nom }}</strong>
                                    <div class="muted">
                                        Filiere : {{ $classe->filiere?->nom ?? 'Non definie' }}
                                    </div>
                                </div>
                                <span class="badge">{{ $classe->actif ? 'Active' : 'Inactive' }}</span>
                            </div>
                        @empty
                            <div class="row">
                                <div>
                                    <strong>Aucune classe</strong>
                                    <div class="muted">Le referentiel des classes est vide.</div>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </article>
            </section>
        </div>
    </body>
</html>
