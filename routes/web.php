<?php

use App\Http\Controllers\BulletinController;
use App\Http\Controllers\ComptabiliteController;
use App\Http\Controllers\EleveInscriptionController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReferentielMatiereController;
use App\Http\Controllers\ResultatTrimestrielController;
use App\Models\AnneeScolaire;
use App\Models\Audit;
use App\Models\Classe;
use App\Models\ClasseMatiere;
use App\Models\Eleve;
use App\Models\Evaluation;
use App\Models\Filiere;
use App\Models\Inscription;
use App\Models\Matiere;
use App\Models\Note;
use App\Models\Paiement;
use App\Models\PaiementStatut;
use App\Models\Resultat;
use App\Models\Trimestre;
use App\Services\BulletinWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

$recordAudit = function (
    string $action,
    string $auditableType,
    ?int $auditableId,
    ?array $anciennesValeurs,
    ?array $nouvellesValeurs,
    ?string $description = null
) {
    Audit::create([
        'user_id' => auth()->id(),
        'action' => $action,
        'auditable_type' => $auditableType,
        'auditable_id' => $auditableId,
        'anciennes_valeurs' => $anciennesValeurs,
        'nouvelles_valeurs' => $nouvellesValeurs,
        'description' => $description,
    ]);
};

$ensureRoles = function (array|string $roles) {
    abort_unless(auth()->user()?->hasAnyRole((array) $roles), 403);
};

$ensureNonParentAccess = function () {
    abort_if(auth()->user()?->hasRole('parent'), 403);
};

Route::get('/', function () {
    $hasCoreTables = Schema::hasTable('annees_scolaires')
        && Schema::hasTable('trimestres')
        && Schema::hasTable('filieres')
        && Schema::hasTable('classes');

    $anneeActive = $hasCoreTables
        ? AnneeScolaire::with(['trimestres', 'classes.filiere'])
            ->where('statut', 'active')
            ->latest('date_debut')
            ->first()
        : null;

    return view('welcome', [
        'anneeActive' => $anneeActive,
        'stats' => [
            'annees' => $hasCoreTables ? AnneeScolaire::count() : 0,
            'trimestres' => $hasCoreTables ? Trimestre::count() : 0,
            'filieres' => $hasCoreTables ? Filiere::count() : 0,
            'classes' => $hasCoreTables ? Classe::count() : 0,
        ],
    ]);
});

