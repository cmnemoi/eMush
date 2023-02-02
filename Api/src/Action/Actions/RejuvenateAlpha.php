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
use Psr\Log\LoggerInterface;

class RejuvenateAlpha extends AbstractAction
{
    protected string $name = ActionEnum::REJUVENATE;

    private PlayerServiceInterface $playerService;
    private PlayerVariableServiceInterface $playerVariableService;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        LoggerInterface $logger,
        PlayerServiceInterface $playerService,
        PlayerVariableServiceInterface $playerVariableService
    ) {
        parent::__construct(
            $eventService,
            $actionService,
            $validator,
            $logger
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
        $maxActionPoint = $this->playerVariableService->getMaxPlayerVariable($this->player, PlayerVariableEnum::ACTION_POINT);
        $maxMovementPoint = $this->playerVariableService->getMaxPlayerVariable($this->player, PlayerVariableEnum::MOVEMENT_POINT);
        $maxMoralePoint = $this->playerVariableService->getMaxPlayerVariable($this->player, PlayerVariableEnum::MORAL_POINT);
        $maxHealthPoint = $this->playerVariableService->getMaxPlayerVariable($this->player, PlayerVariableEnum::HEALTH_POINT);

        if ($maxMoralePoint === null || $maxActionPoint === null || $maxMovementPoint === null || $maxHealthPoint === null) {
            $errorMessage = 'RejuvenateAlpha::applyEffect() - moral, movement, action and health points should have a maximum value';
            $this->logger->error($errorMessage,
                [   
                    'daedalus' => $this->player->getDaedalus()->getId(),
                    'player' => $this->player->getId(),
                    'maxMoralePoint' => $maxMoralePoint,
                    'maxActionPoint' => $maxActionPoint,
                    'maxMovementPoint' => $maxMovementPoint,
                    'maxHealthPoint' => $maxHealthPoint
                ]
            );
            throw new \Error($errorMessage);
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
