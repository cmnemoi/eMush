<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Service\RemoveHealthFromPlayerServiceInterface;
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
        private RemoveHealthFromPlayerServiceInterface $removeHealthFromPlayer,
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
        $this->removeHealthFromPlayer->execute($this->healthToRemove(), player: $this->playerTarget());
        $this->actionHistoryRevealLog->generate(numberOfActions: $this->targetMissingHealthPoints(), action: $this);
    }

    private function targetMissingHealthPoints(): int
    {
        $maxHealth = $this->playerTarget()->getVariableByName(PlayerVariableEnum::HEALTH_POINT)->getMaxValueOrThrow();
        $currentHealth = $this->playerTarget()->getHealthPoint();

        return $maxHealth - $currentHealth;
    }

    private function healthToRemove(): int
    {
        return $this->getOutputQuantity();
    }
}
