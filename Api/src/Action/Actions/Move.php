<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameter;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Move extends AbstractAction
{
    protected string $name = ActionEnum::MOVE;

    private RoomLogServiceInterface $roomLogService;
    private PlayerServiceInterface $playerService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        PlayerServiceInterface $playerService,
        RoomLogServiceInterface $roomLogService
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService,
            $validator
        );

        $this->roomLogService = $roomLogService;
        $this->playerService = $playerService;
    }

    protected function support(?ActionParameter $parameter): bool
    {
        return $parameter instanceof Door;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new HasStatus(['status' => EquipmentStatusEnum::BROKEN, 'contain' => false, 'groups' => ['visibility']]));
    }

    protected function applyEffects(): ActionResult
    {
        /** @var Door $parameter */
        $parameter = $this->parameter;

        $oldRoom = $this->player->getPlace();
        $newRoom = $parameter->getOtherRoom($this->player->getPlace());
        $this->player->setPlace($newRoom);

        $this->playerService->persist($this->player);

        $this->createLog($newRoom, $oldRoom);

        return new Success();
    }

    protected function createLog(Place $newplace, Place $oldPlace): void
    {
        $this->roomLogService->createLog(
            ActionLogEnum::ENTER_ROOM,
            $newplace,
            VisibilityEnum::PUBLIC,
            'actions_log',
            $this->player,
            null,
            null,
            new \DateTime('now')
        );
        $this->roomLogService->createLog(
            ActionLogEnum::EXIT_ROOM,
            $oldPlace,
            VisibilityEnum::PUBLIC,
            'actions_log',
            $this->player,
            null,
            null,
            new \DateTime('now')
        );
    }
}
