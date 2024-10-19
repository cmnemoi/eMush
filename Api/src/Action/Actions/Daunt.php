<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\RemoveActionPointsFromPlayerServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class Daunt extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::DAUNT;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        private RemoveActionPointsFromPlayerServiceInterface $removeActionPointsFromPlayer,
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
        $this->removeActionPointsFromPlayer->execute(
            quantity: $this->actionPointsMalus(),
            player: $this->playerTarget()
        );
    }

    private function actionPointsMalus(): int
    {
        return $this->getOutputQuantity();
    }
}
