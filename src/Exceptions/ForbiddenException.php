<?php

declare(strict_types=1);

namespace HubSpot\Exceptions;

/**
 * A 403, usually a token missing a scope (category MISSING_SCOPES).
 */
final class ForbiddenException extends ApiException
{
    /**
     * The scopes HubSpot says the call needs, so the app can send the portal
     * back through OAuth for exactly those. HubSpot reports them under
     * `missingScopes` (the spec) or `requiredGranularScopes` (per error entry,
     * meaning any one of them is enough).
     *
     * @return list<string>
     */
    public function missingScopes(): array
    {
        $contexts = [$this->error->context ?? []];
        foreach ($this->error->errors ?? [] as $detail) {
            $contexts[] = $detail->context;
        }

        $scopes = [];
        foreach ($contexts as $context) {
            foreach (['missingScopes', 'requiredGranularScopes'] as $key) {
                foreach (is_array($context[$key] ?? null) ? $context[$key] : [] as $scope) {
                    if (is_string($scope)) {
                        $scopes[$scope] = true;
                    }
                }
            }
        }

        return array_keys($scopes);
    }
}
