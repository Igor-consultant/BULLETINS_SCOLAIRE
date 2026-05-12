<x-app-layout>
    <x-slot name="header">
        <div class="i3p-card overflow-hidden">
            <div class="grid gap-8 px-6 py-8 lg:grid-cols-[1.2fr_0.8fr] lg:px-8">
                <div class="space-y-4">
                    <span class="i3p-badge border-[#b02f25]/20 bg-[#b02f25]/10 text-[#8e251d]">Tableau de bord institutionnel</span>
                    <div>
                        <h1 class="i3p-title">Bienvenue, {{ Auth::user()->name }}</h1>
                        <p class="i3p-copy mt-3 max-w-3xl">
                            Cet espace centralise le socle du projet I3P : referentiels scolaires, profils utilisateurs
                            et progression des modules de gestion des bulletins.
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <span class="i3p-badge border-[#0ca6e8]/20 bg-[#0ca6e8]/10 text-[#0f4d6a]">{{ Auth::user()->getRoleNames()->implode(', ') ?: 'utilisateur' }}</span>
                        @if ($anneeActive)
                            <span class="i3p-badge border-emerald-200 bg-emerald-50 text-emerald-700">Annee active : {{ $anneeActive->libelle }}</span>
                        @endif
                    </div>
                </div>

                <div class="rounded-[2rem] border border-white/70 bg-gradient-to-br from-[#10233d] to-[#1f4765] p-6 text-white shadow-[0_18px_60px_rgba(15,23,42,0.18)]">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="i3p-kicker text-[#f0c5ba]">Identite visuelle I3P</p>
                            <h2 class="mt-3 text-[1.7rem] font-bold leading-tight">Triangle rouge, axe bleu, base claire.</h2>
                        </div>
                        <img src="{{ asset('images/logo_i3p.jpg') }}" alt="Logo I3P" class="h-20 w-auto rounded-2xl border border-white/20 bg-white/90 p-1">
                    </div>
                    <p class="mt-4 text-[15px] leading-7 text-slate-200">
                        La charte applique les marqueurs du logo pour une interface plus formelle, scolaire et administrative.
                    </p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="i3p-container mt-8 space-y-8">
        @if (session('status'))
            <div class="rounded-2xl px-5 py-4 text-sm font-semibold {{ session('status_type') === 'error' ? 'border border-[#b02f25]/20 bg-[#fff1ef] text-[#8e251d]' : 'border border-emerald-200 bg-emerald-50 text-emerald-700' }}">
                {{ session('status') }}
            </div>
        @endif

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="i3p-stat-card">
                <div class="i3p-label">Annees</div>
                <div class="i3p-metric mt-3 text-[#8e251d]">{{ $stats['annees'] }}</div>
            </article>
            <article class="i3p-stat-card">
                <div class="i3p-label">Trimestres</div>
                <div class="i3p-metric mt-3 text-[#0f4d6a]">{{ $stats['trimestres'] }}</div>
            </article>
            <article class="i3p-stat-card">
                <div class="i3p-label">Filieres</div>
                <div class="i3p-metric mt-3 text-[#8e251d]">{{ $stats['filieres'] }}</div>
            </article>
            <article class="i3p-stat-card">
                <div class="i3p-label">Classes</div>
                <div class="i3p-metric mt-3 text-[#0f4d6a]">{{ $stats['classes'] }}</div>
            </article>
        </section>

        <section class="grid gap-6 lg:grid-cols-[1.15fr_0.85fr]">
            <article class="i3p-card p-6">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="i3p-kicker text-[#b02f25]">Referentiel actif</p>
                        <h2 class="i3p-section-title mt-2">Annee scolaire et periodes</h2>
                    </div>
                    @if ($anneeActive)
                        <span class="i3p-badge border-emerald-200 bg-emerald-50 text-emerald-700">{{ ucfirst($anneeActive->statut) }}</span>
                    @endif
                </div>

                @if ($anneeActive)
                    <div class="mt-5 rounded-2xl border border-slate-200 bg-white p-5">
                        <h3 class="i3p-panel-title">{{ $anneeActive->libelle }}</h3>
                        <p class="mt-2 text-[14px] text-slate-600">
                            Du {{ $anneeActive->date_debut?->format('d/m/Y') }} au {{ $anneeActive->date_fin?->format('d/m/Y') }}
                        </p>
                    </div>

                    <div class="mt-5 grid gap-3">
                        @foreach ($anneeActive->trimestres->sortBy('ordre') as $trimestre)
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="flex items-center justify-between gap-4">
                                    <div>
                                        <div class="text-[1rem] font-bold text-slate-900">{{ $trimestre->libelle }}</div>
                                        <div class="mt-1 text-[14px] text-slate-600">
                                            Ordre {{ $trimestre->ordre }}
                                            @if ($trimestre->date_debut && $trimestre->date_fin)
                                                · {{ $trimestre->date_debut->format('d/m/Y') }} au {{ $trimestre->date_fin->format('d/m/Y') }}
                                            @endif
                                        </div>
                                    </div>
                                    <span class="i3p-badge border-[#0ca6e8]/20 bg-[#0ca6e8]/10 text-[#0f4d6a]">{{ ucfirst($trimestre->statut) }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="mt-5 rounded-2xl border border-dashed border-slate-300 bg-white/70 p-5 text-sm text-slate-600">
                        Aucune annee scolaire active n'est encore definie.
                    </div>
                @endif
            </article>

            <article class="i3p-card p-6">
                <p class="i3p-kicker text-[#b02f25]">Socle metier</p>
                <h2 class="i3p-section-title mt-2">Classes initialisees</h2>
                <div class="mt-5 space-y-3">
                    @forelse ($anneeActive?->classes?->sortBy('code') ?? [] as $classe)
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <div class="text-[1rem] font-bold text-slate-900">{{ $classe->code }} - {{ $classe->nom }}</div>
                                    <div class="mt-1 text-[14px] text-slate-600">Filiere : {{ $classe->filiere?->nom ?? 'Non definie' }}</div>
                                </div>
                                <span class="i3p-badge border-[#0ca6e8]/20 bg-[#0ca6e8]/10 text-[#0f4d6a]">{{ $classe->actif ? 'Active' : 'Inactive' }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-300 bg-white/70 p-5 text-sm text-slate-600">
                            Aucune classe n'est encore configuree.
                        </div>
                    @endforelse
                </div>

                <div class="mt-6 rounded-3xl border border-[#b02f25]/10 bg-[#fff8f6] p-5">
                    <p class="i3p-kicker text-[#b02f25]">Prochaines etapes</p>
                    <ul class="mt-3 space-y-2 text-[14px] text-slate-700">
                        <li>Gestion des matieres et coefficients par classe</li>
                        <li>Dossiers eleves et inscriptions annuelles</li>
                        <li>Saisie des notes et calculs trimestriels</li>
                        <li>Generation des bulletins PDF et portail parents</li>
                    </ul>
                    <div class="mt-5 flex flex-wrap gap-3">
                        <a href="{{ route('referentiels.matieres') }}" class="i3p-link !border-[#b02f25]/20 !bg-[#b02f25]/10 !text-[#7d221b]">
                            Ouvrir le module matieres
                        </a>
                        <a href="{{ route('eleves.inscriptions') }}" class="i3p-link !border-[#0ca6e8]/20 !bg-[#0ca6e8]/10 !text-[#0f4d6a]">
                            Ouvrir le module eleves
                        </a>
                        <a href="{{ route('comptabilite.statuts') }}" class="i3p-link !border-[#b02f25]/20 !bg-[#b02f25]/10 !text-[#7d221b]">
                            Ouvrir la comptabilite
                        </a>
                        <a href="{{ route('notes.evaluations') }}" class="i3p-link !border-emerald-200 !bg-emerald-50 !text-emerald-700">
                            Ouvrir le module notes
                        </a>
                        <a href="{{ route('resultats.trimestriels') }}" class="i3p-link !border-slate-200 !bg-slate-100 !text-slate-700">
                            Ouvrir les resultats
                        </a>
                    </div>
                </div>
            </article>
        </section>
    </div>
</x-app-layout>
