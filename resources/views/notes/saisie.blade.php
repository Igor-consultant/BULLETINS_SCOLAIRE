<x-app-layout>
    <x-slot name="header">
        <div class="i3p-card overflow-hidden">
            <div class="grid gap-8 px-6 py-8 lg:grid-cols-[1.2fr_0.8fr] lg:px-8">
                <div>
                    <span class="i3p-badge border-[#b02f25]/20 bg-[#b02f25]/10 text-[#8e251d]">Saisie des notes</span>
                    <h1 class="i3p-title mt-4">{{ $evaluation->libelle }}</h1>
                    <p class="i3p-copy mt-3 max-w-3xl">
                        Saisie par eleve pour {{ $evaluation->classeMatiere?->classe?->code }} en {{ $evaluation->classeMatiere?->matiere?->libelle }}.
                        Les absences restent separees d'une note.
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

    <div class="i3p-container mt-8 space-y-6">
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

        <section class="i3p-card p-6">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="i3p-kicker text-[#b02f25]">Grille de saisie</p>
                    <h2 class="i3p-section-title mt-2">Notes par eleve</h2>
                </div>
                <a href="{{ route('notes.evaluations') }}" class="i3p-link !border-[#0ca6e8]/20 !bg-[#0ca6e8]/10 !text-[#0f4d6a]">
                    Retour aux evaluations
                </a>
            </div>

            <form method="POST" action="{{ route('notes.evaluations.saisie', $evaluation) }}" class="mt-6">
                @csrf

                <div class="overflow-x-auto">
                    <table class="i3p-table">
                        <thead>
                            <tr class="border-b border-slate-200 text-left">
                                <th>Matricule</th>
                                <th>Eleve</th>
                                <th>Note</th>
                                <th>Absence</th>
                                <th>Observation</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($inscriptions as $inscription)
                                @php
                                    $eleve = $inscription->eleve;
                                    $note = $notesByEleve->get($eleve->id);
                                    $oldNote = old("notes.{$eleve->id}.note", $note?->note);
                                    $oldAbsence = old("notes.{$eleve->id}.absence", $note?->absence ? '1' : '0');
                                    $oldObservation = old("notes.{$eleve->id}.observation", $note?->observation);
                                @endphp
                                <tr class="border-b border-slate-100 last:border-b-0">
                                    <td class="font-bold text-slate-900">{{ $eleve->matricule }}</td>
                                    <td>
                                        <div class="text-[15px] font-bold text-slate-900">{{ $eleve->nom }} {{ $eleve->prenoms }}</div>
                                        <div class="mt-1 text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500">{{ $eleve->sexe }}</div>
                                    </td>
                                    <td>
                                        <input
                                            type="number"
                                            name="notes[{{ $eleve->id }}][note]"
                                            value="{{ $oldNote }}"
                                            min="0"
                                            max="{{ (float) $evaluation->note_sur }}"
                                            step="0.01"
                                            class="w-28 rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-[#0ca6e8] focus:outline-none focus:ring-2 focus:ring-[#0ca6e8]/20"
                                        >
                                    </td>
                                    <td>
                                        <label class="inline-flex items-center gap-3 text-sm text-slate-700">
                                            <input
                                                type="hidden"
                                                name="notes[{{ $eleve->id }}][absence]"
                                                value="0"
                                            >
                                            <input
                                                type="checkbox"
                                                name="notes[{{ $eleve->id }}][absence]"
                                                value="1"
                                                @checked($oldAbsence === '1' || $oldAbsence === 1 || $oldAbsence === true)
                                                class="h-4 w-4 rounded border-slate-300 text-[#b02f25] focus:ring-[#b02f25]/20"
                                            >
                                            Absent
                                        </label>
                                    </td>
                                    <td>
                                        <input
                                            type="text"
                                            name="notes[{{ $eleve->id }}][observation]"
                                            value="{{ $oldObservation }}"
                                            maxlength="1000"
                                            class="w-full min-w-[220px] rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-[#0ca6e8] focus:outline-none focus:ring-2 focus:ring-[#0ca6e8]/20"
                                            placeholder="Observation facultative"
                                        >
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 flex flex-wrap gap-3">
                    <button type="submit" class="i3p-link !border-[#b02f25]/20 !bg-[#b02f25]/10 !text-[#7d221b]">
                        Enregistrer la saisie
                    </button>
                    <span class="rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-600">
                        Si l'eleve est absent, laisse la note vide et coche "Absent".
                    </span>
                </div>
            </form>
        </section>
    </div>
</x-app-layout>
