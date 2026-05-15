<x-app-layout>
    <x-slot name="header">
        <div class="i3p-card overflow-hidden">
            <div class="grid gap-8 px-6 py-8 lg:grid-cols-[1.1fr_0.9fr] lg:px-8">
                <div>
                    <span class="i3p-badge border-[#b02f25]/20 bg-[#b02f25]/10 text-[#8e251d]">Administration des evaluations</span>
                    <h1 class="i3p-title mt-4">
                        {{ $mode === 'create' ? 'Nouvelle evaluation' : 'Modifier une evaluation' }}
                    </h1>
                    <p class="i3p-copy mt-3 max-w-3xl">
                        Definis la classe, la matiere, le trimestre et les parametres de notation avant la saisie des notes.
                    </p>
                </div>

                <div class="rounded-[2rem] border border-white/70 bg-gradient-to-br from-[#10233d] to-[#1f4765] p-6 text-white shadow-[0_18px_60px_rgba(15,23,42,0.18)]">
                    <p class="i3p-kicker text-[#f0c5ba]">Controle rapide</p>
                    <div class="mt-4 space-y-3">
                        <div class="rounded-2xl border border-white/10 bg-white/10 px-4 py-3">
                            <div class="i3p-label text-slate-200">Mode</div>
                            <div class="mt-2 text-[2rem] font-bold">{{ $mode === 'create' ? 'Creation' : 'Edition' }}</div>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/10 px-4 py-3">
                            <div class="i3p-label text-slate-200">Classes matieres</div>
                            <div class="mt-2 text-[2rem] font-bold">{{ $classeMatieres->count() }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="i3p-container mt-8 space-y-8">
        @if ($errors->any())
            <div class="rounded-2xl border border-[#b02f25]/20 bg-[#fff5f3] px-5 py-4 text-sm text-[#8e251d]">
                <div class="font-bold">Le formulaire comporte des erreurs.</div>
                <ul class="mt-2 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="grid gap-5 xl:grid-cols-[1.1fr_0.9fr]">
            <article class="i3p-card p-6">
                <p class="i3p-kicker text-[#b02f25]">Structure de saisie</p>
                <h2 class="i3p-section-title mt-2">Fiche d evaluation</h2>
                <div class="mt-5 grid gap-4 sm:grid-cols-3">
                    <div class="i3p-priority-card">
                        <div class="i3p-action-kicker">Bloc 1</div>
                        <div class="mt-2 text-base font-bold text-slate-950">Cadre scolaire</div>
                        <div class="mt-2 text-sm leading-6 text-slate-600">Classe, matiere et trimestre de rattachement.</div>
                    </div>
                    <div class="i3p-priority-card">
                        <div class="i3p-action-kicker">Bloc 2</div>
                        <div class="mt-2 text-base font-bold text-slate-950">Nature de l evaluation</div>
                        <div class="mt-2 text-sm leading-6 text-slate-600">Libelle, type et date de l epreuve.</div>
                    </div>
                    <div class="i3p-priority-card">
                        <div class="i3p-action-kicker">Bloc 3</div>
                        <div class="mt-2 text-base font-bold text-slate-950">Bareme</div>
                        <div class="mt-2 text-sm leading-6 text-slate-600">Note maximale, coefficient local et statut.</div>
                    </div>
                </div>
            </article>

            <article class="i3p-card p-6">
                <p class="i3p-kicker text-[#b02f25]">Conseil de parametrage</p>
                <h2 class="i3p-section-title mt-2">Bonnes pratiques</h2>
                <div class="mt-5 space-y-4">
                    <div class="i3p-priority-card">
                        <div class="i3p-priority-title">Utiliser un libelle explicite</div>
                        <div class="mt-2 text-sm leading-6 text-slate-600">Exemple: Devoir 1 d algebra, Composition T1 physique, interrogation orale.</div>
                    </div>
                    <div class="i3p-priority-card">
                        <div class="i3p-priority-title">Laisser en brouillon tant que la saisie n est pas complete</div>
                        <div class="mt-2 text-sm leading-6 text-slate-600">La validation doit intervenir quand le controle de coherence est termine.</div>
                    </div>
                    <div class="i3p-priority-card">
                        <div class="i3p-priority-title">Fixer un bareme stable</div>
                        <div class="mt-2 text-sm leading-6 text-slate-600">Le bareme ne doit pas varier apres le debut de la saisie des notes.</div>
                    </div>
                </div>
            </article>
        </section>

        <form method="POST" action="{{ $mode === 'create' ? route('notes.evaluations.store') : route('notes.evaluations.update', $evaluation) }}" class="space-y-8">
            @csrf
            @if ($mode === 'edit')
                @method('PUT')
            @endif

            <section class="rounded-[1.75rem] border border-slate-200 bg-white p-6">
                <div>
                    <p class="i3p-kicker text-[#b02f25]">Bloc 1</p>
                    <h2 class="mt-2 text-xl font-bold tracking-[-0.02em] text-slate-950">Cadre scolaire</h2>
                </div>

                <div class="mt-6 grid gap-6 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label for="classe_matiere_id" class="i3p-label">Classe et matiere</label>
                        <select id="classe_matiere_id" name="classe_matiere_id" class="mt-2 w-full" required>
                            <option value="">Selectionner une classe matiere</option>
                            @foreach ($classeMatieres as $classeMatiere)
                                <option value="{{ $classeMatiere->id }}" @selected(old('classe_matiere_id', $evaluation->classe_matiere_id) == $classeMatiere->id)>
                                    {{ $classeMatiere->classe?->code }} - {{ $classeMatiere->classe?->nom }} / {{ $classeMatiere->matiere?->libelle }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="trimestre_id" class="i3p-label">Trimestre</label>
                        <select id="trimestre_id" name="trimestre_id" class="mt-2 w-full" required>
                            <option value="">Selectionner un trimestre</option>
                            @foreach ($trimestres as $trimestre)
                                <option value="{{ $trimestre->id }}" @selected(old('trimestre_id', $evaluation->trimestre_id) == $trimestre->id)>
                                    {{ $trimestre->libelle }} - {{ $trimestre->anneeScolaire?->libelle }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </section>

            <section class="rounded-[1.75rem] border border-slate-200 bg-white p-6">
                <div>
                    <p class="i3p-kicker text-[#b02f25]">Bloc 2</p>
                    <h2 class="mt-2 text-xl font-bold tracking-[-0.02em] text-slate-950">Nature de l evaluation</h2>
                </div>

                <div class="mt-6 grid gap-6 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label for="libelle" class="i3p-label">Libelle</label>
                        <input id="libelle" name="libelle" type="text" value="{{ old('libelle', $evaluation->libelle) }}" class="mt-2 w-full" required>
                    </div>

                    <div>
                        <label for="type" class="i3p-label">Type</label>
                        <select id="type" name="type" class="mt-2 w-full" required>
                            <option value="devoir" @selected(old('type', $evaluation->type) === 'devoir')>Devoir</option>
                            <option value="composition" @selected(old('type', $evaluation->type) === 'composition')>Composition</option>
                        </select>
                    </div>

                    <div>
                        <label for="date_evaluation" class="i3p-label">Date d evaluation</label>
                        <input id="date_evaluation" name="date_evaluation" type="date" value="{{ old('date_evaluation', $evaluation->date_evaluation?->format('Y-m-d')) }}" class="mt-2 w-full">
                    </div>
                </div>
            </section>

            <section class="rounded-[1.75rem] border border-slate-200 bg-white p-6">
                <div>
                    <p class="i3p-kicker text-[#b02f25]">Bloc 3</p>
                    <h2 class="mt-2 text-xl font-bold tracking-[-0.02em] text-slate-950">Bareme et publication</h2>
                </div>

                <div class="mt-6 grid gap-6 md:grid-cols-2">
                    <div>
                        <label for="note_sur" class="i3p-label">Note sur</label>
                        <input id="note_sur" name="note_sur" type="number" min="1" step="0.01" value="{{ old('note_sur', $evaluation->note_sur !== null ? (float) $evaluation->note_sur : 20) }}" class="mt-2 w-full" required>
                    </div>

                    <div>
                        <label for="coefficient_local" class="i3p-label">Coefficient local</label>
                        <input id="coefficient_local" name="coefficient_local" type="number" min="0" step="0.01" value="{{ old('coefficient_local', $evaluation->coefficient_local !== null ? (float) $evaluation->coefficient_local : '') }}" class="mt-2 w-full">
                    </div>

                    <div>
                        <label for="statut" class="i3p-label">Statut</label>
                        <select id="statut" name="statut" class="mt-2 w-full" required>
                            <option value="brouillon" @selected(old('statut', $evaluation->statut) === 'brouillon')>Brouillon</option>
                            <option value="validee" @selected(old('statut', $evaluation->statut) === 'validee')>Validee</option>
                        </select>
                    </div>
                </div>
            </section>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('notes.evaluations') }}" class="i3p-link !border-slate-200 !bg-slate-100 !text-slate-700">
                    Retour
                </a>
                <button type="submit" class="i3p-link !border-[#b02f25]/20 !bg-[#b02f25]/10 !text-[#7d221b]">
                    {{ $mode === 'create' ? 'Creer l evaluation' : 'Enregistrer les modifications' }}
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
