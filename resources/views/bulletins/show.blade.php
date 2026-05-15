<x-app-layout>
    <x-slot name="header">
        <div class="i3p-bulletin">
            <div class="flex flex-col gap-8 lg:flex-row lg:items-start lg:justify-between">
                <div class="flex items-start gap-5">
                    <img src="{{ asset('images/logo_i3p.jpg') }}" alt="Logo I3P" class="h-20 w-auto rounded-2xl border border-slate-200 bg-white p-2 shadow-sm">
                    <div>
                        <p class="i3p-kicker text-[#b02f25]">Institut I3P</p>
                        <h1 class="i3p-title mt-2">Bulletin trimestriel</h1>
                        <p class="mt-3 max-w-3xl text-[15px] leading-7 text-slate-600">
                            Document scolaire de demonstration etabli a partir des resultats enregistres pour
                            <span class="font-bold text-slate-900">{{ $eleve->nom }} {{ $eleve->prenoms }}</span>.
                        </p>
                    </div>
                </div>

                <div class="w-full max-w-xl rounded-[1.75rem] border border-[#10233d]/10 bg-gradient-to-br from-[#10233d] to-[#1f4765] p-6 text-white shadow-[0_18px_60px_rgba(15,23,42,0.18)]">
                    <p class="i3p-kicker text-[#f0c5ba]">Reference academique</p>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        <div class="rounded-2xl border border-white/10 bg-white/10 px-4 py-3">
                            <div class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-200">Annee</div>
                            <div class="mt-2 text-[15px] font-bold">{{ $annee?->libelle ?? 'Non definie' }}</div>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/10 px-4 py-3">
                            <div class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-200">Trimestre</div>
                            <div class="mt-2 text-[15px] font-bold">{{ $trimestre->libelle }}</div>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/10 px-4 py-3">
                            <div class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-200">Classe</div>
                            <div class="mt-2 text-[15px] font-bold">{{ $classe?->code }} - {{ $classe?->nom }}</div>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/10 px-4 py-3">
                            <div class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-200">Filiere</div>
                            <div class="mt-2 text-[15px] font-bold">{{ $classe?->filiere?->nom ?? 'Non definie' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="i3p-container mt-8 space-y-8">
        <section class="grid gap-5 xl:grid-cols-[1.15fr_0.85fr]">
            <article class="i3p-bulletin">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="i3p-kicker text-[#b02f25]">Lecture du bulletin</p>
                        <h2 class="i3p-section-title mt-2">Synthese de publication</h2>
                        <p class="mt-3 max-w-2xl text-[14px] leading-7 text-slate-600">
                            Cette vue doit permettre de controler rapidement la synthese eleve avant export PDF ou generation en lot.
                        </p>
                    </div>
                    <span class="i3p-badge border-slate-200 bg-slate-100 text-slate-700">{{ $resultats->count() }} matiere(s)</span>
                </div>

                <div class="mt-6 i3p-bulletin-grid">
                    <article class="i3p-bulletin-cell">
                        <div class="i3p-label">Eleve</div>
                        <div class="mt-3 text-[1.15rem] font-bold text-slate-900">{{ $eleve->nom }} {{ $eleve->prenoms }}</div>
                        <div class="i3p-bulletin-meta mt-2">Matricule : {{ $eleve->matricule }}</div>
                    </article>
                    <article class="i3p-bulletin-cell">
                        <div class="i3p-label">Moyenne generale</div>
                        <div class="i3p-metric mt-3 text-[#0f4d6a]">
                            {{ $synthese['moyenne_generale'] !== null ? number_format($synthese['moyenne_generale'], 2, ',', ' ') : 'N/D' }}
                        </div>
                        <div class="i3p-bulletin-meta mt-2">{{ $synthese['appreciation_generale'] }}</div>
                    </article>
                    <article class="i3p-bulletin-cell">
                        <div class="i3p-label">Total points</div>
                        <div class="i3p-metric mt-3 text-[#8e251d]">{{ number_format($synthese['total_points'], 2, ',', ' ') }}</div>
                    </article>
                    <article class="i3p-bulletin-cell">
                        <div class="i3p-label">Rang</div>
                        <div class="i3p-metric mt-3 text-[#0f4d6a]">{{ $synthese['rang'] ?? 'N/D' }}</div>
                        <div class="i3p-bulletin-meta mt-2">{{ $synthese['effectif'] }} eleve(s) dans la classe</div>
                    </article>
                </div>
            </article>

            <article class="i3p-bulletin">
                <p class="i3p-kicker text-[#b02f25]">Actions finales</p>
                <h2 class="i3p-section-title mt-2">Publication</h2>
                <div class="mt-6 space-y-4">
                    <a href="{{ route('resultats.trimestriels') }}" class="i3p-action-row">
                        <span class="font-bold text-slate-950">Retour aux resultats</span>
                        <span class="text-sm text-slate-500">Revenir a la vue de validation trimestrielle</span>
                    </a>
                    <a href="{{ route('bulletins.pdf', [$eleve, $trimestre]) }}" class="i3p-action-row">
                        <span class="font-bold text-slate-950">Exporter le bulletin PDF</span>
                        <span class="text-sm text-slate-500">Obtenir le document diffus able</span>
                    </a>
                    <a href="{{ route('bulletins.lots') }}" class="i3p-action-row">
                        <span class="font-bold text-slate-950">Ouvrir la generation en lot</span>
                        <span class="text-sm text-slate-500">Produire une archive ZIP pour toute une classe</span>
                    </a>
                </div>
            </article>
        </section>

        <section class="i3p-bulletin">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="i3p-kicker text-[#b02f25]">Releve academique</p>
                    <h2 class="i3p-section-title mt-2">Resultats par matiere</h2>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('resultats.trimestriels') }}" class="i3p-link !border-[#0ca6e8]/20 !bg-[#0ca6e8]/10 !text-[#0f4d6a]">
                        Retour aux resultats
                    </a>
                    <a href="{{ route('bulletins.pdf', [$eleve, $trimestre]) }}" class="i3p-link !border-[#b02f25]/20 !bg-[#b02f25]/10 !text-[#7d221b]">
                        Export PDF
                    </a>
                </div>
            </div>

            <div class="mt-6 overflow-x-auto">
                <table class="i3p-table i3p-bulletin-table">
                    <thead>
                        <tr class="text-left">
                            <th>Matiere</th>
                            <th>Coef.</th>
                            <th>Note de classe</th>
                            <th>Composition</th>
                            <th>Moyenne / 20</th>
                            <th>Points</th>
                            <th>Rang</th>
                            <th>Professeur</th>
                            <th>Appreciation</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($lignes as $ligne)
                            <tr class="last:border-b-0">
                                <td class="font-bold text-slate-900">{{ $ligne['matiere'] }}</td>
                                <td class="text-slate-700">{{ rtrim(rtrim(number_format((float) $ligne['coefficient'], 2, '.', ''), '0'), '.') }}</td>
                                <td class="text-slate-700">{{ $ligne['moyenne_devoirs'] !== null ? number_format((float) $ligne['moyenne_devoirs'], 2, ',', ' ') : 'N/D' }}</td>
                                <td class="text-slate-700">{{ $ligne['composition'] !== null ? number_format((float) $ligne['composition'], 2, ',', ' ') : 'N/D' }}</td>
                                <td>
                                    <span class="i3p-badge border-emerald-200 bg-emerald-50 text-emerald-700">
                                        {{ $ligne['moyenne_matiere'] !== null ? number_format((float) $ligne['moyenne_matiere'], 2, ',', ' ') : 'N/D' }}
                                    </span>
                                </td>
                                <td class="text-slate-700">{{ $ligne['points'] !== null ? number_format((float) $ligne['points'], 2, ',', ' ') : 'N/D' }}</td>
                                <td class="text-slate-700">{{ $ligne['rang'] ?? 'N/D' }}</td>
                                <td class="text-slate-700">{{ $ligne['professeur'] ?? 'N/D' }}</td>
                                <td class="text-slate-700">{{ $ligne['appreciation'] ?? 'N/D' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td class="font-bold text-slate-900">Totaux</td>
                            <td class="font-bold text-slate-900">{{ rtrim(rtrim(number_format((float) $synthese['total_coefficients'], 2, '.', ''), '0'), '.') }}</td>
                            <td></td>
                            <td></td>
                            <td class="font-bold text-slate-900">{{ $synthese['moyenne_generale'] !== null ? number_format($synthese['moyenne_generale'], 2, ',', ' ') : 'N/D' }}</td>
                            <td class="font-bold text-slate-900">{{ number_format($synthese['total_points'], 2, ',', ' ') }}</td>
                            <td class="font-bold text-slate-900">{{ $synthese['rang'] ?? 'N/D' }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="mt-8 grid gap-4 lg:grid-cols-[1fr_0.8fr]">
                <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-5">
                    <p class="i3p-kicker text-[#b02f25]">Appreciation generale</p>
                    <p class="mt-3 text-[14px] leading-7 text-slate-700">
                        {{ $synthese['appreciation_generale'] }}
                    </p>
                    <div class="mt-4 text-sm text-slate-500">
                        Premier: {{ $synthese['premier'] !== null ? number_format($synthese['premier'], 2, ',', ' ') : 'N/D' }}
                        | Dernier: {{ $synthese['dernier'] !== null ? number_format($synthese['dernier'], 2, ',', ' ') : 'N/D' }}
                    </div>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <p class="i3p-kicker text-[#b02f25]">Visa administratif</p>
                    <div class="mt-3 text-sm text-slate-600">Sanction: {{ $synthese['sanction'] }}</div>
                    <div class="mt-6 grid gap-6 sm:grid-cols-2">
                        <div>
                            <div class="text-[13px] font-bold text-slate-700">Titulaire</div>
                            <div class="mt-12 border-t border-slate-300 pt-2 text-[12px] text-slate-500">Signature</div>
                        </div>
                        <div>
                            <div class="text-[13px] font-bold text-slate-700">Direction</div>
                            <div class="mt-12 border-t border-slate-300 pt-2 text-[12px] text-slate-500">Cachet / Signature</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-app-layout>
