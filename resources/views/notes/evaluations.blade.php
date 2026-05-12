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
                    <a href="{{ route('notes.evaluations.create') }}" class="i3p-link !border-[#b02f25]/20 !bg-[#b02f25]/10 !text-[#7d221b]">
                        Nouvelle evaluation
                    </a>
                </div>
            </div>

            <div class="mt-6 overflow-x-auto">
                <table class="i3p-table">
                    <thead>
                        <tr class="border-b border-slate-200 text-left">
                            <th>Evaluation</th>
                            <th>Classe</th>
                            <th>Matiere</th>
                            <th>Trimestre</th>
                            <th>Type</th>
                            <th>Notes</th>
                            <th>Absences</th>
                            <th>Statut</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($evaluations as $evaluation)
                            <tr class="border-b border-slate-100 last:border-b-0">
                                <td>
                                    <div class="text-[15px] font-bold text-slate-900">{{ $evaluation->libelle }}</div>
                                    <div class="mt-1 text-slate-500">
                                        {{ $evaluation->date_evaluation?->format('d/m/Y') ?? 'Date non definie' }}
                                        · Note sur {{ rtrim(rtrim(number_format((float) $evaluation->note_sur, 2, '.', ''), '0'), '.') }}
                                    </div>
                                </td>
                                <td>
                                    <div class="font-bold text-slate-900">{{ $evaluation->classeMatiere?->classe?->code }}</div>
                                    <div class="mt-1 text-slate-600">{{ $evaluation->classeMatiere?->classe?->filiere?->nom ?? 'Non definie' }}</div>
                                </td>
                                <td>
                                    <div class="font-bold text-slate-900">{{ $evaluation->classeMatiere?->matiere?->libelle }}</div>
                                    <div class="mt-1 text-slate-600">{{ $evaluation->classeMatiere?->enseignant_nom ?? 'Enseignant non defini' }}</div>
                                </td>
                                <td class="text-slate-700">{{ $evaluation->trimestre?->libelle }}</td>
                                <td>
                                    <span class="i3p-badge border-[#0ca6e8]/20 bg-[#0ca6e8]/10 text-[#0f4d6a]">
                                        {{ ucfirst($evaluation->type) }}
                                    </span>
                                </td>
                                <td class="font-bold text-slate-900">{{ $evaluation->notes_count }}</td>
                                <td>
                                    @if ($evaluation->absences_count > 0)
                                        <span class="i3p-badge border-amber-200 bg-amber-50 text-amber-700">{{ $evaluation->absences_count }}</span>
                                    @else
                                        <span class="text-slate-500">0</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="i3p-badge {{ $evaluation->statut === 'validee' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-slate-200 bg-slate-50 text-slate-700' }}">
                                        {{ ucfirst($evaluation->statut) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ route('notes.evaluations.show', $evaluation) }}" class="i3p-link !border-[#b02f25]/20 !bg-[#b02f25]/10 !text-[#7d221b]">
                                            Saisir
                                        </a>
                                        <a href="{{ route('notes.evaluations.edit', $evaluation) }}" class="i3p-link !border-slate-200 !bg-slate-50 !text-slate-700">
                                            Modifier
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>
