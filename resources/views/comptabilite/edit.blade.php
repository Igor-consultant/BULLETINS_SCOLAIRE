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

        <section class="grid gap-5 xl:grid-cols-[1.1fr_0.9fr]">
            <article class="i3p-card p-6">
                <p class="i3p-kicker text-[#b02f25]">Structure de mise a jour</p>
                <h2 class="i3p-section-title mt-2">Statut, montants et acces</h2>
                <div class="mt-5 grid gap-4 sm:grid-cols-3">
                    <div class="i3p-priority-card">
                        <div class="i3p-action-kicker">Bloc 1</div>
                        <div class="mt-2 text-base font-bold text-slate-950">Decision comptable</div>
                        <div class="mt-2 text-sm leading-6 text-slate-600">Choisir le bon statut et la politique d acces au bulletin.</div>
                    </div>
                    <div class="i3p-priority-card">
                        <div class="i3p-action-kicker">Bloc 2</div>
                        <div class="mt-2 text-base font-bold text-slate-950">Montants</div>
                        <div class="mt-2 text-sm leading-6 text-slate-600">Renseigner ce qui est attendu, paye et la date la plus recente.</div>
                    </div>
                    <div class="i3p-priority-card">
                        <div class="i3p-action-kicker">Bloc 3</div>
                        <div class="mt-2 text-base font-bold text-slate-950">Observation</div>
                        <div class="mt-2 text-sm leading-6 text-slate-600">Expliquer les cas particuliers et les decisions exceptionnelles.</div>
                    </div>
                </div>
            </article>

            <article class="i3p-card p-6">
                <p class="i3p-kicker text-[#b02f25]">Conseil de gestion</p>
                <h2 class="i3p-section-title mt-2">Bonnes pratiques</h2>
                <div class="mt-5 space-y-4">
                    <div class="i3p-priority-card">
                        <div class="i3p-priority-title">Garder acces et statut coherents</div>
                        <div class="mt-2 text-sm leading-6 text-slate-600">Un dossier bloque ne devrait pas laisser un acces bulletin autorise sans justification claire.</div>
                    </div>
                    <div class="i3p-priority-card">
                        <div class="i3p-priority-title">Documenter les exceptions</div>
                        <div class="mt-2 text-sm leading-6 text-slate-600">Toute autorisation exceptionnelle doit etre accompagnee d une observation explicite.</div>
                    </div>
                </div>
            </article>
        </section>

        <section class="i3p-card p-6">
            <form method="POST" action="{{ route('comptabilite.update', $paiementStatut) }}" class="space-y-8">
                @csrf
                @method('PUT')

                <section class="rounded-[1.75rem] border border-slate-200 bg-white p-6">
                    <div>
                        <p class="i3p-kicker text-[#b02f25]">Bloc 1</p>
                        <h2 class="mt-2 text-xl font-bold tracking-[-0.02em] text-slate-950">Decision comptable</h2>
                    </div>

                    <div class="mt-6 grid gap-6 lg:grid-cols-2">
                        <div>
                            <label for="statut" class="i3p-label">Statut</label>
                            <select id="statut" name="statut" class="mt-2 w-full">
                                @foreach ($statutsDisponibles as $value => $label)
                                    <option value="{{ $value }}" @selected(old('statut', $paiementStatut->statut) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="rounded-[1.4rem] border border-slate-200 bg-slate-50/70 px-4 py-4">
                            <div class="i3p-label">Acces au bulletin</div>
                            <label for="autorise_acces_bulletin" class="mt-3 inline-flex items-center gap-3 text-sm font-semibold text-slate-700">
                                <input type="hidden" name="autorise_acces_bulletin" value="0">
                                <input id="autorise_acces_bulletin" type="checkbox" name="autorise_acces_bulletin" value="1" @checked(old('autorise_acces_bulletin', $paiementStatut->autorise_acces_bulletin)) class="h-5 w-5 rounded border-slate-300 text-[#0ca6e8] focus:ring-[#0ca6e8]/30">
                                Autoriser l acces au bulletin
                            </label>
                        </div>
                    </div>
                </section>

                <section class="rounded-[1.75rem] border border-slate-200 bg-white p-6">
                    <div>
                        <p class="i3p-kicker text-[#b02f25]">Bloc 2</p>
                        <h2 class="mt-2 text-xl font-bold tracking-[-0.02em] text-slate-950">Montants et date</h2>
                    </div>

                    <div class="mt-6 grid gap-6 lg:grid-cols-3">
                        <div>
                            <label for="montant_attendu" class="i3p-label">Montant attendu</label>
                            <input id="montant_attendu" name="montant_attendu" type="number" min="0" step="0.01" value="{{ old('montant_attendu', $paiementStatut->montant_attendu) }}" class="mt-2 w-full">
                        </div>

                        <div>
                            <label for="montant_paye" class="i3p-label">Montant paye</label>
                            <input id="montant_paye" name="montant_paye" type="number" min="0" step="0.01" value="{{ old('montant_paye', $paiementStatut->montant_paye) }}" class="mt-2 w-full">
                        </div>

                        <div>
                            <label for="date_dernier_paiement" class="i3p-label">Date du dernier paiement</label>
                            <input id="date_dernier_paiement" name="date_dernier_paiement" type="date" value="{{ old('date_dernier_paiement', $paiementStatut->date_dernier_paiement?->format('Y-m-d')) }}" class="mt-2 w-full">
                        </div>
                    </div>
                </section>

                <section class="rounded-[1.75rem] border border-slate-200 bg-white p-6">
                    <div>
                        <p class="i3p-kicker text-[#b02f25]">Bloc 3</p>
                        <h2 class="mt-2 text-xl font-bold tracking-[-0.02em] text-slate-950">Observation</h2>
                    </div>

                    <div class="mt-6">
                        <label for="observation" class="i3p-label">Observation</label>
                        <textarea id="observation" name="observation" rows="4" class="mt-2 w-full">{{ old('observation', $paiementStatut->observation) }}</textarea>
                    </div>
                </section>

                <div class="flex flex-wrap gap-3">
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
