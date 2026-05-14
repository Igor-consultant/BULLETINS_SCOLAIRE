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

            <form method="GET" action="{{ route('bulletins.historiques') }}" class="mt-6 grid gap-4 lg:grid-cols-4">
                <div>
                    <label for="annee_scolaire_id" class="block text-sm font-semibold text-slate-700">Annee scolaire</label>
                    <select id="annee_scolaire_id" name="annee_scolaire_id" class="mt-2 w-full rounded-2xl border-slate-200 bg-white text-sm shadow-sm focus:border-[#0ca6e8] focus:ring-[#0ca6e8]">
                        <option value="">Toutes</option>
                        @foreach ($historicalYears as $year)
                            <option value="{{ $year->id }}" @selected($selectedYearId === $year->id)>{{ $year->libelle }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="classe_id" class="block text-sm font-semibold text-slate-700">Classe</label>
                    <select id="classe_id" name="classe_id" class="mt-2 w-full rounded-2xl border-slate-200 bg-white text-sm shadow-sm focus:border-[#0ca6e8] focus:ring-[#0ca6e8]">
                        <option value="">Toutes</option>
                        @foreach ($classes as $classe)
                            <option value="{{ $classe->id }}" @selected($selectedClasseId === $classe->id)>{{ $classe->code }} - {{ $classe->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="eleve_id" class="block text-sm font-semibold text-slate-700">Eleve</label>
                    <select id="eleve_id" name="eleve_id" class="mt-2 w-full rounded-2xl border-slate-200 bg-white text-sm shadow-sm focus:border-[#0ca6e8] focus:ring-[#0ca6e8]">
                        <option value="">Tous</option>
                        @foreach ($eleves as $eleve)
                            <option value="{{ $eleve->id }}" @selected($selectedEleveId === $eleve->id)>{{ $eleve->matricule }} - {{ $eleve->nom }} {{ $eleve->prenoms }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="i3p-link !border-[#b02f25]/20 !bg-[#b02f25]/10 !text-[#7d221b]">
                        Appliquer les filtres
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
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
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
                                <div class="rounded-2xl bg-slate-50 px-4 py-3">
                                    <div class="text-slate-500">Bulletins</div>
                                    <div class="mt-1 text-xl font-bold text-slate-900">{{ $finalization->imported_bulletin_count }}</div>
                                </div>
                                <div class="rounded-2xl bg-slate-50 px-4 py-3">
                                    <div class="text-slate-500">Resultats</div>
                                    <div class="mt-1 text-xl font-bold text-slate-900">{{ $finalization->imported_result_count }}</div>
                                </div>
                                <div class="rounded-2xl bg-slate-50 px-4 py-3">
                                    <div class="text-slate-500">Lot</div>
                                    <div class="mt-1 text-xl font-bold text-slate-900">{{ $finalization->sheet_name }}</div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-300 bg-white/70 p-5 text-sm text-slate-600">
                            Aucun import historique ne correspond aux filtres courants.
                        </div>
                    @endforelse
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
                    <p class="i3p-kicker text-[#b02f25]">Resultats importes</p>
                    <h2 class="i3p-section-title mt-2">Vue detaillee</h2>
                </div>
                <div class="text-sm text-slate-500">
                    Page {{ $results->currentPage() }} / {{ $results->lastPage() }}
                </div>
            </div>

            <div class="mt-6 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Annee</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Classe</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Trimestre</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Eleve</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Matiere</th>
                            <th class="px-4 py-3 text-right font-semibold text-slate-700">Moy. classe</th>
                            <th class="px-4 py-3 text-right font-semibold text-slate-700">Composition</th>
                            <th class="px-4 py-3 text-right font-semibold text-slate-700">Moy. matiere</th>
                            <th class="px-4 py-3 text-right font-semibold text-slate-700">Points</th>
                            <th class="px-4 py-3 text-right font-semibold text-slate-700">Rang</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($results as $row)
                            <tr class="hover:bg-slate-50/80">
                                <td class="px-4 py-3 text-slate-700">{{ $row->annee_libelle }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $row->classe_code }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $row->trimestre_libelle }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-slate-900">{{ $row->matricule }}</div>
                                    <div class="text-slate-600">{{ $row->nom }} {{ $row->prenoms }}</div>
                                </td>
                                <td class="px-4 py-3 text-slate-700">{{ $row->matiere_libelle }}</td>
                                <td class="px-4 py-3 text-right text-slate-700">{{ $row->moyenne_devoirs !== null ? number_format((float) $row->moyenne_devoirs, 2, ',', ' ') : '—' }}</td>
                                <td class="px-4 py-3 text-right text-slate-700">{{ $row->composition !== null ? number_format((float) $row->composition, 2, ',', ' ') : '—' }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-slate-900">{{ $row->moyenne_matiere !== null ? number_format((float) $row->moyenne_matiere, 2, ',', ' ') : '—' }}</td>
                                <td class="px-4 py-3 text-right text-slate-700">{{ $row->points !== null ? number_format((float) $row->points, 2, ',', ' ') : '—' }}</td>
                                <td class="px-4 py-3 text-right text-slate-700">{{ $row->rang ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-4 py-8 text-center text-sm text-slate-500">
                                    Aucun resultat historique ne correspond aux filtres selectionnes.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $results->links() }}
            </div>
        </section>
    </div>
</x-app-layout>
