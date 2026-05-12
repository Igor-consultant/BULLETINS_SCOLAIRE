<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use App\Models\Resultat;
use App\Services\BulletinWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ResultatTrimestrielController extends Controller
{
    public function __construct(
        private readonly BulletinWorkflowService $workflow,
    ) {
    }

    public function index(): View
    {
        $this->ensureRoles(['administration', 'direction']);

        $data = $this->workflow->buildTrimestrielData();
        $trimestre = $data['trimestre'];

        if ($trimestre) {
            $data['resultatsParClasse'] = $data['resultatsParClasse']->map(function (array $bloc) use ($trimestre) {
                $bloc['eleves'] = $bloc['eleves']->map(function (array $ligne) use ($trimestre) {
                    $paiementStatut = $this->workflow->findPaiementStatutForTrimestre($ligne['eleve'], $trimestre);

                    $ligne['paiement_statut'] = $paiementStatut;
                    $ligne['acces_bulletin_autorise'] = $paiementStatut?->autorise_acces_bulletin ?? true;

                    return $ligne;
                });

                return $bloc;
            });
        }

        return view('resultats.trimestriels', $data);
    }

    public function store(): RedirectResponse
    {
        $this->ensureRoles(['administration', 'direction']);

        $data = $this->workflow->buildTrimestrielData();
        $trimestre = $data['trimestre'];
        $savedRows = 0;

        if (! $trimestre) {
            return redirect()
                ->route('resultats.trimestriels')
                ->with('status', 'Aucun trimestre actif n est disponible pour l enregistrement.');
        }

        foreach ($data['resultatsParClasse'] as $bloc) {
            $classe = $bloc['classe'];

            foreach ($bloc['eleves'] as $ligne) {
                foreach ($ligne['matieres'] as $matiere) {
                    if (! $matiere['matiere_id']) {
                        continue;
                    }

                    $resultatExistant = Resultat::query()
                        ->where('eleve_id', $ligne['eleve']->id)
                        ->where('classe_id', $classe->id)
                        ->where('trimestre_id', $trimestre->id)
                        ->where('matiere_id', $matiere['matiere_id'])
                        ->first();

                    $anciennesValeurs = $resultatExistant
                        ? [
                            'coefficient' => $resultatExistant->coefficient,
                            'moyenne_devoirs' => $resultatExistant->moyenne_devoirs,
                            'composition' => $resultatExistant->composition,
                            'moyenne_matiere' => $resultatExistant->moyenne_matiere,
                            'points' => $resultatExistant->points,
                            'rang' => $resultatExistant->rang,
                            'statut_calcul' => $resultatExistant->statut_calcul,
                        ]
                        : null;
                    $nouvellesValeurs = [
                        'coefficient' => $matiere['coefficient'],
                        'moyenne_devoirs' => $matiere['moyenne_devoirs'],
                        'composition' => $matiere['composition'],
                        'moyenne_matiere' => $matiere['moyenne_matiere'],
                        'points' => $matiere['points'],
                        'rang' => $ligne['rang'],
                        'statut_calcul' => 'provisoire',
                    ];

                    $resultat = Resultat::updateOrCreate(
                        [
                            'eleve_id' => $ligne['eleve']->id,
                            'classe_id' => $classe->id,
                            'trimestre_id' => $trimestre->id,
                            'matiere_id' => $matiere['matiere_id'],
                        ],
                        $nouvellesValeurs
                    );

                    if ($anciennesValeurs !== $nouvellesValeurs) {
                        $this->recordAudit(
                            $resultatExistant ? 'resultat_modifie' : 'resultat_cree',
                            'resultat',
                            $resultat->id,
                            $anciennesValeurs,
                            $nouvellesValeurs,
                            "Enregistrement du resultat trimestriel pour {$ligne['eleve']->nom} {$ligne['eleve']->prenoms} en {$matiere['matiere']}."
                        );
                    }

                    $savedRows++;
                }
            }
        }

        return redirect()
            ->route('resultats.trimestriels')
            ->with('status', $savedRows.' ligne(s) de resultats enregistree(s) en base.');
    }

    protected function ensureRoles(array|string $roles): void
    {
        abort_unless(auth()->user()?->hasAnyRole((array) $roles), 403);
    }

    protected function recordAudit(
        string $action,
        string $auditableType,
        ?int $auditableId,
        ?array $anciennesValeurs,
        ?array $nouvellesValeurs,
        ?string $description = null
    ): void {
        Audit::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'auditable_type' => $auditableType,
            'auditable_id' => $auditableId,
            'anciennes_valeurs' => $anciennesValeurs,
            'nouvelles_valeurs' => $nouvellesValeurs,
            'description' => $description,
        ]);
    }
}