Route::get('/dashboard', function () use ($ensureNonParentAccess) {
    $ensureNonParentAccess();

    $anneeActive = AnneeScolaire::with(['trimestres', 'classes.filiere'])
        ->where('statut', 'active')
        ->latest('date_debut')
        ->first();

    return view('dashboard', [
        'anneeActive' => $anneeActive,
        'stats' => [
            'annees' => AnneeScolaire::count(),
            'trimestres' => Trimestre::count(),
            'filieres' => Filiere::count(),
            'classes' => Classe::count(),
        ],
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/direction', function () use ($ensureRoles) {
    $ensureRoles(['administration', 'direction']);

    $anneeActive = AnneeScolaire::query()
        ->where('statut', 'active')
        ->latest('date_debut')
        ->first();

    $trimestreActif = Trimestre::query()
        ->where('annee_scolaire_id', $anneeActive?->id)
        ->orderBy('ordre')
        ->first();

    $classes = Classe::query()
        ->with('filiere')
        ->where('annee_scolaire_id', $anneeActive?->id)
        ->orderBy('code')
        ->get();

    $moyennesParClasse = $classes->map(function (Classe $classe) use ($trimestreActif) {
        $resultats = Resultat::query()
            ->with('eleve')
            ->where('classe_id', $classe->id)
            ->where('trimestre_id', $trimestreActif?->id)
            ->get()
            ->groupBy('eleve_id');

        $eleves = $resultats->map(function ($rows) {
            $totalPoints = $rows->sum(fn ($row) => (float) $row->points);
            $totalCoefficients = $rows->sum(fn ($row) => (float) $row->coefficient);
            $moyenne = $totalCoefficients > 0 ? $totalPoints / $totalCoefficients : null;
            $first = $rows->first();

            return [
                'eleve' => $first?->eleve,
                'moyenne' => $moyenne,
            ];
        })->filter(fn ($item) => $item['moyenne'] !== null)->values();

        $meilleur = $eleves->sortByDesc('moyenne')->first();
        $moyenneClasse = $eleves->count() > 0
            ? $eleves->avg('moyenne')
            : null;

        return [
            'classe' => $classe,
            'moyenne_classe' => $moyenneClasse,
            'meilleur' => $meilleur,
            'eleves_avec_resultats' => $eleves->count(),
        ];
    })->filter(fn ($item) => $item['moyenne_classe'] !== null)->values();

    $effectifsParClasse = $classes->map(function (Classe $classe) use ($trimestreActif) {
        $effectif = Inscription::query()
            ->where('classe_id', $classe->id)
            ->count();

        $avecResultats = Resultat::query()
            ->where('classe_id', $classe->id)
            ->when($trimestreActif, fn ($query) => $query->where('trimestre_id', $trimestreActif->id))
            ->distinct('eleve_id')
            ->count('eleve_id');

        return [
            'classe' => $classe,
            'effectif' => $effectif,
            'avec_resultats' => $avecResultats,
        ];
    });

    $elevesAvecBulletin = Resultat::query()
        ->when($trimestreActif, fn ($query) => $query->where('trimestre_id', $trimestreActif->id))
        ->distinct('eleve_id')
        ->count('eleve_id');

    $totalEleves = Eleve::count();
    $autorisationsBulletin = PaiementStatut::where('autorise_acces_bulletin', true)->count();
    $blocagesBulletin = PaiementStatut::where('autorise_acces_bulletin', false)->count();
    $meilleureClasse = $moyennesParClasse->sortByDesc('moyenne_classe')->first();
    $meilleurGlobal = $moyennesParClasse
        ->map(fn ($item) => $item['meilleur'])
        ->filter(fn ($item) => isset($item['moyenne']))
        ->sortByDesc('moyenne')
        ->first();
    $moyenneGlobale = $moyennesParClasse->count() > 0
        ? $moyennesParClasse->avg('moyenne_classe')
        : null;

    return view('direction.dashboard', [
        'anneeActive' => $anneeActive,
        'trimestreActif' => $trimestreActif,
        'moyennesParClasse' => $moyennesParClasse,
        'effectifsParClasse' => $effectifsParClasse,
        'auditsRecents' => Audit::query()->with('user')->latest()->limit(5)->get(),
        'stats' => [
            'eleves' => $totalEleves,
            'classes' => Classe::count(),
            'resultats' => Resultat::count(),
            'bulletins_disponibles' => $elevesAvecBulletin,
            'bulletins_indisponibles' => max(0, $totalEleves - $elevesAvecBulletin),
            'matieres_calculees' => Resultat::count(),
            'autorisations_bulletin' => $autorisationsBulletin,
            'blocages_bulletin' => $blocagesBulletin,
            'moyenne_globale' => $moyenneGlobale,
            'meilleure_classe' => $meilleureClasse,
            'meilleur_global' => $meilleurGlobal,
            'comptabilite' => [
                'a_jour' => PaiementStatut::where('statut', 'a_jour')->count(),
                'partiel' => PaiementStatut::where('statut', 'partiel')->count(),
                'en_retard' => PaiementStatut::where('statut', 'en_retard')->count(),
                'bloque' => PaiementStatut::where('statut', 'bloque')->count(),
                'autorisation_exceptionnelle' => PaiementStatut::where('statut', 'autorisation_exceptionnelle')->count(),
            ],
            'audits_recents' => Audit::count(),
        ],
    ]);
})->middleware(['auth', 'verified'])->name('direction.dashboard');

Route::get('/referentiels/matieres', [ReferentielMatiereController::class, 'index'])->middleware(['auth', 'verified'])->name('referentiels.matieres');
Route::get('/referentiels/matieres/create', [ReferentielMatiereController::class, 'create'])->middleware(['auth', 'verified'])->name('referentiels.matieres.create');
Route::post('/referentiels/matieres', [ReferentielMatiereController::class, 'store'])->middleware(['auth', 'verified'])->name('referentiels.matieres.store');
Route::get('/referentiels/matieres/{matiere}/edit', [ReferentielMatiereController::class, 'edit'])->middleware(['auth', 'verified'])->name('referentiels.matieres.edit');
Route::put('/referentiels/matieres/{matiere}', [ReferentielMatiereController::class, 'update'])->middleware(['auth', 'verified'])->name('referentiels.matieres.update');

Route::get('/eleves/inscriptions', [EleveInscriptionController::class, 'index'])->middleware(['auth', 'verified'])->name('eleves.inscriptions');
Route::get('/eleves/inscriptions/create', [EleveInscriptionController::class, 'create'])->middleware(['auth', 'verified'])->name('eleves.inscriptions.create');
Route::post('/eleves/inscriptions', [EleveInscriptionController::class, 'store'])->middleware(['auth', 'verified'])->name('eleves.inscriptions.store');
Route::get('/eleves/inscriptions/{inscription}/edit', [EleveInscriptionController::class, 'edit'])->middleware(['auth', 'verified'])->name('eleves.inscriptions.edit');
Route::put('/eleves/inscriptions/{inscription}', [EleveInscriptionController::class, 'update'])->middleware(['auth', 'verified'])->name('eleves.inscriptions.update');

Route::get('/comptabilite/statuts', [ComptabiliteController::class, 'index'])->middleware(['auth', 'verified'])->name('comptabilite.statuts');
Route::get('/comptabilite/statuts/{paiementStatut}/edit', [ComptabiliteController::class, 'edit'])->middleware(['auth', 'verified'])->name('comptabilite.edit');
Route::put('/comptabilite/statuts/{paiementStatut}', [ComptabiliteController::class, 'update'])->middleware(['auth', 'verified'])->name('comptabilite.update');
Route::get('/comptabilite/statuts/{paiementStatut}/paiements', [ComptabiliteController::class, 'payments'])->middleware(['auth', 'verified'])->name('comptabilite.paiements');
Route::post('/comptabilite/statuts/{paiementStatut}/paiements', [ComptabiliteController::class, 'storePayment'])->middleware(['auth', 'verified'])->name('comptabilite.paiements.store');
Route::get('/comptabilite/statuts/{paiementStatut}/paiements/{paiement}/edit', [ComptabiliteController::class, 'editPayment'])->middleware(['auth', 'verified'])->name('comptabilite.paiements.edit');
Route::put('/comptabilite/statuts/{paiementStatut}/paiements/{paiement}', [ComptabiliteController::class, 'updatePayment'])->middleware(['auth', 'verified'])->name('comptabilite.paiements.update');
Route::delete('/comptabilite/statuts/{paiementStatut}/paiements/{paiement}', [ComptabiliteController::class, 'destroyPayment'])->middleware(['auth', 'verified'])->name('comptabilite.paiements.destroy');

Route::get('/notes/evaluations', [EvaluationController::class, 'index'])->middleware(['auth', 'verified'])->name('notes.evaluations');
Route::get('/notes/evaluations/create', [EvaluationController::class, 'create'])->middleware(['auth', 'verified'])->name('notes.evaluations.create');
Route::post('/notes/evaluations', [EvaluationController::class, 'store'])->middleware(['auth', 'verified'])->name('notes.evaluations.store');
Route::get('/notes/evaluations/{evaluation}/edit', [EvaluationController::class, 'edit'])->middleware(['auth', 'verified'])->name('notes.evaluations.edit');
Route::put('/notes/evaluations/{evaluation}', [EvaluationController::class, 'update'])->middleware(['auth', 'verified'])->name('notes.evaluations.update');
Route::get('/notes/evaluations/{evaluation}', [EvaluationController::class, 'show'])->middleware(['auth', 'verified'])->name('notes.evaluations.show');
Route::post('/notes/evaluations/{evaluation}/saisie', [EvaluationController::class, 'storeNotes'])->middleware(['auth', 'verified'])->name('notes.evaluations.saisie');

Route::get('/resultats/trimestriels', [ResultatTrimestrielController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('resultats.trimestriels');

Route::get('/audits', function (Request $request) use ($ensureRoles) {
    $ensureRoles('administration');

    $query = Audit::query()
        ->with('user')
        ->latest();

    if ($request->filled('action')) {
        $query->where('action', $request->string('action')->toString());
    }

    if ($request->filled('auditable_type')) {
        $query->where('auditable_type', $request->string('auditable_type')->toString());
    }

    if ($request->filled('user_id')) {
        $query->where('user_id', $request->integer('user_id'));
    }

    $audits = $query
        ->limit(100)
        ->get();

    return view('audits.index', [
        'audits' => $audits,
        'stats' => [
            'total' => Audit::count(),
            'notes' => Audit::where('auditable_type', 'note')->count(),
            'resultats' => Audit::where('auditable_type', 'resultat')->count(),
        ],
        'filters' => [
            'actions' => Audit::query()->distinct()->orderBy('action')->pluck('action'),
            'auditable_types' => Audit::query()->distinct()->orderBy('auditable_type')->pluck('auditable_type'),
            'users' => \App\Models\User::query()
                ->whereIn('id', Audit::query()->whereNotNull('user_id')->distinct()->pluck('user_id'))
                ->orderBy('name')
                ->get(['id', 'name']),
        ],
    ]);
})->middleware(['auth', 'verified'])->name('audits.index');

Route::post('/resultats/trimestriels/enregistrer', [ResultatTrimestrielController::class, 'store'])
    ->middleware(['auth', 'verified'])
    ->name('resultats.trimestriels.enregistrer');

Route::get('/bulletins/lots', [BulletinController::class, 'lots'])
    ->middleware(['auth', 'verified'])
    ->name('bulletins.lots');
Route::post('/bulletins/lots/generer', [BulletinController::class, 'generateLot'])
    ->middleware(['auth', 'verified'])
    ->name('bulletins.lots.generer');
Route::get('/bulletins/{eleve}/{trimestre}', [BulletinController::class, 'show'])
    ->middleware(['auth', 'verified'])
    ->name('bulletins.show');
Route::get('/bulletins/{eleve}/{trimestre}/pdf', [BulletinController::class, 'pdf'])
    ->middleware(['auth', 'verified'])
    ->name('bulletins.pdf');

$findPaiementStatutForTrimestre = function (Eleve $eleve, Trimestre $trimestre) {
    return app(BulletinWorkflowService::class)->findPaiementStatutForTrimestre($eleve, $trimestre);
};

Route::get('/portail/parent', function (Request $request) use ($findPaiementStatutForTrimestre) {
    if (! auth()->user()?->hasRole('parent')) {
        return redirect()
            ->route('dashboard')
            ->with('status', 'Ce portail est reserve aux comptes parents. Connecte-toi avec un profil parent pour y acceder.')
            ->with('status_type', 'error');
    }

    $relations = auth()->user()
        ->parentEleves()
        ->with([
            'eleve.inscriptions.classe.filiere',
            'eleve.resultats.trimestre.anneeScolaire',
        ])
        ->orderBy('eleve_id')
        ->get();

    abort_if($relations->isEmpty(), 404);

    $selectedEleveId = $request->integer('eleve_id');
    $parentRelation = $relations
        ->firstWhere('eleve_id', $selectedEleveId)
        ?? $relations->first();

    abort_if(! $parentRelation || ! $parentRelation->eleve, 404);

    $eleveOptions = $relations->map(function ($relation) {
        return [
            'eleve_id' => $relation->eleve_id,
            'label' => trim(($relation->eleve?->matricule ?? '').' - '.($relation->eleve?->nom ?? '').' '.($relation->eleve?->prenoms ?? '')),
            'lien_parente' => $relation->lien_parente,
        ];
    });

    $parentRelation->loadMissing([
        'eleve.inscriptions.classe.filiere',
        'eleve.inscriptions.anneeScolaire',
        'eleve.resultats.trimestre.anneeScolaire',
    ]);

    $parentRelation = auth()->user()
        ->parentEleves()
        ->with([
            'eleve.inscriptions.classe.filiere',
            'eleve.resultats.trimestre.anneeScolaire',
        ])
        ->whereKey($parentRelation->id)
        ->first();

    $eleve = $parentRelation->eleve;
    $dernierResultat = $eleve->resultats
        ->sortByDesc(fn ($resultat) => ($resultat->trimestre?->anneeScolaire?->date_debut?->timestamp ?? 0) * 10 + ($resultat->trimestre?->ordre ?? 0))
        ->first();

    $trimestre = $dernierResultat?->trimestre;
    $inscriptionActive = $eleve->inscriptions
        ->sortByDesc('annee_scolaire_id')
        ->first();
    $paiementStatut = $trimestre
        ? $findPaiementStatutForTrimestre($eleve, $trimestre)
        : ($inscriptionActive?->annee_scolaire_id
            ? PaiementStatut::query()
                ->where('eleve_id', $eleve->id)
                ->where('annee_scolaire_id', $inscriptionActive->annee_scolaire_id)
                ->latest('id')
                ->first()
            : null);
    $resultatsTrimestre = $trimestre
        ? $eleve->resultats->where('trimestre_id', $trimestre->id)
        : collect();
    $totalPoints = $resultatsTrimestre->sum(fn ($resultat) => (float) $resultat->points);
    $totalCoefficients = $resultatsTrimestre->sum(fn ($resultat) => (float) $resultat->coefficient);
    $moyenneGenerale = $totalCoefficients > 0 ? $totalPoints / $totalCoefficients : null;
    $rang = $resultatsTrimestre->pluck('rang')->filter(fn ($value) => $value !== null)->first();
    $statutPaiement = $paiementStatut?->statut;
    $messagesPaiement = [
        'a_jour' => [
            'titre' => 'Acces normalise',
            'message' => 'La situation comptable est reguliere. Le bulletin peut etre consulte normalement.',
        ],
        'partiel' => [
            'titre' => 'Regularisation attendue',
            'message' => 'Le paiement est partiel. La consultation du bulletin reste suspendue jusqu a confirmation par la comptabilite.',
        ],
        'en_retard' => [
            'titre' => 'Retard de paiement',
            'message' => 'Un retard a ete signale. Merci de prendre attache avec la comptabilite pour la regularisation.',
        ],
        'bloque' => [
            'titre' => 'Acces refuse',
            'message' => 'Aucun acces au bulletin n est autorise tant que la situation financiere n est pas regularisee.',
        ],
        'autorisation_exceptionnelle' => [
            'titre' => 'Acces exceptionnel accorde',
            'message' => 'Une autorisation derogatoire a ete accordee. Le bulletin reste consultable a titre exceptionnel.',
        ],
    ];
    $messagePaiement = $messagesPaiement[$statutPaiement] ?? [
        'titre' => 'Situation a verifier',
        'message' => 'Aucune decision comptable explicite n est encore disponible pour cet eleve.',
    ];

    return view('portail.parent', [
        'eleveOptions' => $eleveOptions,
        'parentRelation' => $parentRelation,
        'eleve' => $eleve,
        'trimestre' => $trimestre,
        'classe' => $inscriptionActive?->classe,
        'anneeScolaire' => $inscriptionActive?->anneeScolaire,
        'paiementStatut' => $paiementStatut,
        'accesBulletinAutorise' => $paiementStatut?->autorise_acces_bulletin ?? true,
        'messagePaiement' => $messagePaiement,
        'resultatsRapides' => [
            'moyenne_generale' => $moyenneGenerale,
            'rang' => $rang,
            'matieres' => $resultatsTrimestre->count(),
        ],
    ]);
})->middleware(['auth', 'verified'])->name('portail.parent');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
