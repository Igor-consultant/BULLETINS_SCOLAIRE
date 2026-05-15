<?php

declare(strict_types=1);

chdir(__DIR__.'/..');
require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$anneeActive = App\Models\AnneeScolaire::query()
    ->where('statut', 'active')
    ->latest('date_debut')
    ->first();

if (! $anneeActive) {
    fwrite(STDERR, "Aucune annee scolaire active trouvee.\n");
    exit(1);
}

$filieres = App\Models\Filiere::query()
    ->whereIn('code', ['GI', 'ELN', 'ELT'])
    ->get()
    ->keyBy('code');

$targets = [
    'STA' => ['nom' => 'Tronc commun industriel', 'filiere_code' => 'GI'],
    'PF2' => ['nom' => 'Premiere F2', 'filiere_code' => 'ELN'],
    'PF3' => ['nom' => 'Premiere F3', 'filiere_code' => 'ELT'],
    'TF2' => ['nom' => 'Terminale F2', 'filiere_code' => 'ELN'],
    'TE' => ['nom' => 'Terminale E', 'filiere_code' => 'GI'],
];

$summary = [
    'annee_active' => [
        'id' => $anneeActive->id,
        'libelle' => $anneeActive->libelle,
    ],
    'updated' => [],
    'unchanged' => [],
    'missing' => [],
];

Illuminate\Support\Facades\DB::transaction(function () use ($anneeActive, $filieres, $targets, &$summary): void {
    foreach ($targets as $code => $target) {
        $classe = App\Models\Classe::query()
            ->where('annee_scolaire_id', $anneeActive->id)
            ->where('code', $code)
            ->first();

        if (! $classe) {
            $summary['missing'][] = [
                'code' => $code,
                'reason' => 'classe absente dans l annee active',
            ];
            continue;
        }

        $filiere = $filieres->get($target['filiere_code']);
        if (! $filiere) {
            $summary['missing'][] = [
                'code' => $code,
                'reason' => 'filiere cible absente',
                'filiere_code' => $target['filiere_code'],
            ];
            continue;
        }

        $before = [
            'nom' => $classe->nom,
            'filiere_id' => $classe->filiere_id,
        ];

        $after = [
            'nom' => $target['nom'],
            'filiere_id' => $filiere->id,
        ];

        if ($before === $after) {
            $summary['unchanged'][] = [
                'code' => $code,
                'nom' => $classe->nom,
                'filiere_code' => $filiere->code,
            ];
            continue;
        }

        $classe->update($after);

        $summary['updated'][] = [
            'code' => $code,
            'before' => [
                'nom' => $before['nom'],
                'filiere_code' => optional(App\Models\Filiere::find($before['filiere_id']))->code,
            ],
            'after' => [
                'nom' => $target['nom'],
                'filiere_code' => $filiere->code,
            ],
        ];
    }
});

$reportDir = getcwd().'/storage/app/reports';
if (! is_dir($reportDir)) {
    mkdir($reportDir, 0777, true);
}

$reportPath = $reportDir.'/alignement_classes_filieres_report.json';
file_put_contents($reportPath, json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
