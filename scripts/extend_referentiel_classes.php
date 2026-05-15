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

$filieresDefinition = [
    'GI' => ['nom' => 'Genie industriel', 'description' => 'Parcours oriente procedes, production et maintenance.'],
    'ELN' => ['nom' => 'Electronique', 'description' => 'Parcours oriente systemes electroniques et instrumentation.'],
    'ELT' => ['nom' => 'Electrotechnique', 'description' => 'Parcours oriente installations electriques et automatismes.'],
    'SIN' => ['nom' => 'Systeme d information et du numerique', 'description' => 'Parcours oriente informatique, reseaux et systemes numeriques.'],
    'GC' => ['nom' => 'Genie civil', 'description' => 'Parcours oriente batiments, topographie, metrage et chantiers.'],
    'GM' => ['nom' => 'Genie mecanique', 'description' => 'Parcours oriente mecanique, fabrication et maintenance.'],
    'RTC' => ['nom' => 'Reseaux et telecommunication', 'description' => 'Parcours oriente telecoms, systemes et infrastructures reseau.'],
];

$classesDefinition = [
    ['code' => 'STA', 'nom' => 'Tronc commun industriel', 'filiere' => 'GI'],
    ['code' => 'SSIN', 'nom' => 'Seconde SIN', 'filiere' => 'SIN'],
    ['code' => 'SF4', 'nom' => 'Seconde F4', 'filiere' => 'GC'],
    ['code' => 'PE', 'nom' => 'Premiere E', 'filiere' => 'GI'],
    ['code' => 'PF1', 'nom' => 'Premiere F1', 'filiere' => 'GM'],
    ['code' => 'PF2', 'nom' => 'Premiere F2', 'filiere' => 'ELN'],
    ['code' => 'PF3', 'nom' => 'Premiere F3', 'filiere' => 'ELT'],
    ['code' => 'PF4', 'nom' => 'Premiere F4', 'filiere' => 'GC'],
    ['code' => 'PH5', 'nom' => 'Premiere H5', 'filiere' => 'RTC'],
    ['code' => 'TF1', 'nom' => 'Terminale F1', 'filiere' => 'GM'],
    ['code' => 'TF2', 'nom' => 'Terminale F2', 'filiere' => 'ELN'],
    ['code' => 'TF3', 'nom' => 'Terminale F3', 'filiere' => 'ELT'],
    ['code' => 'TF4', 'nom' => 'Terminale F4', 'filiere' => 'GC'],
    ['code' => 'TE', 'nom' => 'Terminale E', 'filiere' => 'GI'],
    ['code' => 'TH5', 'nom' => 'Terminale H5', 'filiere' => 'RTC'],
];

$summary = [
    'annee_active' => [
        'id' => $anneeActive->id,
        'libelle' => $anneeActive->libelle,
    ],
    'filieres' => ['created' => [], 'updated' => [], 'unchanged' => []],
    'classes' => ['created' => [], 'updated' => [], 'unchanged' => []],
];

Illuminate\Support\Facades\DB::transaction(function () use ($anneeActive, $filieresDefinition, $classesDefinition, &$summary): void {
    $filieres = collect();

    foreach ($filieresDefinition as $code => $data) {
        $existing = App\Models\Filiere::query()->where('code', $code)->first();

        if (! $existing) {
            $created = App\Models\Filiere::query()->create([
                'code' => $code,
                'nom' => $data['nom'],
                'description' => $data['description'],
                'actif' => true,
            ]);
            $filieres->put($code, $created);
            $summary['filieres']['created'][] = ['code' => $code, 'nom' => $data['nom']];
            continue;
        }

        $next = [
            'nom' => $data['nom'],
            'description' => $data['description'],
            'actif' => true,
        ];
        $current = [
            'nom' => $existing->nom,
            'description' => $existing->description,
            'actif' => (bool) $existing->actif,
        ];

        if ($current === $next) {
            $summary['filieres']['unchanged'][] = ['code' => $code, 'nom' => $existing->nom];
        } else {
            $existing->update($next);
            $summary['filieres']['updated'][] = ['code' => $code, 'nom' => $data['nom']];
        }

        $filieres->put($code, $existing->fresh());
    }

    foreach ($classesDefinition as $data) {
        $filiere = $filieres->get($data['filiere']);
        $existing = App\Models\Classe::query()
            ->where('annee_scolaire_id', $anneeActive->id)
            ->where('code', $data['code'])
            ->first();

        $payload = [
            'nom' => $data['nom'],
            'filiere_id' => $filiere->id,
            'actif' => true,
        ];

        if (! $existing) {
            App\Models\Classe::query()->create([
                'code' => $data['code'],
                'annee_scolaire_id' => $anneeActive->id,
                ...$payload,
            ]);
            $summary['classes']['created'][] = [
                'code' => $data['code'],
                'nom' => $data['nom'],
                'filiere_code' => $data['filiere'],
            ];
            continue;
        }

        $current = [
            'nom' => $existing->nom,
            'filiere_id' => $existing->filiere_id,
            'actif' => (bool) $existing->actif,
        ];

        if ($current === $payload) {
            $summary['classes']['unchanged'][] = [
                'code' => $data['code'],
                'nom' => $existing->nom,
                'filiere_code' => $data['filiere'],
            ];
            continue;
        }

        $existing->update($payload);
        $summary['classes']['updated'][] = [
            'code' => $data['code'],
            'nom' => $data['nom'],
            'filiere_code' => $data['filiere'],
        ];
    }
});

$reportDir = getcwd().'/storage/app/reports';
if (! is_dir($reportDir)) {
    mkdir($reportDir, 0777, true);
}

$reportPath = $reportDir.'/extension_referentiel_classes_report.json';
file_put_contents($reportPath, json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
