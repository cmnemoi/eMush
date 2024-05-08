<?php

declare(strict_types=1);

namespace Mush\Action\Service;

use Mush\Action\Entity\ActionHolderInterface;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Exploration\Entity\Planet;
use Mush\Hunter\Entity\Hunter;
use Mush\Player\Entity\Player;
use Mush\Project\Entity\Project;

final class GetActionTargetFromContextService
{
    public const array CONTEXT_KEY_CLASS_NAME_MAP = [
        'player' => Player::class,
        'door' => Door::class,
        'item' => GameItem::class,
        'equipment' => GameEquipment::class,
        'planet' => Planet::class,
        'project' => Project::class,
        'terminal' => GameEquipment::class,
        'hunter' => Hunter::class
    ];

    public function execute(array $context): ?ActionHolderInterface
    {
        $actionTarget = null;
        foreach ($context as $key => $value) {
            if (
                \array_key_exists($key, self::CONTEXT_KEY_CLASS_NAME_MAP)
                && $value instanceof ActionHolderInterface
                && $value->getClassName() === self::CONTEXT_KEY_CLASS_NAME_MAP[$key]
            ) {
                $actionTarget = $value;
            }
        }

        return $actionTarget;
    }
}
