<?php

namespace App\Http\Controllers;

use App\Models\Classe;
use App\Models\ClasseMatiere;
use App\Models\Matiere;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReferentielMatiereController extends Controller
{
    public function index(): View
    {
        $this->ensureRoles(['administration', 'direction']);

        $matieres = Matiere::query()
            ->withCount('classeMatieres')
            ->orderBy('libelle')
            ->get();

        $classes = Classe::query()
            ->with([
                'filiere',
                'classeMatieres.matiere',
            ])
            ->orderBy('code')
            ->get();

        return view('referentiels.matieres', [
            'matieres' => $matieres,
            'classes' => $classes,
            'stats' => [
                'matieres' => Matiere::count(),
                'affectations' => ClasseMatiere::count(),
                'classes_couvertes' => Classe::whereHas('classeMatieres')->count(),
            ],
        ]);
    }

    public function create(): View
    {
        $this->ensureRoles(['administration', 'direction']);

        return view('referentiels.matieres-form', [
            'matiere' => new Matiere([
                'actif' => true,
            ]),
            'classes' => Classe::query()
                ->with('filiere')
                ->orderBy('code')
                ->get(),
            'affectations' => collect(),
            'mode' => 'create',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureRoles(['administration', 'direction']);

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:255', 'unique:matieres,code'],
            'libelle' => ['required', 'string', 'max:255'],
            'actif' => ['nullable', 'boolean'],
            'affectations' => ['nullable', 'array'],
            'affectations.*.classe_id' => ['required', 'exists:classes,id'],
            'affectations.*.coefficient' => ['required', 'numeric', 'min:0'],
            'affectations.*.enseignant_nom' => ['nullable', 'string', 'max:255'],
            'affectations.*.actif' => ['nullable', 'boolean'],
        ]);

        $matiere = Matiere::create([
            'code' => $validated['code'],
            'libelle' => $validated['libelle'],
            'actif' => (bool) ($validated['actif'] ?? false),
        ]);

        $affectationsCreees = collect($validated['affectations'] ?? [])
            ->map(function (array $affectation) use ($matiere) {
                return ClasseMatiere::create([
                    'classe_id' => (int) $affectation['classe_id'],
                    'matiere_id' => $matiere->id,
                    'coefficient' => $affectation['coefficient'],
                    'enseignant_nom' => $affectation['enseignant_nom'] ?? null,
                    'actif' => (bool) ($affectation['actif'] ?? false),
                ]);
            })
            ->map(fn (ClasseMatiere $ligne) => [
                'classe_id' => $ligne->classe_id,
                'coefficient' => (float) $ligne->coefficient,
                'enseignant_nom' => $ligne->enseignant_nom,
                'actif' => (bool) $ligne->actif,
            ])
            ->values()
            ->all();

        $this->recordAudit(
            'matiere_creee',
            'matiere',
            $matiere->id,
            null,
            [
                'code' => $matiere->code,
                'libelle' => $matiere->libelle,
                'actif' => (bool) $matiere->actif,
                'affectations' => $affectationsCreees,
            ],
            "Creation de la matiere {$matiere->libelle}."
        );

        return redirect()
            ->route('referentiels.matieres')
            ->with('status', 'Matiere creee avec succes.');
    }

    public function edit(Matiere $matiere): View
    {
        $this->ensureRoles(['administration', 'direction']);

        $matiere->load([
            'classeMatieres' => fn ($query) => $query->orderBy('classe_id'),
        ]);

        return view('referentiels.matieres-form', [
            'matiere' => $matiere,
            'classes' => Classe::query()
                ->with('filiere')
                ->orderBy('code')
                ->get(),
            'affectations' => $matiere->classeMatieres,
            'mode' => 'edit',
        ]);
    }

    public function update(Request $request, Matiere $matiere): RedirectResponse
    {
        $this->ensureRoles(['administration', 'direction']);

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:255', 'unique:matieres,code,'.$matiere->id],
            'libelle' => ['required', 'string', 'max:255'],
            'actif' => ['nullable', 'boolean'],
            'affectations' => ['nullable', 'array'],
            'affectations.*.classe_id' => ['required', 'exists:classes,id'],
            'affectations.*.coefficient' => ['required', 'numeric', 'min:0'],
            'affectations.*.enseignant_nom' => ['nullable', 'string', 'max:255'],
            'affectations.*.actif' => ['nullable', 'boolean'],
        ]);

        $anciennesValeurs = [
            'code' => $matiere->code,
            'libelle' => $matiere->libelle,
            'actif' => (bool) $matiere->actif,
            'affectations' => $matiere->classeMatieres()
                ->orderBy('classe_id')
                ->get()
                ->map(fn (ClasseMatiere $ligne) => [
                    'classe_id' => $ligne->classe_id,
                    'coefficient' => (float) $ligne->coefficient,
                    'enseignant_nom' => $ligne->enseignant_nom,
                    'actif' => (bool) $ligne->actif,
                ])
                ->values()
                ->all(),
        ];

        $matiere->update([
            'code' => $validated['code'],
            'libelle' => $validated['libelle'],
            'actif' => (bool) ($validated['actif'] ?? false),
        ]);

        $matiere->classeMatieres()->delete();

        $nouvellesAffectations = collect($validated['affectations'] ?? [])
            ->map(function (array $affectation) use ($matiere) {
                return ClasseMatiere::create([
                    'classe_id' => (int) $affectation['classe_id'],
                    'matiere_id' => $matiere->id,
                    'coefficient' => $affectation['coefficient'],
                    'enseignant_nom' => $affectation['enseignant_nom'] ?? null,
                    'actif' => (bool) ($affectation['actif'] ?? false),
                ]);
            })
            ->map(fn (ClasseMatiere $ligne) => [
                'classe_id' => $ligne->classe_id,
                'coefficient' => (float) $ligne->coefficient,
                'enseignant_nom' => $ligne->enseignant_nom,
                'actif' => (bool) $ligne->actif,
            ])
            ->values()
            ->all();

        $nouvellesValeurs = [
            'code' => $matiere->code,
            'libelle' => $matiere->libelle,
            'actif' => (bool) $matiere->actif,
            'affectations' => $nouvellesAffectations,
        ];

        if ($anciennesValeurs !== $nouvellesValeurs) {
            $this->recordAudit(
                'matiere_modifiee',
                'matiere',
                $matiere->id,
                $anciennesValeurs,
                $nouvellesValeurs,
                "Modification de la matiere {$matiere->libelle}."
            );
        }

        return redirect()
            ->route('referentiels.matieres')
            ->with('status', 'Matiere modifiee avec succes.');
    }
}
