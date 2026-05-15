<x-app-layout>
    <x-slot name="header">
        <div class="i3p-card overflow-hidden">
            <div class="grid gap-8 px-6 py-8 lg:grid-cols-[1.2fr_0.8fr] lg:px-8">
                <div>
                    <span class="i3p-badge border-[#b02f25]/20 bg-[#b02f25]/10 text-[#8e251d]">Saisie des notes</span>
                    <h1 class="i3p-title mt-4">{{ $evaluation->libelle }}</h1>
                    <p class="i3p-copy mt-3 max-w-3xl">
                        Saisie par eleve pour {{ $evaluation->classeMatiere?->classe?->code }} en {{ $evaluation->classeMatiere?->matiere?->libelle }}.
                        Les absences restent separees d une note.
                    </p>
                </div>

                <div class="rounded-[2rem] border border-white/70 bg-gradient-to-br from-[#10233d] to-[#1f4765] p-6 text-white shadow-[0_18px_60px_rgba(15,23,42,0.18)]">
                    <p class="i3p-kicker text-[#f0c5ba]">Contexte</p>
                    <div class="mt-4 space-y-3 text-[14px] leading-7 text-slate-200">
                        <div><span class="font-bold text-white">Classe :</span> {{ $evaluation->classeMatiere?->classe?->code }} - {{ $evaluation->classeMatiere?->classe?->nom }}</div>
                        <div><span class="font-bold text-white">Trimestre :</span> {{ $evaluation->trimestre?->libelle }}</div>
                        <div><span class="font-bold text-white">Type :</span> {{ ucfirst($evaluation->type) }}</div>
                        <div><span class="font-bold text-white">Note sur :</span> {{ rtrim(rtrim(number_format((float) $evaluation->note_sur, 2, '.', ''), '0'), '.') }}</div>
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

        @if ($errors->any())
            <div class="rounded-2xl border border-[#b02f25]/20 bg-[#fff5f3] px-5 py-4 text-sm text-[#8e251d]">
                <div class="font-bold">La saisie comporte des erreurs.</div>
                <ul class="mt-2 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="grid gap-5 xl:grid-cols-[1.15fr_0.85fr]">
            <article class="i3p-card p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="i3p-kicker text-[#b02f25]">Avancement de la saisie</p>
                        <h2 class="i3p-section-title mt-2">Lecture operationnelle</h2>
                        <p class="mt-3 max-w-2xl text-[14px] leading-7 text-slate-600">
                            Cette vue doit permettre de saisir vite, distinguer les absences, et reperer ce qui reste a completer avant validation.
                        </p>
                    </div>
                    <span class="i3p-badge border-slate-200 bg-slate-100 text-slate-700">{{ $stats['eleves'] }} eleve(s)</span>
                </div>

                <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="i3p-priority-card">
                        <div class="i3p-action-kicker">Saisies</div>
                        <div class="mt-2 text-2xl font-bold text-slate-950">{{ $stats['notes_saisies'] }}</div>
                        <div class="mt-2 text-sm text-slate-600">notes deja enregistrees</div>
                    </div>
                    <div class="i3p-priority-card">
                        <div class="i3p-action-kicker">Absences</div>
                        <div class="mt-2 text-2xl font-bold text-amber-700">{{ $stats['absences'] }}</div>
                        <div class="mt-2 text-sm text-slate-600">eleves marques absents</div>
                    </div>
                    <div class="i3p-priority-card">
                        <div class="i3p-action-kicker">Restants</div>
                        <div class="mt-2 text-2xl font-bold text-[#8e251d]">{{ $stats['restants'] }}</div>
                        <div class="mt-2 text-sm text-slate-600">lignes encore non saisies</div>
                    </div>
                    <div class="i3p-priority-card">
                        <div class="i3p-action-kicker">Statut</div>
                        <div class="mt-2 text-2xl font-bold text-[#0f4d6a]">{{ ucfirst($evaluation->statut) }}</div>
                        <div class="mt-2 text-sm text-slate-600">etat courant de l evaluation</div>
                    </div>
                </div>
            </article>

            <article class="i3p-card p-6">
                <p class="i3p-kicker text-[#b02f25]">Rappel de saisie</p>
                <h2 class="i3p-section-title mt-2">Regles utiles</h2>
                <div class="mt-6 space-y-4">
                    <div class="i3p-priority-card">
                        <div class="i3p-priority-title">Note comprise entre 0 et {{ rtrim(rtrim(number_format((float) $evaluation->note_sur, 2, '.', ''), '0'), '.') }}</div>
                        <div class="mt-2 text-sm leading-6 text-slate-600">Toute note hors bareme sera refusee a l enregistrement.</div>
                    </div>
                    <div class="i3p-priority-card">
                        <div class="i3p-priority-title">Absence sans note</div>
                        <div class="mt-2 text-sm leading-6 text-slate-600">Si l eleve est absent, coche la case et laisse la note vide.</div>
                    </div>
                    <div class="i3p-priority-card">
                        <div class="i3p-priority-title">Observation facultative</div>
                        <div class="mt-2 text-sm leading-6 text-slate-600">Tu peux noter un cas particulier sans alourdir la grille.</div>
                    </div>
                </div>
            </article>
        </section>

        <section class="i3p-card p-6">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                <div>
                    <p class="i3p-kicker text-[#b02f25]">Grille de saisie</p>
                    <h2 class="i3p-section-title mt-2">Notes par eleve</h2>
                </div>
                <a href="{{ route('notes.evaluations') }}" class="i3p-link !border-[#0ca6e8]/20 !bg-[#0ca6e8]/10 !text-[#0f4d6a]">
                    Retour aux evaluations
                </a>
            </div>

            <form method="POST" action="{{ route('notes.evaluations.saisie', $evaluation) }}" class="mt-6 space-y-6">
                @csrf

                <div class="space-y-4">
                    @foreach ($inscriptions as $inscription)
                        @php
                            $eleve = $inscription->eleve;
                            $note = $notesByEleve->get($eleve->id);
                            $oldNote = old("notes.{$eleve->id}.note", $note?->note);
                            $oldAbsence = old("notes.{$eleve->id}.absence", $note?->absence ? '1' : '0');
                            $oldObservation = old("notes.{$eleve->id}.observation", $note?->observation);
                        @endphp

                        <article class="i3p-record-card">
                            <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-3">
                                        <span class="i3p-badge border-slate-200 bg-slate-100 text-slate-700">{{ $eleve->matricule }}</span>
                                        <span class="i3p-badge border-[#0ca6e8]/20 bg-[#0ca6e8]/10 text-[#0f4d6a]">{{ $eleve->sexe ?: 'N/D' }}</span>
                                    </div>

                                    <div class="mt-4">
                                        <h3 class="text-xl font-bold tracking-[-0.02em] text-slate-950">{{ $eleve->nom }} {{ $eleve->prenoms }}</h3>
                                    </div>

                                    <div class="mt-5 grid gap-4 md:grid-cols-3">
                                        <div class="i3p-record-meta">
                                            <label for="note_{{ $eleve->id }}" class="i3p-label">Note</label>
                                            <input
                                                id="note_{{ $eleve->id }}"
                                                type="number"
                                                name="notes[{{ $eleve->id }}][note]"
                                                value="{{ $oldNote }}"
                                                min="0"
                                                max="{{ (float) $evaluation->note_sur }}"
                                                step="0.01"
                                                class="mt-2 w-full"
                                            >
                                        </div>

                                        <div class="i3p-record-meta">
                                            <div class="i3p-label">Absence</div>
                                            <label class="mt-3 inline-flex items-center gap-3 text-sm font-semibold text-slate-700">
                                                <input type="hidden" name="notes[{{ $eleve->id }}][absence]" value="0">
                                                <input
                                                    type="checkbox"
                                                    name="notes[{{ $eleve->id }}][absence]"
                                                    value="1"
                                                    @checked($oldAbsence === '1' || $oldAbsence === 1 || $oldAbsence === true)
                                                    class="h-4 w-4 rounded border-slate-300 text-[#b02f25] focus:ring-[#b02f25]/20"
                                                >
                                                Marquer absent
                                            </label>
                                        </div>

                                        <div class="i3p-record-meta md:col-span-1">
                                            <label for="observation_{{ $eleve->id }}" class="i3p-label">Observation</label>
                                            <input
                                                id="observation_{{ $eleve->id }}"
                                                type="text"
                                                name="notes[{{ $eleve->id }}][observation]"
                                                value="{{ $oldObservation }}"
                                                maxlength="1000"
                                                class="mt-2 w-full"
                                                placeholder="Observation facultative"
                                            >
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="i3p-link !border-[#b02f25]/20 !bg-[#b02f25]/10 !text-[#7d221b]">
                        Enregistrer la saisie
                    </button>
                    <span class="rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-600">
                        Si l eleve est absent, laisse la note vide et coche "Marquer absent".
                    </span>
                </div>
            </form>
        </section>
    </div>
</x-app-layout>
