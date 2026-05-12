<x-app-layout>
    <x-slot name="header">
        <div class="i3p-card overflow-hidden">
            <div class="grid gap-8 px-6 py-8 lg:grid-cols-[1.15fr_0.85fr] lg:px-8">
                <div>
                    <span class="i3p-badge border-[#b02f25]/20 bg-[#b02f25]/10 text-[#8e251d]">Referentiel pedagogique</span>
                    <h1 class="mt-4 text-3xl font-semibold text-slate-900 sm:text-4xl">Matieres et coefficients par classe</h1>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600 sm:text-base">
                        Cette page presente le programme initial par classe : matieres actives, coefficients et enseignants associes.
                    </p>
                </div>

                <div class="rounded-[2rem] border border-white/70 bg-gradient-to-br from-[#10233d] to-[#1f4765] p-6 text-white shadow-[0_18px_60px_rgba(15,23,42,0.18)]">
                    <p class="text-xs uppercase tracking-[0.24em] text-[#f0c5ba]">Lecture rapide</p>
                    <div class="mt-4 grid gap-3 sm:grid-cols-3 lg:grid-cols-1">
                        <div class="rounded-2xl border border-white/10 bg-white/10 px-4 py-3">
                            <div class="text-xs uppercase tracking-[0.18em] text-slate-200">Matieres</div>
                            <div class="mt-2 text-3xl font-semibold">{{ $stats['matieres'] }}</div>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/10 px-4 py-3">
                            <div class="text-xs uppercase tracking-[0.18em] text-slate-200">Affectations</div>
                            <div class="mt-2 text-3xl font-semibold">{{ $stats['affectations'] }}</div>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/10 px-4 py-3">
                            <div class="text-xs uppercase tracking-[0.18em] text-slate-200">Classes couvertes</div>
                            <div class="mt-2 text-3xl font-semibold">{{ $stats['classes_couvertes'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="i3p-container mt-8 space-y-8">
        <section class="grid gap-6 lg:grid-cols-[0.9fr_1.1fr]">
            <article class="i3p-card p-6">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.24em] text-[#b02f25]">Catalogue</p>
                        <h2 class="mt-2 text-2xl font-semibold text-slate-900">Matieres actives</h2>
                    </div>
                    <a href="{{ route('referentiels.matieres.create') }}" class="inline-flex items-center rounded-full border border-[#b02f25]/15 bg-[#b02f25] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#8f261e]">
                        Nouvelle matiere
                    </a>
                </div>

                <div class="mt-5 space-y-3">
                    @foreach ($matieres as $matiere)
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <div class="font-semibold text-slate-900">{{ $matiere->libelle }}</div>
                                    <div class="mt-1 text-sm text-slate-600">Code : {{ $matiere->code }}</div>
                                </div>
                                <span class="i3p-badge border-[#0ca6e8]/20 bg-[#0ca6e8]/10 text-[#0f4d6a]">
                                    {{ $matiere->classe_matieres_count }} classe(s)
                                </span>
                            </div>
                            <div class="mt-3">
                                <a href="{{ route('referentiels.matieres.edit', $matiere) }}" class="inline-flex items-center rounded-full border border-slate-200 px-3 py-1.5 text-xs font-semibold uppercase tracking-[0.18em] text-slate-700 transition hover:border-[#b02f25]/20 hover:text-[#8e251d]">
                                    Modifier
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </article>

            <article class="i3p-card p-6">
                <div>
                    <p class="text-xs uppercase tracking-[0.24em] text-[#b02f25]">Programme par classe</p>
                    <h2 class="mt-2 text-2xl font-semibold text-slate-900">Affectations et coefficients</h2>
                </div>

                <div class="mt-5 space-y-4">
                    @foreach ($classes as $classe)
                        <div class="rounded-[1.75rem] border border-slate-200 bg-white p-5">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <div class="text-lg font-semibold text-slate-900">{{ $classe->code }} - {{ $classe->nom }}</div>
                                    <div class="mt-1 text-sm text-slate-600">Filiere : {{ $classe->filiere?->nom ?? 'Non definie' }}</div>
                                </div>
                                <span class="i3p-badge border-[#b02f25]/20 bg-[#b02f25]/10 text-[#8e251d]">
                                    {{ $classe->classeMatieres->count() }} matiere(s)
                                </span>
                            </div>

                            <div class="mt-4 overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead>
                                        <tr class="text-left text-slate-500">
                                            <th class="pb-3 pe-4 font-semibold">Matiere</th>
                                            <th class="pb-3 pe-4 font-semibold">Coefficient</th>
                                            <th class="pb-3 font-semibold">Enseignant</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($classe->classeMatieres->sortBy(fn ($item) => $item->matiere?->libelle) as $ligne)
                                            <tr class="border-t border-slate-100">
                                                <td class="py-3 pe-4">
                                                    <div class="font-medium text-slate-900">{{ $ligne->matiere?->libelle }}</div>
                                                    <div class="text-xs uppercase tracking-[0.18em] text-slate-500">{{ $ligne->matiere?->code }}</div>
                                                </td>
                                                <td class="py-3 pe-4 text-slate-700">{{ number_format((float) $ligne->coefficient, 2, ',', ' ') }}</td>
                                                <td class="py-3 text-slate-700">{{ $ligne->enseignant_nom ?: 'Non renseigne' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>
            </article>
        </section>
    </div>
</x-app-layout>
