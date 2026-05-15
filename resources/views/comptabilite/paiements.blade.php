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
                            <div class="i3p-label text-slate-200">Ecart restant</div>
                            <div class="mt-2 text-[2rem] font-bold">{{ number_format((float) $stats['ecart'], 0, ',', ' ') }}</div>
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

        <section class="grid gap-5 xl:grid-cols-[1.1fr_0.9fr]">
            <article class="i3p-card p-6">
                <p class="i3p-kicker text-[#b02f25]">Encaissement</p>
                <h2 class="i3p-section-title mt-2">Nouvelle ecriture</h2>
                <form method="POST" action="{{ route('comptabilite.paiements.store', $paiementStatut) }}" class="mt-6 grid gap-6 lg:grid-cols-2">
                    @csrf

                    <div>
                        <label for="date_paiement" class="i3p-label">Date de paiement</label>
                        <input id="date_paiement" name="date_paiement" type="date" value="{{ old('date_paiement', now()->format('Y-m-d')) }}" class="mt-2 w-full" required>
                    </div>

                    <div>
                        <label for="montant" class="i3p-label">Montant</label>
                        <input id="montant" name="montant" type="number" min="0.01" step="0.01" value="{{ old('montant') }}" class="mt-2 w-full" required>
                    </div>

                    <div>
                        <label for="mode_paiement" class="i3p-label">Mode de paiement</label>
                        <select id="mode_paiement" name="mode_paiement" class="mt-2 w-full" required>
                            @foreach ($modesPaiement as $value => $label)
                                <option value="{{ $value }}" @selected(old('mode_paiement', 'especes') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="reference" class="i3p-label">Reference</label>
                        <input id="reference" name="reference" type="text" value="{{ old('reference') }}" class="mt-2 w-full">
                    </div>

                    <div class="lg:col-span-2">
                        <label for="libelle" class="i3p-label">Libelle</label>
                        <input id="libelle" name="libelle" type="text" value="{{ old('libelle') }}" class="mt-2 w-full" placeholder="Exemple : 1er versement trimestre 1">
                    </div>

                    <div class="lg:col-span-2">
                        <label for="observation" class="i3p-label">Observation</label>
                        <textarea id="observation" name="observation" rows="3" class="mt-2 w-full">{{ old('observation') }}</textarea>
                    </div>

                    <div class="lg:col-span-2">
                        <button type="submit" class="i3p-link !border-[#b02f25]/20 !bg-[#b02f25]/10 !text-[#7d221b]">
                            Enregistrer le paiement
                        </button>
                    </div>
                </form>
            </article>

            <article class="i3p-card p-6">
                <p class="i3p-kicker text-[#b02f25]">Lecture rapide</p>
                <h2 class="i3p-section-title mt-2">Resume du dossier</h2>
                <div class="mt-6 space-y-4">
                    <div class="i3p-priority-card">
                        <div class="i3p-label">Statut</div>
                        <div class="mt-2 text-lg font-bold text-slate-950">{{ str_replace('_', ' ', ucfirst($paiementStatut->statut)) }}</div>
                    </div>
                    <div class="i3p-priority-card">
                        <div class="i3p-label">Acces bulletin</div>
                        <div class="mt-2 text-lg font-bold {{ $paiementStatut->autorise_acces_bulletin ? 'text-emerald-700' : 'text-[#8e251d]' }}">
                            {{ $paiementStatut->autorise_acces_bulletin ? 'Autorise' : 'Bloque' }}
                        </div>
                    </div>
                    <div class="i3p-priority-card">
                        <div class="i3p-label">Nombre d ecritures</div>
                        <div class="mt-2 text-lg font-bold text-slate-950">{{ $stats['ecritures'] }}</div>
                    </div>
                    <div class="i3p-priority-card">
                        <div class="i3p-label">Retour rapide</div>
                        <div class="mt-3">
                            <a href="{{ route('comptabilite.statuts') }}" class="i3p-link !border-slate-200 !bg-slate-100 !text-slate-700">
                                Retour aux statuts
                            </a>
                        </div>
                    </div>
                </div>
            </article>
        </section>

        <section class="i3p-card p-6">
            <div>
                <p class="i3p-kicker text-[#b02f25]">Historique</p>
                <h2 class="i3p-section-title mt-2">Ecritures enregistrees</h2>
            </div>

            <div class="mt-6 space-y-4">
                @forelse ($paiementStatut->paiements as $paiement)
                    <article class="i3p-record-card">
                        <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-3">
                                    <span class="i3p-badge border-slate-200 bg-slate-100 text-slate-700">{{ $paiement->date_paiement?->format('d/m/Y') }}</span>
                                    <span class="i3p-badge border-[#0ca6e8]/20 bg-[#0ca6e8]/10 text-[#0f4d6a]">{{ ucfirst(str_replace('_', ' ', $paiement->mode_paiement)) }}</span>
                                </div>

                                <div class="mt-4">
                                    <h3 class="text-xl font-bold tracking-[-0.02em] text-slate-950">{{ number_format((float) $paiement->montant, 0, ',', ' ') }}</h3>
                                    <p class="mt-2 text-sm text-slate-500">{{ $paiement->libelle ?: 'Libelle non renseigne' }}</p>
                                </div>

                                <div class="mt-5 grid gap-4 md:grid-cols-3">
                                    <div class="i3p-record-meta">
                                        <div class="i3p-label">Reference</div>
                                        <div class="mt-2 font-bold text-slate-950">{{ $paiement->reference ?: 'N/D' }}</div>
                                    </div>
                                    <div class="i3p-record-meta md:col-span-2">
                                        <div class="i3p-label">Observation</div>
                                        <div class="mt-2 text-sm leading-6 text-slate-600">{{ $paiement->observation ?: 'Aucune observation' }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex w-full flex-col gap-3 xl:w-[14rem]">
                                <a href="{{ route('comptabilite.paiements.edit', [$paiementStatut, $paiement]) }}" class="i3p-link !border-slate-200 !bg-slate-100 !text-slate-700">
                                    Modifier
                                </a>
                                <form method="POST" action="{{ route('comptabilite.paiements.destroy', [$paiementStatut, $paiement]) }}" onsubmit="return confirm('Confirmer la suppression de cette ecriture de paiement ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="i3p-link w-full !border-[#b02f25]/20 !bg-[#fff5f3] !text-[#8e251d]">
                                        Supprimer
                                    </button>
                                </form>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-3xl border border-dashed border-slate-300 bg-white/70 px-6 py-8 text-center">
                        <div class="text-lg font-bold text-slate-950">Aucun paiement enregistre pour le moment.</div>
                        <div class="mt-2 text-sm leading-7 text-slate-600">
                            Ajoute une premiere ecriture pour demarrer le registre.
                        </div>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
