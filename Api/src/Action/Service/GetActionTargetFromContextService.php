<?php

declare(strict_types=1);

namespace Mush\Action\Service;

use Mush\Action\Entity\ActionHolderInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;

final class GetActionTargetFromContextService
{
    public const array CONTEXT_KEY_CLASS_NAME_MAP = [
        'terminal' => GameEquipment::class,
        'terminalItem' => GameItem::class,
    ];

    public function execute(array $context): ?ActionHolderInterface
    {
        $actionTarget = null;
        foreach ($context as $key => $value) {
            if (
                $value instanceof ActionHolderInterface
                && (
                    (\array_key_exists($key, self::CONTEXT_KEY_CLASS_NAME_MAP)
                    && $value->getClassName() === self::CONTEXT_KEY_CLASS_NAME_MAP[$key])
                    || $value->getClassName() === $key
                )
            ) {
                $actionTarget = $value;
            }
        }

        return $actionTarget;
    }
}
