<?php

namespace App\Http\Controllers;

use App\Models\Paiement;
use App\Models\PaiementStatut;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ComptabiliteController extends Controller
{
    public function index(): View
    {
        $this->ensureRoles(['administration', 'direction', 'comptabilite']);

        $statuts = PaiementStatut::query()
            ->with([
                'eleve',
                'anneeScolaire',
            ])
            ->orderByDesc('annee_scolaire_id')
            ->orderBy('eleve_id')
            ->get();

        return view('comptabilite.statuts', [
            'statuts' => $statuts,
            'stats' => [
                'lignes' => PaiementStatut::count(),
                'autorises' => PaiementStatut::where('autorise_acces_bulletin', true)->count(),
                'bloques' => PaiementStatut::where('autorise_acces_bulletin', false)->count(),
                'eleves_couverts' => PaiementStatut::distinct('eleve_id')->count('eleve_id'),
            ],
        ]);
    }

    public function edit(PaiementStatut $paiementStatut): View
    {
        $this->ensureRoles(['administration', 'direction', 'comptabilite']);

        return view('comptabilite.edit', [
            'paiementStatut' => $paiementStatut->load(['eleve', 'anneeScolaire']),
            'statutsDisponibles' => $this->statutsDisponibles(),
        ]);
    }

    public function update(Request $request, PaiementStatut $paiementStatut): RedirectResponse
    {
        $this->ensureRoles(['administration', 'direction', 'comptabilite']);

        $validated = $request->validate([
            'statut' => ['required', 'in:a_jour,partiel,en_retard,bloque,autorisation_exceptionnelle'],
            'montant_attendu' => ['nullable', 'numeric', 'min:0'],
            'montant_paye' => ['nullable', 'numeric', 'min:0'],
            'date_dernier_paiement' => ['nullable', 'date'],
            'observation' => ['nullable', 'string', 'max:1000'],
            'autorise_acces_bulletin' => ['nullable', 'boolean'],
        ]);

        $anciennesValeurs = [
            'statut' => $paiementStatut->statut,
            'montant_attendu' => $paiementStatut->montant_attendu,
            'montant_paye' => $paiementStatut->montant_paye,
            'date_dernier_paiement' => $paiementStatut->date_dernier_paiement?->format('Y-m-d'),
            'observation' => $paiementStatut->observation,
            'autorise_acces_bulletin' => (bool) $paiementStatut->autorise_acces_bulletin,
        ];

        $nouvellesValeurs = [
            'statut' => $validated['statut'],
            'montant_attendu' => $validated['montant_attendu'] ?? null,
            'montant_paye' => $validated['montant_paye'] ?? null,
            'date_dernier_paiement' => $validated['date_dernier_paiement'] ?? null,
            'observation' => $validated['observation'] ?? null,
            'autorise_acces_bulletin' => $request->boolean('autorise_acces_bulletin'),
        ];

        $paiementStatut->update($nouvellesValeurs);

        if ($anciennesValeurs !== $nouvellesValeurs) {
            $this->recordAudit(
                'paiement_statut_modifie',
                'paiement_statut',
                $paiementStatut->id,
                $anciennesValeurs,
                $nouvellesValeurs,
                "Mise a jour du statut comptable pour {$paiementStatut->eleve?->nom} {$paiementStatut->eleve?->prenoms}."
            );
        }

        return redirect()
            ->route('comptabilite.statuts')
            ->with('status', 'Statut financier mis a jour avec succes.');
    }

    public function payments(PaiementStatut $paiementStatut): View
    {
        $this->ensureRoles(['administration', 'direction', 'comptabilite']);

        return view('comptabilite.paiements', [
            'paiementStatut' => $paiementStatut->load([
                'eleve',
                'anneeScolaire',
                'paiements' => fn ($query) => $query->latest('date_paiement')->latest('id'),
            ]),
            'modesPaiement' => $this->modesPaiement(),
        ]);
    }

    public function storePayment(Request $request, PaiementStatut $paiementStatut): RedirectResponse
    {
        $this->ensureRoles(['administration', 'direction', 'comptabilite']);

        $validated = $request->validate([
            'date_paiement' => ['required', 'date'],
            'montant' => ['required', 'numeric', 'min:0.01'],
            'mode_paiement' => ['required', 'in:especes,virement,mobile_money,cheque'],
            'reference' => ['nullable', 'string', 'max:255'],
            'libelle' => ['nullable', 'string', 'max:255'],
            'observation' => ['nullable', 'string', 'max:1000'],
        ]);

        $paiement = $paiementStatut->paiements()->create($validated);

        $nouveauMontantPaye = $paiementStatut->paiements()->sum('montant');
        $anciennesValeursStatut = [
            'montant_paye' => $paiementStatut->montant_paye !== null ? (float) $paiementStatut->montant_paye : null,
            'date_dernier_paiement' => $paiementStatut->date_dernier_paiement?->format('Y-m-d'),
        ];

        $paiementStatut->update([
            'montant_paye' => $nouveauMontantPaye,
            'date_dernier_paiement' => $validated['date_paiement'],
        ]);

        $this->recordAudit(
            'paiement_cree',
            'paiement',
            $paiement->id,
            null,
            [
                'paiement_statut_id' => $paiement->paiement_statut_id,
                'date_paiement' => $paiement->date_paiement?->format('Y-m-d'),
                'montant' => (float) $paiement->montant,
                'mode_paiement' => $paiement->mode_paiement,
                'reference' => $paiement->reference,
                'libelle' => $paiement->libelle,
                'observation' => $paiement->observation,
            ],
            "Enregistrement d un paiement pour {$paiementStatut->eleve?->nom} {$paiementStatut->eleve?->prenoms}."
        );

        $this->recordAudit(
            'paiement_statut_maj_auto',
            'paiement_statut',
            $paiementStatut->id,
            $anciennesValeursStatut,
            [
                'montant_paye' => (float) $paiementStatut->montant_paye,
                'date_dernier_paiement' => $paiementStatut->date_dernier_paiement?->format('Y-m-d'),
            ],
            "Recalcul automatique du montant paye pour {$paiementStatut->eleve?->nom} {$paiementStatut->eleve?->prenoms}."
        );

        return redirect()
            ->route('comptabilite.paiements', $paiementStatut)
            ->with('status', 'Paiement enregistre avec succes.');
    }

    public function editPayment(PaiementStatut $paiementStatut, Paiement $paiement): View
    {
        $this->ensureRoles(['administration', 'direction', 'comptabilite']);
        abort_unless($paiement->paiement_statut_id === $paiementStatut->id, 404);

        return view('comptabilite.paiement-edit', [
            'paiementStatut' => $paiementStatut->load(['eleve', 'anneeScolaire']),
            'paiement' => $paiement,
            'modesPaiement' => $this->modesPaiement(),
        ]);
    }

    public function updatePayment(Request $request, PaiementStatut $paiementStatut, Paiement $paiement): RedirectResponse
    {
        $this->ensureRoles(['administration', 'direction', 'comptabilite']);
        abort_unless($paiement->paiement_statut_id === $paiementStatut->id, 404);

        $validated = $request->validate([
            'date_paiement' => ['required', 'date'],
            'montant' => ['required', 'numeric', 'min:0.01'],
            'mode_paiement' => ['required', 'in:especes,virement,mobile_money,cheque'],
            'reference' => ['nullable', 'string', 'max:255'],
            'libelle' => ['nullable', 'string', 'max:255'],
            'observation' => ['nullable', 'string', 'max:1000'],
        ]);

        $anciennesValeursPaiement = [
            'date_paiement' => $paiement->date_paiement?->format('Y-m-d'),
            'montant' => (float) $paiement->montant,
            'mode_paiement' => $paiement->mode_paiement,
            'reference' => $paiement->reference,
            'libelle' => $paiement->libelle,
            'observation' => $paiement->observation,
        ];

        $ancienStatut = [
            'montant_paye' => $paiementStatut->montant_paye !== null ? (float) $paiementStatut->montant_paye : null,
            'date_dernier_paiement' => $paiementStatut->date_dernier_paiement?->format('Y-m-d'),
        ];

        $paiement->update($validated);

        $nouveauMontantPaye = $paiementStatut->paiements()->sum('montant');
        $dernierPaiement = $paiementStatut->paiements()->latest('date_paiement')->latest('id')->first();

        $paiementStatut->update([
            'montant_paye' => $nouveauMontantPaye,
            'date_dernier_paiement' => $dernierPaiement?->date_paiement,
        ]);

        $nouvellesValeursPaiement = [
            'date_paiement' => $paiement->date_paiement?->format('Y-m-d'),
            'montant' => (float) $paiement->montant,
            'mode_paiement' => $paiement->mode_paiement,
            'reference' => $paiement->reference,
            'libelle' => $paiement->libelle,
            'observation' => $paiement->observation,
        ];

        if ($anciennesValeursPaiement !== $nouvellesValeursPaiement) {
            $this->recordAudit(
                'paiement_modifie',
                'paiement',
                $paiement->id,
                $anciennesValeursPaiement,
                $nouvellesValeursPaiement,
                "Modification d un paiement pour {$paiementStatut->eleve?->nom} {$paiementStatut->eleve?->prenoms}."
            );
        }

        $nouveauStatut = [
            'montant_paye' => (float) $paiementStatut->montant_paye,
            'date_dernier_paiement' => $paiementStatut->date_dernier_paiement?->format('Y-m-d'),
        ];

        if ($ancienStatut !== $nouveauStatut) {
            $this->recordAudit(
                'paiement_statut_maj_auto',
                'paiement_statut',
                $paiementStatut->id,
                $ancienStatut,
                $nouveauStatut,
                "Recalcul automatique du montant paye apres modification d ecriture pour {$paiementStatut->eleve?->nom} {$paiementStatut->eleve?->prenoms}."
            );
        }

        return redirect()
            ->route('comptabilite.paiements', $paiementStatut)
            ->with('status', 'Paiement modifie avec succes.');
    }

    public function destroyPayment(PaiementStatut $paiementStatut, Paiement $paiement): RedirectResponse
    {
        $this->ensureRoles(['administration', 'direction', 'comptabilite']);
        abort_unless($paiement->paiement_statut_id === $paiementStatut->id, 404);

        $anciennesValeursPaiement = [
            'date_paiement' => $paiement->date_paiement?->format('Y-m-d'),
            'montant' => (float) $paiement->montant,
            'mode_paiement' => $paiement->mode_paiement,
            'reference' => $paiement->reference,
            'libelle' => $paiement->libelle,
            'observation' => $paiement->observation,
        ];

        $ancienStatut = [
            'montant_paye' => $paiementStatut->montant_paye !== null ? (float) $paiementStatut->montant_paye : null,
            'date_dernier_paiement' => $paiementStatut->date_dernier_paiement?->format('Y-m-d'),
        ];

        $paiement->delete();

        $nouveauMontantPaye = $paiementStatut->paiements()->sum('montant');
        $dernierPaiement = $paiementStatut->paiements()->latest('date_paiement')->latest('id')->first();

        $paiementStatut->update([
            'montant_paye' => $nouveauMontantPaye > 0 ? $nouveauMontantPaye : null,
            'date_dernier_paiement' => $dernierPaiement?->date_paiement,
        ]);

        $this->recordAudit(
            'paiement_supprime',
            'paiement',
            null,
            $anciennesValeursPaiement,
            null,
            "Suppression d un paiement pour {$paiementStatut->eleve?->nom} {$paiementStatut->eleve?->prenoms}."
        );

        $nouveauStatut = [
            'montant_paye' => $paiementStatut->montant_paye !== null ? (float) $paiementStatut->montant_paye : null,
            'date_dernier_paiement' => $paiementStatut->date_dernier_paiement?->format('Y-m-d'),
        ];

        if ($ancienStatut !== $nouveauStatut) {
            $this->recordAudit(
                'paiement_statut_maj_auto',
                'paiement_statut',
                $paiementStatut->id,
                $ancienStatut,
                $nouveauStatut,
                "Recalcul automatique du montant paye apres suppression d ecriture pour {$paiementStatut->eleve?->nom} {$paiementStatut->eleve?->prenoms}."
            );
        }

        return redirect()
            ->route('comptabilite.paiements', $paiementStatut)
            ->with('status', 'Paiement supprime avec succes.');
    }

    private function statutsDisponibles(): array
    {
        return [
            'a_jour' => 'A jour',
            'partiel' => 'Partiel',
            'en_retard' => 'En retard',
            'bloque' => 'Bloque',
            'autorisation_exceptionnelle' => 'Autorisation exceptionnelle',
        ];
    }

    private function modesPaiement(): array
    {
        return [
            'especes' => 'Especes',
            'virement' => 'Virement',
            'mobile_money' => 'Mobile money',
            'cheque' => 'Cheque',
        ];
    }
}
