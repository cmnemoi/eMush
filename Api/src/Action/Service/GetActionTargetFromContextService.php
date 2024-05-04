<?php

declare(strict_types=1);

namespace Mush\Action\Service;

use Mush\Action\Entity\ActionHolderInterface;

final class GetActionTargetFromContextService
{
    public function execute(array $context): ?ActionHolderInterface
    {
        $actionTarget = null;
        foreach ($context as $key => $value) {
            if ($value instanceof ActionHolderInterface && $value->getClassName() === $key) {
                $actionTarget = $value;
            }
        }

        return $actionTarget;
    }
}
