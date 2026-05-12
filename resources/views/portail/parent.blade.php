<x-app-layout>
    <x-slot name="header">
        <div class="i3p-parent-hero">
            <div class="relative z-10 grid gap-8 lg:grid-cols-[1.15fr_0.85fr]">
                <div class="space-y-5">
                    <span class="i3p-badge border-[#b02f25]/20 bg-[#b02f25]/10 text-[#8e251d]">Portail parents I3P</span>
                    <div>
                        <h1 class="i3p-title">Tableau de bord parent</h1>
                        <p class="i3p-copy mt-3 max-w-3xl">
                            Cet espace permet de consulter la situation scolaire, financiere et documentaire de
                            <span class="font-bold text-slate-900">{{ $eleve->nom }} {{ $eleve->prenoms }}</span>
                            dans un cadre de lecture simple et institutionnel.
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <span class="i3p-parent-chip">{{ Auth::user()->name }}</span>
                        <span class="i3p-parent-chip">{{ $parentRelation->lien_parente ?? 'Lien non defini' }}</span>
                        <span class="i3p-parent-chip">{{ $eleveOptions->count() }} enfant(s) rattache(s)</span>
                    </div>
                </div>

                <div class="i3p-parent-summary">
                    <p class="i3p-kicker text-[#f0c5ba]">Responsable rattache</p>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        <div class="i3p-parent-summary-item">
                            <div class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-200">Compte</div>
                            <div class="mt-2 text-[15px] font-bold">{{ Auth::user()->name }}</div>
                        </div>
                        <div class="i3p-parent-summary-item">
                            <div class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-200">Lien</div>
                            <div class="mt-2 text-[15px] font-bold">{{ $parentRelation->lien_parente ?? 'Non defini' }}</div>
                        </div>
                        <div class="i3p-parent-summary-item sm:col-span-2">
                            <div class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-200">Eleve selectionne</div>
                            <div class="mt-2 text-[15px] font-bold">{{ $eleve->matricule }} - {{ $eleve->nom }} {{ $eleve->prenoms }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="i3p-container mt-8 space-y-8">
        <section class="i3p-parent-selector">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="i3p-kicker text-[#b02f25]">Selection de l eleve</p>
                    <h2 class="i3p-section-title mt-2">Choisir l enfant a consulter</h2>
                    <p class="mt-2 text-[14px] leading-7 text-slate-600">
                        Change d enfant pour afficher sa situation scolaire, son statut financier et l acces a son bulletin.
                    </p>
                </div>
                <form method="GET" action="{{ route('portail.parent') }}" class="w-full max-w-xl">
                    <label for="eleve_id" class="i3p-label">Eleve rattache</label>
                    <div class="mt-2 flex flex-col gap-3 sm:flex-row">
                        <select
                            id="eleve_id"
                            name="eleve_id"
                            class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-[#0ca6e8] focus:outline-none focus:ring-2 focus:ring-[#0ca6e8]/20"
                        >
                            @foreach ($eleveOptions as $option)
                                <option value="{{ $option['eleve_id'] }}" @selected((int) $option['eleve_id'] === (int) $eleve->id)>
                                    {{ $option['label'] }}{{ $option['lien_parente'] ? ' / '.$option['lien_parente'] : '' }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="i3p-parent-action border-[#0ca6e8]/20 bg-[#0ca6e8]/10 text-[#0f4d6a] hover:border-[#0ca6e8]/35 hover:bg-[#0ca6e8]/15">
                            Afficher
                        </button>
                    </div>
                </form>
            </div>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="i3p-stat-card">
                <div class="i3p-label">Matricule</div>
                <div class="mt-3 text-[1.2rem] font-bold text-[#8e251d]">{{ $eleve->matricule }}</div>
            </article>
            <article class="i3p-stat-card">
                <div class="i3p-label">Classe</div>
                <div class="mt-3 text-[1.2rem] font-bold text-[#0f4d6a]">{{ $classe?->code ?? 'N/D' }}</div>
            </article>
            <article class="i3p-stat-card">
                <div class="i3p-label">Trimestre</div>
                <div class="mt-3 text-[1.2rem] font-bold text-[#8e251d]">{{ $trimestre?->libelle ?? 'N/D' }}</div>
            </article>
            <article class="i3p-stat-card">
                <div class="i3p-label">Acces bulletin</div>
                <div class="mt-3">
                    <span class="i3p-badge {{ $accesBulletinAutorise ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-[#b02f25]/20 bg-[#fff1ef] text-[#8e251d]' }}">
                        {{ $accesBulletinAutorise ? 'Autorise' : 'Bloque' }}
                    </span>
                </div>
            </article>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <article class="i3p-stat-card">
                <div class="i3p-label">Annee scolaire</div>
                <div class="mt-3 text-[1.1rem] font-bold text-[#0f4d6a]">{{ $anneeScolaire?->libelle ?? 'N/D' }}</div>
            </article>
            <article class="i3p-stat-card">
                <div class="i3p-label">Moyenne generale</div>
                <div class="mt-3 text-[1.4rem] font-bold text-[#8e251d]">
                    {{ $resultatsRapides['moyenne_generale'] !== null ? number_format($resultatsRapides['moyenne_generale'], 2, ',', ' ') : 'N/D' }}
                </div>
            </article>
            <article class="i3p-stat-card">
                <div class="i3p-label">Rang</div>
                <div class="mt-3 text-[1.4rem] font-bold text-[#0f4d6a]">{{ $resultatsRapides['rang'] ?? 'N/D' }}</div>
            </article>
        </section>

        <section class="grid gap-6 xl:grid-cols-2">
            <article class="i3p-card p-6">
                <p class="i3p-kicker text-[#b02f25]">Identification</p>
                <h2 class="i3p-section-title mt-2">Informations de l eleve</h2>

                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                        <div class="i3p-label">Nom complet</div>
                        <div class="mt-2 text-[15px] font-bold text-slate-900">{{ $eleve->nom }} {{ $eleve->prenoms }}</div>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                        <div class="i3p-label">Sexe</div>
                        <div class="mt-2 text-[15px] font-bold text-slate-900">{{ $eleve->sexe ?: 'N/D' }}</div>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                        <div class="i3p-label">Classe actuelle</div>
                        <div class="mt-2 text-[15px] font-bold text-slate-900">
                            {{ $classe?->code ? $classe->code.' - '.$classe->nom : 'N/D' }}
                        </div>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                        <div class="i3p-label">Filiere</div>
                        <div class="mt-2 text-[15px] font-bold text-slate-900">{{ $classe?->filiere?->nom ?? 'N/D' }}</div>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                        <div class="i3p-label">Date de naissance</div>
                        <div class="mt-2 text-[15px] font-bold text-slate-900">{{ $eleve->date_naissance?->format('d/m/Y') ?? 'N/D' }}</div>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                        <div class="i3p-label">Lieu de naissance</div>
                        <div class="mt-2 text-[15px] font-bold text-slate-900">{{ $eleve->lieu_naissance ?: 'N/D' }}</div>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                        <div class="i3p-label">Contact principal</div>
                        <div class="mt-2 text-[15px] font-bold text-slate-900">{{ $eleve->contact_principal ?: 'N/D' }}</div>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                        <div class="i3p-label">Parent declare</div>
                        <div class="mt-2 text-[15px] font-bold text-slate-900">{{ $eleve->nom_parent ?: 'N/D' }}</div>
                    </div>
                </div>
            </article>

            <article class="i3p-card p-6">
                <p class="i3p-kicker text-[#b02f25]">Scolarite actuelle</p>
                <h2 class="i3p-section-title mt-2">Situation scolaire</h2>

                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                        <div class="i3p-label">Classe</div>
                        <div class="mt-2 text-[15px] font-bold text-slate-900">
                            {{ $classe?->code ? $classe->code.' - '.$classe->nom : 'N/D' }}
                        </div>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                        <div class="i3p-label">Filiere</div>
                        <div class="mt-2 text-[15px] font-bold text-slate-900">{{ $classe?->filiere?->nom ?? 'N/D' }}</div>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                        <div class="i3p-label">Annee scolaire</div>
                        <div class="mt-2 text-[15px] font-bold text-slate-900">{{ $anneeScolaire?->libelle ?? 'N/D' }}</div>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                        <div class="i3p-label">Matieres calculees</div>
                        <div class="mt-2 text-[15px] font-bold text-slate-900">{{ $resultatsRapides['matieres'] }}</div>
                    </div>
                </div>
            </article>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
            <article class="i3p-card p-6">
                <p class="i3p-kicker text-[#b02f25]">Situation financiere</p>
                <h2 class="i3p-section-title mt-2">Condition d acces au bulletin</h2>

                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-2xl border border-slate-200 bg-white p-4 sm:col-span-2">
                        <div class="i3p-label">Statut</div>
                        <div class="mt-2">
                            <span class="i3p-badge {{
                                $paiementStatut?->statut === 'a_jour' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' :
                                ($paiementStatut?->statut === 'autorisation_exceptionnelle' ? 'border-[#0ca6e8]/20 bg-[#0ca6e8]/10 text-[#0f4d6a]' :
                                ($paiementStatut?->statut === 'bloque' ? 'border-[#b02f25]/20 bg-[#fff1ef] text-[#8e251d]' :
                                'border-amber-200 bg-amber-50 text-amber-700'))
                            }}">
                                {{ $paiementStatut ? str_replace('_', ' ', ucfirst($paiementStatut->statut)) : 'Non defini' }}
                            </span>
                        </div>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                        <div class="i3p-label">Montant attendu</div>
                        <div class="mt-2 text-[15px] font-bold text-slate-900">
                            {{ $paiementStatut?->montant_attendu !== null ? number_format((float) $paiementStatut->montant_attendu, 0, ',', ' ') : 'N/D' }}
                        </div>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                        <div class="i3p-label">Montant paye</div>
                        <div class="mt-2 text-[15px] font-bold text-slate-900">
                            {{ $paiementStatut?->montant_paye !== null ? number_format((float) $paiementStatut->montant_paye, 0, ',', ' ') : 'N/D' }}
                        </div>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                        <div class="i3p-label">Dernier paiement</div>
                        <div class="mt-2 text-[15px] font-bold text-slate-900">
                            {{ $paiementStatut?->date_dernier_paiement?->format('d/m/Y') ?? 'N/D' }}
                        </div>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                        <div class="i3p-label">Acces bulletin</div>
                        <div class="mt-2">
                            <span class="i3p-badge {{ $accesBulletinAutorise ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-[#b02f25]/20 bg-[#fff1ef] text-[#8e251d]' }}">
                                {{ $accesBulletinAutorise ? 'Autorise' : 'Bloque' }}
                            </span>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-4 sm:col-span-2">
                        <div class="i3p-label">Observation</div>
                        <div class="mt-2 text-[14px] leading-7 text-slate-700">
                            {{ $paiementStatut?->observation ?: 'Aucune observation comptable disponible.' }}
                        </div>
                    </div>
                </div>
            </article>

            <article class="i3p-card p-6">
                <p class="i3p-kicker text-[#b02f25]">Suivi du bulletin</p>
                <h2 class="i3p-section-title mt-2">Consultation parent</h2>

                <div class="mt-5 space-y-4">
                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                        <div class="i3p-label">Trimestre disponible</div>
                        <div class="mt-2 text-[15px] font-bold text-slate-900">{{ $trimestre?->libelle ?? 'Aucun trimestre disponible' }}</div>
                    </div>

                    <div class="rounded-2xl border {{ $accesBulletinAutorise ? 'border-emerald-200 bg-emerald-50/70' : 'border-[#b02f25]/20 bg-[#fff1ef]' }} p-4">
                        <div class="text-[13px] font-bold uppercase tracking-[0.16em] {{ $accesBulletinAutorise ? 'text-emerald-700' : 'text-[#8e251d]' }}">
                            {{ $messagePaiement['titre'] }}
                        </div>
                        <div class="mt-2 text-[14px] leading-7 {{ $accesBulletinAutorise ? 'text-emerald-800' : 'text-[#8e251d]' }}">
                            {{ $messagePaiement['message'] }}
                        </div>
                    </div>

                    @if ($trimestre && $accesBulletinAutorise)
                        <a href="{{ route('bulletins.show', [$eleve, $trimestre]) }}" class="i3p-parent-action border-[#0ca6e8]/20 bg-[#0ca6e8]/10 text-[#0f4d6a] hover:border-[#0ca6e8]/35 hover:bg-[#0ca6e8]/15">
                            Consulter le bulletin
                        </a>
                    @else
                        <div class="rounded-2xl border border-[#b02f25]/20 bg-[#fff1ef] px-5 py-4 text-sm font-semibold text-[#8e251d]">
                            La consultation du bulletin n est pas disponible pour le moment.
                        </div>
                    @endif
                </div>
            </article>
        </section>
    </div>
</x-app-layout>
