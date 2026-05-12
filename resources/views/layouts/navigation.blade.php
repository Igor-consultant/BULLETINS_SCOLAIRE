<nav x-data="{ open: false }" class="border-b border-white/70 bg-white/75 backdrop-blur-xl">
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
    @endphp
    <div class="i3p-container">
        <div class="flex min-h-[88px] justify-between gap-6 py-4">
            <div class="flex items-center gap-5">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-14 w-auto rounded-xl border border-slate-200 bg-white p-1 shadow-sm" />
                    </a>
                </div>

                <div>
                    <div class="text-[11px] font-semibold uppercase tracking-[0.24em] text-[#b02f25]">I3P</div>
                    <div class="text-lg font-semibold text-slate-900">BULLETINS SCOLAIRE</div>
                    <div class="text-sm text-slate-500">Plateforme de gestion des bulletins et de suivi pedagogique</div>
                </div>

                <div class="hidden space-x-3 sm:flex">
                    @if ($isParent)
                        <a href="{{ route('portail.parent') }}" class="i3p-link {{ request()->routeIs('portail.parent') ? '!border-[#b02f25]/25 !bg-[#b02f25]/10 !text-[#7d221b]' : '' }}">
                            Portail parent
                        </a>
                    @else
                        <a href="{{ route('dashboard') }}" class="i3p-link {{ request()->routeIs('dashboard') ? '!border-[#b02f25]/25 !bg-[#b02f25]/10 !text-[#7d221b]' : '' }}">
                            Tableau de bord
                        </a>
                        @if ($canAccessDirection)
                            <a href="{{ route('direction.dashboard') }}" class="i3p-link {{ request()->routeIs('direction.*') ? '!border-[#b02f25]/25 !bg-[#b02f25]/10 !text-[#7d221b]' : '' }}">
                                Direction
                            </a>
                        @endif
                        @if ($canManageReferentiels)
                            <a href="{{ route('referentiels.matieres') }}" class="i3p-link {{ request()->routeIs('referentiels.matieres') ? '!border-[#b02f25]/25 !bg-[#b02f25]/10 !text-[#7d221b]' : '' }}">
                                Matieres
                            </a>
                        @endif
                        @if ($canManageEleves)
                            <a href="{{ route('eleves.inscriptions') }}" class="i3p-link {{ request()->routeIs('eleves.inscriptions') ? '!border-[#b02f25]/25 !bg-[#b02f25]/10 !text-[#7d221b]' : '' }}">
                                Eleves
                            </a>
                        @endif
                        @if ($canAccessComptabilite)
                            <a href="{{ route('comptabilite.statuts') }}" class="i3p-link {{ request()->routeIs('comptabilite.statuts') ? '!border-[#b02f25]/25 !bg-[#b02f25]/10 !text-[#7d221b]' : '' }}">
                                Comptabilite
                            </a>
                        @endif
                        @if ($canAccessNotes)
                            <a href="{{ route('notes.evaluations') }}" class="i3p-link {{ request()->routeIs('notes.evaluations') ? '!border-[#b02f25]/25 !bg-[#b02f25]/10 !text-[#7d221b]' : '' }}">
                                Notes
                            </a>
                        @endif
                        @if ($canAccessResultats)
                            <a href="{{ route('resultats.trimestriels') }}" class="i3p-link {{ request()->routeIs('resultats.trimestriels') ? '!border-[#b02f25]/25 !bg-[#b02f25]/10 !text-[#7d221b]' : '' }}">
                                Resultats
                            </a>
                        @endif
                        @if ($canAccessAudits)
                            <a href="{{ route('audits.index') }}" class="i3p-link {{ request()->routeIs('audits.*') ? '!border-[#b02f25]/25 !bg-[#b02f25]/10 !text-[#7d221b]' : '' }}">
                                Audits
                            </a>
                        @endif
                    @endif
                    <a href="{{ route('profile.edit') }}" class="i3p-link {{ request()->routeIs('profile.*') ? '!border-[#b02f25]/25 !bg-[#b02f25]/10 !text-[#7d221b]' : '' }}">
                        Profil
                    </a>
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="56">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center gap-3 rounded-2xl border border-slate-200/80 bg-white px-4 py-3 text-sm font-medium text-slate-700 shadow-sm transition hover:border-[#0ca6e8]/30 hover:text-slate-900">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-[#0ca6e8] to-[#0a7bb5] text-sm font-bold text-white">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                            <div class="text-left">
                                <div>{{ Auth::user()->name }}</div>
                                <div class="text-xs text-slate-500">{{ Auth::user()->getRoleNames()->implode(', ') ?: 'utilisateur' }}</div>
                            </div>

                            <svg class="ms-1 h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            Profil
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                Deconnexion
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white p-2 text-slate-500 shadow-sm transition hover:text-slate-700">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="i3p-container pb-4">
            <div class="space-y-2 rounded-3xl border border-white/70 bg-white/85 p-4 shadow-[0_18px_60px_rgba(15,23,42,0.12)]">
                @if ($isParent)
                    <x-responsive-nav-link :href="route('portail.parent')" :active="request()->routeIs('portail.parent')">
                        Portail parent
                    </x-responsive-nav-link>
                @else
                    <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        Tableau de bord
                    </x-responsive-nav-link>

                    @if ($canAccessDirection)
                        <x-responsive-nav-link :href="route('direction.dashboard')" :active="request()->routeIs('direction.*')">
                            Direction
                        </x-responsive-nav-link>
                    @endif

                    @if ($canManageReferentiels)
                        <x-responsive-nav-link :href="route('referentiels.matieres')" :active="request()->routeIs('referentiels.matieres')">
                            Matieres
                        </x-responsive-nav-link>
                    @endif

                    @if ($canManageEleves)
                        <x-responsive-nav-link :href="route('eleves.inscriptions')" :active="request()->routeIs('eleves.inscriptions')">
                            Eleves
                        </x-responsive-nav-link>
                    @endif

                    @if ($canAccessComptabilite)
                        <x-responsive-nav-link :href="route('comptabilite.statuts')" :active="request()->routeIs('comptabilite.statuts')">
                            Comptabilite
                        </x-responsive-nav-link>
                    @endif

                    @if ($canAccessNotes)
                        <x-responsive-nav-link :href="route('notes.evaluations')" :active="request()->routeIs('notes.evaluations')">
                            Notes
                        </x-responsive-nav-link>
                    @endif

                    @if ($canAccessResultats)
                        <x-responsive-nav-link :href="route('resultats.trimestriels')" :active="request()->routeIs('resultats.trimestriels')">
                            Resultats
                        </x-responsive-nav-link>
                    @endif

                    @if ($canAccessAudits)
                        <x-responsive-nav-link :href="route('audits.index')" :active="request()->routeIs('audits.*')">
                            Audits
                        </x-responsive-nav-link>
                    @endif
                @endif

                <x-responsive-nav-link :href="route('profile.edit')" :active="request()->routeIs('profile.*')">
                    Profil
                </x-responsive-nav-link>
            </div>
        </div>

        <div class="border-t border-white/70 pb-4 pt-2">
            <div class="i3p-container">
                <div class="rounded-3xl border border-white/70 bg-white/85 px-4 py-4 shadow-[0_18px_60px_rgba(15,23,42,0.12)]">
                    <div class="font-medium text-base text-slate-900">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-slate-500">{{ Auth::user()->email }}</div>
                    <div class="mt-1 text-xs uppercase tracking-[0.18em] text-[#b02f25]">{{ Auth::user()->getRoleNames()->implode(', ') ?: 'utilisateur' }}</div>

                    <div class="mt-3 space-y-1">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-responsive-nav-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                Deconnexion
                            </x-responsive-nav-link>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>
