<x-app-layout>
    <x-slot name="header">
        <div class="i3p-card overflow-hidden">
            <div class="grid gap-8 px-6 py-8 lg:grid-cols-[1.15fr_0.85fr] lg:px-8">
                <div>
                    <span class="i3p-badge border-[#b02f25]/20 bg-[#b02f25]/10 text-[#8e251d]">Pilotage direction</span>
                    <h1 class="i3p-title mt-4">Tableau de bord direction</h1>
                    <p class="i3p-copy mt-3 max-w-3xl">
                        Vue synthetique de la scolarite, des bulletins, de la comptabilite et de l activite recente du logiciel.
                    </p>
                </div>

                <div class="rounded-[2rem] border border-white/70 bg-gradient-to-br from-[#10233d] to-[#1f4765] p-6 text-white shadow-[0_18px_60px_rgba(15,23,42,0.18)]">
                    <p class="i3p-kicker text-[#f0c5ba]">Contexte</p>
                    <div class="mt-4 space-y-3 text-[14px] leading-7 text-slate-200">
                        <div><span class="font-bold text-white">Annee active :</span> {{ $anneeActive?->libelle ?? 'Non definie' }}</div>
                        <div><span class="font-bold text-white">Trimestre actif :</span> {{ $trimestreActif?->libelle ?? 'Non defini' }}</div>
                        <div><span class="font-bold text-white">Utilisateur :</span> {{ Auth::user()->name }}</div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="i3p-container mt-8 space-y-8">
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="i3p-stat-card">
                <div class="i3p-label">Eleves</div>
                <div class="i3p-metric mt-3 text-[#8e251d]">{{ $stats['eleves'] }}</div>
            </article>
            <article class="i3p-stat-card">
                <div class="i3p-label">Classes</div>
                <div class="i3p-metric mt-3 text-[#0f4d6a]">{{ $stats['classes'] }}</div>
            </article>
            <article class="i3p-stat-card">
                <div class="i3p-label">Bulletins disponibles</div>
                <div class="i3p-metric mt-3 text-[#8e251d]">{{ $stats['bulletins_disponibles'] }}</div>
            </article>
            <article class="i3p-stat-card">
                <div class="i3p-label">Bulletins indisponibles</div>
                <div class="i3p-metric mt-3 text-[#0f4d6a]">{{ $stats['bulletins_indisponibles'] }}</div>
            </article>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="i3p-stat-card">
                <div class="i3p-label">Resultats enregistres</div>
                <div class="i3p-metric mt-3 text-[#8e251d]">{{ $stats['resultats'] }}</div>
            </article>
            <article class="i3p-stat-card">
                <div class="i3p-label">Matieres calculees</div>
                <div class="i3p-metric mt-3 text-[#0f4d6a]">{{ $stats['matieres_calculees'] }}</div>
            </article>
            <article class="i3p-stat-card">
                <div class="i3p-label">Acces autorises</div>
                <div class="i3p-metric mt-3 text-emerald-700">{{ $stats['autorisations_bulletin'] }}</div>
            </article>
            <article class="i3p-stat-card">
                <div class="i3p-label">Acces bloques</div>
                <div class="i3p-metric mt-3 text-[#8e251d]">{{ $stats['blocages_bulletin'] }}</div>
            </article>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1fr_1fr]">
            <article class="i3p-card p-6">
                <p class="i3p-kicker text-[#b02f25]">Comptabilite scolaire</p>
                <h2 class="i3p-section-title mt-2">Repartition des statuts financiers</h2>
                <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                        <div class="i3p-label">A jour</div>
                        <div class="mt-2 text-[1.8rem] font-bold text-emerald-700">{{ $stats['comptabilite']['a_jour'] }}</div>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                        <div class="i3p-label">Partiel</div>
                        <div class="mt-2 text-[1.8rem] font-bold text-amber-700">{{ $stats['comptabilite']['partiel'] }}</div>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                        <div class="i3p-label">En retard</div>
                        <div class="mt-2 text-[1.8rem] font-bold text-amber-700">{{ $stats['comptabilite']['en_retard'] }}</div>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                        <div class="i3p-label">Bloque</div>
                        <div class="mt-2 text-[1.8rem] font-bold text-[#8e251d]">{{ $stats['comptabilite']['bloque'] }}</div>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-4 sm:col-span-2 xl:col-span-1">
                        <div class="i3p-label">Autorisation exceptionnelle</div>
                        <div class="mt-2 text-[1.8rem] font-bold text-[#0f4d6a]">{{ $stats['comptabilite']['autorisation_exceptionnelle'] }}</div>
                    </div>
                </div>
            </article>

            <article class="i3p-card p-6">
                <p class="i3p-kicker text-[#b02f25]">Audit et activite</p>
                <h2 class="i3p-section-title mt-2">Dernieres actions sensibles</h2>
                <div class="mt-5 space-y-3">
                    @forelse ($auditsRecents as $audit)
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="flex flex-wrap items-center gap-3">
                                <span class="i3p-badge border-[#b02f25]/20 bg-[#b02f25]/10 text-[#8e251d]">{{ str_replace('_', ' ', $audit->action) }}</span>
                                <span class="i3p-badge border-slate-200 bg-slate-100 text-slate-700">{{ strtoupper($audit->auditable_type) }}</span>
                            </div>
                            <div class="mt-3 text-[14px] font-bold text-slate-900">{{ $audit->description ?: 'Aucune description' }}</div>
                            <div class="mt-1 text-[13px] text-slate-600">
                                {{ $audit->user?->name ?? 'Systeme' }} · {{ $audit->created_at?->format('d/m/Y H:i:s') }}
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-300 bg-white/70 p-5 text-sm text-slate-600">
                            Aucune activite auditee recente.
                        </div>
                    @endforelse
                </div>
            </article>
        </section>

        <section class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
            <article class="i3p-card p-6">
                <p class="i3p-kicker text-[#b02f25]">Performance globale</p>
                <h2 class="i3p-section-title mt-2">Indicateurs de synthese</h2>
                <div class="mt-5 space-y-4">
                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                        <div class="i3p-label">Moyenne generale globale</div>
                        <div class="mt-2 text-[1.9rem] font-bold text-[#8e251d]">
                            {{ $stats['moyenne_globale'] !== null ? number_format($stats['moyenne_globale'], 2, ',', ' ') : 'N/D' }}
                        </div>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                        <div class="i3p-label">Meilleure classe</div>
                        <div class="mt-2 text-[15px] font-bold text-slate-900">
                            {{ $stats['meilleure_classe']['classe']->code ?? 'N/D' }}
                        </div>
                        <div class="mt-1 text-[13px] text-slate-600">
                            {{ isset($stats['meilleure_classe']['moyenne_classe']) ? number_format($stats['meilleure_classe']['moyenne_classe'], 2, ',', ' ') : 'N/D' }}
                        </div>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                        <div class="i3p-label">Meilleur eleve global</div>
                        <div class="mt-2 text-[15px] font-bold text-slate-900">
                            {{ $stats['meilleur_global']['eleve']?->nom ?? 'N/D' }} {{ $stats['meilleur_global']['eleve']?->prenoms ?? '' }}
                        </div>
                        <div class="mt-1 text-[13px] text-slate-600">
                            {{ isset($stats['meilleur_global']['moyenne']) ? number_format($stats['meilleur_global']['moyenne'], 2, ',', ' ') : 'N/D' }}
                        </div>
                    </div>
                </div>
            </article>

            <article class="i3p-card p-6">
                <p class="i3p-kicker text-[#b02f25]">Couverture par classe</p>
                <h2 class="i3p-section-title mt-2">Effectifs et resultats</h2>
                <div class="mt-6 overflow-x-auto">
                    <table class="i3p-table">
                        <thead>
                            <tr class="border-b border-slate-200 text-left">
                                <th>Classe</th>
                                <th>Effectif</th>
                                <th>Avec resultats</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($effectifsParClasse as $ligne)
                                <tr class="border-b border-slate-100 last:border-b-0">
                                    <td class="font-bold text-slate-900">{{ $ligne['classe']->code }} - {{ $ligne['classe']->nom }}</td>
                                    <td class="text-slate-700">{{ $ligne['effectif'] }}</td>
                                    <td class="text-slate-700">{{ $ligne['avec_resultats'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </article>
        </section>

        <section class="i3p-card p-6">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="i3p-kicker text-[#b02f25]">Performance scolaire</p>
                    <h2 class="i3p-section-title mt-2">Moyennes par classe</h2>
                </div>
                <span class="i3p-badge border-[#0ca6e8]/20 bg-[#0ca6e8]/10 text-[#0f4d6a]">
                    {{ $moyennesParClasse->count() }} classe(s) analysee(s)
                </span>
            </div>

            <div class="mt-6 overflow-x-auto">
                <table class="i3p-table">
                    <thead>
                        <tr class="border-b border-slate-200 text-left">
                            <th>Classe</th>
                            <th>Filiere</th>
                            <th>Moyenne de classe</th>
                            <th>Meilleur eleve</th>
                            <th>Moyenne du meilleur</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($moyennesParClasse as $ligne)
                            <tr class="border-b border-slate-100 last:border-b-0">
                                <td class="font-bold text-slate-900">{{ $ligne['classe']->code }} - {{ $ligne['classe']->nom }}</td>
                                <td class="text-slate-700">{{ $ligne['classe']->filiere?->nom ?? 'N/D' }}</td>
                                <td class="text-slate-700">{{ $ligne['moyenne_classe'] !== null ? number_format($ligne['moyenne_classe'], 2, ',', ' ') : 'N/D' }}</td>
                                <td class="text-slate-700">
                                    {{ $ligne['meilleur']['eleve']?->nom ?? 'N/D' }} {{ $ligne['meilleur']['eleve']?->prenoms ?? '' }}
                                </td>
                                <td class="text-slate-700">
                                    {{ isset($ligne['meilleur']['moyenne']) ? number_format($ligne['meilleur']['moyenne'], 2, ',', ' ') : 'N/D' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-5 text-sm text-slate-600">Aucune moyenne exploitable n est encore disponible.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>
