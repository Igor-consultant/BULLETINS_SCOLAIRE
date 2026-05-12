<x-app-layout>
    <x-slot name="header">
        <div class="i3p-card overflow-hidden">
            <div class="grid gap-8 px-6 py-8 lg:grid-cols-[1.15fr_0.85fr] lg:px-8">
                <div>
                    <span class="i3p-badge border-[#b02f25]/20 bg-[#b02f25]/10 text-[#8e251d]">Comptabilite scolaire</span>
                    <h1 class="i3p-title mt-4">Modifier un statut financier</h1>
                    <p class="i3p-copy mt-3 max-w-3xl">
                        Mets a jour ici le statut comptable, les montants et l autorisation d acces au bulletin pour l eleve selectionne.
                    </p>
                </div>

                <div class="rounded-[2rem] border border-white/70 bg-gradient-to-br from-[#10233d] to-[#1f4765] p-6 text-white shadow-[0_18px_60px_rgba(15,23,42,0.18)]">
                    <p class="i3p-kicker text-[#f0c5ba]">Eleve concerne</p>
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
            <form method="POST" action="{{ route('comptabilite.update', $paiementStatut) }}" class="grid gap-6 lg:grid-cols-2">
                @csrf
                @method('PUT')

                <div>
                    <label for="statut" class="i3p-label">Statut</label>
                    <select id="statut" name="statut" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-[#0ca6e8] focus:outline-none focus:ring-2 focus:ring-[#0ca6e8]/20">
                        @foreach ($statutsDisponibles as $value => $label)
                            <option value="{{ $value }}" @selected(old('statut', $paiementStatut->statut) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3">
                    <input type="hidden" name="autorise_acces_bulletin" value="0">
                    <input id="autorise_acces_bulletin" type="checkbox" name="autorise_acces_bulletin" value="1" @checked(old('autorise_acces_bulletin', $paiementStatut->autorise_acces_bulletin)) class="h-5 w-5 rounded border-slate-300 text-[#0ca6e8] focus:ring-[#0ca6e8]/30">
                    <label for="autorise_acces_bulletin" class="text-sm font-semibold text-slate-700">Autoriser l acces au bulletin</label>
                </div>

                <div>
                    <label for="montant_attendu" class="i3p-label">Montant attendu</label>
                    <input id="montant_attendu" name="montant_attendu" type="number" min="0" step="0.01" value="{{ old('montant_attendu', $paiementStatut->montant_attendu) }}" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-[#0ca6e8] focus:outline-none focus:ring-2 focus:ring-[#0ca6e8]/20">
                </div>

                <div>
                    <label for="montant_paye" class="i3p-label">Montant paye</label>
                    <input id="montant_paye" name="montant_paye" type="number" min="0" step="0.01" value="{{ old('montant_paye', $paiementStatut->montant_paye) }}" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-[#0ca6e8] focus:outline-none focus:ring-2 focus:ring-[#0ca6e8]/20">
                </div>

                <div>
                    <label for="date_dernier_paiement" class="i3p-label">Date du dernier paiement</label>
                    <input id="date_dernier_paiement" name="date_dernier_paiement" type="date" value="{{ old('date_dernier_paiement', $paiementStatut->date_dernier_paiement?->format('Y-m-d')) }}" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-[#0ca6e8] focus:outline-none focus:ring-2 focus:ring-[#0ca6e8]/20">
                </div>

                <div class="lg:col-span-2">
                    <label for="observation" class="i3p-label">Observation</label>
                    <textarea id="observation" name="observation" rows="4" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-[#0ca6e8] focus:outline-none focus:ring-2 focus:ring-[#0ca6e8]/20">{{ old('observation', $paiementStatut->observation) }}</textarea>
                </div>

                <div class="lg:col-span-2 flex flex-wrap gap-3">
                    <button type="submit" class="i3p-link !border-[#b02f25]/20 !bg-[#b02f25]/10 !text-[#7d221b]">
                        Enregistrer les modifications
                    </button>
                    <a href="{{ route('comptabilite.statuts') }}" class="i3p-link !border-slate-200 !bg-slate-100 !text-slate-700">
                        Retour a la liste
                    </a>
                </div>
            </form>
        </section>
    </div>
</x-app-layout>
