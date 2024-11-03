<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\RoomLog\Entity\LogParameterInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

final class Depress extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::DEPRESS;

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof Player;
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $this->removeMoralePointsToTarget();
    }

    private function removeMoralePointsToTarget(): void
    {
        $playerVariableEvent = new PlayerVariableEvent(
            player: $this->target(),
            variableName: PlayerVariableEnum::MORAL_POINT,
            quantity: -$this->getOutputQuantity(),
            tags: $this->getTags(),
            time: new \DateTime(),
        );
        $playerVariableEvent->setAuthor($this->player);
        $this->eventService->callEvent($playerVariableEvent, VariableEventInterface::CHANGE_VARIABLE);
    }

    private function target(): Player
    {
        return $this->target instanceof Player ? $this->target : throw new \RuntimeException('Depress action target must be a player');
    }
}
