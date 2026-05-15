<?php

namespace App\Http\Controllers;

use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\Eleve;
use App\Models\Inscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EleveInscriptionController extends Controller
{
    public function index(Request $request): View
    {
        $this->ensureRoles(['administration', 'direction']);

        $search = trim((string) $request->string('q'));
        $selectedStatus = $request->string('statut')->toString();
        $selectedClasseId = $request->integer('classe_id');
        $selectedAnneeId = $request->integer('annee_scolaire_id');

        $inscriptionsQuery = Inscription::query()
            ->with([
                'eleve',
                'classe.filiere',
                'anneeScolaire',
            ]);

        if ($search !== '') {
            $inscriptionsQuery->whereHas('eleve', function ($query) use ($search) {
                $query
                    ->where('matricule', 'like', "%{$search}%")
                    ->orWhere('nom', 'like', "%{$search}%")
                    ->orWhere('prenoms', 'like', "%{$search}%")
                    ->orWhere('nom_parent', 'like', "%{$search}%")
                    ->orWhere('contact_parent', 'like', "%{$search}%")
                    ->orWhere('contact_principal', 'like', "%{$search}%");
            });
        }

        if ($selectedStatus !== '') {
            $inscriptionsQuery->where('statut', $selectedStatus);
        }

        if ($selectedClasseId > 0) {
            $inscriptionsQuery->where('classe_id', $selectedClasseId);
        }

        if ($selectedAnneeId > 0) {
            $inscriptionsQuery->where('annee_scolaire_id', $selectedAnneeId);
        }

        $inscriptions = $inscriptionsQuery
            ->orderByDesc('annee_scolaire_id')
            ->orderBy('classe_id')
            ->orderBy('eleve_id')
            ->get();

        $historicalByEleve = DB::table('historical_import_result_mappings')
            ->selectRaw('eleve_id, COUNT(*) AS result_count, COUNT(DISTINCT annee_scolaire_id) AS year_count, COUNT(DISTINCT classe_id) AS class_count')
            ->groupBy('eleve_id')
            ->get()
            ->keyBy('eleve_id');

        $activeYear = AnneeScolaire::query()
            ->where('statut', 'active')
            ->latest('date_debut')
            ->first();

        $classes = Classe::query()
            ->with('filiere')
            ->orderBy('code')
            ->get();

        $anneesScolaires = AnneeScolaire::query()
            ->orderByDesc('date_debut')
            ->get();

        $filteredHistoricalCount = $inscriptions
            ->filter(fn (Inscription $inscription) => $historicalByEleve->has($inscription->eleve_id))
            ->count();

        $statusBreakdown = [
            'inscrit' => (clone $inscriptionsQuery)->where('statut', 'inscrit')->count(),
            'transfere' => (clone $inscriptionsQuery)->where('statut', 'transfere')->count(),
            'abandonne' => (clone $inscriptionsQuery)->where('statut', 'abandonne')->count(),
            'suspendu' => (clone $inscriptionsQuery)->where('statut', 'suspendu')->count(),
        ];

        return view('eleves.inscriptions', [
            'inscriptions' => $inscriptions,
            'historicalByEleve' => $historicalByEleve,
            'activeYear' => $activeYear,
            'classes' => $classes,
            'anneesScolaires' => $anneesScolaires,
            'filters' => [
                'q' => $search,
                'statut' => $selectedStatus,
                'classe_id' => $selectedClasseId > 0 ? $selectedClasseId : null,
                'annee_scolaire_id' => $selectedAnneeId > 0 ? $selectedAnneeId : null,
            ],
            'stats' => [
                'eleves' => Eleve::count(),
                'inscriptions' => Inscription::count(),
                'classes_couvertes' => Inscription::distinct('classe_id')->count('classe_id'),
                'eleves_historiques' => $historicalByEleve->count(),
                'resultats_filtres' => $inscriptions->count(),
                'historiques_filtres' => $filteredHistoricalCount,
                'statuts_filtres' => $statusBreakdown,
            ],
        ]);
    }

    public function create(): View
    {
        $this->ensureRoles(['administration', 'direction']);

        return view('eleves.form', [
            'mode' => 'create',
            'eleve' => new Eleve(),
            'inscription' => new Inscription(),
            'classes' => Classe::query()->with('filiere')->orderBy('code')->get(),
            'anneesScolaires' => AnneeScolaire::query()->orderByDesc('date_debut')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureRoles(['administration', 'direction']);

        $validated = $request->validate([
            'matricule' => ['required', 'string', 'max:255', 'unique:eleves,matricule'],
            'nom' => ['required', 'string', 'max:255'],
            'prenoms' => ['required', 'string', 'max:255'],
            'sexe' => ['nullable', 'in:M,F'],
            'date_naissance' => ['nullable', 'date'],
            'lieu_naissance' => ['nullable', 'string', 'max:255'],
            'contact_principal' => ['nullable', 'string', 'max:255'],
            'nom_parent' => ['nullable', 'string', 'max:255'],
            'contact_parent' => ['nullable', 'string', 'max:255'],
            'adresse' => ['nullable', 'string', 'max:1000'],
            'classe_id' => ['required', 'exists:classes,id'],
            'annee_scolaire_id' => ['required', 'exists:annees_scolaires,id'],
            'statut' => ['required', 'in:inscrit,transfere,abandonne,suspendu'],
            'date_inscription' => ['nullable', 'date'],
        ]);

        $eleve = Eleve::create([
            'matricule' => $validated['matricule'],
            'nom' => $validated['nom'],
            'prenoms' => $validated['prenoms'],
            'sexe' => $validated['sexe'] ?? null,
            'date_naissance' => $validated['date_naissance'] ?? null,
            'lieu_naissance' => $validated['lieu_naissance'] ?? null,
            'contact_principal' => $validated['contact_principal'] ?? null,
            'nom_parent' => $validated['nom_parent'] ?? null,
            'contact_parent' => $validated['contact_parent'] ?? null,
            'adresse' => $validated['adresse'] ?? null,
            'actif' => true,
        ]);

        $inscription = Inscription::create([
            'eleve_id' => $eleve->id,
            'classe_id' => $validated['classe_id'],
            'annee_scolaire_id' => $validated['annee_scolaire_id'],
            'statut' => $validated['statut'],
            'date_inscription' => $validated['date_inscription'] ?? null,
        ]);

        $this->recordAudit(
            'eleve_cree',
            'eleve',
            $eleve->id,
            null,
            [
                'matricule' => $eleve->matricule,
                'nom' => $eleve->nom,
                'prenoms' => $eleve->prenoms,
                'classe_id' => $inscription->classe_id,
                'annee_scolaire_id' => $inscription->annee_scolaire_id,
                'statut' => $inscription->statut,
            ],
            "Creation de l eleve {$eleve->nom} {$eleve->prenoms} avec inscription initiale."
        );

        return redirect()
            ->route('eleves.inscriptions')
            ->with('status', 'Eleve et inscription crees avec succes.');
    }

    public function edit(Inscription $inscription): View
    {
        $this->ensureRoles(['administration', 'direction']);

        return view('eleves.form', [
            'mode' => 'edit',
            'eleve' => $inscription->load(['eleve'])->eleve,
            'inscription' => $inscription->load(['classe', 'anneeScolaire']),
            'classes' => Classe::query()->with('filiere')->orderBy('code')->get(),
            'anneesScolaires' => AnneeScolaire::query()->orderByDesc('date_debut')->get(),
        ]);
    }

    public function update(Request $request, Inscription $inscription): RedirectResponse
    {
        $this->ensureRoles(['administration', 'direction']);

        $eleve = $inscription->eleve;

        $validated = $request->validate([
            'matricule' => ['required', 'string', 'max:255', 'unique:eleves,matricule,'.$eleve->id],
            'nom' => ['required', 'string', 'max:255'],
            'prenoms' => ['required', 'string', 'max:255'],
            'sexe' => ['nullable', 'in:M,F'],
            'date_naissance' => ['nullable', 'date'],
            'lieu_naissance' => ['nullable', 'string', 'max:255'],
            'contact_principal' => ['nullable', 'string', 'max:255'],
            'nom_parent' => ['nullable', 'string', 'max:255'],
            'contact_parent' => ['nullable', 'string', 'max:255'],
            'adresse' => ['nullable', 'string', 'max:1000'],
            'classe_id' => ['required', 'exists:classes,id'],
            'annee_scolaire_id' => ['required', 'exists:annees_scolaires,id'],
            'statut' => ['required', 'in:inscrit,transfere,abandonne,suspendu'],
            'date_inscription' => ['nullable', 'date'],
        ]);

        $anciennesValeurs = [
            'matricule' => $eleve->matricule,
            'nom' => $eleve->nom,
            'prenoms' => $eleve->prenoms,
            'classe_id' => $inscription->classe_id,
            'annee_scolaire_id' => $inscription->annee_scolaire_id,
            'statut' => $inscription->statut,
        ];

        $eleve->update([
            'matricule' => $validated['matricule'],
            'nom' => $validated['nom'],
            'prenoms' => $validated['prenoms'],
            'sexe' => $validated['sexe'] ?? null,
            'date_naissance' => $validated['date_naissance'] ?? null,
            'lieu_naissance' => $validated['lieu_naissance'] ?? null,
            'contact_principal' => $validated['contact_principal'] ?? null,
            'nom_parent' => $validated['nom_parent'] ?? null,
            'contact_parent' => $validated['contact_parent'] ?? null,
            'adresse' => $validated['adresse'] ?? null,
        ]);

        $inscription->update([
            'classe_id' => $validated['classe_id'],
            'annee_scolaire_id' => $validated['annee_scolaire_id'],
            'statut' => $validated['statut'],
            'date_inscription' => $validated['date_inscription'] ?? null,
        ]);

        $nouvellesValeurs = [
            'matricule' => $eleve->matricule,
            'nom' => $eleve->nom,
            'prenoms' => $eleve->prenoms,
            'classe_id' => $inscription->classe_id,
            'annee_scolaire_id' => $inscription->annee_scolaire_id,
            'statut' => $inscription->statut,
        ];

        if ($anciennesValeurs !== $nouvellesValeurs) {
            $this->recordAudit(
                'eleve_modifie',
                'eleve',
                $eleve->id,
                $anciennesValeurs,
                $nouvellesValeurs,
                "Modification de l eleve {$eleve->nom} {$eleve->prenoms} et de son inscription."
            );
        }

        return redirect()
            ->route('eleves.inscriptions')
            ->with('status', 'Eleve et inscription modifies avec succes.');
    }
}
