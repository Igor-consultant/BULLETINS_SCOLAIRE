<?php

namespace App\Http\Controllers;

use App\Models\ClasseMatiere;
use App\Models\Evaluation;
use App\Models\Inscription;
use App\Models\Note;
use App\Models\Trimestre;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EvaluationController extends Controller
{
    public function index(): View
    {
        $this->ensureRoles(['administration', 'direction', 'enseignant']);

        $evaluations = Evaluation::query()
            ->with([
                'trimestre',
                'classeMatiere.classe.filiere',
                'classeMatiere.matiere',
            ])
            ->withCount('notes')
            ->withCount([
                'notes as absences_count' => fn ($query) => $query->where('absence', true),
            ])
            ->orderByDesc('date_evaluation')
            ->orderByDesc('id')
            ->get();

        return view('notes.evaluations', [
            'evaluations' => $evaluations,
            'stats' => [
                'evaluations' => Evaluation::count(),
                'notes' => Note::count(),
                'absences' => Note::where('absence', true)->count(),
                'classes_couvertes' => Evaluation::query()
                    ->distinct('classe_matiere_id')
                    ->count('classe_matiere_id'),
            ],
        ]);
    }

    public function create(): View
    {
        $this->ensureRoles(['administration', 'direction', 'enseignant']);

        return view('notes.evaluation-form', [
            'mode' => 'create',
            'evaluation' => new Evaluation([
                'type' => 'devoir',
                'note_sur' => 20,
                'statut' => 'brouillon',
            ]),
            'classeMatieres' => $this->classeMatieres(),
            'trimestres' => $this->trimestres(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureRoles(['administration', 'direction', 'enseignant']);

        $validated = $this->validateEvaluation($request);
        $evaluation = Evaluation::create($validated);
        $evaluation->load(['classeMatiere.classe', 'classeMatiere.matiere', 'trimestre']);

        $this->recordAudit(
            'evaluation_creee',
            'evaluation',
            $evaluation->id,
            null,
            $this->evaluationValues($evaluation),
            "Creation de l evaluation {$evaluation->libelle}."
        );

        return redirect()
            ->route('notes.evaluations')
            ->with('status', 'Evaluation creee avec succes.');
    }

    public function edit(Evaluation $evaluation): View
    {
        $this->ensureRoles(['administration', 'direction', 'enseignant']);

        return view('notes.evaluation-form', [
            'mode' => 'edit',
            'evaluation' => $evaluation,
            'classeMatieres' => $this->classeMatieres(),
            'trimestres' => $this->trimestres(),
        ]);
    }

    public function update(Request $request, Evaluation $evaluation): RedirectResponse
    {
        $this->ensureRoles(['administration', 'direction', 'enseignant']);

        $validated = $this->validateEvaluation($request);
        $anciennesValeurs = $this->evaluationValues($evaluation);
        $evaluation->update($validated);
        $nouvellesValeurs = $this->evaluationValues($evaluation);

        if ($anciennesValeurs !== $nouvellesValeurs) {
            $this->recordAudit(
                'evaluation_modifiee',
                'evaluation',
                $evaluation->id,
                $anciennesValeurs,
                $nouvellesValeurs,
                "Modification de l evaluation {$evaluation->libelle}."
            );
        }

        return redirect()
            ->route('notes.evaluations')
            ->with('status', 'Evaluation modifiee avec succes.');
    }

    public function show(Evaluation $evaluation): View
    {
        $this->ensureRoles(['administration', 'direction', 'enseignant']);

        $evaluation->load([
            'trimestre.anneeScolaire',
            'classeMatiere.classe.filiere',
            'classeMatiere.matiere',
            'notes',
        ]);

        $classe = $evaluation->classeMatiere?->classe;
        $anneeScolaireId = $evaluation->trimestre?->annee_scolaire_id;

        $inscriptions = Inscription::query()
            ->with('eleve')
            ->where('classe_id', $classe?->id)
            ->where('annee_scolaire_id', $anneeScolaireId)
            ->orderBy('id')
            ->get();

        return view('notes.saisie', [
            'evaluation' => $evaluation,
            'inscriptions' => $inscriptions,
            'notesByEleve' => $evaluation->notes->keyBy('eleve_id'),
        ]);
    }

    public function storeNotes(Request $request, Evaluation $evaluation): RedirectResponse
    {
        $this->ensureRoles(['administration', 'direction', 'enseignant']);

        $evaluation->load(['trimestre.anneeScolaire', 'classeMatiere.classe']);

        $classe = $evaluation->classeMatiere?->classe;
        $anneeScolaireId = $evaluation->trimestre?->annee_scolaire_id;

        $inscriptions = Inscription::query()
            ->with('eleve')
            ->where('classe_id', $classe?->id)
            ->where('annee_scolaire_id', $anneeScolaireId)
            ->get();

        $eleveIds = $inscriptions->pluck('eleve_id')->map(fn ($id) => (string) $id)->all();

        $validated = $request->validate([
            'notes' => ['required', 'array'],
            'notes.*.note' => ['nullable', 'numeric'],
            'notes.*.absence' => ['nullable', 'boolean'],
            'notes.*.observation' => ['nullable', 'string', 'max:1000'],
        ]);

        $errors = [];

        foreach ($inscriptions as $inscription) {
            $eleveId = (string) $inscription->eleve_id;
            $payload = $validated['notes'][$eleveId] ?? [];
            $absence = filter_var($payload['absence'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $note = $payload['note'] ?? null;

            if (! in_array($eleveId, $eleveIds, true)) {
                continue;
            }

            if (! $absence && ($note === null || $note === '')) {
                $errors["notes.$eleveId.note"] = "La note est obligatoire pour {$inscription->eleve->nom} {$inscription->eleve->prenoms}.";
                continue;
            }

            if (! $absence && is_numeric($note) && ((float) $note < 0 || (float) $note > (float) $evaluation->note_sur)) {
                $errors["notes.$eleveId.note"] = "La note de {$inscription->eleve->nom} {$inscription->eleve->prenoms} doit etre comprise entre 0 et {$evaluation->note_sur}.";
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        foreach ($inscriptions as $inscription) {
            $eleveId = (string) $inscription->eleve_id;
            $payload = $validated['notes'][$eleveId] ?? [];
            $absence = filter_var($payload['absence'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $note = $payload['note'] ?? null;
            $noteExistante = Note::query()
                ->where('evaluation_id', $evaluation->id)
                ->where('eleve_id', $inscription->eleve_id)
                ->first();

            $anciennesValeurs = $noteExistante
                ? [
                    'note' => $noteExistante->note,
                    'absence' => (bool) $noteExistante->absence,
                    'observation' => $noteExistante->observation,
                ]
                : null;
            $nouvellesValeurs = [
                'note' => $absence ? null : ($note === '' ? null : $note),
                'absence' => $absence,
                'observation' => $payload['observation'] ?? null,
            ];

            $noteModele = Note::updateOrCreate(
                [
                    'evaluation_id' => $evaluation->id,
                    'eleve_id' => $inscription->eleve_id,
                ],
                $nouvellesValeurs
            );

            if ($anciennesValeurs !== $nouvellesValeurs) {
                $this->recordAudit(
                    $noteExistante ? 'note_modifiee' : 'note_creee',
                    'note',
                    $noteModele->id,
                    $anciennesValeurs,
                    $nouvellesValeurs,
                    "Saisie des notes pour {$inscription->eleve->nom} {$inscription->eleve->prenoms} dans l evaluation {$evaluation->libelle}."
                );
            }
        }

        return redirect()
            ->route('notes.evaluations.show', $evaluation)
            ->with('status', 'Saisie des notes enregistree avec succes.');
    }

    private function validateEvaluation(Request $request): array
    {
        return $request->validate([
            'classe_matiere_id' => ['required', 'exists:classe_matieres,id'],
            'trimestre_id' => ['required', 'exists:trimestres,id'],
            'libelle' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:devoir,composition'],
            'date_evaluation' => ['nullable', 'date'],
            'note_sur' => ['required', 'numeric', 'min:1'],
            'coefficient_local' => ['nullable', 'numeric', 'min:0'],
            'statut' => ['required', 'in:brouillon,validee'],
        ]);
    }

    private function evaluationValues(Evaluation $evaluation): array
    {
        return [
            'classe_matiere_id' => $evaluation->classe_matiere_id,
            'trimestre_id' => $evaluation->trimestre_id,
            'libelle' => $evaluation->libelle,
            'type' => $evaluation->type,
            'date_evaluation' => $evaluation->date_evaluation?->format('Y-m-d'),
            'note_sur' => (float) $evaluation->note_sur,
            'coefficient_local' => $evaluation->coefficient_local !== null ? (float) $evaluation->coefficient_local : null,
            'statut' => $evaluation->statut,
        ];
    }

    private function classeMatieres()
    {
        return ClasseMatiere::query()
            ->with(['classe.filiere', 'matiere'])
            ->orderBy('classe_id')
            ->get();
    }

    private function trimestres()
    {
        return Trimestre::query()
            ->with('anneeScolaire')
            ->orderBy('annee_scolaire_id')
            ->orderBy('ordre')
            ->get();
    }
}
