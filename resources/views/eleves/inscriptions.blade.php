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

        <section class="grid gap-5 xl:grid-cols-[1.15fr_0.85fr]">
            <article class="i3p-card p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="i3p-kicker text-[#b02f25]">Poste de travail scolarite</p>
                        <h2 class="i3p-section-title mt-2">Lecture operationnelle</h2>
                        <p class="mt-3 max-w-2xl text-[14px] leading-7 text-slate-600">
                            Cet ecran sert a suivre les dossiers, reperer les situations incompletes, retrouver un eleve rapidement
                            et ouvrir l historique sans perdre le contexte.
                        </p>
                    </div>
                    <span class="i3p-badge border-slate-200 bg-slate-100 text-slate-700">
                        {{ $stats['resultats_filtres'] }} ligne(s)
                    </span>
                </div>

                <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="i3p-priority-card">
                        <div class="i3p-action-kicker">Actifs</div>
                        <div class="mt-2 text-2xl font-bold text-slate-950">{{ $stats['statuts_filtres']['inscrit'] }}</div>
                        <div class="mt-2 text-sm text-slate-600">inscriptions actuellement inscrites</div>
                    </div>
                    <div class="i3p-priority-card">
                        <div class="i3p-action-kicker">Transferts</div>
                        <div class="mt-2 text-2xl font-bold text-[#0f4d6a]">{{ $stats['statuts_filtres']['transfere'] }}</div>
                        <div class="mt-2 text-sm text-slate-600">dossiers a suivre hors flux standard</div>
                    </div>
                    <div class="i3p-priority-card">
                        <div class="i3p-action-kicker">Suspensions</div>
                        <div class="mt-2 text-2xl font-bold text-[#8e251d]">{{ $stats['statuts_filtres']['suspendu'] }}</div>
                        <div class="mt-2 text-sm text-slate-600">cas a verifier avec la direction</div>
                    </div>
                    <div class="i3p-priority-card">
                        <div class="i3p-action-kicker">Historiques</div>
                        <div class="mt-2 text-2xl font-bold text-slate-950">{{ $stats['historiques_filtres'] }}</div>
                        <div class="mt-2 text-sm text-slate-600">eleves filtres avec resultats anciens</div>
                    </div>
                </div>
            </article>

            <article class="i3p-card p-6">
                <p class="i3p-kicker text-[#b02f25]">Cadre scolaire</p>
                <h2 class="i3p-section-title mt-2">Contexte actuel</h2>
                <div class="mt-6 space-y-4">
                    <div class="i3p-priority-card">
                        <div class="i3p-label">Annee active</div>
                        <div class="mt-2 text-lg font-bold text-slate-950">{{ $activeYear?->libelle ?? 'Non definie' }}</div>
                    </div>
                    <div class="i3p-priority-card">
                        <div class="i3p-label">Classes disponibles</div>
                        <div class="mt-2 text-lg font-bold text-slate-950">{{ $classes->count() }}</div>
                    </div>
                    <div class="i3p-priority-card">
                        <div class="i3p-label">Usage de l ecran</div>
                        <div class="mt-2 text-sm leading-6 text-slate-600">
                            Recherche un eleve, filtre par statut, puis ouvre soit la fiche d edition soit l historique.
                        </div>
                    </div>
                </div>
            </article>
        </section>

        <section class="i3p-card p-6">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="i3p-kicker text-[#b02f25]">Recherche ciblee</p>
                    <h2 class="i3p-section-title mt-2">Trouver un dossier rapidement</h2>
                    <p class="mt-3 text-[14px] leading-7 text-slate-600">
                        Filtre par eleve, parent, statut, classe ou annee pour reduire la liste au strict necessaire.
                    </p>
                </div>
                <a href="{{ route('eleves.inscriptions.create') }}" class="i3p-link !border-[#0ca6e8]/20 !bg-[#0ca6e8]/10 !text-[#0f4d6a]">
                    Nouveau eleve
                </a>
            </div>

            <form method="GET" action="{{ route('eleves.inscriptions') }}" class="mt-6 grid gap-4 xl:grid-cols-[1.2fr_0.65fr_0.8fr_0.8fr_auto_auto]">
                <div>
                    <label for="q" class="i3p-label">Recherche</label>
                    <input id="q" name="q" type="text" value="{{ $filters['q'] }}" placeholder="Matricule, nom, prenoms, parent, contact" class="mt-2 w-full">
                </div>
                <div>
                    <label for="statut" class="i3p-label">Statut</label>
                    <select id="statut" name="statut" class="mt-2 w-full">
                        <option value="">Tous</option>
                        <option value="inscrit" @selected($filters['statut'] === 'inscrit')>Inscrit</option>
                        <option value="transfere" @selected($filters['statut'] === 'transfere')>Transfere</option>
                        <option value="abandonne" @selected($filters['statut'] === 'abandonne')>Abandonne</option>
                        <option value="suspendu" @selected($filters['statut'] === 'suspendu')>Suspendu</option>
                    </select>
                </div>
                <div>
                    <label for="classe_id" class="i3p-label">Classe</label>
                    <select id="classe_id" name="classe_id" class="mt-2 w-full">
                        <option value="">Toutes</option>
                        @foreach ($classes as $classe)
                            <option value="{{ $classe->id }}" @selected((string) $filters['classe_id'] === (string) $classe->id)>
                                {{ $classe->code }} - {{ $classe->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="annee_scolaire_id" class="i3p-label">Annee</label>
                    <select id="annee_scolaire_id" name="annee_scolaire_id" class="mt-2 w-full">
                        <option value="">Toutes</option>
                        @foreach ($anneesScolaires as $annee)
                            <option value="{{ $annee->id }}" @selected((string) $filters['annee_scolaire_id'] === (string) $annee->id)>
                                {{ $annee->libelle }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="i3p-link w-full !border-[#b02f25]/20 !bg-[#b02f25]/10 !text-[#7d221b]">
                        Filtrer
                    </button>
                </div>
                <div class="flex items-end">
                    <a href="{{ route('eleves.inscriptions') }}" class="i3p-link w-full !border-slate-200 !bg-slate-100 !text-slate-700">
                        Reinitialiser
                    </a>
                </div>
            </form>
        </section>

        <section class="i3p-card p-6">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="i3p-kicker text-[#b02f25]">Registre actuel</p>
                    <h2 class="i3p-section-title mt-2">Liste des inscriptions</h2>
                </div>
                <span class="i3p-badge border-slate-200 bg-slate-100 text-slate-700">
                    {{ $inscriptions->count() }} dossier(s) affiche(s)
                </span>
            </div>

            <div class="mt-6 space-y-4">
                @forelse ($inscriptions as $inscription)
                    @php
                        $historicalSummary = $historicalByEleve->get($inscription->eleve_id);
                        $statutClasses = match ($inscription->statut) {
                            'inscrit' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                            'transfere' => 'border-[#0ca6e8]/20 bg-[#0ca6e8]/10 text-[#0f4d6a]',
                            'suspendu' => 'border-[#b02f25]/20 bg-[#fff1ef] text-[#8e251d]',
                            'abandonne' => 'border-amber-200 bg-amber-50 text-amber-700',
                            default => 'border-slate-200 bg-slate-100 text-slate-700',
                        };
                    @endphp

                    <article class="i3p-record-card">
                        <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-3">
                                    <span class="i3p-badge border-slate-200 bg-slate-100 text-slate-700">{{ $inscription->eleve?->matricule }}</span>
                                    <span class="i3p-badge {{ $statutClasses }}">{{ ucfirst($inscription->statut) }}</span>
                                    @if ($historicalSummary)
                                        <span class="i3p-badge border-[#0ca6e8]/20 bg-[#0ca6e8]/10 text-[#0f4d6a]">
                                            {{ $historicalSummary->year_count }} annee(s) historique(s)
                                        </span>
                                    @endif
                                </div>

                                <div class="mt-4">
                                    <h3 class="text-xl font-bold tracking-[-0.02em] text-slate-950">
                                        {{ $inscription->eleve?->nom }} {{ $inscription->eleve?->prenoms }}
                                    </h3>
                                    <p class="mt-2 text-sm text-slate-500">
                                        {{ $inscription->eleve?->sexe ?: 'Sexe non precise' }}
                                        @if ($inscription->eleve?->date_naissance)
                                            · Ne(e) le {{ $inscription->eleve->date_naissance->format('d/m/Y') }}
                                        @endif
                                        @if ($inscription->eleve?->lieu_naissance)
                                            · {{ $inscription->eleve->lieu_naissance }}
                                        @endif
                                    </p>
                                </div>

                                <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                                    <div class="i3p-record-meta">
                                        <div class="i3p-label">Classe</div>
                                        <div class="mt-2 font-bold text-slate-950">{{ $inscription->classe?->code }} - {{ $inscription->classe?->nom }}</div>
                                        <div class="mt-1 text-sm text-slate-500">{{ $inscription->classe?->filiere?->nom ?? 'Filiere non definie' }}</div>
                                    </div>
                                    <div class="i3p-record-meta">
                                        <div class="i3p-label">Annee scolaire</div>
                                        <div class="mt-2 font-bold text-slate-950">{{ $inscription->anneeScolaire?->libelle ?? 'N/D' }}</div>
                                        <div class="mt-1 text-sm text-slate-500">
                                            @if ($inscription->date_inscription)
                                                Inscrit le {{ $inscription->date_inscription->format('d/m/Y') }}
                                            @else
                                                Date d inscription non renseignee
                                            @endif
                                        </div>
                                    </div>
                                    <div class="i3p-record-meta">
                                        <div class="i3p-label">Contact principal</div>
                                        <div class="mt-2 font-bold text-slate-950">{{ $inscription->eleve?->contact_principal ?: 'Non renseigne' }}</div>
                                        <div class="mt-1 text-sm text-slate-500">{{ $inscription->eleve?->adresse ?: 'Adresse non renseignee' }}</div>
                                    </div>
                                    <div class="i3p-record-meta">
                                        <div class="i3p-label">Responsable</div>
                                        <div class="mt-2 font-bold text-slate-950">{{ $inscription->eleve?->nom_parent ?: 'Parent non renseigne' }}</div>
                                        <div class="mt-1 text-sm text-slate-500">{{ $inscription->eleve?->contact_parent ?: 'Contact parent non renseigne' }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex w-full flex-col gap-3 xl:w-[14rem]">
                                <a href="{{ route('eleves.inscriptions.edit', $inscription) }}" class="i3p-link !border-[#0ca6e8]/20 !bg-[#0ca6e8]/10 !text-[#0f4d6a]">
                                    Modifier le dossier
                                </a>
                                @if ($historicalSummary)
                                    <a href="{{ route('bulletins.historiques', ['eleve_id' => $inscription->eleve_id]) }}" class="i3p-link !border-[#b02f25]/20 !bg-[#b02f25]/10 !text-[#7d221b]">
                                        Ouvrir l historique
                                    </a>
                                @endif
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-3xl border border-dashed border-slate-300 bg-white/70 px-6 py-8 text-center">
                        <div class="text-lg font-bold text-slate-950">Aucun dossier ne correspond aux filtres.</div>
                        <div class="mt-2 text-sm leading-7 text-slate-600">
                            Elargis les filtres ou cree un nouvel eleve pour alimenter le registre.
                        </div>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
