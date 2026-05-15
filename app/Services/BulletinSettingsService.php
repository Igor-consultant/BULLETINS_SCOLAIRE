<?php

namespace App\Services;

use App\Models\BulletinSetting;

class BulletinSettingsService
{
    public function defaults(): array
    {
        return [
            'header' => [
                'institution_name' => 'INSTITUT POLYTECHNIQUE PIERRE PRIE',
                'contact_line' => 'B.P. 4215 Tel 05 530 27 09 / 06 622 22 89',
                'email_line' => 'E-mail : i3ppointenoire@gmail.com. Pointe-Noire',
                'republic_title' => 'REPUBLIQUE DU CONGO',
                'republic_subtitle' => 'Unite - Travail - Progres',
            ],
            'calculation' => [
                'section_title' => 'Calculs par matiere',
                'section_description' => 'Regles de calcul appliquees pour la note de classe, la composition, la moyenne par matiere et les points.',
                'devoir_weight' => 1.0,
                'composition_weight' => 1.0,
            ],
            'trimestrial' => [
                'section_title' => 'Calculs trimestriels',
                'section_description' => 'Regles appliquees pour le total des points, la moyenne generale et le rang trimestriel.',
                'tie_break_strategy' => 'alphabetique',
            ],
            'short_appreciations' => [
                'section_title' => 'Regles d appreciation par matiere',
                'section_description' => 'Seuils utilises pour afficher les appreciations courtes par matiere.',
                'rules' => [
                    ['min' => null, 'max' => 2, 'label' => 'Nul'],
                    ['min' => 2, 'max' => 5, 'label' => 'Mauvais'],
                    ['min' => 5, 'max' => 8, 'label' => 'Faible'],
                    ['min' => 8, 'max' => 10, 'label' => 'Insuffisant'],
                    ['min' => 10, 'max' => 11, 'label' => 'Moyen'],
                    ['min' => 11, 'max' => 12, 'label' => 'Passable'],
                    ['min' => 12, 'max' => 14, 'label' => 'Assez bien'],
                    ['min' => 14, 'max' => 16, 'label' => 'Bien'],
                    ['min' => 16, 'max' => 18, 'label' => 'Tres bien'],
                    ['min' => 18, 'max' => null, 'label' => 'Excellent'],
                ],
            ],
            'general_appreciations' => [
                'section_title' => 'Regles d appreciation generale',
                'section_description' => 'Seuils utilises pour afficher les appreciations generales du bulletin.',
                'rules' => [
                    ['min' => null, 'max' => 2, 'label' => 'Travail nul. Un reveil immediat est indispensable.'],
                    ['min' => 2, 'max' => 5, 'label' => 'Mauvais travail. Reveille-toi et reprends les bases.'],
                    ['min' => 5, 'max' => 8, 'label' => 'Travail faible. Encore beaucoup d efforts a fournir.'],
                    ['min' => 8, 'max' => 10, 'label' => 'Travail insuffisant. Encore des efforts et tu reussiras.'],
                    ['min' => 10, 'max' => 11, 'label' => 'Travail moyen. Peut encore mieux faire.'],
                    ['min' => 11, 'max' => 12, 'label' => 'Travail acceptable. Peut encore mieux faire.'],
                    ['min' => 12, 'max' => 14, 'label' => 'Assez bon travail. Il faut maintenir cet elan.'],
                    ['min' => 14, 'max' => 16, 'label' => 'Bon travail.'],
                    ['min' => 16, 'max' => 18, 'label' => 'Tres bon travail.'],
                    ['min' => 18, 'max' => null, 'label' => 'Excellent travail. Felicitations.'],
                ],
            ],
            'sanctions' => [
                'section_title' => 'Regles de sanction',
                'section_description' => 'Seuils utilises pour la sanction du bulletin.',
                'rules' => [
                    ['min' => null, 'max' => 7, 'label' => 'Echoue'],
                    ['min' => 7, 'max' => 9, 'label' => 'Ajourne'],
                    ['min' => 9, 'max' => 10, 'label' => 'Rachete'],
                    ['min' => 10, 'max' => null, 'label' => 'Admis'],
                ],
            ],
            'application' => [
                'section_title' => 'Regles d exploitation dans l application',
                'section_description' => 'Options appliquees pour l acces et la diffusion des bulletins.',
                'payment_gate_enabled' => true,
            ],
        ];
    }

    public function all(): array
    {
        $configured = BulletinSetting::query()
            ->get()
            ->pluck('value', 'key')
            ->toArray();

        return array_replace_recursive($this->defaults(), $configured);
    }

    public function header(): array
    {
        return $this->all()['header'];
    }

    public function calculation(): array
    {
        return $this->all()['calculation'];
    }

    public function trimestrial(): array
    {
        return $this->all()['trimestrial'];
    }

    public function shortAppreciationRules(): array
    {
        return $this->all()['short_appreciations']['rules'] ?? [];
    }

    public function generalAppreciationRules(): array
    {
        return $this->all()['general_appreciations']['rules'] ?? [];
    }

    public function sanctionRules(): array
    {
        return $this->all()['sanctions']['rules'] ?? [];
    }

    public function application(): array
    {
        return $this->all()['application'];
    }

    public function paymentGateEnabled(): bool
    {
        return (bool) ($this->application()['payment_gate_enabled'] ?? true);
    }

    public function moyenneMatiere(?float $moyenneDevoirs, ?float $composition): ?float
    {
        if ($moyenneDevoirs === null || $composition === null) {
            return null;
        }

        $config = $this->calculation();
        $devoirWeight = max(0.0, (float) ($config['devoir_weight'] ?? 1));
        $compositionWeight = max(0.0, (float) ($config['composition_weight'] ?? 1));
        $totalWeight = $devoirWeight + $compositionWeight;

        if ($totalWeight <= 0) {
            return null;
        }

        return (($moyenneDevoirs * $devoirWeight) + ($composition * $compositionWeight)) / $totalWeight;
    }

    public function shortAppreciation(?float $moyenne): ?string
    {
        if ($moyenne === null) {
            return null;
        }

        return $this->resolveRuleLabel($moyenne, $this->shortAppreciationRules());
    }

    public function generalAppreciation(?float $moyenne): string
    {
        if ($moyenne === null) {
            return 'Bulletin incomplet pour le moment.';
        }

        return $this->resolveRuleLabel($moyenne, $this->generalAppreciationRules()) ?? 'Bulletin incomplet pour le moment.';
    }

    public function sanction(?float $moyenne): string
    {
        if ($moyenne === null) {
            return 'Bulletin incomplet';
        }

        return $this->resolveRuleLabel($moyenne, $this->sanctionRules()) ?? 'Bulletin incomplet';
    }

    public function update(array $payload): void
    {
        foreach ($payload as $key => $value) {
            BulletinSetting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
    }

    private function resolveRuleLabel(float $moyenne, array $rules): ?string
    {
        foreach ($rules as $rule) {
            $min = array_key_exists('min', $rule) ? $rule['min'] : null;
            $max = array_key_exists('max', $rule) ? $rule['max'] : null;
            $matchesMin = $min === null || $moyenne >= (float) $min;
            $matchesMax = $max === null || $moyenne < (float) $max;

            if ($matchesMin && $matchesMax) {
                return (string) ($rule['label'] ?? '');
            }
        }

        return null;
    }
}
