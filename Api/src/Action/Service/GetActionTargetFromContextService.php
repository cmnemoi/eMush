<?php

declare(strict_types=1);

namespace Mush\Action\Service;

use Mush\Action\Entity\ActionTargetInterface;

final class GetActionTargetFromContextService
{
    public function execute(array $context): ?ActionTargetInterface
    {
        $actionTarget = null;
        foreach ($context as $key => $value) {
            if ($value instanceof ActionTargetInterface && $value->getActionTargetName($context) === $key) {
                $actionTarget = $value;
            }
        }

        return $actionTarget;
    }
}
