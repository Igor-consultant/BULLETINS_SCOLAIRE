<x-app-layout>
    <x-slot name="header">
        <div class="i3p-card overflow-hidden">
            <div class="grid gap-8 px-6 py-8 lg:grid-cols-[1.1fr_0.9fr] lg:px-8">
                <div>
                    <span class="i3p-badge border-[#b02f25]/20 bg-[#b02f25]/10 text-[#8e251d]">Administration pedagogique</span>
                    <h1 class="mt-4 text-3xl font-semibold text-slate-900 sm:text-4xl">
                        {{ $mode === 'create' ? 'Nouvelle matiere' : 'Modifier une matiere' }}
                    </h1>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600 sm:text-base">
                        Renseigne la matiere puis ses affectations par classe, avec coefficient et enseignant principal.
                    </p>
                </div>

                <div class="rounded-[2rem] border border-white/70 bg-gradient-to-br from-[#10233d] to-[#1f4765] p-6 text-white shadow-[0_18px_60px_rgba(15,23,42,0.18)]">
                    <p class="text-xs uppercase tracking-[0.24em] text-[#f0c5ba]">Controle rapide</p>
                    <div class="mt-4 space-y-3">
                        <div class="rounded-2xl border border-white/10 bg-white/10 px-4 py-3">
                            <div class="text-xs uppercase tracking-[0.18em] text-slate-200">Mode</div>
                            <div class="mt-2 text-2xl font-semibold">{{ $mode === 'create' ? 'Creation' : 'Edition' }}</div>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/10 px-4 py-3">
                            <div class="text-xs uppercase tracking-[0.18em] text-slate-200">Classes disponibles</div>
                            <div class="mt-2 text-2xl font-semibold">{{ $classes->count() }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    @php
        $selectedClasses = collect(old('affectations', $affectations->map(fn ($ligne) => [
            'classe_id' => $ligne->classe_id,
            'coefficient' => $ligne->coefficient,
            'enseignant_nom' => $ligne->enseignant_nom,
            'actif' => (bool) $ligne->actif,
        ])->keyBy('classe_id')->toArray()));
    @endphp

    <div class="i3p-container mt-8 space-y-8">
        <form method="POST" action="{{ $mode === 'create' ? route('referentiels.matieres.store') : route('referentiels.matieres.update', $matiere) }}" class="space-y-8">
            @csrf
            @if ($mode === 'edit')
                @method('PUT')
            @endif

            <section class="i3p-card p-6">
                <div>
                    <p class="text-xs uppercase tracking-[0.24em] text-[#b02f25]">Identite de la matiere</p>
                    <h2 class="mt-2 text-2xl font-semibold text-slate-900">Informations principales</h2>
                </div>

                <div class="mt-6 grid gap-6 md:grid-cols-2">
                    <div>
                        <label for="code" class="text-sm font-semibold text-slate-700">Code</label>
                        <input id="code" name="code" type="text" value="{{ old('code', $matiere->code) }}" class="mt-2 w-full rounded-2xl border-slate-300 shadow-sm focus:border-[#b02f25] focus:ring-[#b02f25]" required>
                        @error('code')
                            <p class="mt-2 text-sm text-[#b02f25]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="libelle" class="text-sm font-semibold text-slate-700">Libelle</label>
                        <input id="libelle" name="libelle" type="text" value="{{ old('libelle', $matiere->libelle) }}" class="mt-2 w-full rounded-2xl border-slate-300 shadow-sm focus:border-[#b02f25] focus:ring-[#b02f25]" required>
                        @error('libelle')
                            <p class="mt-2 text-sm text-[#b02f25]">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-6">
                    <label class="inline-flex items-center gap-3 text-sm font-semibold text-slate-700">
                        <input type="checkbox" name="actif" value="1" class="rounded border-slate-300 text-[#b02f25] shadow-sm focus:ring-[#b02f25]" {{ old('actif', $matiere->actif) ? 'checked' : '' }}>
                        Matiere active
                    </label>
                </div>
            </section>

            <section class="i3p-card p-6">
                <div>
                    <p class="text-xs uppercase tracking-[0.24em] text-[#b02f25]">Affectations</p>
                    <h2 class="mt-2 text-2xl font-semibold text-slate-900">Classes, coefficients et enseignants</h2>
                    <p class="mt-2 text-sm text-slate-600">
                        Coche les classes a couvrir puis renseigne le coefficient et l enseignant principal.
                    </p>
                </div>

                <div class="mt-6 space-y-4">
                    @foreach ($classes as $classe)
                        @php
                            $ligne = $selectedClasses->get((string) $classe->id, []);
                            $checked = !empty($ligne);
                        @endphp
                        <div class="rounded-[1.75rem] border border-slate-200 bg-white p-5">
                            <div class="grid gap-4 lg:grid-cols-[1.1fr_0.45fr_0.8fr_0.35fr] lg:items-end">
                                <div>
                                    <label class="inline-flex items-start gap-3">
                                        <input type="checkbox" name="affectations[{{ $classe->id }}][classe_id]" value="{{ $classe->id }}" class="mt-1 rounded border-slate-300 text-[#b02f25] shadow-sm focus:ring-[#b02f25]" {{ $checked ? 'checked' : '' }}>
                                        <span>
                                            <span class="block text-base font-semibold text-slate-900">{{ $classe->code }} - {{ $classe->nom }}</span>
                                            <span class="mt-1 block text-sm text-slate-600">Filiere : {{ $classe->filiere?->nom ?? 'Non definie' }}</span>
                                        </span>
                                    </label>
                                </div>

                                <div>
                                    <label for="coefficient_{{ $classe->id }}" class="text-sm font-semibold text-slate-700">Coefficient</label>
                                    <input id="coefficient_{{ $classe->id }}" name="affectations[{{ $classe->id }}][coefficient]" type="number" min="0" step="0.01" value="{{ old("affectations.{$classe->id}.coefficient", $ligne['coefficient'] ?? '') }}" class="mt-2 w-full rounded-2xl border-slate-300 shadow-sm focus:border-[#b02f25] focus:ring-[#b02f25]">
                                </div>

                                <div>
                                    <label for="enseignant_{{ $classe->id }}" class="text-sm font-semibold text-slate-700">Enseignant</label>
                                    <input id="enseignant_{{ $classe->id }}" name="affectations[{{ $classe->id }}][enseignant_nom]" type="text" value="{{ old("affectations.{$classe->id}.enseignant_nom", $ligne['enseignant_nom'] ?? '') }}" class="mt-2 w-full rounded-2xl border-slate-300 shadow-sm focus:border-[#b02f25] focus:ring-[#b02f25]">
                                </div>

                                <div>
                                    <label class="inline-flex items-center gap-2 text-sm font-semibold text-slate-700">
                                        <input type="checkbox" name="affectations[{{ $classe->id }}][actif]" value="1" class="rounded border-slate-300 text-[#b02f25] shadow-sm focus:ring-[#b02f25]" {{ old("affectations.{$classe->id}.actif", $ligne['actif'] ?? false) ? 'checked' : '' }}>
                                        Active
                                    </label>
                                </div>
                            </div>
                            @error("affectations.{$classe->id}.coefficient")
                                <p class="mt-2 text-sm text-[#b02f25]">{{ $message }}</p>
                            @enderror
                        </div>
                    @endforeach
                </div>
            </section>

            <div class="flex flex-wrap items-center justify-end gap-3">
                <a href="{{ route('referentiels.matieres') }}" class="inline-flex items-center rounded-full border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:text-slate-900">
                    Retour
                </a>
                <button type="submit" class="inline-flex items-center rounded-full border border-[#b02f25]/15 bg-[#b02f25] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#8f261e]">
                    {{ $mode === 'create' ? 'Creer la matiere' : 'Enregistrer les modifications' }}
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
