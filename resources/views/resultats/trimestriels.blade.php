<x-app-layout>
    <x-slot name="header">
        <div class="i3p-card overflow-hidden">
            <div class="grid gap-8 px-6 py-8 lg:grid-cols-[1.15fr_0.85fr] lg:px-8">
                <div>
                    <span class="i3p-badge border-[#b02f25]/20 bg-[#b02f25]/10 text-[#8e251d]">Resultats trimestriels</span>
                    <h1 class="i3p-title mt-4">Synthese de calcul</h1>
                    <p class="i3p-copy mt-3 max-w-3xl">
                        Cette page applique la regle metier du projet :
                        <strong>moyenne matiere = (moyenne des devoirs + composition) / 2</strong>.
                    </p>
                </div>

                <div class="rounded-[2rem] border border-white/70 bg-gradient-to-br from-[#10233d] to-[#1f4765] p-6 text-white shadow-[0_18px_60px_rgba(15,23,42,0.18)]">
                    <p class="i3p-kicker text-[#f0c5ba]">Contexte</p>
                    <div class="mt-4 space-y-3 text-[14px] leading-7 text-slate-200">
                        <div><span class="font-bold text-white">Annee :</span> {{ $anneeActive?->libelle ?? 'Non definie' }}</div>
                        <div><span class="font-bold text-white">Trimestre :</span> {{ $trimestre?->libelle ?? 'Non defini' }}</div>
                        <div><span class="font-bold text-white">Classes calculees :</span> {{ $stats['classes'] }}</div>
                        <div><span class="font-bold text-white">Eleves couverts :</span> {{ $stats['eleves'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="i3p-container mt-8 space-y-8">
        @if (session('status'))
            <div class="rounded-2xl px-5 py-4 text-sm font-semibold {{ session('status_type') === 'error' ? 'border border-[#b02f25]/20 bg-[#fff1ef] text-[#8e251d]' : 'border border-emerald-200 bg-emerald-50 text-emerald-700' }}">
                {{ session('status') }}
            </div>
        @endif

        <section class="grid gap-5 xl:grid-cols-[1.15fr_0.85fr]">
            <article class="i3p-card p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="i3p-kicker text-[#b02f25]">Validation academique</p>
                        <h2 class="i3p-section-title mt-2">Lecture de pilotage</h2>
                        <p class="mt-3 max-w-2xl text-[14px] leading-7 text-slate-600">
                            Cet ecran doit permettre de verifier la couverture des calculs, les blocages comptables et les cas incomplets avant publication des bulletins.
                        </p>
                    </div>
                    <span class="i3p-badge border-slate-200 bg-slate-100 text-slate-700">{{ $stats['filtres'] ?? $stats['eleves'] }} eleve(s)</span>
                </div>

                <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="i3p-priority-card">
                        <div class="i3p-action-kicker">Classes</div>
                        <div class="mt-2 text-2xl font-bold text-slate-950">{{ $stats['classes_filtrees'] ?? $stats['classes'] }}</div>
                        <div class="mt-2 text-sm text-slate-600">classes visibles apres filtre</div>
                    </div>
                    <div class="i3p-priority-card">
                        <div class="i3p-action-kicker">Autorises</div>
                        <div class="mt-2 text-2xl font-bold text-emerald-700">{{ $stats['bulletins_autorises'] ?? 0 }}</div>
                        <div class="mt-2 text-sm text-slate-600">eleves avec acces bulletin</div>
                    </div>
                    <div class="i3p-priority-card">
                        <div class="i3p-action-kicker">Bloques</div>
                        <div class="mt-2 text-2xl font-bold text-[#8e251d]">{{ $stats['bulletins_bloques'] ?? 0 }}</div>
                        <div class="mt-2 text-sm text-slate-600">eleves bloques par comptabilite</div>
                    </div>
                    <div class="i3p-priority-card">
                        <div class="i3p-action-kicker">Incomplets</div>
                        <div class="mt-2 text-2xl font-bold text-amber-700">{{ $stats['incomplets'] ?? 0 }}</div>
                        <div class="mt-2 text-sm text-slate-600">dossiers sans calcul complet</div>
                    </div>
                </div>
            </article>

            <article class="i3p-card p-6">
                <p class="i3p-kicker text-[#b02f25]">Action centrale</p>
                <h2 class="i3p-section-title mt-2">Enregistrer les calculs</h2>
                <p class="mt-3 text-[14px] leading-7 text-slate-600">
                    Quand la lecture est satisfaisante, persiste les resultats calcules pour alimenter les bulletins et le portail parent.
                </p>
                <div class="mt-6">
                    <form method="POST" action="{{ route('resultats.trimestriels.enregistrer') }}">
                        @csrf
                        <button type="submit" class="i3p-link !border-[#b02f25]/20 !bg-[#b02f25]/10 !text-[#7d221b]">
                            Enregistrer en base
                        </button>
                    </form>
                </div>
            </article>
        </section>

        <section class="i3p-card p-6">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="i3p-kicker text-[#b02f25]">Recherche ciblee</p>
                    <h2 class="i3p-section-title mt-2">Trouver une classe ou un eleve</h2>
                    <p class="mt-3 text-[14px] leading-7 text-slate-600">
                        Filtre par eleve, classe ou statut d acces au bulletin pour concentrer la verification sur l essentiel.
                    </p>
                </div>
            </div>

            <form method="GET" action="{{ route('resultats.trimestriels') }}" class="mt-6 grid gap-4 xl:grid-cols-[1.2fr_0.9fr_0.9fr_auto_auto]">
                <div>
                    <label for="q" class="i3p-label">Recherche</label>
                    <input id="q" name="q" type="text" value="{{ $filters['q'] ?? '' }}" placeholder="Matricule, nom, prenoms" class="mt-2 w-full">
                </div>
                <div>
                    <label for="classe_id" class="i3p-label">Classe</label>
                    <select id="classe_id" name="classe_id" class="mt-2 w-full">
                        <option value="">Toutes</option>
                        @foreach ($classes as $classe)
                            <option value="{{ $classe->id }}" @selected((string) ($filters['classe_id'] ?? '') === (string) $classe->id)>
                                {{ $classe->code }} - {{ $classe->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="acces" class="i3p-label">Etat</label>
                    <select id="acces" name="acces" class="mt-2 w-full">
                        <option value="">Tous</option>
                        <option value="autorise" @selected(($filters['acces'] ?? '') === 'autorise')>Bulletin autorise</option>
                        <option value="bloque" @selected(($filters['acces'] ?? '') === 'bloque')>Bulletin bloque</option>
                        <option value="incomplet" @selected(($filters['acces'] ?? '') === 'incomplet')>Calcul incomplet</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="i3p-link w-full !border-[#b02f25]/20 !bg-[#b02f25]/10 !text-[#7d221b]">Filtrer</button>
                </div>
                <div class="flex items-end">
                    <a href="{{ route('resultats.trimestriels') }}" class="i3p-link w-full !border-slate-200 !bg-slate-100 !text-slate-700">Reinitialiser</a>
                </div>
            </form>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <article class="i3p-stat-card">
                <div class="i3p-label">Classes</div>
                <div class="i3p-metric mt-3 text-[#8e251d]">{{ $stats['classes'] }}</div>
            </article>
            <article class="i3p-stat-card">
                <div class="i3p-label">Eleves</div>
                <div class="i3p-metric mt-3 text-[#0f4d6a]">{{ $stats['eleves'] }}</div>
            </article>
            <article class="i3p-stat-card">
                <div class="i3p-label">Matieres calculees</div>
                <div class="i3p-metric mt-3 text-[#8e251d]">{{ $stats['matieres_calculees'] }}</div>
            </article>
        </section>

        @forelse ($resultatsParClasse as $bloc)
            <section class="i3p-card p-6">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="i3p-kicker text-[#b02f25]">Classe</p>
                        <h2 class="i3p-section-title mt-2">{{ $bloc['classe']->code }} - {{ $bloc['classe']->nom }}</h2>
                        <p class="mt-2 text-[14px] text-slate-600">Filiere : {{ $bloc['classe']->filiere?->nom ?? 'Non definie' }}</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="i3p-badge border-[#0ca6e8]/20 bg-[#0ca6e8]/10 text-[#0f4d6a]">
                            {{ $bloc['eleves']->count() }} eleves
                        </span>
                        <a href="{{ route('bulletins.historiques', ['classe_id' => $bloc['classe']->id]) }}" class="i3p-link !border-[#b02f25]/20 !bg-[#b02f25]/10 !text-[#7d221b]">
                            Historique classe
                        </a>
                    </div>
                </div>

                <div class="mt-6 space-y-4">
                    @foreach ($bloc['eleves'] as $ligne)
                        @php
                            $accessClasses = ($ligne['acces_bulletin_autorise'] ?? true)
                                ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                                : 'border-[#b02f25]/20 bg-[#fff1ef] text-[#8e251d]';
                        @endphp
                        <article class="i3p-record-card">
                            <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-3">
                                        <span class="i3p-badge border-slate-200 bg-slate-100 text-slate-700">
                                            {{ $ligne['eleve']->matricule }}
                                        </span>
                                        <span class="i3p-badge {{ $accessClasses }}">
                                            {{ ($ligne['acces_bulletin_autorise'] ?? true) ? 'Bulletin autorise' : 'Bulletin bloque' }}
                                        </span>
                                        @if ($ligne['rang'] !== null)
                                            <span class="i3p-badge border-[#b02f25]/20 bg-[#b02f25]/10 text-[#8e251d]">
                                                Rang : {{ $ligne['rang'] }}
                                            </span>
                                        @endif
                                    </div>

                                    <div class="mt-4">
                                        <h3 class="text-xl font-bold tracking-[-0.02em] text-slate-950">
                                            {{ $ligne['eleve']->nom }} {{ $ligne['eleve']->prenoms }}
                                        </h3>
                                        <p class="mt-2 text-sm text-slate-500">
                                            {{ $ligne['matieres']->count() }} matiere(s) avec calcul disponible
                                        </p>
                                    </div>

                                    <div class="mt-5 grid gap-4 md:grid-cols-3 xl:grid-cols-4">
                                        <div class="i3p-record-meta">
                                            <div class="i3p-label">Moyenne generale</div>
                                            <div class="mt-2 font-bold text-slate-950">
                                                {{ $ligne['moyenne_generale'] !== null ? number_format($ligne['moyenne_generale'], 2, ',', ' ') : 'N/D' }}
                                            </div>
                                        </div>
                                        <div class="i3p-record-meta">
                                            <div class="i3p-label">Total points</div>
                                            <div class="mt-2 font-bold text-slate-950">{{ number_format($ligne['total_points'], 2, ',', ' ') }}</div>
                                        </div>
                                        <div class="i3p-record-meta">
                                            <div class="i3p-label">Total coefficients</div>
                                            <div class="mt-2 font-bold text-slate-950">{{ rtrim(rtrim(number_format((float) $ligne['total_coefficients'], 2, '.', ''), '0'), '.') }}</div>
                                        </div>
                                        <div class="i3p-record-meta">
                                            <div class="i3p-label">Acces bulletin</div>
                                            <div class="mt-2 font-bold {{ ($ligne['acces_bulletin_autorise'] ?? true) ? 'text-emerald-700' : 'text-[#8e251d]' }}">
                                                {{ ($ligne['acces_bulletin_autorise'] ?? true) ? 'Autorise' : 'Bloque' }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-5 overflow-x-auto">
                                        <table class="i3p-table">
                                            <thead>
                                                <tr class="border-b border-slate-200 text-left">
                                                    <th>Matiere</th>
                                                    <th>Coef.</th>
                                                    <th>Moy. devoirs</th>
                                                    <th>Composition</th>
                                                    <th>Moyenne matiere</th>
                                                    <th>Points</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($ligne['matieres'] as $matiere)
                                                    <tr class="border-b border-slate-100 last:border-b-0">
                                                        <td class="font-bold text-slate-900">{{ $matiere['matiere'] }}</td>
                                                        <td class="text-slate-700">{{ rtrim(rtrim(number_format((float) $matiere['coefficient'], 2, '.', ''), '0'), '.') }}</td>
                                                        <td class="text-slate-700">{{ $matiere['moyenne_devoirs'] !== null ? number_format($matiere['moyenne_devoirs'], 2, ',', ' ') : 'N/D' }}</td>
                                                        <td class="text-slate-700">{{ $matiere['composition'] !== null ? number_format($matiere['composition'], 2, ',', ' ') : 'N/D' }}</td>
                                                        <td>
                                                            @if ($matiere['moyenne_matiere'] !== null)
                                                                <span class="i3p-badge border-emerald-200 bg-emerald-50 text-emerald-700">
                                                                    {{ number_format($matiere['moyenne_matiere'], 2, ',', ' ') }}
                                                                </span>
                                                            @else
                                                                <span class="text-slate-500">N/D</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-slate-700">{{ $matiere['points'] !== null ? number_format($matiere['points'], 2, ',', ' ') : 'N/D' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="flex w-full flex-col gap-3 xl:w-[14rem]">
                                    <a href="{{ route('bulletins.historiques', ['eleve_id' => $ligne['eleve']->id, 'classe_id' => $bloc['classe']->id]) }}" class="i3p-link !border-slate-200 !bg-slate-100 !text-slate-700">
                                        Historique eleve
                                    </a>
                                    @if ($trimestre && ($ligne['acces_bulletin_autorise'] ?? true))
                                        <a href="{{ route('bulletins.show', [$ligne['eleve'], $trimestre]) }}" class="i3p-link !border-[#0ca6e8]/20 !bg-[#0ca6e8]/10 !text-[#0f4d6a]">
                                            Ouvrir le bulletin
                                        </a>
                                    @elseif ($trimestre)
                                        <span class="i3p-link !cursor-default !border-[#b02f25]/20 !bg-[#fff5f3] !text-[#8e251d]">
                                            Bulletin bloque
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @empty
            <section class="i3p-card p-6">
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white/70 p-5 text-sm text-slate-600">
                    Aucun resultat exploitable n'est encore disponible pour le trimestre en cours.
                </div>
            </section>
        @endforelse
    </div>
</x-app-layout>
