<x-app-layout>
    <x-slot name="header">
        <div class="i3p-card overflow-hidden">
            <div class="grid gap-8 px-6 py-8 lg:grid-cols-[1.15fr_0.85fr] lg:px-8">
                <div>
                    <span class="i3p-badge border-[#b02f25]/20 bg-[#b02f25]/10 text-[#8e251d]">Generation en lot</span>
                    <h1 class="i3p-title mt-4">Bulletins par classe</h1>
                    <p class="i3p-copy mt-3 max-w-3xl">
                        Selectionne une classe et un trimestre pour produire une archive ZIP contenant les bulletins PDF des eleves ayant des resultats enregistres.
                    </p>
                </div>

                <div class="rounded-[2rem] border border-white/70 bg-gradient-to-br from-[#10233d] to-[#1f4765] p-6 text-white shadow-[0_18px_60px_rgba(15,23,42,0.18)]">
                    <p class="i3p-kicker text-[#f0c5ba]">Preparation</p>
                    <div class="mt-4 space-y-3 text-[14px] leading-7 text-slate-200">
                        <div><span class="font-bold text-white">Classes disponibles :</span> {{ $classes->count() }}</div>
                        <div><span class="font-bold text-white">Trimestres disponibles :</span> {{ $trimestres->count() }}</div>
                        <div><span class="font-bold text-white">Sortie :</span> archive ZIP de bulletins PDF</div>
                    </div>
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

        @if ($errors->any())
            <div class="rounded-2xl border border-[#b02f25]/20 bg-[#fff5f3] px-5 py-4 text-sm text-[#8e251d]">
                <div class="font-bold">La demande comporte des erreurs.</div>
                <ul class="mt-2 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="grid gap-5 xl:grid-cols-[1.1fr_0.9fr]">
            <article class="i3p-card p-6">
                <p class="i3p-kicker text-[#b02f25]">Preparation du lot</p>
                <h2 class="i3p-section-title mt-2">Choisir la classe et le trimestre</h2>
                <p class="mt-3 text-[14px] leading-7 text-slate-600">
                    Les eleves bloques par la comptabilite sont exclus automatiquement du lot ZIP pour garantir une sortie conforme.
                </p>

                <form method="POST" action="{{ route('bulletins.lots.generer') }}" class="mt-6 grid gap-6 lg:grid-cols-2">
                    @csrf

                    <div>
                        <label for="classe_id" class="i3p-label">Classe</label>
                        <select id="classe_id" name="classe_id" class="mt-2 w-full">
                            <option value="">Selectionner une classe</option>
                            @foreach ($classes as $classe)
                                <option value="{{ $classe->id }}" @selected((string) old('classe_id') === (string) $classe->id)>
                                    {{ $classe->code }} - {{ $classe->nom }}{{ $classe->filiere ? ' / '.$classe->filiere->nom : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="trimestre_id" class="i3p-label">Trimestre</label>
                        <select id="trimestre_id" name="trimestre_id" class="mt-2 w-full">
                            <option value="">Selectionner un trimestre</option>
                            @foreach ($trimestres as $trimestre)
                                <option value="{{ $trimestre->id }}" @selected((string) old('trimestre_id') === (string) $trimestre->id)>
                                    {{ $trimestre->libelle }}{{ $trimestre->anneeScolaire ? ' / '.$trimestre->anneeScolaire->libelle : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="lg:col-span-2 flex flex-wrap gap-3">
                        <button type="submit" class="i3p-link !border-[#b02f25]/20 !bg-[#b02f25]/10 !text-[#7d221b]">
                            Generer l archive ZIP
                        </button>
                        <a href="{{ route('resultats.trimestriels') }}" class="i3p-link !border-[#0ca6e8]/20 !bg-[#0ca6e8]/10 !text-[#0f4d6a]">
                            Retour aux resultats
                        </a>
                    </div>
                </form>
            </article>

            <article class="i3p-card p-6">
                <p class="i3p-kicker text-[#b02f25]">Rappel</p>
                <h2 class="i3p-section-title mt-2">Ce que produit le lot</h2>
                <div class="mt-6 space-y-4">
                    <div class="i3p-priority-card">
                        <div class="i3p-priority-title">1. PDF individuels</div>
                        <div class="mt-2 text-sm leading-6 text-slate-600">Un bulletin PDF par eleve autorise dans la classe choisie.</div>
                    </div>
                    <div class="i3p-priority-card">
                        <div class="i3p-priority-title">2. Archive ZIP</div>
                        <div class="mt-2 text-sm leading-6 text-slate-600">Tous les PDF sont regroupes dans une archive unique telechargeable.</div>
                    </div>
                    <div class="i3p-priority-card">
                        <div class="i3p-priority-title">3. Exclusions explicites</div>
                        <div class="mt-2 text-sm leading-6 text-slate-600">Les cas bloques sont exclus automatiquement pour rester conformes aux regles comptables.</div>
                    </div>
                </div>
            </article>
        </section>
    </div>
</x-app-layout>
