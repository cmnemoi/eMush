<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameter;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\Hideable;
use Mush\Action\Validator\PreMush;
use Mush\Action\Validator\Reach;
use Mush\Action\Validator\Room;
use Mush\Action\Validator\Status;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Hide extends AbstractAction
{
    protected string $name = ActionEnum::HIDE;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private PlayerServiceInterface $playerService;
    private StatusServiceInterface $statusService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        GameEquipmentServiceInterface $gameEquipmentService,
        StatusServiceInterface $statusService,
        PlayerServiceInterface $playerService,
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService,
            $validator
        );

        $this->gameEquipmentService = $gameEquipmentService;
        $this->statusService = $statusService;
        $this->playerService = $playerService;
    }

    protected function support(?ActionParameter $parameter): bool
    {
        return $parameter instanceof GameItem;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new Hideable(['groups' => ['visibility']]));
        $metadata->addConstraint(new Status(['status' => EquipmentStatusEnum::HIDDEN, 'contain' => false, 'groups' => ['visibility']]));
        $metadata->addConstraint(new Room(['groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::NO_SHELVING_UNIT]));
        $metadata->addConstraint(new PreMush(['groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::PRE_MUSH_RESTRICTED]));
    }

    protected function applyEffects(): ActionResult
    {
        /** @var GameItem $parameter */
        $parameter = $this->parameter;

        $this->statusService->createCoreStatus(
            EquipmentStatusEnum::HIDDEN,
            $parameter,
            $this->player,
            VisibilityEnum::PRIVATE,
        );

        if ($parameter->getPlayer()) {
            $parameter->setPlayer(null);
            $parameter->setPlace($this->player->getPlace());
        }

        $this->gameEquipmentService->persist($parameter);
        $this->playerService->persist($this->player);

        return new Success($this->parameter);
    }
}
