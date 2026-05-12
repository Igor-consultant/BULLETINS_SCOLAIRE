<?php

namespace Database\Seeders;

use App\Models\AnneeScolaire;
use App\Models\Eleve;
use App\Models\PaiementStatut;
use Illuminate\Database\Seeder;

class PaiementsStatutsSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $annee = AnneeScolaire::query()
            ->where('statut', 'active')
            ->latest('date_debut')
            ->first();

        if (! $annee) {
            return;
        }

        $statuts = [
            'I3P-2025-001' => [
                'statut' => 'a_jour',
                'montant_attendu' => 150000,
                'montant_paye' => 150000,
                'date_dernier_paiement' => '2025-11-05',
                'observation' => 'Situation reguliere.',
                'autorise_acces_bulletin' => true,
            ],
            'I3P-2025-002' => [
                'statut' => 'partiel',
                'montant_attendu' => 150000,
                'montant_paye' => 90000,
                'date_dernier_paiement' => '2025-10-28',
                'observation' => 'Paiement partiel en attente de regularisation.',
                'autorise_acces_bulletin' => false,
            ],
            'I3P-2025-003' => [
                'statut' => 'en_retard',
                'montant_attendu' => 150000,
                'montant_paye' => 60000,
                'date_dernier_paiement' => '2025-09-30',
                'observation' => 'Retard signale par la comptabilite.',
                'autorise_acces_bulletin' => false,
            ],
            'I3P-2025-004' => [
                'statut' => 'autorisation_exceptionnelle',
                'montant_attendu' => 150000,
                'montant_paye' => 70000,
                'date_dernier_paiement' => '2025-10-20',
                'observation' => 'Autorisation ponctuelle accordee par la direction.',
                'autorise_acces_bulletin' => true,
            ],
            'I3P-2025-005' => [
                'statut' => 'bloque',
                'montant_attendu' => 150000,
                'montant_paye' => 0,
                'date_dernier_paiement' => null,
                'observation' => 'Aucun reglement enregistre.',
                'autorise_acces_bulletin' => false,
            ],
            'I3P-2025-006' => [
                'statut' => 'a_jour',
                'montant_attendu' => 150000,
                'montant_paye' => 150000,
                'date_dernier_paiement' => '2025-11-02',
                'observation' => 'Paiement complet confirme.',
                'autorise_acces_bulletin' => true,
            ],
        ];

        $eleves = Eleve::query()
            ->whereIn('matricule', array_keys($statuts))
            ->get()
            ->keyBy('matricule');

        foreach ($statuts as $matricule => $payload) {
            $eleve = $eleves->get($matricule);

            if (! $eleve) {
                continue;
            }

            PaiementStatut::updateOrCreate(
                [
                    'eleve_id' => $eleve->id,
                    'annee_scolaire_id' => $annee->id,
                ],
                $payload
            );
        }
    }
}
