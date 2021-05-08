<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameter;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\FullHealth;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Player\Event\PlayerModifierEvent;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UseBandage extends AbstractAction
{
    const BANDAGE_HEAL = 2;

    protected string $name = ActionEnum::USE_BANDAGE;

    private PlayerServiceInterface $playerService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        PlayerServiceInterface $playerService,
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService,
            $validator
        );

        $this->playerService = $playerService;
    }

    protected function support(?ActionParameter $parameter): bool
    {
        return $parameter instanceof GameEquipment;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new FullHealth(['target' => FullHealth::PLAYER, 'groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::HEAL_NO_INJURY]));
    }

    protected function applyEffects(): ActionResult
    {
        /** @var GameEquipment $parameter */
        $parameter = $this->parameter;

        $initialHealth = $this->player->getHealthPoint();

        $playerModifierEvent = new PlayerModifierEvent($this->player, self::BANDAGE_HEAL);
        $playerModifierEvent->setIsDisplayedRoomLog(false);
        $this->eventDispatcher->dispatch($playerModifierEvent, PlayerModifierEvent::HEALTH_POINT_MODIFIER);

        $this->playerService->persist($this->player);

        $equipmentEvent = new EquipmentEvent($parameter, VisibilityEnum::HIDDEN);
        $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);

        $healedQuantity = $this->player->getHealthPoint() - $initialHealth;

        return new Success(null, $healedQuantity);
    }
}
