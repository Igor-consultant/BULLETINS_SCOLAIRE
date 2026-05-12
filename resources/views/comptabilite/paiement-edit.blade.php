<x-app-layout>
    <x-slot name="header">
        <div class="i3p-card overflow-hidden">
            <div class="grid gap-8 px-6 py-8 lg:grid-cols-[1.15fr_0.85fr] lg:px-8">
                <div>
                    <span class="i3p-badge border-[#b02f25]/20 bg-[#b02f25]/10 text-[#8e251d]">Comptabilite detaillee</span>
                    <h1 class="i3p-title mt-4">Modifier une ecriture de paiement</h1>
                    <p class="i3p-copy mt-3 max-w-3xl">
                        Ajuste ici une ecriture existante pour {{ $paiementStatut->eleve?->matricule }} - {{ $paiementStatut->eleve?->nom }} {{ $paiementStatut->eleve?->prenoms }}.
                    </p>
                </div>

                <div class="rounded-[2rem] border border-white/70 bg-gradient-to-br from-[#10233d] to-[#1f4765] p-6 text-white shadow-[0_18px_60px_rgba(15,23,42,0.18)]">
                    <p class="i3p-kicker text-[#f0c5ba]">Contexte</p>
                    <div class="mt-4 space-y-3 text-[14px] leading-7 text-slate-200">
                        <div><span class="font-bold text-white">Eleve :</span> {{ $paiementStatut->eleve?->matricule }} - {{ $paiementStatut->eleve?->nom }} {{ $paiementStatut->eleve?->prenoms }}</div>
                        <div><span class="font-bold text-white">Annee :</span> {{ $paiementStatut->anneeScolaire?->libelle }}</div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="i3p-container mt-8 space-y-8">
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
            <form method="POST" action="{{ route('comptabilite.paiements.update', [$paiementStatut, $paiement]) }}" class="grid gap-6 lg:grid-cols-2">
                @csrf
                @method('PUT')

                <div>
                    <label for="date_paiement" class="i3p-label">Date de paiement</label>
                    <input id="date_paiement" name="date_paiement" type="date" value="{{ old('date_paiement', $paiement->date_paiement?->format('Y-m-d')) }}" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-[#0ca6e8] focus:outline-none focus:ring-2 focus:ring-[#0ca6e8]/20" required>
                </div>

                <div>
                    <label for="montant" class="i3p-label">Montant</label>
                    <input id="montant" name="montant" type="number" min="0.01" step="0.01" value="{{ old('montant', (float) $paiement->montant) }}" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-[#0ca6e8] focus:outline-none focus:ring-2 focus:ring-[#0ca6e8]/20" required>
                </div>

                <div>
                    <label for="mode_paiement" class="i3p-label">Mode de paiement</label>
                    <select id="mode_paiement" name="mode_paiement" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-[#0ca6e8] focus:outline-none focus:ring-2 focus:ring-[#0ca6e8]/20" required>
                        @foreach ($modesPaiement as $value => $label)
                            <option value="{{ $value }}" @selected(old('mode_paiement', $paiement->mode_paiement) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="reference" class="i3p-label">Reference</label>
                    <input id="reference" name="reference" type="text" value="{{ old('reference', $paiement->reference) }}" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-[#0ca6e8] focus:outline-none focus:ring-2 focus:ring-[#0ca6e8]/20">
                </div>

                <div class="lg:col-span-2">
                    <label for="libelle" class="i3p-label">Libelle</label>
                    <input id="libelle" name="libelle" type="text" value="{{ old('libelle', $paiement->libelle) }}" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-[#0ca6e8] focus:outline-none focus:ring-2 focus:ring-[#0ca6e8]/20">
                </div>

                <div class="lg:col-span-2">
                    <label for="observation" class="i3p-label">Observation</label>
                    <textarea id="observation" name="observation" rows="4" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-[#0ca6e8] focus:outline-none focus:ring-2 focus:ring-[#0ca6e8]/20">{{ old('observation', $paiement->observation) }}</textarea>
                </div>

                <div class="lg:col-span-2 flex flex-wrap gap-3">
                    <button type="submit" class="i3p-link !border-[#b02f25]/20 !bg-[#b02f25]/10 !text-[#7d221b]">
                        Enregistrer les modifications
                    </button>
                    <a href="{{ route('comptabilite.paiements', $paiementStatut) }}" class="i3p-link !border-slate-200 !bg-slate-50 !text-slate-700">
                        Retour au registre
                    </a>
                </div>
            </form>
        </section>
    </div>
</x-app-layout>
