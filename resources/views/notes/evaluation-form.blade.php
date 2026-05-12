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

    <div class="i3p-container mt-8 space-y-6">
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

        <form method="POST" action="{{ $mode === 'create' ? route('notes.evaluations.store') : route('notes.evaluations.update', $evaluation) }}" class="space-y-8">
            @csrf
            @if ($mode === 'edit')
                @method('PUT')
            @endif

            <section class="i3p-card p-6">
                <div>
                    <p class="i3p-kicker text-[#b02f25]">Parametres generaux</p>
                    <h2 class="i3p-section-title mt-2">Fiche d evaluation</h2>
                </div>

                <div class="mt-6 grid gap-6 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label for="classe_matiere_id" class="text-sm font-semibold text-slate-700">Classe et matiere</label>
                        <select id="classe_matiere_id" name="classe_matiere_id" class="mt-2 w-full rounded-2xl border-slate-300 shadow-sm focus:border-[#b02f25] focus:ring-[#b02f25]" required>
                            <option value="">Selectionner une classe matiere</option>
                            @foreach ($classeMatieres as $classeMatiere)
                                <option value="{{ $classeMatiere->id }}" @selected(old('classe_matiere_id', $evaluation->classe_matiere_id) == $classeMatiere->id)>
                                    {{ $classeMatiere->classe?->code }} - {{ $classeMatiere->classe?->nom }} / {{ $classeMatiere->matiere?->libelle }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="trimestre_id" class="text-sm font-semibold text-slate-700">Trimestre</label>
                        <select id="trimestre_id" name="trimestre_id" class="mt-2 w-full rounded-2xl border-slate-300 shadow-sm focus:border-[#b02f25] focus:ring-[#b02f25]" required>
                            <option value="">Selectionner un trimestre</option>
                            @foreach ($trimestres as $trimestre)
                                <option value="{{ $trimestre->id }}" @selected(old('trimestre_id', $evaluation->trimestre_id) == $trimestre->id)>
                                    {{ $trimestre->libelle }} - {{ $trimestre->anneeScolaire?->libelle }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="libelle" class="text-sm font-semibold text-slate-700">Libelle</label>
                        <input id="libelle" name="libelle" type="text" value="{{ old('libelle', $evaluation->libelle) }}" class="mt-2 w-full rounded-2xl border-slate-300 shadow-sm focus:border-[#b02f25] focus:ring-[#b02f25]" required>
                    </div>

                    <div>
                        <label for="type" class="text-sm font-semibold text-slate-700">Type</label>
                        <select id="type" name="type" class="mt-2 w-full rounded-2xl border-slate-300 shadow-sm focus:border-[#b02f25] focus:ring-[#b02f25]" required>
                            <option value="devoir" @selected(old('type', $evaluation->type) === 'devoir')>Devoir</option>
                            <option value="composition" @selected(old('type', $evaluation->type) === 'composition')>Composition</option>
                        </select>
                    </div>

                    <div>
                        <label for="date_evaluation" class="text-sm font-semibold text-slate-700">Date d evaluation</label>
                        <input id="date_evaluation" name="date_evaluation" type="date" value="{{ old('date_evaluation', $evaluation->date_evaluation?->format('Y-m-d')) }}" class="mt-2 w-full rounded-2xl border-slate-300 shadow-sm focus:border-[#b02f25] focus:ring-[#b02f25]">
                    </div>

                    <div>
                        <label for="note_sur" class="text-sm font-semibold text-slate-700">Note sur</label>
                        <input id="note_sur" name="note_sur" type="number" min="1" step="0.01" value="{{ old('note_sur', $evaluation->note_sur !== null ? (float) $evaluation->note_sur : 20) }}" class="mt-2 w-full rounded-2xl border-slate-300 shadow-sm focus:border-[#b02f25] focus:ring-[#b02f25]" required>
                    </div>

                    <div>
                        <label for="coefficient_local" class="text-sm font-semibold text-slate-700">Coefficient local</label>
                        <input id="coefficient_local" name="coefficient_local" type="number" min="0" step="0.01" value="{{ old('coefficient_local', $evaluation->coefficient_local !== null ? (float) $evaluation->coefficient_local : '') }}" class="mt-2 w-full rounded-2xl border-slate-300 shadow-sm focus:border-[#b02f25] focus:ring-[#b02f25]">
                    </div>

                    <div>
                        <label for="statut" class="text-sm font-semibold text-slate-700">Statut</label>
                        <select id="statut" name="statut" class="mt-2 w-full rounded-2xl border-slate-300 shadow-sm focus:border-[#b02f25] focus:ring-[#b02f25]" required>
                            <option value="brouillon" @selected(old('statut', $evaluation->statut) === 'brouillon')>Brouillon</option>
                            <option value="validee" @selected(old('statut', $evaluation->statut) === 'validee')>Validee</option>
                        </select>
                    </div>
                </div>
            </section>

            <div class="flex flex-wrap items-center justify-end gap-3">
                <a href="{{ route('notes.evaluations') }}" class="inline-flex items-center rounded-full border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:text-slate-900">
                    Retour
                </a>
                <button type="submit" class="inline-flex items-center rounded-full border border-[#b02f25]/15 bg-[#b02f25] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#8f261e]">
                    {{ $mode === 'create' ? 'Creer l evaluation' : 'Enregistrer les modifications' }}
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
