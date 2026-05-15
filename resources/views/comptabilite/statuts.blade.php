<x-app-layout>
    <x-slot name="header">
        <div class="i3p-card overflow-hidden">
            <div class="grid gap-8 px-6 py-8 lg:grid-cols-[1.15fr_0.85fr] lg:px-8">
                <div>
                    <span class="i3p-badge border-[#b02f25]/20 bg-[#b02f25]/10 text-[#8e251d]">Comptabilite scolaire</span>
                    <h1 class="i3p-title mt-4">Statuts financiers</h1>
                    <p class="i3p-copy mt-3 max-w-3xl">
                        Cette page centralise les statuts de paiement utilises pour controler l acces aux bulletins et, plus tard, au portail parents.
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

        <section class="grid gap-5 xl:grid-cols-[1.15fr_0.85fr]">
            <article class="i3p-card p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="i3p-kicker text-[#b02f25]">Controle financier</p>
                        <h2 class="i3p-section-title mt-2">Lecture de pilotage</h2>
                        <p class="mt-3 max-w-2xl text-[14px] leading-7 text-slate-600">
                            Cet ecran doit permettre de reperer vite les blocages, les situations partielles et les acces bulletin a arbitrer.
                        </p>
                    </div>
                    <span class="i3p-badge border-slate-200 bg-slate-100 text-slate-700">{{ $stats['filtres'] }} ligne(s)</span>
                </div>

                <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="i3p-priority-card">
                        <div class="i3p-action-kicker">A jour</div>
                        <div class="mt-2 text-2xl font-bold text-emerald-700">{{ $stats['a_jour'] }}</div>
                        <div class="mt-2 text-sm text-slate-600">dossiers financiers reguliers</div>
                    </div>
                    <div class="i3p-priority-card">
                        <div class="i3p-action-kicker">Partiels</div>
                        <div class="mt-2 text-2xl font-bold text-amber-700">{{ $stats['partiel'] }}</div>
                        <div class="mt-2 text-sm text-slate-600">paiements a completer</div>
                    </div>
                    <div class="i3p-priority-card">
                        <div class="i3p-action-kicker">Retards</div>
                        <div class="mt-2 text-2xl font-bold text-[#8e251d]">{{ $stats['en_retard'] }}</div>
                        <div class="mt-2 text-sm text-slate-600">situations a traiter en priorite</div>
                    </div>
                    <div class="i3p-priority-card">
                        <div class="i3p-action-kicker">Exceptions</div>
                        <div class="mt-2 text-2xl font-bold text-[#0f4d6a]">{{ $stats['autorisation_exceptionnelle'] }}</div>
                        <div class="mt-2 text-sm text-slate-600">acces accordes par decision</div>
                    </div>
                </div>
            </article>

            <article class="i3p-card p-6">
                <p class="i3p-kicker text-[#b02f25]">Priorites</p>
                <h2 class="i3p-section-title mt-2">Usage conseille</h2>
                <div class="mt-6 space-y-4">
                    <div class="i3p-priority-card">
                        <div class="i3p-priority-title">1. Filtrer les dossiers critiques</div>
                        <div class="mt-2 text-sm leading-6 text-slate-600">Commencer par les statuts bloques, en retard ou partiels.</div>
                    </div>
                    <div class="i3p-priority-card">
                        <div class="i3p-priority-title">2. Ajuster le statut</div>
                        <div class="mt-2 text-sm leading-6 text-slate-600">Mettre a jour les montants, l observation et l acces bulletin.</div>
                    </div>
                    <div class="i3p-priority-card">
                        <div class="i3p-priority-title">3. Ouvrir le registre des paiements</div>
                        <div class="mt-2 text-sm leading-6 text-slate-600">Verifier les ecritures avant toute decision sur le bulletin.</div>
                    </div>
                </div>
            </article>
        </section>

        <section class="i3p-card p-6">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="i3p-kicker text-[#b02f25]">Recherche ciblee</p>
                    <h2 class="i3p-section-title mt-2">Trouver un dossier comptable</h2>
                    <p class="mt-3 text-[14px] leading-7 text-slate-600">
                        Recherche par eleve ou parent, puis filtre par statut financier et acces au bulletin.
                    </p>
                </div>
            </div>

            <form method="GET" action="{{ route('comptabilite.statuts') }}" class="mt-6 grid gap-4 xl:grid-cols-[1.15fr_0.75fr_0.75fr_auto_auto]">
                <div>
                    <label for="q" class="i3p-label">Recherche</label>
                    <input id="q" name="q" type="text" value="{{ $filters['q'] }}" placeholder="Matricule, nom, prenoms, parent" class="mt-2 w-full">
                </div>
                <div>
                    <label for="statut" class="i3p-label">Statut</label>
                    <select id="statut" name="statut" class="mt-2 w-full">
                        <option value="">Tous</option>
                        @foreach ($statutsDisponibles as $value => $label)
                            <option value="{{ $value }}" @selected($filters['statut'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="acces" class="i3p-label">Acces bulletin</label>
                    <select id="acces" name="acces" class="mt-2 w-full">
                        <option value="">Tous</option>
                        <option value="autorise" @selected($filters['acces'] === 'autorise')>Autorise</option>
                        <option value="bloque" @selected($filters['acces'] === 'bloque')>Bloque</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="i3p-link w-full !border-[#b02f25]/20 !bg-[#b02f25]/10 !text-[#7d221b]">Filtrer</button>
                </div>
                <div class="flex items-end">
                    <a href="{{ route('comptabilite.statuts') }}" class="i3p-link w-full !border-slate-200 !bg-slate-100 !text-slate-700">Reinitialiser</a>
                </div>
            </form>
        </section>

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

            <div class="mt-6 space-y-4">
                @forelse ($statuts as $statut)
                    @php
                        $statusClasses = match ($statut->statut) {
                            'a_jour' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                            'autorisation_exceptionnelle' => 'border-[#0ca6e8]/20 bg-[#0ca6e8]/10 text-[#0f4d6a]',
                            'bloque' => 'border-[#b02f25]/20 bg-[#fff1ef] text-[#8e251d]',
                            default => 'border-amber-200 bg-amber-50 text-amber-700',
                        };
                        $accessClasses = $statut->autorise_acces_bulletin
                            ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                            : 'border-[#b02f25]/20 bg-[#fff1ef] text-[#8e251d]';
                        $ecart = max(0, (float) ($statut->montant_attendu ?? 0) - (float) ($statut->montant_paye ?? 0));
                    @endphp

                    <article class="i3p-record-card">
                        <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-3">
                                    <span class="i3p-badge border-slate-200 bg-slate-100 text-slate-700">
                                        {{ $statut->eleve?->matricule }}
                                    </span>
                                    <span class="i3p-badge {{ $statusClasses }}">
                                        {{ str_replace('_', ' ', ucfirst($statut->statut)) }}
                                    </span>
                                    <span class="i3p-badge {{ $accessClasses }}">
                                        {{ $statut->autorise_acces_bulletin ? 'Autorise' : 'Bloque' }}
                                    </span>
                                </div>

                                <div class="mt-4">
                                    <h3 class="text-xl font-bold tracking-[-0.02em] text-slate-950">
                                        {{ $statut->eleve?->nom }} {{ $statut->eleve?->prenoms }}
                                    </h3>
                                    <p class="mt-2 text-sm text-slate-500">
                                        {{ $statut->anneeScolaire?->libelle ?? 'Annee non definie' }}
                                        · {{ $statut->observation ?: 'Aucune observation comptable' }}
                                    </p>
                                </div>

                                <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                                    <div class="i3p-record-meta">
                                        <div class="i3p-label">Montant attendu</div>
                                        <div class="mt-2 font-bold text-slate-950">
                                            {{ $statut->montant_attendu !== null ? number_format((float) $statut->montant_attendu, 0, ',', ' ') : 'N/D' }}
                                        </div>
                                    </div>
                                    <div class="i3p-record-meta">
                                        <div class="i3p-label">Montant paye</div>
                                        <div class="mt-2 font-bold text-slate-950">
                                            {{ $statut->montant_paye !== null ? number_format((float) $statut->montant_paye, 0, ',', ' ') : '0' }}
                                        </div>
                                    </div>
                                    <div class="i3p-record-meta">
                                        <div class="i3p-label">Ecart restant</div>
                                        <div class="mt-2 font-bold text-[#8e251d]">{{ number_format($ecart, 0, ',', ' ') }}</div>
                                    </div>
                                    <div class="i3p-record-meta">
                                        <div class="i3p-label">Dernier paiement</div>
                                        <div class="mt-2 font-bold text-slate-950">{{ $statut->date_dernier_paiement?->format('d/m/Y') ?? 'N/D' }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex w-full flex-col gap-3 xl:w-[14rem]">
                                <a href="{{ route('comptabilite.edit', $statut) }}" class="i3p-link !border-[#0ca6e8]/20 !bg-[#0ca6e8]/10 !text-[#0f4d6a]">
                                    Modifier le statut
                                </a>
                                <a href="{{ route('comptabilite.paiements', $statut) }}" class="i3p-link !border-slate-200 !bg-slate-100 !text-slate-700">
                                    Voir les paiements
                                </a>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-3xl border border-dashed border-slate-300 bg-white/70 px-6 py-8 text-center">
                        <div class="text-lg font-bold text-slate-950">Aucun dossier comptable ne correspond aux filtres.</div>
                        <div class="mt-2 text-sm leading-7 text-slate-600">
                            Elargis les filtres pour retrouver les statuts financiers.
                        </div>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
