<?php

namespace App\Http\Controllers;

use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\Eleve;
use App\Models\Inscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EleveInscriptionController extends Controller
{
    public function index(): View
    {
        $this->ensureRoles(['administration', 'direction']);

        $inscriptions = Inscription::query()
            ->with([
                'eleve',
                'classe.filiere',
                'anneeScolaire',
            ])
            ->orderByDesc('annee_scolaire_id')
            ->orderBy('classe_id')
            ->get();

        return view('eleves.inscriptions', [
            'inscriptions' => $inscriptions,
            'stats' => [
                'eleves' => Eleve::count(),
                'inscriptions' => Inscription::count(),
                'classes_couvertes' => Inscription::distinct('classe_id')->count('classe_id'),
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
