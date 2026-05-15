<?php

declare(strict_types=1);

chdir(__DIR__.'/..');
require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$rows = App\Models\ClasseMatiere::query()
    ->with(['classe.filiere', 'matiere'])
    ->get()
    ->map(fn ($cm) => [
        'classe_code' => optional($cm->classe)->code,
        'classe_nom' => optional($cm->classe)->nom,
        'filiere_code' => optional(optional($cm->classe)->filiere)->code,
        'filiere_nom' => optional(optional($cm->classe)->filiere)->nom,
        'matiere_code' => optional($cm->matiere)->code,
        'matiere_libelle' => optional($cm->matiere)->libelle,
        'coefficient' => (float) $cm->coefficient,
        'actif' => (bool) $cm->actif,
    ])
    ->values()
    ->all();

$exportDir = getcwd().'/storage/app/reports';
if (! is_dir($exportDir)) {
    mkdir($exportDir, 0777, true);
}

$path = $exportDir.'/classe_matieres_export.json';
file_put_contents($path, json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo $path;
