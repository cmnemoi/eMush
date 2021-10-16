<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Event\RoomEvent;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Extinguish extends AttemptAction
{
    protected string $name = ActionEnum::EXTINGUISH;

    private PlayerServiceInterface $playerService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        PlayerServiceInterface $playerService,
        RandomServiceInterface $randomService,
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService,
            $validator,
            $randomService,
        );

        $this->playerService = $playerService;
        $this->randomService = $randomService;
    }

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter instanceof GameEquipment;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new HasStatus(['status' => StatusEnum::FIRE, 'target' => HasStatus::PLAYER_ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new HasStatus([
            'status' => EquipmentStatusEnum::BROKEN,
            'contain' => false,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::BROKEN_EQUIPMENT,
        ]));
    }

    protected function applyEffects(): ActionResult
    {
        $response = $this->makeAttempt();

        if ($response instanceof Success) {
            $roomEvent = new RoomEvent(
                $this->player->getPlace(),
                $this->getActionName(),
                new \DateTime()
            );

            $this->eventDispatcher->dispatch($roomEvent, RoomEvent::STOP_FIRE);
        }

        $this->playerService->persist($this->player);

        return $response;
    }
}
