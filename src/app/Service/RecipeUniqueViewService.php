<?php

declare(strict_types=1);

namespace App\Service;

use App\Models\Recipe;

class RecipeUniqueViewService
{
    public function record(
        Recipe $recipe,
        ?string $userId,
        ?string $visitorId,
        ?string $ipAddress,
        ?string $userAgent,
    ): void {
        $visitorKey = $userId !== null
            ? 'user:'.$userId
            : 'guest:'.($visitorId ?? $ipAddress.':'.$userAgent);

        $recipe->uniqueViews()->firstOrCreate([
            'visitor_key' => hash('sha256', $visitorKey),
        ]);
    }
}
