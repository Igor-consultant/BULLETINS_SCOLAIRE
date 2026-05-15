<x-app-layout>
    <x-slot name="header">
        <div class="i3p-card overflow-hidden">
            <div class="grid gap-8 px-6 py-8 lg:grid-cols-[1.15fr_0.85fr] lg:px-8">
                <div>
                    <span class="i3p-badge border-[#b02f25]/20 bg-[#b02f25]/10 text-[#8e251d]">Scolarite</span>
                    <h1 class="i3p-title mt-4">{{ $mode === 'create' ? 'Nouvel eleve' : 'Modifier eleve / inscription' }}</h1>
                    <p class="i3p-copy mt-3 max-w-3xl">
                        {{ $mode === 'create' ? "Ce formulaire permet d'ajouter un nouvel eleve et son inscription initiale." : "Ce formulaire permet de mettre a jour l'identite de l'eleve et les informations de son inscription." }}
                    </p>
                </div>

                <div class="rounded-[2rem] border border-white/70 bg-gradient-to-br from-[#10233d] to-[#1f4765] p-6 text-white shadow-[0_18px_60px_rgba(15,23,42,0.18)]">
                    <p class="i3p-kicker text-[#f0c5ba]">Saisie metier</p>
                    <div class="mt-4 space-y-3 text-[14px] leading-7 text-slate-200">
                        <div><span class="font-bold text-white">Mode :</span> {{ $mode === 'create' ? 'Creation' : 'Mise a jour' }}</div>
                        @if ($mode === 'edit')
                            <div><span class="font-bold text-white">Eleve :</span> {{ $eleve->matricule }} - {{ $eleve->nom }} {{ $eleve->prenoms }}</div>
                        @endif
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
                <p class="i3p-kicker text-[#b02f25]">Structure de saisie</p>
                <h2 class="i3p-section-title mt-2">Dossier eleve + inscription</h2>
                <div class="mt-5 grid gap-4 sm:grid-cols-3">
                    <div class="i3p-priority-card">
                        <div class="i3p-action-kicker">Bloc 1</div>
                        <div class="mt-2 text-base font-bold text-slate-950">Identite</div>
                        <div class="mt-2 text-sm leading-6 text-slate-600">Matricule, nom, prenoms, sexe, naissance.</div>
                    </div>
                    <div class="i3p-priority-card">
                        <div class="i3p-action-kicker">Bloc 2</div>
                        <div class="mt-2 text-base font-bold text-slate-950">Contacts</div>
                        <div class="mt-2 text-sm leading-6 text-slate-600">Telephone, parent, contact parent, adresse.</div>
                    </div>
                    <div class="i3p-priority-card">
                        <div class="i3p-action-kicker">Bloc 3</div>
                        <div class="mt-2 text-base font-bold text-slate-950">Scolarite</div>
                        <div class="mt-2 text-sm leading-6 text-slate-600">Classe, annee scolaire, statut, date d inscription.</div>
                    </div>
                </div>
            </article>

            <article class="i3p-card p-6">
                <p class="i3p-kicker text-[#b02f25]">Conseil de saisie</p>
                <h2 class="i3p-section-title mt-2">Bonnes pratiques</h2>
                <div class="mt-5 space-y-4">
                    <div class="i3p-priority-card">
                        <div class="i3p-priority-title">Renseigner un matricule stable</div>
                        <div class="mt-2 text-sm leading-6 text-slate-600">Le matricule est le repere le plus pratique pour toute recherche future.</div>
                    </div>
                    <div class="i3p-priority-card">
                        <div class="i3p-priority-title">Completer les contacts parents</div>
                        <div class="mt-2 text-sm leading-6 text-slate-600">Ils seront utiles pour le portail parent, le suivi administratif et les relances.</div>
                    </div>
                    <div class="i3p-priority-card">
                        <div class="i3p-priority-title">Verifier la coherence scolaire</div>
                        <div class="mt-2 text-sm leading-6 text-slate-600">Classe, annee et statut doivent refleter la situation reelle avant la saisie des notes.</div>
                    </div>
                </div>
            </article>
        </section>

        <section class="i3p-card p-6">
            <form method="POST" action="{{ $mode === 'create' ? route('eleves.inscriptions.store') : route('eleves.inscriptions.update', $inscription) }}" class="space-y-8">
                @csrf
                @if ($mode === 'edit')
                    @method('PUT')
                @endif

                <section class="rounded-[1.75rem] border border-slate-200 bg-white p-6">
                    <div>
                        <p class="i3p-kicker text-[#b02f25]">Bloc 1</p>
                        <h3 class="mt-2 text-xl font-bold tracking-[-0.02em] text-slate-950">Identite de l eleve</h3>
                    </div>

                    <div class="mt-6 grid gap-6 lg:grid-cols-2">
                        <div>
                            <label for="matricule" class="i3p-label">Matricule</label>
                            <input id="matricule" name="matricule" type="text" value="{{ old('matricule', $eleve->matricule) }}" class="mt-2 w-full">
                        </div>

                        <div>
                            <label for="nom" class="i3p-label">Nom</label>
                            <input id="nom" name="nom" type="text" value="{{ old('nom', $eleve->nom) }}" class="mt-2 w-full">
                        </div>

                        <div>
                            <label for="prenoms" class="i3p-label">Prenoms</label>
                            <input id="prenoms" name="prenoms" type="text" value="{{ old('prenoms', $eleve->prenoms) }}" class="mt-2 w-full">
                        </div>

                        <div>
                            <label for="sexe" class="i3p-label">Sexe</label>
                            <select id="sexe" name="sexe" class="mt-2 w-full">
                                <option value="">Selectionner</option>
                                <option value="M" @selected(old('sexe', $eleve->sexe) === 'M')>M</option>
                                <option value="F" @selected(old('sexe', $eleve->sexe) === 'F')>F</option>
                            </select>
                        </div>

                        <div>
                            <label for="date_naissance" class="i3p-label">Date de naissance</label>
                            <input id="date_naissance" name="date_naissance" type="date" value="{{ old('date_naissance', $eleve->date_naissance?->format('Y-m-d')) }}" class="mt-2 w-full">
                        </div>

                        <div>
                            <label for="lieu_naissance" class="i3p-label">Lieu de naissance</label>
                            <input id="lieu_naissance" name="lieu_naissance" type="text" value="{{ old('lieu_naissance', $eleve->lieu_naissance) }}" class="mt-2 w-full">
                        </div>
                    </div>
                </section>

                <section class="rounded-[1.75rem] border border-slate-200 bg-white p-6">
                    <div>
                        <p class="i3p-kicker text-[#b02f25]">Bloc 2</p>
                        <h3 class="mt-2 text-xl font-bold tracking-[-0.02em] text-slate-950">Contacts et responsable</h3>
                    </div>

                    <div class="mt-6 grid gap-6 lg:grid-cols-2">
                        <div>
                            <label for="contact_principal" class="i3p-label">Contact principal</label>
                            <input id="contact_principal" name="contact_principal" type="text" value="{{ old('contact_principal', $eleve->contact_principal) }}" class="mt-2 w-full">
                        </div>

                        <div>
                            <label for="nom_parent" class="i3p-label">Nom du parent</label>
                            <input id="nom_parent" name="nom_parent" type="text" value="{{ old('nom_parent', $eleve->nom_parent) }}" class="mt-2 w-full">
                        </div>

                        <div>
                            <label for="contact_parent" class="i3p-label">Contact du parent</label>
                            <input id="contact_parent" name="contact_parent" type="text" value="{{ old('contact_parent', $eleve->contact_parent) }}" class="mt-2 w-full">
                        </div>

                        <div class="lg:col-span-2">
                            <label for="adresse" class="i3p-label">Adresse</label>
                            <textarea id="adresse" name="adresse" rows="3" class="mt-2 w-full">{{ old('adresse', $eleve->adresse) }}</textarea>
                        </div>
                    </div>
                </section>

                <section class="rounded-[1.75rem] border border-slate-200 bg-white p-6">
                    <div>
                        <p class="i3p-kicker text-[#b02f25]">Bloc 3</p>
                        <h3 class="mt-2 text-xl font-bold tracking-[-0.02em] text-slate-950">Affectation scolaire</h3>
                    </div>

                    <div class="mt-6 grid gap-6 lg:grid-cols-2">
                        <div>
                            <label for="classe_id" class="i3p-label">Classe</label>
                            <select id="classe_id" name="classe_id" class="mt-2 w-full">
                                <option value="">Selectionner une classe</option>
                                @foreach ($classes as $classe)
                                    <option value="{{ $classe->id }}" @selected((string) old('classe_id', $inscription->classe_id) === (string) $classe->id)>{{ $classe->code }} - {{ $classe->nom }}{{ $classe->filiere ? ' / '.$classe->filiere->nom : '' }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="annee_scolaire_id" class="i3p-label">Annee scolaire</label>
                            <select id="annee_scolaire_id" name="annee_scolaire_id" class="mt-2 w-full">
                                <option value="">Selectionner une annee</option>
                                @foreach ($anneesScolaires as $annee)
                                    <option value="{{ $annee->id }}" @selected((string) old('annee_scolaire_id', $inscription->annee_scolaire_id) === (string) $annee->id)>{{ $annee->libelle }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="statut" class="i3p-label">Statut d inscription</label>
                            <select id="statut" name="statut" class="mt-2 w-full">
                                @foreach (['inscrit' => 'Inscrit', 'transfere' => 'Transfere', 'abandonne' => 'Abandonne', 'suspendu' => 'Suspendu'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('statut', $inscription->statut ?: 'inscrit') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="date_inscription" class="i3p-label">Date d inscription</label>
                            <input id="date_inscription" name="date_inscription" type="date" value="{{ old('date_inscription', $inscription->date_inscription?->format('Y-m-d')) }}" class="mt-2 w-full">
                        </div>
                    </div>
                </section>

                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="i3p-link !border-[#b02f25]/20 !bg-[#b02f25]/10 !text-[#7d221b]">
                        {{ $mode === 'create' ? 'Creer eleve et inscription' : 'Enregistrer les modifications' }}
                    </button>
                    <a href="{{ route('eleves.inscriptions') }}" class="i3p-link !border-slate-200 !bg-slate-100 !text-slate-700">
                        Retour a la liste
                    </a>
                </div>
            </form>
        </section>
    </div>
</x-app-layout>
