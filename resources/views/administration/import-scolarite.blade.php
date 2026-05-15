<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="i3p-kicker text-[#b02f25]">Administration</p>
            <h1 class="i3p-title mt-2">Import scolarite annee en cours</h1>
            <p class="mt-3 max-w-3xl text-[15px] leading-7 text-slate-600">
                Reprends les donnees de l annee active sans repartir de zero: eleves, classes, matieres, inscriptions, resultats, statuts financiers et paiements.
            </p>
        </div>
    </x-slot>

    <div class="i3p-container mt-8 space-y-8">
        <section class="i3p-bulletin">
            <p class="i3p-kicker text-[#b02f25]">Contexte cible</p>
            <h2 class="i3p-section-title mt-2">Annee active</h2>
            <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50/70 px-5 py-4 text-sm text-slate-700">
                @if ($anneeActive)
                    <span class="font-bold text-slate-950">{{ $anneeActive->libelle }}</span>
                    importee directement dans l application.
                @else
                    <span class="font-bold text-rose-700">Aucune annee active n est configuree.</span>
                @endif
            </div>
        </section>

        @if (session('status'))
            <section class="rounded-3xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800">
                <div class="font-bold">{{ session('status') }}</div>
                @if (session('import_summary'))
                    <div class="mt-3 text-xs text-emerald-700">
                        <pre class="whitespace-pre-wrap">{{ json_encode(session('import_summary'), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                    </div>
                @endif
            </section>
        @endif

        @if ($errors->any())
            <section class="rounded-3xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-700">
                Certains champs ou fichiers doivent etre corriges avant l import.
            </section>
        @endif

        <form method="POST" action="{{ route('administration.import-scolarite.store') }}" enctype="multipart/form-data" class="space-y-8">
            @csrf

            <section class="grid gap-6 lg:grid-cols-2">
                <article class="i3p-bulletin">
                    <p class="i3p-kicker text-[#b02f25]">Mode 1</p>
                    <h2 class="i3p-section-title mt-2">Pack JSON de reprise</h2>
                    <p class="mt-3 text-sm leading-7 text-slate-600">
                        Pour une reprise complete depuis une ancienne base ou un export structure: filieres, classes, matieres, classe matieres, eleves, inscriptions, resultats, statuts et paiements.
                    </p>
                    <label class="mt-5 flex items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-4 text-sm text-slate-700">
                        <input type="radio" name="source_type" value="json_pack" @checked(old('source_type', 'json_pack') === 'json_pack') class="mt-1 h-5 w-5 border-slate-300 text-[#0ca6e8] focus:ring-[#0ca6e8]/30">
                        <span>
                            <span class="block font-bold text-slate-950">Importer un pack JSON complet</span>
                            <span class="block mt-1">Ideal pour la reprise integrale de la scolarite de l annee en cours.</span>
                        </span>
                    </label>
                </article>

                <article class="i3p-bulletin">
                    <p class="i3p-kicker text-[#b02f25]">Mode 2</p>
                    <h2 class="i3p-section-title mt-2">Classeur Excel de bulletins</h2>
                    <p class="mt-3 text-sm leading-7 text-slate-600">
                        Pour reimporter rapidement des bulletins Excel similaires au format deja traite dans l application. Le pipeline staging, normalisation, extraction, validation puis import est relance automatiquement.
                    </p>
                    <label class="mt-5 flex items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-4 text-sm text-slate-700">
                        <input type="radio" name="source_type" value="xlsx_bulletins" @checked(old('source_type') === 'xlsx_bulletins') class="mt-1 h-5 w-5 border-slate-300 text-[#0ca6e8] focus:ring-[#0ca6e8]/30">
                        <span>
                            <span class="block font-bold text-slate-950">Importer un classeur XLSX</span>
                            <span class="block mt-1">Ideal pour continuer directement a partir des bulletins actuellement tenus sous Excel.</span>
                        </span>
                    </label>
                </article>
            </section>

            <section class="i3p-bulletin space-y-5">
                <div>
                    <p class="i3p-kicker text-[#b02f25]">Fichier source</p>
                    <h2 class="i3p-section-title mt-2">Televersement</h2>
                </div>
                <label class="space-y-2 block">
                    <span class="text-sm font-semibold text-slate-700">Fichier a importer (.json ou .xlsx)</span>
                    <input type="file" name="dataset" accept=".json,.xlsx" class="i3p-input">
                </label>
                <div class="rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-4 text-sm leading-7 text-slate-600">
                    Le fichier est importe dans l annee scolaire active et les donnees sont mises a jour par correspondance. Rien n est force a repartir de zero.
                </div>
            </section>

            <div class="flex justify-end">
                <button type="submit" class="inline-flex items-center rounded-2xl bg-[#10233d] px-6 py-3 text-sm font-bold uppercase tracking-[0.18em] text-white shadow-[0_18px_40px_rgba(15,23,42,0.2)] transition hover:bg-[#17395a]">
                    Lancer l import
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
