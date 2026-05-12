<?php

namespace App\Services;

use App\Models\Audit;

class AuditTrailService
{
    public function record(
        string $action,
        string $auditableType,
        ?int $auditableId,
        ?array $anciennesValeurs,
        ?array $nouvellesValeurs,
        ?string $description = null
    ): void {
        Audit::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'auditable_type' => $auditableType,
            'auditable_id' => $auditableId,
            'anciennes_valeurs' => $anciennesValeurs,
            'nouvelles_valeurs' => $nouvellesValeurs,
            'description' => $description,
        ]);
    }
}
