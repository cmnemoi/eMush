<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerModifierEvent;
use Mush\RoomLog\Entity\LogParameterInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class Comfort extends AbstractAction
{
    public const BASE_CONFORT = 2;

    protected string $name = ActionEnum::COMFORT;

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter instanceof Player;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        //@TODO add validator on shrink skill ?
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
    }

    protected function applyEffects(): ActionResult
    {
        /** @var Player $parameter */
        $parameter = $this->parameter;

        $playerModifierEvent = new PlayerModifierEvent(
            $parameter,
            self::BASE_CONFORT,
            $this->getActionName(),
            new \DateTime(),
        );
        $this->eventDispatcher->dispatch($playerModifierEvent, PlayerModifierEvent::MORAL_POINT_MODIFIER);

        return new Success();
    }
}
