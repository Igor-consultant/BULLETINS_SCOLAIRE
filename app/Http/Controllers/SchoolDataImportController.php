<?php

namespace App\Http\Controllers;

use App\Models\AnneeScolaire;
use App\Services\CurrentYearSchoolDataImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SchoolDataImportController extends Controller
{
    public function __construct(
        private readonly CurrentYearSchoolDataImportService $importer,
    ) {
    }

    public function create(): View
    {
        $this->ensureRoles('administration');

        return view('administration.import-scolarite', [
            'anneeActive' => AnneeScolaire::query()
                ->where('statut', 'active')
                ->latest('date_debut')
                ->first(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureRoles('administration');

        $validated = $request->validate([
            'source_type' => ['required', 'in:json_pack,xlsx_bulletins'],
            'dataset' => ['required', 'file', 'mimes:json,xlsx'],
        ]);

        $anneeActive = AnneeScolaire::query()
            ->where('statut', 'active')
            ->latest('date_debut')
            ->first();

        abort_unless($anneeActive !== null, 422, "Aucune annee scolaire active n'est configuree.");

        $summary = $validated['source_type'] === 'json_pack'
            ? $this->importer->importJsonPack($request->file('dataset'), $anneeActive)
            : $this->importer->importWorkbook($request->file('dataset'), $anneeActive);

        return redirect()
            ->route('administration.import-scolarite.create')
            ->with('status', 'Import termine avec succes.')
            ->with('import_summary', $summary);
    }
}
