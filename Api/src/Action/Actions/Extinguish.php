<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameter;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\ParameterHasAction;
use Mush\Action\Validator\Reach;
use Mush\Action\Validator\Status;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Service\PlaceServiceInterface;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class Extinguish extends AttemptAction
{
    protected string $name = ActionEnum::EXTINGUISH;

    /** @var GameEquipment */
    protected $parameter;

    private PlayerServiceInterface $playerService;
    private PlaceServiceInterface $placeService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        PlayerServiceInterface $playerService,
        RandomServiceInterface $randomService,
        PlaceServiceInterface $placeService,
        ActionServiceInterface $actionService
    ) {
        parent::__construct(
            $randomService,
            $eventDispatcher,
            $actionService
        );

        $this->playerService = $playerService;
        $this->randomService = $randomService;
        $this->placeService = $placeService;
    }

    protected function support(?ActionParameter $parameter): bool
    {
        return $parameter instanceof GameEquipment;
    }


    public static function loadVisibilityValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new ParameterHasAction());
        $metadata->addConstraint(new Reach());
        $metadata->addConstraint(new Status(['status' => StatusEnum::FIRE, 'target' => Status::PLAYER_ROOM]));
    }

    public function cannotExecuteReason(): ?string
    {
        if ($this->parameter->isBroken()) {
            return ActionImpossibleCauseEnum::BROKEN_EQUIPMENT;
        }

        return parent::cannotExecuteReason();
    }

    protected function applyEffects(): ActionResult
    {
        $response = $this->makeAttempt();

        if ($response instanceof Success &&
            ($fireStatus = $this->player->getPlace()->getStatusByName(StatusEnum::FIRE))
        ) {
            $this->player->getPlace()->removeStatus($fireStatus);
            $this->placeService->persist($this->player->getPlace());
        }

        $this->playerService->persist($this->player);

        return $response;
    }
}
