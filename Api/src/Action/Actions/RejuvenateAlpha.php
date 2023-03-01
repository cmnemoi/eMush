<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Player\Service\PlayerVariableServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RejuvenateAlpha extends AbstractAction
{
    protected string $name = ActionEnum::REJUVENATE;

    private PlayerServiceInterface $playerService;
    private PlayerVariableServiceInterface $playerVariableService;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        PlayerServiceInterface $playerService,
        PlayerVariableServiceInterface $playerVariableService
    ) {
        parent::__construct(
            $eventService,
            $actionService,
            $validator
        );

        $this->playerService = $playerService;
        $this->playerVariableService = $playerVariableService;
    }

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter === null;
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $maxActionPoint = $this->player->getVariableByName(PlayerVariableEnum::ACTION_POINT)->getMaxValue();
        $maxMovementPoint = $this->player->getVariableByName(PlayerVariableEnum::MOVEMENT_POINT)->getMaxValue();
        $maxMoralePoint = $this->player->getVariableByName(PlayerVariableEnum::MORAL_POINT)->getMaxValue();
        $maxHealthPoint = $this->player->getVariableByName(PlayerVariableEnum::HEALTH_POINT)->getMaxValue();

        if ($maxMoralePoint === null || $maxActionPoint === null || $maxMovementPoint === null || $maxHealthPoint === null) {
            throw new \Exception('moral, movement, action and health points should have a maximum value');
        }

        $this->player
            ->setActionPoint($maxActionPoint)
            ->setMovementPoint($maxMovementPoint)
            ->setMoralPoint($maxMoralePoint)
            ->setHealthPoint($maxHealthPoint)
        ;

        $this->playerService->persist($this->player);
    }
}
