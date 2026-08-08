<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Enum\ActionVariableEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\PlaceType;
use Mush\Action\Validator\PrivateProperty;
use Mush\Action\Validator\Reach;
use Mush\Disease\Service\DiseaseCauseServiceInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\InteractWithEquipmentEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Special implementation of the Shoot action that targets equipments instead of players. Destroy the equipment on success. Use the % of success from the mechanic of the weapon used.
 */
class ShootEquipment extends AttemptAction
{
    private const string CAT_DEATH_TAG = 'cat_death';
    protected ActionEnum $name = ActionEnum::SHOOT_EQUIPMENT;
    protected GameEquipmentServiceInterface $gameEquipmentService;

    private DiseaseCauseServiceInterface $diseaseCauseService;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        RandomServiceInterface $randomService,
        GameEquipmentServiceInterface $gameEquipmentService,
        DiseaseCauseServiceInterface $diseaseCauseService,
        private PlayerServiceInterface $playerService,
    ) {
        parent::__construct(
            $eventService,
            $actionService,
            $validator,
            $randomService,
        );

        $this->diseaseCauseService = $diseaseCauseService;
        $this->gameEquipmentService = $gameEquipmentService;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new PlaceType(['groups' => ['execute'], 'type' => 'planet', 'allowIfTypeMatches' => false, 'message' => ActionImpossibleCauseEnum::ON_PLANET]));
        $metadata->addConstraint(new PrivateProperty(['groups' => ['execute']]));
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameEquipment && EquipmentEnum::canGetShot($target->getName());
    }

    public function getSuccessRate(): int
    {
        $actionConfig = clone $this->actionConfig;
        $baseAccuracy = $this->getGameEquipmentActionProvider()->getWeaponMechanicOrThrow()->getBaseAccuracy();
        $actionConfig->setSuccessRate($baseAccuracy);

        return $this->actionService->getActionModifiedActionVariable(
            player: $this->player,
            actionConfig: $actionConfig,
            actionProvider: $this->actionProvider,
            actionTarget: $this->target,
            variableName: ActionVariableEnum::PERCENTAGE_SUCCESS,
            tags: $this->getTags()
        );
    }

    public function getTags(): array
    {
        $tags = parent::getTags();

        $tags[] = $this->itemActionProvider()->getName();
        $tags[] = $this->gameEquipmentTarget()->getName();

        return $tags;
    }

    protected function applyEffect(ActionResult $result): void
    {
        if ($result instanceof Success) {
            $this->destroyEquipment();
        }
    }

    private function destroyEquipment(): void
    {
        $item = $this->gameItemTarget();
        $interactEvent = new InteractWithEquipmentEvent(
            $item,
            $this->player,
            VisibilityEnum::PUBLIC,
            $this->getTags(),
            new \DateTime(),
        );

        $this->eventService->callEvent($this->addInfectedTags($interactEvent, $item), EquipmentEvent::EQUIPMENT_DESTROYED);
    }

    private function addInfectedTags(InteractWithEquipmentEvent $event, GameItem $item): InteractWithEquipmentEvent
    {
        if ($item->getName() === ItemEnum::SCHRODINGER) {
            $event->addTag(self::CAT_DEATH_TAG);
        }

        foreach (EquipmentStatusEnum::getPetInfectedStatus() as $itemName => $statusName) {
            if ($item->hasStatus($statusName)) {
                $event->addTag($statusName);
            }
        }

        return $event;
    }
}
