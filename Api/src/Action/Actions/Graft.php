<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Fail;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\ActionProviderIsInPlayerInventory;
use Mush\Action\Validator\FruitToGraftGivesDifferentPlant;
use Mush\Action\Validator\HasSkill;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\InteractWithEquipmentEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class Graft extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::GRAFT;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        private GameEquipmentServiceInterface $gameEquipmentService,
        private RandomServiceInterface $randomService,
    ) {
        parent::__construct($eventService, $actionService, $validator);
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraints([
            new HasSkill([
                'skill' => SkillEnum::BOTANIST,
                'groups' => ['visibility'],
            ]),
            new FruitToGraftGivesDifferentPlant([
                'groups' => ['visibility'],
            ]),
            new ActionProviderIsInPlayerInventory([
                'groups' => ['visibility'],
            ]),
        ]);
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameItem && $target->hasMechanicByName(EquipmentMechanicEnum::PLANT);
    }

    protected function checkResult(): ActionResult
    {
        if ($this->player->hasStatus(PlayerStatusEnum::DIRTY) || $this->plant()->isPlantUnhealthy()) {
            return new Fail();
        }

        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        if ($result->isASuccess()) {
            $this->createGraftedFruitPlant();
        } else {
            $this->createHydropot();
        }

        $this->destroyPlant();
        $this->destroyGraftedFruit();
    }

    private function createGraftedFruitPlant(): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: $this->graftedFruit()->getPlantNameOrThrow(),
            equipmentHolder: $this->player->getPlace(),
            reasons: $this->getTags(),
            time: new \DateTime(),
        );
    }

    private function createHydropot(): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::HYDROPOT,
            equipmentHolder: $this->player->getPlace(),
            reasons: $this->getTags(),
            time: new \DateTime(),
            author: $this->player,
        );
    }

    private function destroyPlant(): void
    {
        $equipmentEvent = new InteractWithEquipmentEvent(
            equipment: $this->plant(),
            author: $this->player,
            visibility: VisibilityEnum::HIDDEN,
            tags: $this->getTags(),
            time: new \DateTime()
        );
        $this->eventService->callEvent($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);
    }

    private function destroyGraftedFruit(): void
    {
        $equipmentEvent = new InteractWithEquipmentEvent(
            equipment: $this->graftedFruit(),
            author: $this->player,
            visibility: VisibilityEnum::HIDDEN,
            tags: $this->getTags(),
            time: new \DateTime()
        );
        $this->eventService->callEvent($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);
    }

    private function graftedFruit(): GameItem
    {
        return $this->actionProvider instanceof GameItem ? $this->actionProvider : throw new \RuntimeException('Action provider must be a GameItem');
    }

    private function plant(): GameItem
    {
        return $this->target instanceof GameItem ? $this->target : throw new \RuntimeException('Target must be a GameItem');
    }
}
