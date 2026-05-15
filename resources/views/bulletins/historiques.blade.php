<x-app-layout>
    <x-slot name="header">
        <div class="i3p-card overflow-hidden">
            <div class="grid gap-8 px-6 py-8 lg:grid-cols-[1.05fr_0.95fr] lg:px-8">
                <div class="space-y-4">
                    <span class="i3p-badge border-[#0ca6e8]/20 bg-[#0ca6e8]/10 text-[#0f4d6a]">Historique importe</span>
                    <div>
                        <h1 class="i3p-title">Consultation des resultats historiques</h1>
                        <p class="i3p-copy mt-3 max-w-3xl">
                            Cette page centralise les resultats issus du classeur historique importe, avec un filtre par annee, classe et eleve.
                        </p>
                    </div>
                </div>

                <div class="rounded-[2rem] border border-white/70 bg-gradient-to-br from-[#10233d] to-[#1f4765] p-6 text-white shadow-[0_18px_60px_rgba(15,23,42,0.18)]">
                    <p class="i3p-kicker text-[#f0c5ba]">Perimetre charge</p>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div>
                            <div class="text-xs uppercase tracking-[0.18em] text-slate-300">Annees</div>
                            <div class="mt-2 text-3xl font-bold">{{ $stats['annees'] }}</div>
                        </div>
                        <div>
                            <div class="text-xs uppercase tracking-[0.18em] text-slate-300">Classes</div>
                            <div class="mt-2 text-3xl font-bold">{{ $stats['classes'] }}</div>
                        </div>
                        <div>
                            <div class="text-xs uppercase tracking-[0.18em] text-slate-300">Eleves</div>
                            <div class="mt-2 text-3xl font-bold">{{ $stats['eleves'] }}</div>
                        </div>
                        <div>
                            <div class="text-xs uppercase tracking-[0.18em] text-slate-300">Bulletins</div>
                            <div class="mt-2 text-3xl font-bold">{{ $stats['bulletins'] }}</div>
                        </div>
                    </div>
                    <div class="mt-5 rounded-2xl border border-white/10 bg-white/10 px-4 py-3 text-sm text-slate-100">
                        {{ $stats['resultats'] }} lignes de resultats disponibles dans la vue filtree.
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="i3p-container mt-8 space-y-8">
        <section class="grid gap-5 xl:grid-cols-[1.15fr_0.85fr]">
            <article class="i3p-card p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="i3p-kicker text-[#b02f25]">Archive exploitable</p>
                        <h2 class="i3p-section-title mt-2">Lecture de pilotage</h2>
                        <p class="mt-3 max-w-2xl text-[14px] leading-7 text-slate-600">
                            Cet ecran doit permettre de retrouver rapidement une trajectoire scolaire historique, une classe ancienne ou une matiere precise.
                        </p>
                    </div>
                    <span class="i3p-badge border-slate-200 bg-slate-100 text-slate-700">{{ number_format($results->total(), 0, ',', ' ') }} ligne(s)</span>
                </div>

                <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="i3p-priority-card">
                        <div class="i3p-action-kicker">Annees</div>
                        <div class="mt-2 text-2xl font-bold text-slate-950">{{ $stats['annees'] }}</div>
                        <div class="mt-2 text-sm text-slate-600">periodes historiques disponibles</div>
                    </div>
                    <div class="i3p-priority-card">
                        <div class="i3p-action-kicker">Classes</div>
                        <div class="mt-2 text-2xl font-bold text-[#0f4d6a]">{{ $stats['classes'] }}</div>
                        <div class="mt-2 text-sm text-slate-600">classes reconstruites depuis l import</div>
                    </div>
                    <div class="i3p-priority-card">
                        <div class="i3p-action-kicker">Eleves</div>
                        <div class="mt-2 text-2xl font-bold text-slate-950">{{ $stats['eleves'] }}</div>
                        <div class="mt-2 text-sm text-slate-600">eleves touches par les archives</div>
                    </div>
                    <div class="i3p-priority-card">
                        <div class="i3p-action-kicker">Bulletins</div>
                        <div class="mt-2 text-2xl font-bold text-[#8e251d]">{{ $stats['bulletins'] }}</div>
                        <div class="mt-2 text-sm text-slate-600">bulletins reconstruits dans les lots</div>
                    </div>
                </div>
            </article>

            <article class="i3p-card p-6">
                <p class="i3p-kicker text-[#b02f25]">Focus eleve</p>
                <h2 class="i3p-section-title mt-2">
                    {{ $selectedEleve ? $selectedEleve->matricule.' - '.$selectedEleve->nom.' '.$selectedEleve->prenoms : 'Selectionner un eleve pour un detail cible' }}
                </h2>
                <div class="mt-5 rounded-3xl border border-[#b02f25]/10 bg-[#fff8f6] p-5">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <div class="text-xs uppercase tracking-[0.18em] text-[#b02f25]">Perimetre</div>
                            <div class="mt-2 text-sm text-slate-700">
                                {{ $selectedYearId ? 'Annee filtree' : 'Toutes les annees' }} · {{ $selectedClasseId ? 'Classe filtree' : 'Toutes les classes' }}
                            </div>
                        </div>
                        <div>
                            <div class="text-xs uppercase tracking-[0.18em] text-[#b02f25]">Resultats affiches</div>
                            <div class="mt-2 text-sm text-slate-700">{{ number_format($results->total(), 0, ',', ' ') }} ligne(s)</div>
                        </div>
                    </div>
                </div>
            </article>
        </section>

        <section class="i3p-card p-6">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="i3p-kicker text-[#b02f25]">Filtres</p>
                    <h2 class="i3p-section-title mt-2">Cibler un historique</h2>
                </div>
                <a href="{{ route('bulletins.historiques') }}" class="i3p-link !border-slate-200 !bg-slate-100 !text-slate-700">
                    Reinitialiser
                </a>
            </div>

            <form method="GET" action="{{ route('bulletins.historiques') }}" class="mt-6 grid gap-4 lg:grid-cols-[1.1fr_0.9fr_0.9fr_1fr_auto]">
                <div>
                    <label for="q" class="i3p-label">Recherche</label>
                    <input id="q" name="q" type="text" value="{{ $search }}" placeholder="Matricule, nom, classe, matiere" class="mt-2 w-full">
                </div>
                <div>
                    <label for="annee_scolaire_id" class="i3p-label">Annee scolaire</label>
                    <select id="annee_scolaire_id" name="annee_scolaire_id" class="mt-2 w-full">
                        <option value="">Toutes</option>
                        @foreach ($historicalYears as $year)
                            <option value="{{ $year->id }}" @selected($selectedYearId === $year->id)>{{ $year->libelle }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="classe_id" class="i3p-label">Classe</label>
                    <select id="classe_id" name="classe_id" class="mt-2 w-full">
                        <option value="">Toutes</option>
                        @foreach ($classes as $classe)
                            <option value="{{ $classe->id }}" @selected($selectedClasseId === $classe->id)>{{ $classe->code }} - {{ $classe->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="eleve_id" class="i3p-label">Eleve</label>
                    <select id="eleve_id" name="eleve_id" class="mt-2 w-full">
                        <option value="">Tous</option>
                        @foreach ($eleves as $eleve)
                            <option value="{{ $eleve->id }}" @selected($selectedEleveId === $eleve->id)>{{ $eleve->matricule }} - {{ $eleve->nom }} {{ $eleve->prenoms }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="i3p-link w-full !border-[#b02f25]/20 !bg-[#b02f25]/10 !text-[#7d221b]">
                        Appliquer
                    </button>
                </div>
            </form>
        </section>

        <section class="grid gap-6 lg:grid-cols-[0.95fr_1.05fr]">
            <article class="i3p-card p-6">
                <p class="i3p-kicker text-[#b02f25]">Finalisations</p>
                <h2 class="i3p-section-title mt-2">Lots importes conserves</h2>
                <div class="mt-5 space-y-3">
                    @forelse ($finalizations as $finalization)
                        <article class="i3p-record-card">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <div class="text-[1rem] font-bold text-slate-900">{{ $finalization->academic_year_label }} / {{ $finalization->class_code }}</div>
                                    <div class="mt-1 text-[14px] text-slate-600">
                                        Source {{ $finalization->sheet_name }} · batch #{{ $finalization->batch_id }}
                                    </div>
                                </div>
                                <span class="i3p-badge border-[#0ca6e8]/20 bg-[#0ca6e8]/10 text-[#0f4d6a]">
                                    {{ $finalization->imported_student_count }} eleves
                                </span>
                            </div>
                            <div class="mt-4 grid gap-3 sm:grid-cols-3 text-sm">
                                <div class="i3p-record-meta">
                                    <div class="text-slate-500">Bulletins</div>
                                    <div class="mt-1 text-xl font-bold text-slate-900">{{ $finalization->imported_bulletin_count }}</div>
                                </div>
                                <div class="i3p-record-meta">
                                    <div class="text-slate-500">Resultats</div>
                                    <div class="mt-1 text-xl font-bold text-slate-900">{{ $finalization->imported_result_count }}</div>
                                </div>
                                <div class="i3p-record-meta">
                                    <div class="text-slate-500">Lot</div>
                                    <div class="mt-1 text-xl font-bold text-slate-900">{{ $finalization->sheet_name }}</div>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-300 bg-white/70 p-5 text-sm text-slate-600">
                            Aucun import historique ne correspond aux filtres courants.
                        </div>
                    @endforelse
                </div>
            </article>

            <article class="i3p-card p-6">
                <p class="i3p-kicker text-[#b02f25]">Resultats importes</p>
                <h2 class="i3p-section-title mt-2">Vue detaillee</h2>
                <div class="mt-5 space-y-3">
                    @forelse ($results as $row)
                        <article class="i3p-record-card">
                            <div class="flex flex-wrap items-center gap-3">
                                <span class="i3p-badge border-slate-200 bg-slate-100 text-slate-700">{{ $row->annee_libelle }}</span>
                                <span class="i3p-badge border-[#0ca6e8]/20 bg-[#0ca6e8]/10 text-[#0f4d6a]">{{ $row->classe_code }}</span>
                                <span class="i3p-badge border-slate-200 bg-slate-100 text-slate-700">{{ $row->trimestre_libelle }}</span>
                            </div>
                            <div class="mt-4">
                                <div class="text-lg font-bold text-slate-950">{{ $row->matricule }} - {{ $row->nom }} {{ $row->prenoms }}</div>
                                <div class="mt-1 text-sm text-slate-500">{{ $row->matiere_libelle }}</div>
                            </div>
                            <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                                <div class="i3p-record-meta">
                                    <div class="i3p-label">Moy. devoirs</div>
                                    <div class="mt-2 font-bold text-slate-950">{{ $row->moyenne_devoirs !== null ? number_format((float) $row->moyenne_devoirs, 2, ',', ' ') : 'N/D' }}</div>
                                </div>
                                <div class="i3p-record-meta">
                                    <div class="i3p-label">Composition</div>
                                    <div class="mt-2 font-bold text-slate-950">{{ $row->composition !== null ? number_format((float) $row->composition, 2, ',', ' ') : 'N/D' }}</div>
                                </div>
                                <div class="i3p-record-meta">
                                    <div class="i3p-label">Moy. matiere</div>
                                    <div class="mt-2 font-bold text-slate-950">{{ $row->moyenne_matiere !== null ? number_format((float) $row->moyenne_matiere, 2, ',', ' ') : 'N/D' }}</div>
                                </div>
                                <div class="i3p-record-meta">
                                    <div class="i3p-label">Points</div>
                                    <div class="mt-2 font-bold text-slate-950">{{ $row->points !== null ? number_format((float) $row->points, 2, ',', ' ') : 'N/D' }}</div>
                                </div>
                                <div class="i3p-record-meta">
                                    <div class="i3p-label">Rang</div>
                                    <div class="mt-2 font-bold text-slate-950">{{ $row->rang ?? 'N/D' }}</div>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-300 bg-white/70 p-5 text-sm text-slate-600">
                            Aucun resultat historique ne correspond aux filtres selectionnes.
                        </div>
                    @endforelse
                </div>

                <div class="mt-6">
                    {{ $results->links() }}
                </div>
            </article>
        </section>
    </div>
</x-app-layout>
