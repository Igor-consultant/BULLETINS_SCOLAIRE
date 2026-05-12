<?php

namespace App\Http\Controllers;

use App\Services\AuditTrailService;

abstract class Controller
{
    protected function ensureRoles(array|string $roles): void
    {
        abort_unless(auth()->user()?->hasAnyRole((array) $roles), 403);
    }

    protected function recordAudit(
        string $action,
        string $auditableType,
        ?int $auditableId,
        ?array $anciennesValeurs,
        ?array $nouvellesValeurs,
        ?string $description = null
    ): void {
        app(AuditTrailService::class)->record(
            $action,
            $auditableType,
            $auditableId,
            $anciennesValeurs,
            $nouvellesValeurs,
            $description
        );
    }
}
