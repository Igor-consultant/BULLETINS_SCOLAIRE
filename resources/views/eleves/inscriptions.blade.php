<x-app-layout>
    <x-slot name="header">
        <div class="i3p-card overflow-hidden">
            <div class="grid gap-8 px-6 py-8 lg:grid-cols-[1.15fr_0.85fr] lg:px-8">
                <div>
                    <span class="i3p-badge border-[#b02f25]/20 bg-[#b02f25]/10 text-[#8e251d]">Scolarite</span>
                    <h1 class="i3p-title mt-4">Eleves et inscriptions</h1>
                    <p class="i3p-copy mt-3 max-w-3xl">
                        Cette page centralise les eleves deja charges dans le socle I3P, avec leur classe, leur statut d'inscription et leurs contacts.
                    </p>
                </div>

                <div class="rounded-[2rem] border border-white/70 bg-gradient-to-br from-[#10233d] to-[#1f4765] p-6 text-white shadow-[0_18px_60px_rgba(15,23,42,0.18)]">
                    <p class="i3p-kicker text-[#f0c5ba]">Synthese</p>
                    <div class="mt-4 grid gap-3 sm:grid-cols-3 lg:grid-cols-1">
                        <div class="rounded-2xl border border-white/10 bg-white/10 px-4 py-3">
                            <div class="i3p-label text-slate-200">Eleves</div>
                            <div class="mt-2 text-[2rem] font-bold">{{ $stats['eleves'] }}</div>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/10 px-4 py-3">
                            <div class="i3p-label text-slate-200">Inscriptions</div>
                            <div class="mt-2 text-[2rem] font-bold">{{ $stats['inscriptions'] }}</div>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/10 px-4 py-3">
                            <div class="i3p-label text-slate-200">Classes couvertes</div>
                            <div class="mt-2 text-[2rem] font-bold">{{ $stats['classes_couvertes'] }}</div>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/10 px-4 py-3">
                            <div class="i3p-label text-slate-200">Eleves avec historique</div>
                            <div class="mt-2 text-[2rem] font-bold">{{ $stats['eleves_historiques'] }}</div>
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
                    <p class="i3p-kicker text-[#b02f25]">Registre actuel</p>
                    <h2 class="i3p-section-title mt-2">Liste des inscriptions</h2>
                </div>
                <a href="{{ route('eleves.inscriptions.create') }}" class="i3p-link !border-[#0ca6e8]/20 !bg-[#0ca6e8]/10 !text-[#0f4d6a]">
                    Nouveau eleve
                </a>
            </div>

            <div class="mt-6 overflow-x-auto">
                <table class="i3p-table">
                    <thead>
                        <tr class="border-b border-slate-200 text-left">
                            <th>Matricule</th>
                            <th>Eleve</th>
                            <th>Classe</th>
                            <th>Annee</th>
                            <th>Contacts</th>
                            <th>Statut</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($inscriptions as $inscription)
                            @php
                                $historicalSummary = $historicalByEleve->get($inscription->eleve_id);
                            @endphp
                            <tr class="border-b border-slate-100 last:border-b-0">
                                <td class="font-bold text-slate-900">{{ $inscription->eleve?->matricule }}</td>
                                <td>
                                    <div class="text-[15px] font-bold text-slate-900">
                                        {{ $inscription->eleve?->nom }} {{ $inscription->eleve?->prenoms }}
                                    </div>
                                    <div class="mt-2 flex flex-wrap items-center gap-2">
                                        <span class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500">
                                            {{ $inscription->eleve?->sexe ?: 'N/A' }}
                                        </span>
                                        @if ($historicalSummary)
                                            <span class="i3p-badge border-[#0ca6e8]/20 bg-[#0ca6e8]/10 text-[#0f4d6a]">
                                                {{ $historicalSummary->year_count }} annee(s) historique(s)
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="font-bold text-slate-900">{{ $inscription->classe?->code }}</div>
                                    <div class="mt-1 text-slate-600">{{ $inscription->classe?->filiere?->nom ?? 'Non definie' }}</div>
                                </td>
                                <td class="text-slate-700">
                                    {{ $inscription->anneeScolaire?->libelle }}
                                </td>
                                <td>
                                    <div class="text-slate-700">{{ $inscription->eleve?->contact_principal ?: 'Non renseigne' }}</div>
                                    <div class="mt-1 text-slate-500">
                                        {{ $inscription->eleve?->nom_parent ?: 'Parent non renseigne' }}
                                        @if ($inscription->eleve?->contact_parent)
                                            · {{ $inscription->eleve?->contact_parent }}
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="i3p-badge border-[#0ca6e8]/20 bg-[#0ca6e8]/10 text-[#0f4d6a]">
                                        {{ ucfirst($inscription->statut) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ route('eleves.inscriptions.edit', $inscription) }}" class="i3p-link !border-[#0ca6e8]/20 !bg-[#0ca6e8]/10 !text-[#0f4d6a]">
                                            Modifier
                                        </a>
                                        @if ($historicalSummary)
                                            <a href="{{ route('bulletins.historiques', ['eleve_id' => $inscription->eleve_id]) }}" class="i3p-link !border-[#b02f25]/20 !bg-[#b02f25]/10 !text-[#7d221b]">
                                                Historique
                                            </a>
                                        @endif
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
