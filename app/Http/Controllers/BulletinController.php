<?php

namespace App\Http\Controllers;

use App\Models\Classe;
use App\Models\Eleve;
use App\Models\Resultat;
use App\Models\Trimestre;
use App\Services\BulletinWorkflowService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use ZipArchive;

class BulletinController extends Controller
{
    public function __construct(
        private readonly BulletinWorkflowService $workflow,
    ) {
    }

    public function lots(): View
    {
        $this->ensureRoles(['administration', 'direction']);

        $classes = Classe::query()
            ->with('filiere')
            ->whereHas('resultats')
            ->orderBy('code')
            ->get();

        $trimestres = Trimestre::query()
            ->whereHas('resultats')
            ->with('anneeScolaire')
            ->orderBy('annee_scolaire_id')
            ->orderBy('ordre')
            ->get();

        return view('bulletins.lots', [
            'classes' => $classes,
            'trimestres' => $trimestres,
        ]);
    }

    public function generateLot(Request $request): Response|RedirectResponse
    {
        $this->ensureRoles(['administration', 'direction']);

        $validated = $request->validate([
            'classe_id' => ['required', 'integer', 'exists:classes,id'],
            'trimestre_id' => ['required', 'integer', 'exists:trimestres,id'],
        ]);

        $classe = Classe::query()
            ->with('filiere')
            ->findOrFail($validated['classe_id']);

        $trimestre = Trimestre::query()
            ->with('anneeScolaire')
            ->findOrFail($validated['trimestre_id']);

        $resultats = Resultat::query()
            ->where('classe_id', $classe->id)
            ->where('trimestre_id', $trimestre->id)
            ->orderBy('eleve_id')
            ->get();

        if ($resultats->isEmpty()) {
            return redirect()
                ->route('bulletins.lots')
                ->with('status', 'Aucun bulletin disponible pour cette classe et ce trimestre.');
        }

        $eleves = Eleve::query()
            ->whereIn('id', $resultats->pluck('eleve_id')->unique())
            ->orderBy('nom')
            ->orderBy('prenoms')
            ->get();

        $batchKey = 'classe-'.$classe->code.'-trimestre-'.$trimestre->ordre.'-'.now()->format('Ymd_His');
        $batchDir = storage_path('app/bulletins-lots/'.$batchKey);
        $safeClasse = preg_replace('/[^A-Za-z0-9_-]/', '-', $classe->code.'-'.$classe->nom);
        $safeAnnee = preg_replace('/[^A-Za-z0-9_-]/', '-', (string) ($trimestre->anneeScolaire?->libelle ?? 'annee'));

        if (! is_dir($batchDir)) {
            mkdir($batchDir, 0777, true);
        }

        $generatedFiles = [];
        $blockedStudents = [];

        foreach ($eleves as $eleve) {
            $paiementStatut = $this->workflow->findPaiementStatutForTrimestre($eleve, $trimestre);

            if (! $this->workflow->bulletinAccessAllowed($eleve, $trimestre)) {
                $blockedStudents[] = $eleve->matricule.' - '.$eleve->nom.' '.$eleve->prenoms;

                continue;
            }

            $data = $this->workflow->buildBulletinData($eleve, $trimestre);

            if (($data['classe']?->id ?? null) !== $classe->id) {
                continue;
            }

            $pdf = Pdf::loadView('bulletins.pdf', $data)->setPaper('a4');
            $safeEleve = preg_replace('/[^A-Za-z0-9_-]/', '-', $eleve->nom.'-'.$eleve->prenoms);
            $pdfFilename = 'bulletin-'.$classe->code.'-'.$eleve->matricule.'-'.$safeEleve.'.pdf';
            $pdfPath = $batchDir.'/'.$pdfFilename;
            file_put_contents($pdfPath, $pdf->output());
            $generatedFiles[] = $pdfFilename;
        }

        if ($generatedFiles === []) {
            if (is_dir($batchDir)) {
                @rmdir($batchDir);
            }

            return redirect()
                ->route('bulletins.lots')
                ->withInput()
                ->with('status', 'Aucun bulletin autorise n est disponible pour cette classe et ce trimestre.')
                ->with('status_type', 'error');
        }

        $manifestLines = [
            'LOT DE BULLETINS I3P',
            'Classe : '.$classe->code.' - '.$classe->nom,
            'Filiere : '.($classe->filiere?->nom ?? 'Non definie'),
            'Annee scolaire : '.($trimestre->anneeScolaire?->libelle ?? 'Non definie'),
            'Trimestre : '.$trimestre->libelle,
            'Date generation : '.now()->format('d/m/Y H:i:s'),
            'Nombre de bulletins : '.count($generatedFiles),
            'Eleves bloques exclus : '.count($blockedStudents),
            '',
            'FICHIERS :',
        ];

        foreach ($generatedFiles as $fileName) {
            $manifestLines[] = '- '.$fileName;
        }

        if ($blockedStudents !== []) {
            $manifestLines[] = '';
            $manifestLines[] = 'ELEVES EXCLUS POUR BLOCAGE COMPTABLE :';

            foreach ($blockedStudents as $studentLabel) {
                $manifestLines[] = '- '.$studentLabel;
            }
        }

        file_put_contents($batchDir.'/README_LOT.txt', implode(PHP_EOL, $manifestLines));

        $zipFilename = 'lot-bulletins-'.$safeClasse.'-'.$safeAnnee.'-trimestre-'.$trimestre->ordre.'.zip';
        $zipPath = storage_path('app/bulletins-lots/'.$zipFilename);
        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach (glob($batchDir.'/*') ?: [] as $file) {
            $zip->addFile($file, basename($file));
        }

        $zip->close();

        foreach (glob($batchDir.'/*') ?: [] as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        if (is_dir($batchDir)) {
            rmdir($batchDir);
        }

        $lotsRoot = storage_path('app/bulletins-lots');
        foreach (glob($lotsRoot.'/classe-*') ?: [] as $oldDir) {
            if (! is_dir($oldDir)) {
                continue;
            }

            foreach (glob($oldDir.'/*') ?: [] as $oldFile) {
                if (is_file($oldFile)) {
                    unlink($oldFile);
                }
            }

            @rmdir($oldDir);
        }

        return response()
            ->download($zipPath)
            ->deleteFileAfterSend(true)
            ->header('X-I3P-Generated-Bulletins', (string) count($generatedFiles))
            ->header('X-I3P-Blocked-Students', (string) count($blockedStudents));
    }

    public function show(Eleve $eleve, Trimestre $trimestre): View|RedirectResponse
    {
        $this->ensureRoles(['administration', 'direction']);

        if ($response = $this->ensureBulletinAccess($eleve, $trimestre)) {
            return $response;
        }

        return view('bulletins.show', $this->workflow->buildBulletinData($eleve, $trimestre));
    }

    public function pdf(Eleve $eleve, Trimestre $trimestre): Response|RedirectResponse
    {
        $this->ensureRoles(['administration', 'direction']);

        if ($response = $this->ensureBulletinAccess($eleve, $trimestre)) {
            return $response;
        }

        $data = $this->workflow->buildBulletinData($eleve, $trimestre);

        $pdf = Pdf::loadView('bulletins.pdf', $data)
            ->setPaper('a4');

        $filename = 'bulletin-'.$eleve->matricule.'-trimestre-'.$trimestre->ordre.'.pdf';

        return $pdf->stream($filename);
    }

    protected function ensureRoles(array|string $roles): void
    {
        abort_unless(auth()->user()?->hasAnyRole((array) $roles), 403);
    }

    private function ensureBulletinAccess(Eleve $eleve, Trimestre $trimestre): ?RedirectResponse
    {
        if (! $this->workflow->bulletinAccessAllowed($eleve, $trimestre)) {
            return redirect()
                ->route('resultats.trimestriels')
                ->with('status', "Acces au bulletin bloque pour {$eleve->nom} {$eleve->prenoms} en raison du statut financier.")
                ->with('status_type', 'error');
        }

        return null;
    }
}
