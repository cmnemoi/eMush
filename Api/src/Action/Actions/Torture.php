<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\RoomLog\Service\ActionHistoryRevealLogService;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class Torture extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::TORTURE;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        private ActionHistoryRevealLogService $actionHistoryRevealLog,
    ) {
        parent::__construct($eventService, $actionService, $validator);
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
        $this->removeHealthToTarget();
        $this->actionHistoryRevealLog->generate(numberOfActions: $this->missingTargetHealthPoints(), action: $this);
    }

    private function removeHealthToTarget(): void
    {
        $playerVariableEvent = new PlayerVariableEvent(
            player: $this->playerTarget(),
            variableName: PlayerVariableEnum::HEALTH_POINT,
            quantity: -$this->getOutputQuantity(),
            tags: $this->getTags(),
            time: new \DateTime(),
        );
        $this->eventService->callEvent($playerVariableEvent, VariableEventInterface::CHANGE_VARIABLE);
    }

    private function missingTargetHealthPoints(): int
    {
        $maxHealth = $this->playerTarget()->getCharacterConfig()->getMaxHealthPoint();
        $currentHealth = $this->playerTarget()->getHealthPoint();

        return $maxHealth - $currentHealth;
    }
}
