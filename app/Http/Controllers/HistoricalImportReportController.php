<?php

namespace App\Http\Controllers;

use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\Eleve;
use App\Models\HistoricalImportFinalization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HistoricalImportReportController extends Controller
{
    public function index(Request $request): View
    {
        $this->ensureRoles(['administration', 'direction']);

        $search = trim((string) $request->string('q'));
        $selectedYearId = $request->integer('annee_scolaire_id') ?: null;
        $selectedClasseId = $request->integer('classe_id') ?: null;
        $selectedEleveId = $request->integer('eleve_id') ?: null;

        $historicalYears = AnneeScolaire::query()
            ->whereHas('inscriptions', fn ($query) => $query->where('statut', 'historique_importe'))
            ->orderBy('libelle')
            ->get();

        $classes = Classe::query()
            ->whereHas('inscriptions', function ($query) use ($selectedYearId) {
                $query->where('statut', 'historique_importe');

                if ($selectedYearId !== null) {
                    $query->where('annee_scolaire_id', $selectedYearId);
                }
            })
            ->orderBy('code')
            ->get();

        $eleves = Eleve::query()
            ->whereHas('inscriptions', function ($query) use ($selectedYearId, $selectedClasseId) {
                $query->where('statut', 'historique_importe');

                if ($selectedYearId !== null) {
                    $query->where('annee_scolaire_id', $selectedYearId);
                }

                if ($selectedClasseId !== null) {
                    $query->where('classe_id', $selectedClasseId);
                }
            })
            ->orderBy('nom')
            ->orderBy('prenoms')
            ->get();

        $finalizations = HistoricalImportFinalization::query()
            ->with(['batch'])
            ->when($selectedYearId !== null, fn ($query) => $query->where('annee_scolaire_id', $selectedYearId))
            ->when($selectedClasseId !== null, fn ($query) => $query->where('classe_id', $selectedClasseId))
            ->orderBy('academic_year_label')
            ->orderBy('class_code')
            ->get();

        $resultsQuery = DB::table('historical_import_result_mappings as mappings')
            ->join('historical_import_finalizations as finalizations', 'finalizations.id', '=', 'mappings.finalization_id')
            ->join('eleves as eleves', 'eleves.id', '=', 'mappings.eleve_id')
            ->join('classes as classes', 'classes.id', '=', 'mappings.classe_id')
            ->join('annees_scolaires as annees', 'annees.id', '=', 'mappings.annee_scolaire_id')
            ->join('trimestres as trimestres', 'trimestres.id', '=', 'mappings.trimestre_id')
            ->join('matieres as matieres', 'matieres.id', '=', 'mappings.matiere_id')
            ->join('resultats as resultats', 'resultats.id', '=', 'mappings.resultat_id')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('eleves.matricule', 'like', "%{$search}%")
                        ->orWhere('eleves.nom', 'like', "%{$search}%")
                        ->orWhere('eleves.prenoms', 'like', "%{$search}%")
                        ->orWhere('classes.code', 'like', "%{$search}%")
                        ->orWhere('matieres.libelle', 'like', "%{$search}%");
                });
            })
            ->when($selectedYearId !== null, fn ($query) => $query->where('mappings.annee_scolaire_id', $selectedYearId))
            ->when($selectedClasseId !== null, fn ($query) => $query->where('mappings.classe_id', $selectedClasseId))
            ->when($selectedEleveId !== null, fn ($query) => $query->where('mappings.eleve_id', $selectedEleveId))
            ->select([
                'finalizations.sheet_name',
                'finalizations.batch_id',
                'annees.libelle as annee_libelle',
                'classes.code as classe_code',
                'classes.nom as classe_nom',
                'trimestres.libelle as trimestre_libelle',
                'trimestres.ordre as trimestre_ordre',
                'eleves.id as eleve_id',
                'eleves.matricule',
                'eleves.nom',
                'eleves.prenoms',
                'matieres.libelle as matiere_libelle',
                'resultats.moyenne_devoirs',
                'resultats.composition',
                'resultats.moyenne_matiere',
                'resultats.points',
                'resultats.rang',
            ])
            ->orderBy('annees.libelle')
            ->orderBy('classes.code')
            ->orderBy('eleves.nom')
            ->orderBy('eleves.prenoms')
            ->orderBy('trimestres.ordre')
            ->orderBy('matieres.libelle');

        $results = $resultsQuery->paginate(120)->withQueryString();

        $selectedEleve = $selectedEleveId !== null
            ? $eleves->firstWhere('id', $selectedEleveId)
            : null;

        $stats = [
            'annees' => $finalizations->pluck('annee_scolaire_id')->filter()->unique()->count(),
            'classes' => $finalizations->pluck('classe_id')->filter()->unique()->count(),
            'eleves' => (clone $resultsQuery)->distinct('eleves.id')->count('eleves.id'),
            'resultats' => (clone $resultsQuery)->count(),
            'bulletins' => $finalizations->sum('imported_bulletin_count'),
        ];

        return view('bulletins.historiques', [
            'historicalYears' => $historicalYears,
            'classes' => $classes,
            'eleves' => $eleves,
            'finalizations' => $finalizations,
            'results' => $results,
            'search' => $search,
            'selectedYearId' => $selectedYearId,
            'selectedClasseId' => $selectedClasseId,
            'selectedEleveId' => $selectedEleveId,
            'selectedEleve' => $selectedEleve,
            'stats' => $stats,
        ]);
    }

    protected function ensureRoles(array|string $roles): void
    {
        abort_unless(auth()->user()?->hasAnyRole((array) $roles), 403);
    }
}
