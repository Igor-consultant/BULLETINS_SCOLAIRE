<?php

declare(strict_types=1);

$projectRoot = 'C:/xampp/htdocs/BULLETINS_SCOLAIRE';
$preferredPath = 'C:/Users/igors/OneDrive/Documents/New project 6/outputs/missing_classes_crosswalk_refined.json';
$fallbackPath = 'C:/Users/igors/OneDrive/Documents/New project 6/outputs/missing_classes_crosswalk.json';
$inputPath = file_exists($preferredPath) ? $preferredPath : $fallbackPath;
$reportPath = $projectRoot.'/storage/app/reports/injection_nouvelles_classes_matieres_report.json';

if (! file_exists($inputPath)) {
    fwrite(STDERR, "Fichier introuvable: {$inputPath}\n");
    exit(1);
}

chdir($projectRoot);
require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$payload = json_decode(file_get_contents($inputPath), true, 512, JSON_THROW_ON_ERROR);
$rows = $payload['rows'] ?? [];

$anneeActive = App\Models\AnneeScolaire::query()
    ->where('statut', 'active')
    ->latest('date_debut')
    ->first();

if (! $anneeActive) {
    fwrite(STDERR, "Aucune annee scolaire active.\n");
    exit(1);
}

$classes = App\Models\Classe::query()
    ->where('annee_scolaire_id', $anneeActive->id)
    ->get()
    ->keyBy('code');

$summary = [
    'annee_active' => ['id' => $anneeActive->id, 'libelle' => $anneeActive->libelle],
    'rows_requested' => count($rows),
    'matieres_created' => 0,
    'affectations_created' => 0,
    'affectations_updated' => 0,
    'affectations_unchanged' => 0,
    'skipped' => [],
    'per_class' => [],
];

Illuminate\Support\Facades\DB::transaction(function () use ($rows, $classes, &$summary): void {
    foreach ($rows as $row) {
        $classCode = (string) ($row['classe_code'] ?? '');
        $classe = $classes->get($classCode);

        if (! $classe) {
            $summary['skipped'][] = ['reason' => "classe absente: {$classCode}", 'row' => $row];
            continue;
        }

        $matiere = null;
        $existingCode = (string) ($row['matiere_app_code'] ?? '');
        if ($existingCode !== '') {
            $matiere = App\Models\Matiere::query()->where('code', $existingCode)->first();
        }

        if (! $matiere) {
            $targetCode = (string) ($row['code_matiere_cible'] ?? '');
            $targetLibelle = (string) ($row['libelle_matiere_cible'] ?? '');

            if ($targetCode === '' || $targetLibelle === '') {
                $summary['skipped'][] = ['reason' => 'matiere cible incomplete', 'row' => $row];
                continue;
            }

            $matiere = App\Models\Matiere::query()->where('code', $targetCode)->first();
            if (! $matiere) {
                $matiere = App\Models\Matiere::query()->create([
                    'code' => $targetCode,
                    'libelle' => $targetLibelle,
                    'actif' => true,
                ]);
                $summary['matieres_created']++;
            }
        }

        $coefficient = (float) ($row['coefficient_inferé'] ?? 1);
        $affectation = App\Models\ClasseMatiere::query()
            ->where('classe_id', $classe->id)
            ->where('matiere_id', $matiere->id)
            ->first();

        if (! isset($summary['per_class'][$classCode])) {
            $summary['per_class'][$classCode] = [
                'created' => 0,
                'updated' => 0,
                'unchanged' => 0,
            ];
        }

        if (! $affectation) {
            App\Models\ClasseMatiere::query()->create([
                'classe_id' => $classe->id,
                'matiere_id' => $matiere->id,
                'coefficient' => $coefficient,
                'enseignant_nom' => null,
                'actif' => true,
            ]);
            $summary['affectations_created']++;
            $summary['per_class'][$classCode]['created']++;
            continue;
        }

        $before = [(float) $affectation->coefficient, (bool) $affectation->actif];
        $after = [$coefficient, true];

        if ($before === $after) {
            $summary['affectations_unchanged']++;
            $summary['per_class'][$classCode]['unchanged']++;
            continue;
        }

        $affectation->update([
            'coefficient' => $coefficient,
            'actif' => true,
        ]);
        $summary['affectations_updated']++;
        $summary['per_class'][$classCode]['updated']++;
    }
});

if (! is_dir(dirname($reportPath))) {
    mkdir(dirname($reportPath), 0777, true);
}
file_put_contents($reportPath, json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
