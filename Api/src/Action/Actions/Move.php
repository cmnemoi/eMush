<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameter;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\Reach;
use Mush\Action\Validator\Status;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Enum\ReachEnum;
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

    /** @var Door */
    protected $parameter;

    private RoomLogServiceInterface $roomLogService;
    private PlayerServiceInterface $playerService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        PlayerServiceInterface $playerService,
        RoomLogServiceInterface $roomLogService,
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

    protected static function addConstraints(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new Status(['status' => EquipmentStatusEnum::BROKEN, 'groups' => ['visibility']]));
    }

    protected function applyEffects(): ActionResult
    {
        $newRoom = $this->parameter->getOtherRoom($this->player->getPlace());
        $this->player->setPlace($newRoom);

        $this->playerService->persist($this->player);

        $this->createLog();

        return new Success();
    }

    protected function createLog(): void
    {
        $this->roomLogService->createActionLog(
            ActionLogEnum::ENTER_ROOM,
            $this->player->getPlace(),
            $this->player,
            null,
            VisibilityEnum::PUBLIC,
            new \DateTime('now')
        );
        $this->roomLogService->createActionLog(
            ActionLogEnum::EXIT_ROOM,
            $this->parameter->getOtherRoom($this->player->getPlace()),
            $this->player,
            null,
            VisibilityEnum::PUBLIC,
            new \DateTime('now')
        );
    }
}
