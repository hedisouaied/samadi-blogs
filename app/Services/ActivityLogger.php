<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * Persists a lightweight audit trail of important administrative events.
 *
 * Only safe, human-readable metadata is stored. Never pass passwords,
 * tokens or full sensitive payloads in $properties.
 */
class ActivityLogger
{
    public function log(
        string $action,
        ?Model $subject = null,
        ?string $description = null,
        array $properties = [],
        ?int $userId = null,
    ): ActivityLog {
        return ActivityLog::query()->create([
            'user_id' => $userId ?? Auth::id(),
            'action' => $action,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'description' => $description ?? $this->defaultDescription($action, $subject),
            'properties' => $properties ?: null,
            'ip_address' => $this->ipAddress(),
        ]);
    }

    protected function defaultDescription(string $action, ?Model $subject): string
    {
        $label = $subject ? class_basename($subject).' #'.$subject->getKey() : 'system';

        return str($action)->replace('.', ' ')->title()->toString().' ('.$label.')';
    }

    protected function ipAddress(): ?string
    {
        try {
            return Request::ip();
        } catch (\Throwable) {
            return null;
        }
    }
}
