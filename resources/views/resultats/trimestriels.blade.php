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

        <section class="i3p-card p-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="i3p-kicker text-[#b02f25]">Persistance</p>
                    <h2 class="i3p-section-title mt-2">Enregistrer les calculs affiches</h2>
                </div>
                <form method="POST" action="{{ route('resultats.trimestriels.enregistrer') }}">
                    @csrf
                    <button type="submit" class="i3p-link !border-[#b02f25]/20 !bg-[#b02f25]/10 !text-[#7d221b]">
                        Enregistrer en base
                    </button>
                </form>
            </div>
        </section>

        @forelse ($resultatsParClasse as $bloc)
            <section class="i3p-card p-6">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="i3p-kicker text-[#b02f25]">Classe</p>
                        <h2 class="i3p-section-title mt-2">{{ $bloc['classe']->code }} - {{ $bloc['classe']->nom }}</h2>
                        <p class="mt-2 text-[14px] text-slate-600">Filiere : {{ $bloc['classe']->filiere?->nom ?? 'Non definie' }}</p>
                    </div>
                    <span class="i3p-badge border-[#0ca6e8]/20 bg-[#0ca6e8]/10 text-[#0f4d6a]">
                        {{ $bloc['eleves']->count() }} eleves
                    </span>
                </div>

                <div class="mt-6 space-y-6">
                    @foreach ($bloc['eleves'] as $ligne)
                        <div class="rounded-3xl border border-slate-200 bg-white p-5">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <div class="text-[15px] font-bold text-slate-900">
                                        {{ $ligne['eleve']->matricule }} - {{ $ligne['eleve']->nom }} {{ $ligne['eleve']->prenoms }}
                                    </div>
                                    <div class="mt-1 text-[13px] text-slate-600">{{ $ligne['matieres']->count() }} matieres avec calcul disponible</div>
                                </div>
                                <div class="flex flex-wrap items-center gap-3">
                                    @if ($ligne['moyenne_generale'] !== null)
                                        <span class="i3p-badge border-emerald-200 bg-emerald-50 text-emerald-700">
                                            Moyenne generale : {{ number_format($ligne['moyenne_generale'], 2, ',', ' ') }}
                                        </span>
                                    @endif
                                    @if ($ligne['rang'] !== null)
                                        <span class="i3p-badge border-[#b02f25]/20 bg-[#b02f25]/10 text-[#8e251d]">
                                            Rang : {{ $ligne['rang'] }}
                                        </span>
                                    @endif
                                    @if ($trimestre && ($ligne['acces_bulletin_autorise'] ?? true))
                                        <a href="{{ route('bulletins.show', [$ligne['eleve'], $trimestre]) }}" class="i3p-link !border-[#0ca6e8]/20 !bg-[#0ca6e8]/10 !text-[#0f4d6a]">
                                            Bulletin
                                        </a>
                                    @elseif ($trimestre)
                                        <span class="i3p-badge border-[#b02f25]/20 bg-[#fff1ef] text-[#8e251d]">
                                            Bulletin bloque
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="mt-4 overflow-x-auto">
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
                                                <td class="text-slate-700">
                                                    {{ $matiere['moyenne_devoirs'] !== null ? number_format($matiere['moyenne_devoirs'], 2, ',', ' ') : 'N/D' }}
                                                </td>
                                                <td class="text-slate-700">
                                                    {{ $matiere['composition'] !== null ? number_format($matiere['composition'], 2, ',', ' ') : 'N/D' }}
                                                </td>
                                                <td>
                                                    @if ($matiere['moyenne_matiere'] !== null)
                                                        <span class="i3p-badge border-emerald-200 bg-emerald-50 text-emerald-700">
                                                            {{ number_format($matiere['moyenne_matiere'], 2, ',', ' ') }}
                                                        </span>
                                                    @else
                                                        <span class="text-slate-500">N/D</span>
                                                    @endif
                                                </td>
                                                <td class="text-slate-700">
                                                    {{ $matiere['points'] !== null ? number_format($matiere['points'], 2, ',', ' ') : 'N/D' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="border-t border-slate-200 bg-slate-50/70">
                                            <td class="font-bold text-slate-900">Totaux</td>
                                            <td class="font-bold text-slate-900">{{ rtrim(rtrim(number_format((float) $ligne['total_coefficients'], 2, '.', ''), '0'), '.') }}</td>
                                            <td></td>
                                            <td></td>
                                            <td class="font-bold text-slate-900">
                                                {{ $ligne['moyenne_generale'] !== null ? number_format($ligne['moyenne_generale'], 2, ',', ' ') : 'N/D' }}
                                            </td>
                                            <td class="font-bold text-slate-900">
                                                {{ number_format($ligne['total_points'], 2, ',', ' ') }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
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
