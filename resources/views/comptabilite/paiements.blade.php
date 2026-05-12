<x-app-layout>
    <x-slot name="header">
        <div class="i3p-card overflow-hidden">
            <div class="grid gap-8 px-6 py-8 lg:grid-cols-[1.15fr_0.85fr] lg:px-8">
                <div>
                    <span class="i3p-badge border-[#b02f25]/20 bg-[#b02f25]/10 text-[#8e251d]">Comptabilite detaillee</span>
                    <h1 class="i3p-title mt-4">Registre des paiements</h1>
                    <p class="i3p-copy mt-3 max-w-3xl">
                        Suivi detaille des encaissements pour {{ $paiementStatut->eleve?->matricule }} - {{ $paiementStatut->eleve?->nom }} {{ $paiementStatut->eleve?->prenoms }}.
                    </p>
                </div>

                <div class="rounded-[2rem] border border-white/70 bg-gradient-to-br from-[#10233d] to-[#1f4765] p-6 text-white shadow-[0_18px_60px_rgba(15,23,42,0.18)]">
                    <p class="i3p-kicker text-[#f0c5ba]">Synthese financiere</p>
                    <div class="mt-4 space-y-3">
                        <div class="rounded-2xl border border-white/10 bg-white/10 px-4 py-3">
                            <div class="i3p-label text-slate-200">Montant attendu</div>
                            <div class="mt-2 text-[2rem] font-bold">{{ $paiementStatut->montant_attendu !== null ? number_format((float) $paiementStatut->montant_attendu, 0, ',', ' ') : 'N/D' }}</div>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/10 px-4 py-3">
                            <div class="i3p-label text-slate-200">Montant paye</div>
                            <div class="mt-2 text-[2rem] font-bold">{{ $paiementStatut->montant_paye !== null ? number_format((float) $paiementStatut->montant_paye, 0, ',', ' ') : '0' }}</div>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/10 px-4 py-3">
                            <div class="i3p-label text-slate-200">Ecritures</div>
                            <div class="mt-2 text-[2rem] font-bold">{{ $paiementStatut->paiements->count() }}</div>
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

        <section class="i3p-card p-6">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="i3p-kicker text-[#b02f25]">Nouvel encaissement</p>
                    <h2 class="i3p-section-title mt-2">Ajouter une ecriture</h2>
                </div>
                <a href="{{ route('comptabilite.statuts') }}" class="i3p-link !border-slate-200 !bg-slate-50 !text-slate-700">
                    Retour aux statuts
                </a>
            </div>

            <form method="POST" action="{{ route('comptabilite.paiements.store', $paiementStatut) }}" class="mt-6 grid gap-6 lg:grid-cols-2">
                @csrf

                <div>
                    <label for="date_paiement" class="i3p-label">Date de paiement</label>
                    <input id="date_paiement" name="date_paiement" type="date" value="{{ old('date_paiement', now()->format('Y-m-d')) }}" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-[#0ca6e8] focus:outline-none focus:ring-2 focus:ring-[#0ca6e8]/20" required>
                </div>

                <div>
                    <label for="montant" class="i3p-label">Montant</label>
                    <input id="montant" name="montant" type="number" min="0.01" step="0.01" value="{{ old('montant') }}" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-[#0ca6e8] focus:outline-none focus:ring-2 focus:ring-[#0ca6e8]/20" required>
                </div>

                <div>
                    <label for="mode_paiement" class="i3p-label">Mode de paiement</label>
                    <select id="mode_paiement" name="mode_paiement" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-[#0ca6e8] focus:outline-none focus:ring-2 focus:ring-[#0ca6e8]/20" required>
                        @foreach ($modesPaiement as $value => $label)
                            <option value="{{ $value }}" @selected(old('mode_paiement', 'especes') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="reference" class="i3p-label">Reference</label>
                    <input id="reference" name="reference" type="text" value="{{ old('reference') }}" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-[#0ca6e8] focus:outline-none focus:ring-2 focus:ring-[#0ca6e8]/20">
                </div>

                <div class="lg:col-span-2">
                    <label for="libelle" class="i3p-label">Libelle</label>
                    <input id="libelle" name="libelle" type="text" value="{{ old('libelle') }}" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-[#0ca6e8] focus:outline-none focus:ring-2 focus:ring-[#0ca6e8]/20" placeholder="Exemple : 1er versement trimestre 1">
                </div>

                <div class="lg:col-span-2">
                    <label for="observation" class="i3p-label">Observation</label>
                    <textarea id="observation" name="observation" rows="3" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-[#0ca6e8] focus:outline-none focus:ring-2 focus:ring-[#0ca6e8]/20">{{ old('observation') }}</textarea>
                </div>

                <div class="lg:col-span-2">
                    <button type="submit" class="i3p-link !border-[#b02f25]/20 !bg-[#b02f25]/10 !text-[#7d221b]">
                        Enregistrer le paiement
                    </button>
                </div>
            </form>
        </section>

        <section class="i3p-card p-6">
            <div>
                <p class="i3p-kicker text-[#b02f25]">Historique</p>
                <h2 class="i3p-section-title mt-2">Ecritures enregistrees</h2>
            </div>

            <div class="mt-6 overflow-x-auto">
                <table class="i3p-table">
                    <thead>
                        <tr class="border-b border-slate-200 text-left">
                            <th>Date</th>
                            <th>Montant</th>
                            <th>Mode</th>
                            <th>Reference</th>
                            <th>Libelle</th>
                            <th>Observation</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($paiementStatut->paiements as $paiement)
                            <tr class="border-b border-slate-100 last:border-b-0">
                                <td class="text-slate-700">{{ $paiement->date_paiement?->format('d/m/Y') }}</td>
                                <td class="font-bold text-slate-900">{{ number_format((float) $paiement->montant, 0, ',', ' ') }}</td>
                                <td class="text-slate-700">{{ ucfirst(str_replace('_', ' ', $paiement->mode_paiement)) }}</td>
                                <td class="text-slate-700">{{ $paiement->reference ?: 'N/D' }}</td>
                                <td class="text-slate-700">{{ $paiement->libelle ?: 'N/D' }}</td>
                                <td class="text-slate-700">{{ $paiement->observation ?: 'Aucune observation' }}</td>
                                <td>
                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ route('comptabilite.paiements.edit', [$paiementStatut, $paiement]) }}" class="i3p-link !border-slate-200 !bg-slate-50 !text-slate-700">
                                            Modifier
                                        </a>
                                        <form method="POST" action="{{ route('comptabilite.paiements.destroy', [$paiementStatut, $paiement]) }}" onsubmit="return confirm('Confirmer la suppression de cette ecriture de paiement ?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="i3p-link !border-[#b02f25]/20 !bg-[#fff5f3] !text-[#8e251d]">
                                                Supprimer
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-6 text-center text-slate-500">Aucun paiement enregistre pour le moment.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>
