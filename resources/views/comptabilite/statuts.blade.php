<x-app-layout>
    <x-slot name="header">
        <div class="i3p-card overflow-hidden">
            <div class="grid gap-8 px-6 py-8 lg:grid-cols-[1.15fr_0.85fr] lg:px-8">
                <div>
                    <span class="i3p-badge border-[#b02f25]/20 bg-[#b02f25]/10 text-[#8e251d]">Comptabilite scolaire</span>
                    <h1 class="i3p-title mt-4">Statuts financiers</h1>
                    <p class="i3p-copy mt-3 max-w-3xl">
                        Cette page centralise les statuts de paiement utilises pour controler l'acces aux bulletins et, plus tard, au portail parents.
                    </p>
                </div>

                <div class="rounded-[2rem] border border-white/70 bg-gradient-to-br from-[#10233d] to-[#1f4765] p-6 text-white shadow-[0_18px_60px_rgba(15,23,42,0.18)]">
                    <p class="i3p-kicker text-[#f0c5ba]">Synthese</p>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-1">
                        <div class="rounded-2xl border border-white/10 bg-white/10 px-4 py-3">
                            <div class="i3p-label text-slate-200">Statuts</div>
                            <div class="mt-2 text-[2rem] font-bold">{{ $stats['lignes'] }}</div>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/10 px-4 py-3">
                            <div class="i3p-label text-slate-200">Acces autorises</div>
                            <div class="mt-2 text-[2rem] font-bold">{{ $stats['autorises'] }}</div>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/10 px-4 py-3">
                            <div class="i3p-label text-slate-200">Acces bloques</div>
                            <div class="mt-2 text-[2rem] font-bold">{{ $stats['bloques'] }}</div>
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

        <section class="i3p-card p-6">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="i3p-kicker text-[#b02f25]">Registre financier</p>
                    <h2 class="i3p-section-title mt-2">Suivi des statuts par eleve</h2>
                </div>
                <span class="i3p-badge border-[#0ca6e8]/20 bg-[#0ca6e8]/10 text-[#0f4d6a]">
                    {{ $stats['eleves_couverts'] }} eleves couverts
                </span>
            </div>

            <div class="mt-6 overflow-x-auto">
                <table class="i3p-table">
                    <thead>
                        <tr class="border-b border-slate-200 text-left">
                            <th>Eleve</th>
                            <th>Annee</th>
                            <th>Statut</th>
                            <th>Montant attendu</th>
                            <th>Montant paye</th>
                            <th>Dernier paiement</th>
                            <th>Acces bulletin</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($statuts as $statut)
                            <tr class="border-b border-slate-100 last:border-b-0">
                                <td>
                                    <div class="text-[15px] font-bold text-slate-900">
                                        {{ $statut->eleve?->matricule }} - {{ $statut->eleve?->nom }} {{ $statut->eleve?->prenoms }}
                                    </div>
                                    <div class="mt-1 text-slate-500">
                                        {{ $statut->observation ?: 'Aucune observation' }}
                                    </div>
                                </td>
                                <td class="text-slate-700">{{ $statut->anneeScolaire?->libelle }}</td>
                                <td>
                                    <span class="i3p-badge {{
                                        $statut->statut === 'a_jour' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' :
                                        ($statut->statut === 'autorisation_exceptionnelle' ? 'border-[#0ca6e8]/20 bg-[#0ca6e8]/10 text-[#0f4d6a]' :
                                        ($statut->statut === 'bloque' ? 'border-[#b02f25]/20 bg-[#b02f25]/10 text-[#8e251d]' :
                                        'border-amber-200 bg-amber-50 text-amber-700'))
                                    }}">
                                        {{ str_replace('_', ' ', ucfirst($statut->statut)) }}
                                    </span>
                                </td>
                                <td class="text-slate-700">
                                    {{ $statut->montant_attendu !== null ? number_format((float) $statut->montant_attendu, 0, ',', ' ') : 'N/D' }}
                                </td>
                                <td class="text-slate-700">
                                    {{ $statut->montant_paye !== null ? number_format((float) $statut->montant_paye, 0, ',', ' ') : 'N/D' }}
                                </td>
                                <td class="text-slate-700">
                                    {{ $statut->date_dernier_paiement?->format('d/m/Y') ?? 'N/D' }}
                                </td>
                                <td>
                                    <span class="i3p-badge {{ $statut->autorise_acces_bulletin ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-[#b02f25]/20 bg-[#b02f25]/10 text-[#8e251d]' }}">
                                        {{ $statut->autorise_acces_bulletin ? 'Autorise' : 'Bloque' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ route('comptabilite.edit', $statut) }}" class="i3p-link !border-[#0ca6e8]/20 !bg-[#0ca6e8]/10 !text-[#0f4d6a]">
                                            Modifier
                                        </a>
                                        <a href="{{ route('comptabilite.paiements', $statut) }}" class="i3p-link !border-slate-200 !bg-slate-50 !text-slate-700">
                                            Paiements
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
