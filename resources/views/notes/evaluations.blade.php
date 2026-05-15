<x-app-layout>
    <x-slot name="header">
        <div class="i3p-card overflow-hidden">
            <div class="grid gap-8 px-6 py-8 lg:grid-cols-[1.15fr_0.85fr] lg:px-8">
                <div>
                    <span class="i3p-badge border-[#b02f25]/20 bg-[#b02f25]/10 text-[#8e251d]">Evaluations</span>
                    <h1 class="i3p-title mt-4">Notes et evaluations</h1>
                    <p class="i3p-copy mt-3 max-w-3xl">
                        Cette page centralise les devoirs et compositions deja saisis dans le socle I3P, avec leur classe, leur matiere, le volume des notes et les absences signalees.
                    </p>
                </div>

                <div class="rounded-[2rem] border border-white/70 bg-gradient-to-br from-[#10233d] to-[#1f4765] p-6 text-white shadow-[0_18px_60px_rgba(15,23,42,0.18)]">
                    <p class="i3p-kicker text-[#f0c5ba]">Synthese</p>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-1">
                        <div class="rounded-2xl border border-white/10 bg-white/10 px-4 py-3">
                            <div class="i3p-label text-slate-200">Evaluations</div>
                            <div class="mt-2 text-[2rem] font-bold">{{ $stats['evaluations'] }}</div>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/10 px-4 py-3">
                            <div class="i3p-label text-slate-200">Notes</div>
                            <div class="mt-2 text-[2rem] font-bold">{{ $stats['notes'] }}</div>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/10 px-4 py-3">
                            <div class="i3p-label text-slate-200">Absences</div>
                            <div class="mt-2 text-[2rem] font-bold">{{ $stats['absences'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="i3p-container mt-8 space-y-8">
        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <section class="grid gap-5 xl:grid-cols-[1.15fr_0.85fr]">
            <article class="i3p-card p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="i3p-kicker text-[#b02f25]">Flux pedagogique</p>
                        <h2 class="i3p-section-title mt-2">Lecture de pilotage</h2>
                        <p class="mt-3 max-w-2xl text-[14px] leading-7 text-slate-600">
                            Cet ecran doit aider a preparer une evaluation, lancer la saisie, puis reperer rapidement ce qui reste en brouillon ou incomplet.
                        </p>
                    </div>
                    <span class="i3p-badge border-slate-200 bg-slate-100 text-slate-700">
                        {{ $stats['filtres'] }} evaluation(s)
                    </span>
                </div>

                <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="i3p-priority-card">
                        <div class="i3p-action-kicker">Brouillons</div>
                        <div class="mt-2 text-2xl font-bold text-[#8e251d]">{{ $stats['brouillons'] }}</div>
                        <div class="mt-2 text-sm text-slate-600">evaluations encore a finaliser</div>
                    </div>
                    <div class="i3p-priority-card">
                        <div class="i3p-action-kicker">Validees</div>
                        <div class="mt-2 text-2xl font-bold text-emerald-700">{{ $stats['validees'] }}</div>
                        <div class="mt-2 text-sm text-slate-600">evaluations pretes dans le flux</div>
                    </div>
                    <div class="i3p-priority-card">
                        <div class="i3p-action-kicker">Devoirs</div>
                        <div class="mt-2 text-2xl font-bold text-slate-950">{{ $stats['devoirs'] }}</div>
                        <div class="mt-2 text-sm text-slate-600">evaluations de controle continu</div>
                    </div>
                    <div class="i3p-priority-card">
                        <div class="i3p-action-kicker">Compositions</div>
                        <div class="mt-2 text-2xl font-bold text-[#0f4d6a]">{{ $stats['compositions'] }}</div>
                        <div class="mt-2 text-sm text-slate-600">evaluations de composition</div>
                    </div>
                </div>
            </article>

            <article class="i3p-card p-6">
                <p class="i3p-kicker text-[#b02f25]">Rythme conseille</p>
                <h2 class="i3p-section-title mt-2">Sequence de travail</h2>
                <div class="mt-6 space-y-4">
                    <div class="i3p-priority-card">
                        <div class="i3p-priority-title">1. Creer l evaluation</div>
                        <div class="mt-2 text-sm leading-6 text-slate-600">Associer une classe, une matiere, un trimestre et un bareme clair.</div>
                    </div>
                    <div class="i3p-priority-card">
                        <div class="i3p-priority-title">2. Saisir les notes</div>
                        <div class="mt-2 text-sm leading-6 text-slate-600">Saisir par eleve, distinguer proprement les absences et ajouter les observations utiles.</div>
                    </div>
                    <div class="i3p-priority-card">
                        <div class="i3p-priority-title">3. Verifier puis valider</div>
                        <div class="mt-2 text-sm leading-6 text-slate-600">Revenir sur les brouillons avant le calcul des resultats trimestriels.</div>
                    </div>
                </div>
            </article>
        </section>

        <section class="i3p-card p-6">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="i3p-kicker text-[#b02f25]">Recherche ciblee</p>
                    <h2 class="i3p-section-title mt-2">Retrouver une evaluation</h2>
                    <p class="mt-3 text-[14px] leading-7 text-slate-600">
                        Filtre par libelle, classe, matiere, enseignant, type, statut ou trimestre pour ne garder que les evaluations utiles.
                    </p>
                </div>
                <a href="{{ route('notes.evaluations.create') }}" class="i3p-link !border-[#b02f25]/20 !bg-[#b02f25]/10 !text-[#7d221b]">
                    Nouvelle evaluation
                </a>
            </div>

            <form method="GET" action="{{ route('notes.evaluations') }}" class="mt-6 grid gap-4 xl:grid-cols-[1.2fr_0.7fr_0.7fr_0.8fr_auto_auto]">
                <div>
                    <label for="q" class="i3p-label">Recherche</label>
                    <input id="q" name="q" type="text" value="{{ $filters['q'] }}" placeholder="Libelle, classe, matiere, enseignant" class="mt-2 w-full">
                </div>
                <div>
                    <label for="type" class="i3p-label">Type</label>
                    <select id="type" name="type" class="mt-2 w-full">
                        <option value="">Tous</option>
                        <option value="devoir" @selected($filters['type'] === 'devoir')>Devoir</option>
                        <option value="composition" @selected($filters['type'] === 'composition')>Composition</option>
                    </select>
                </div>
                <div>
                    <label for="statut" class="i3p-label">Statut</label>
                    <select id="statut" name="statut" class="mt-2 w-full">
                        <option value="">Tous</option>
                        <option value="brouillon" @selected($filters['statut'] === 'brouillon')>Brouillon</option>
                        <option value="validee" @selected($filters['statut'] === 'validee')>Validee</option>
                    </select>
                </div>
                <div>
                    <label for="trimestre_id" class="i3p-label">Trimestre</label>
                    <select id="trimestre_id" name="trimestre_id" class="mt-2 w-full">
                        <option value="">Tous</option>
                        @foreach ($trimestres as $trimestre)
                            <option value="{{ $trimestre->id }}" @selected((string) $filters['trimestre_id'] === (string) $trimestre->id)>
                                {{ $trimestre->libelle }} - {{ $trimestre->anneeScolaire?->libelle }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="i3p-link w-full !border-[#b02f25]/20 !bg-[#b02f25]/10 !text-[#7d221b]">
                        Filtrer
                    </button>
                </div>
                <div class="flex items-end">
                    <a href="{{ route('notes.evaluations') }}" class="i3p-link w-full !border-slate-200 !bg-slate-100 !text-slate-700">
                        Reinitialiser
                    </a>
                </div>
            </form>
        </section>

        <section class="i3p-card p-6">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="i3p-kicker text-[#b02f25]">Saisie actuelle</p>
                    <h2 class="i3p-section-title mt-2">Liste des evaluations</h2>
                </div>
                <div class="flex items-center gap-3">
                    <span class="i3p-badge border-[#0ca6e8]/20 bg-[#0ca6e8]/10 text-[#0f4d6a]">
                        {{ $stats['classes_couvertes'] }} classes matieres couvertes
                    </span>
                </div>
            </div>

            <div class="mt-6 space-y-4">
                @forelse ($evaluations as $evaluation)
                    @php
                        $statusClasses = $evaluation->statut === 'validee'
                            ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                            : 'border-slate-200 bg-slate-100 text-slate-700';
                        $typeClasses = $evaluation->type === 'composition'
                            ? 'border-[#b02f25]/20 bg-[#b02f25]/10 text-[#8e251d]'
                            : 'border-[#0ca6e8]/20 bg-[#0ca6e8]/10 text-[#0f4d6a]';
                    @endphp

                    <article class="i3p-record-card">
                        <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-3">
                                    <span class="i3p-badge {{ $typeClasses }}">{{ ucfirst($evaluation->type) }}</span>
                                    <span class="i3p-badge {{ $statusClasses }}">{{ ucfirst($evaluation->statut) }}</span>
                                    <span class="i3p-badge border-slate-200 bg-slate-100 text-slate-700">
                                        {{ $evaluation->date_evaluation?->format('d/m/Y') ?? 'Date non definie' }}
                                    </span>
                                </div>

                                <div class="mt-4">
                                    <h3 class="text-xl font-bold tracking-[-0.02em] text-slate-950">{{ $evaluation->libelle }}</h3>
                                    <p class="mt-2 text-sm text-slate-500">
                                        {{ $evaluation->classeMatiere?->classe?->code }} - {{ $evaluation->classeMatiere?->classe?->nom }}
                                        · {{ $evaluation->classeMatiere?->matiere?->libelle }}
                                        · {{ $evaluation->trimestre?->libelle }}
                                    </p>
                                </div>

                                <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                                    <div class="i3p-record-meta">
                                        <div class="i3p-label">Classe</div>
                                        <div class="mt-2 font-bold text-slate-950">{{ $evaluation->classeMatiere?->classe?->code }}</div>
                                        <div class="mt-1 text-sm text-slate-500">{{ $evaluation->classeMatiere?->classe?->filiere?->nom ?? 'Non definie' }}</div>
                                    </div>
                                    <div class="i3p-record-meta">
                                        <div class="i3p-label">Matiere</div>
                                        <div class="mt-2 font-bold text-slate-950">{{ $evaluation->classeMatiere?->matiere?->libelle }}</div>
                                        <div class="mt-1 text-sm text-slate-500">{{ $evaluation->classeMatiere?->enseignant_nom ?? 'Enseignant non defini' }}</div>
                                    </div>
                                    <div class="i3p-record-meta">
                                        <div class="i3p-label">Saisie</div>
                                        <div class="mt-2 font-bold text-slate-950">{{ $evaluation->notes_count }} note(s)</div>
                                        <div class="mt-1 text-sm text-slate-500">{{ $evaluation->absences_count }} absence(s)</div>
                                    </div>
                                    <div class="i3p-record-meta">
                                        <div class="i3p-label">Bareme</div>
                                        <div class="mt-2 font-bold text-slate-950">
                                            Sur {{ rtrim(rtrim(number_format((float) $evaluation->note_sur, 2, '.', ''), '0'), '.') }}
                                        </div>
                                        <div class="mt-1 text-sm text-slate-500">
                                            Coef local {{ $evaluation->coefficient_local !== null ? rtrim(rtrim(number_format((float) $evaluation->coefficient_local, 2, '.', ''), '0'), '.') : 'non defini' }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex w-full flex-col gap-3 xl:w-[14rem]">
                                <a href="{{ route('notes.evaluations.show', $evaluation) }}" class="i3p-link !border-[#b02f25]/20 !bg-[#b02f25]/10 !text-[#7d221b]">
                                    Saisir les notes
                                </a>
                                <a href="{{ route('notes.evaluations.edit', $evaluation) }}" class="i3p-link !border-slate-200 !bg-slate-100 !text-slate-700">
                                    Modifier la fiche
                                </a>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-3xl border border-dashed border-slate-300 bg-white/70 px-6 py-8 text-center">
                        <div class="text-lg font-bold text-slate-950">Aucune evaluation ne correspond aux filtres.</div>
                        <div class="mt-2 text-sm leading-7 text-slate-600">
                            Elargis les filtres ou cree une nouvelle evaluation pour lancer le flux de saisie.
                        </div>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
