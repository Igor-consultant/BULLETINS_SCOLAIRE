<nav x-data="{ open: false }">
    @php
        $user = Auth::user();
        $isParent = $user->hasRole('parent');
        $canAccessDirection = $user->hasAnyRole(['administration', 'direction']);
        $canManageReferentiels = $user->hasAnyRole(['administration', 'direction']);
        $canManageEleves = $user->hasAnyRole(['administration', 'direction']);
        $canAccessComptabilite = $user->hasAnyRole(['administration', 'direction', 'comptabilite']);
        $canAccessNotes = $user->hasAnyRole(['administration', 'direction', 'enseignant']);
        $canAccessResultats = $user->hasAnyRole(['administration', 'direction']);
        $canAccessAudits = $user->hasRole('administration');
        $canManageBulletinSettings = $user->hasRole('administration');

        $routeActive = function (array $patterns) {
            foreach ($patterns as $pattern) {
                if (request()->routeIs($pattern)) {
                    return true;
                }
            }

            return false;
        };

        $currentZone = match (true) {
            request()->routeIs('direction.*') => 'Pilotage direction',
            request()->routeIs('referentiels.*') => 'Referentiels scolaires',
            request()->routeIs('eleves.*') => 'Scolarite eleves',
            request()->routeIs('comptabilite.*') => 'Controle comptable',
            request()->routeIs('notes.*') => 'Saisie pedagogique',
            request()->routeIs('resultats.*') => 'Resultats trimestriels',
            request()->routeIs('bulletins.*') => 'Bulletins et archives',
            request()->routeIs('audits.*') => 'Administration et trace',
            request()->routeIs('profile.*') => 'Compte utilisateur',
            request()->routeIs('portail.parent') => 'Portail parent',
            default => 'Tableau de bord',
        };

        $menuGroups = $isParent
            ? [
                [
                    'label' => 'Famille',
                    'items' => [
                        ['label' => 'Portail parent', 'route' => 'portail.parent', 'patterns' => ['portail.parent']],
                        ['label' => 'Profil', 'route' => 'profile.edit', 'patterns' => ['profile.*']],
                    ],
                ],
            ]
            : array_values(array_filter([
                [
                    'label' => 'Pilotage',
                    'items' => array_values(array_filter([
                        ['label' => 'Tableau de bord', 'route' => 'dashboard', 'patterns' => ['dashboard']],
                        $canAccessDirection ? ['label' => 'Direction', 'route' => 'direction.dashboard', 'patterns' => ['direction.*']] : null,
                    ])),
                ],
                [
                    'label' => 'Operations',
                    'items' => array_values(array_filter([
                        $canManageReferentiels ? ['label' => 'Matieres', 'route' => 'referentiels.matieres', 'patterns' => ['referentiels.*']] : null,
                        $canManageEleves ? ['label' => 'Eleves', 'route' => 'eleves.inscriptions', 'patterns' => ['eleves.*']] : null,
                        $canAccessComptabilite ? ['label' => 'Comptabilite', 'route' => 'comptabilite.statuts', 'patterns' => ['comptabilite.*']] : null,
                        $canAccessNotes ? ['label' => 'Notes', 'route' => 'notes.evaluations', 'patterns' => ['notes.*']] : null,
                        $canAccessResultats ? ['label' => 'Resultats', 'route' => 'resultats.trimestriels', 'patterns' => ['resultats.*']] : null,
                        $canAccessResultats ? ['label' => 'Historiques', 'route' => 'bulletins.historiques', 'patterns' => ['bulletins.historiques']] : null,
                        $canManageBulletinSettings ? ['label' => 'Parametres bulletin', 'route' => 'bulletins.settings.edit', 'patterns' => ['bulletins.settings.*']] : null,
                        $canManageBulletinSettings ? ['label' => 'Import scolarite', 'route' => 'administration.import-scolarite.create', 'patterns' => ['administration.import-scolarite.*']] : null,
                    ])),
                ],
                [
                    'label' => 'Compte',
                    'items' => array_values(array_filter([
                        $canAccessAudits ? ['label' => 'Audits', 'route' => 'audits.index', 'patterns' => ['audits.*']] : null,
                        ['label' => 'Profil', 'route' => 'profile.edit', 'patterns' => ['profile.*']],
                    ])),
                ],
            ]));
    @endphp
    <div class="border-b border-slate-200/70 bg-white/90 backdrop-blur-xl lg:hidden">
        <div class="i3p-container flex min-h-[78px] items-center justify-between gap-4 py-4">
            <div class="flex min-w-0 items-center gap-4">
                <a href="{{ $isParent ? route('portail.parent') : route('dashboard') }}" class="shrink-0">
                    <x-application-logo class="h-12 w-auto rounded-2xl border border-slate-200 bg-white p-1.5 shadow-sm" />
                </a>
                <div class="min-w-0">
                    <div class="truncate text-[11px] font-bold uppercase tracking-[0.24em] text-[#b02f25]">I3P</div>
                    <div class="truncate text-base font-bold text-slate-950">{{ $currentZone }}</div>
                    <div class="truncate text-xs text-slate-500">{{ Auth::user()->getRoleNames()->implode(', ') ?: 'utilisateur' }}</div>
                </div>
            </div>

            <button @click="open = ! open" class="inline-flex h-12 w-12 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:border-[#0ca6e8]/30 hover:text-slate-950">
                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                    <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7h16M4 12h16M4 17h16" />
                    <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    <aside class="fixed inset-y-0 left-0 z-30 hidden w-80 border-r border-slate-200/80 bg-white/92 px-6 py-6 backdrop-blur-xl lg:flex lg:flex-col">
        <a href="{{ $isParent ? route('portail.parent') : route('dashboard') }}" class="flex items-center gap-4 rounded-[1.75rem] border border-slate-200 bg-white px-4 py-4 shadow-[0_18px_60px_rgba(15,23,42,0.08)]">
            <x-application-logo class="h-14 w-auto rounded-2xl border border-slate-200 bg-white p-1.5 shadow-sm" />
            <div class="min-w-0">
                <div class="text-[11px] font-bold uppercase tracking-[0.24em] text-[#b02f25]">I3P</div>
                <div class="truncate text-lg font-bold tracking-[-0.02em] text-slate-950">BULLETINS SCOLAIRE</div>
                <div class="truncate text-sm text-slate-500">Pilotage scolaire et bulletins</div>
            </div>
        </a>

        <div class="mt-6 rounded-[1.75rem] bg-[linear-gradient(150deg,#10233d_0%,#17395a_58%,#0ca6e8_180%)] px-5 py-5 text-white shadow-[0_22px_70px_rgba(15,23,42,0.18)]">
            <div class="text-[11px] font-bold uppercase tracking-[0.24em] text-[#f0c5ba]">Zone active</div>
            <div class="mt-3 text-2xl font-bold tracking-[-0.03em]">{{ $currentZone }}</div>
            <div class="mt-3 text-sm leading-7 text-slate-200">
                {{ $isParent ? 'Consulte la situation scolaire et financiere de l enfant rattache.' : 'Travaille dans un espace structure par roles, modules et operations prioritaires.' }}
            </div>
        </div>

        <div class="mt-6 flex-1 space-y-5 overflow-y-auto pe-1">
            @foreach ($menuGroups as $group)
                <section class="i3p-sidebar-group">
                    <div class="i3p-sidebar-group-title">{{ $group['label'] }}</div>
                    <div class="mt-2 space-y-1.5">
                        @foreach ($group['items'] as $item)
                            @php $active = $routeActive($item['patterns']); @endphp
                            <a href="{{ route($item['route']) }}" class="i3p-sidebar-link {{ $active ? 'is-active' : '' }}">
                                <span>{{ $item['label'] }}</span>
                                @if ($active)
                                    <span class="rounded-full bg-white/70 px-2 py-1 text-[10px] font-bold uppercase tracking-[0.16em] text-[#7d221b]">Actif</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>

        <div class="mt-6 rounded-[1.75rem] border border-slate-200 bg-slate-50/90 p-4">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-br from-[#0ca6e8] to-[#0a7bb5] text-sm font-bold text-white">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <div class="truncate text-sm font-bold text-slate-950">{{ Auth::user()->name }}</div>
                    <div class="truncate text-xs text-slate-500">{{ Auth::user()->email }}</div>
                    <div class="mt-1 truncate text-[11px] font-bold uppercase tracking-[0.16em] text-[#b02f25]">
                        {{ Auth::user()->getRoleNames()->implode(', ') ?: 'utilisateur' }}
                    </div>
                </div>
            </div>

            <div class="mt-4 flex gap-2">
                <a href="{{ route('profile.edit') }}" class="i3p-utility-link">Profil</a>
                <form method="POST" action="{{ route('logout') }}" class="flex-1">
                    @csrf
                    <button type="submit" class="i3p-utility-link w-full justify-center text-[#8e251d]">Deconnexion</button>
                </form>
            </div>
        </div>
    </aside>

    <div :class="{ 'opacity-100 pointer-events-auto': open, 'opacity-0 pointer-events-none': !open }" class="fixed inset-0 z-40 bg-slate-950/35 transition lg:hidden">
        <div @click="open = false" class="absolute inset-0"></div>
        <aside :class="{ 'translate-x-0': open, '-translate-x-full': !open }" class="absolute inset-y-0 left-0 flex w-[88vw] max-w-sm flex-col bg-white px-5 py-5 shadow-[0_24px_80px_rgba(15,23,42,0.20)] transition">
            <div class="flex items-center justify-between gap-4">
                <a href="{{ $isParent ? route('portail.parent') : route('dashboard') }}" class="flex min-w-0 items-center gap-3">
                    <x-application-logo class="h-12 w-auto rounded-2xl border border-slate-200 bg-white p-1.5 shadow-sm" />
                    <div class="min-w-0">
                        <div class="truncate text-[11px] font-bold uppercase tracking-[0.22em] text-[#b02f25]">I3P</div>
                        <div class="truncate text-base font-bold text-slate-950">BULLETINS SCOLAIRE</div>
                    </div>
                </a>

                <button @click="open = false" class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-700 shadow-sm">
                    <svg class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="mt-5 rounded-[1.5rem] bg-slate-50 px-4 py-4">
                <div class="text-[11px] font-bold uppercase tracking-[0.2em] text-[#b02f25]">Zone active</div>
                <div class="mt-2 text-lg font-bold text-slate-950">{{ $currentZone }}</div>
                <div class="mt-2 text-sm text-slate-600">{{ Auth::user()->getRoleNames()->implode(', ') ?: 'utilisateur' }}</div>
            </div>

            <div class="mt-5 flex-1 space-y-5 overflow-y-auto">
                @foreach ($menuGroups as $group)
                    <section class="i3p-sidebar-group">
                        <div class="i3p-sidebar-group-title">{{ $group['label'] }}</div>
                        <div class="mt-2 space-y-1.5">
                            @foreach ($group['items'] as $item)
                                @php $active = $routeActive($item['patterns']); @endphp
                                <a href="{{ route($item['route']) }}" @click="open = false" class="i3p-sidebar-link {{ $active ? 'is-active' : '' }}">
                                    <span>{{ $item['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>

            <div class="mt-5 rounded-[1.5rem] border border-slate-200 bg-white p-4">
                <div class="text-sm font-bold text-slate-950">{{ Auth::user()->name }}</div>
                <div class="mt-1 text-xs text-slate-500">{{ Auth::user()->email }}</div>
                <div class="mt-4 flex gap-2">
                    <a href="{{ route('profile.edit') }}" class="i3p-utility-link">Profil</a>
                    <form method="POST" action="{{ route('logout') }}" class="flex-1">
                        @csrf
                        <button type="submit" class="i3p-utility-link w-full justify-center text-[#8e251d]">Deconnexion</button>
                    </form>
                </div>
            </div>
        </aside>
    </div>
</nav>
