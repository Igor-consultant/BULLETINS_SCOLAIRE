<x-app-layout>
    <x-slot name="header">
        <div class="i3p-card overflow-hidden">
            <div class="grid gap-8 px-6 py-8 lg:grid-cols-[1.15fr_0.85fr] lg:px-8">
                <div>
                    <span class="i3p-badge border-[#b02f25]/20 bg-[#b02f25]/10 text-[#8e251d]">Audit metier</span>
                    <h1 class="i3p-title mt-4">Historique des actions sensibles</h1>
                    <p class="i3p-copy mt-3 max-w-3xl">
                        Cette page centralise les operations historisees sur les notes et les resultats afin de suivre les modifications sensibles du logiciel.
                    </p>
                </div>

                <div class="rounded-[2rem] border border-white/70 bg-gradient-to-br from-[#10233d] to-[#1f4765] p-6 text-white shadow-[0_18px_60px_rgba(15,23,42,0.18)]">
                    <p class="i3p-kicker text-[#f0c5ba]">Synthese</p>
                    <div class="mt-4 grid gap-3 sm:grid-cols-3 lg:grid-cols-1">
                        <div class="rounded-2xl border border-white/10 bg-white/10 px-4 py-3">
                            <div class="i3p-label text-slate-200">Total audits</div>
                            <div class="mt-2 text-[2rem] font-bold">{{ $stats['total'] }}</div>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/10 px-4 py-3">
                            <div class="i3p-label text-slate-200">Notes</div>
                            <div class="mt-2 text-[2rem] font-bold">{{ $stats['notes'] }}</div>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/10 px-4 py-3">
                            <div class="i3p-label text-slate-200">Resultats</div>
                            <div class="mt-2 text-[2rem] font-bold">{{ $stats['resultats'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="i3p-container mt-8 space-y-8">
        <section class="i3p-card p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="i3p-kicker text-[#b02f25]">Filtres</p>
                    <h2 class="i3p-section-title mt-2">Affiner la consultation</h2>
                </div>
                <form method="GET" action="{{ route('audits.index') }}" class="grid w-full gap-4 lg:max-w-4xl lg:grid-cols-4">
                    <div>
                        <label for="action" class="i3p-label">Action</label>
                        <select
                            id="action"
                            name="action"
                            class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-[#0ca6e8] focus:outline-none focus:ring-2 focus:ring-[#0ca6e8]/20"
                        >
                            <option value="">Toutes</option>
                            @foreach ($filters['actions'] as $action)
                                <option value="{{ $action }}" @selected(request('action') === $action)>{{ str_replace('_', ' ', $action) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="auditable_type" class="i3p-label">Type d objet</label>
                        <select
                            id="auditable_type"
                            name="auditable_type"
                            class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-[#0ca6e8] focus:outline-none focus:ring-2 focus:ring-[#0ca6e8]/20"
                        >
                            <option value="">Tous</option>
                            @foreach ($filters['auditable_types'] as $type)
                                <option value="{{ $type }}" @selected(request('auditable_type') === $type)>{{ strtoupper($type) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="user_id" class="i3p-label">Utilisateur</label>
                        <select
                            id="user_id"
                            name="user_id"
                            class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-[#0ca6e8] focus:outline-none focus:ring-2 focus:ring-[#0ca6e8]/20"
                        >
                            <option value="">Tous</option>
                            @foreach ($filters['users'] as $user)
                                <option value="{{ $user->id }}" @selected((string) request('user_id') === (string) $user->id)>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-end gap-3">
                        <button type="submit" class="i3p-link !border-[#0ca6e8]/20 !bg-[#0ca6e8]/10 !text-[#0f4d6a]">
                            Filtrer
                        </button>
                        <a href="{{ route('audits.index') }}" class="i3p-link !border-slate-200 !bg-slate-100 !text-slate-700">
                            Reinitialiser
                        </a>
                    </div>
                </form>
            </div>
        </section>

        <section class="i3p-card p-6">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="i3p-kicker text-[#b02f25]">Journal recent</p>
                    <h2 class="i3p-section-title mt-2">Dernieres operations historisees</h2>
                </div>
                <span class="i3p-badge border-[#0ca6e8]/20 bg-[#0ca6e8]/10 text-[#0f4d6a]">
                    {{ $audits->count() }} ligne(s) affichee(s)
                </span>
            </div>

            <div class="mt-6 space-y-4">
                @forelse ($audits as $audit)
                    <article class="rounded-3xl border border-slate-200 bg-white p-5">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <div class="flex flex-wrap items-center gap-3">
                                    <span class="i3p-badge border-[#b02f25]/20 bg-[#b02f25]/10 text-[#8e251d]">{{ str_replace('_', ' ', $audit->action) }}</span>
                                    <span class="i3p-badge border-slate-200 bg-slate-100 text-slate-700">{{ strtoupper($audit->auditable_type) }}</span>
                                </div>
                                <div class="mt-3 text-[15px] font-bold text-slate-900">{{ $audit->description ?: 'Aucune description' }}</div>
                                <div class="mt-2 text-[13px] text-slate-600">
                                    Utilisateur : {{ $audit->user?->name ?? 'Systeme' }}
                                    · Ligne : {{ $audit->auditable_id ?? 'N/D' }}
                                    · {{ $audit->created_at?->format('d/m/Y H:i:s') }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 grid gap-4 xl:grid-cols-2">
                            <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                                <div class="i3p-label">Anciennes valeurs</div>
                                <pre class="mt-3 overflow-x-auto whitespace-pre-wrap break-words text-[13px] leading-6 text-slate-700">{{ json_encode($audit->anciennes_valeurs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: 'null' }}</pre>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                                <div class="i3p-label">Nouvelles valeurs</div>
                                <pre class="mt-3 overflow-x-auto whitespace-pre-wrap break-words text-[13px] leading-6 text-slate-700">{{ json_encode($audit->nouvelles_valeurs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: 'null' }}</pre>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-white/70 p-5 text-sm text-slate-600">
                        Aucune operation auditee n est encore disponible.
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
