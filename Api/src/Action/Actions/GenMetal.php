<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\ClassConstraint;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\PlaceName;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Entity\Collection\ProbaCollection;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Enum\RoomEnum;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class GenMetal extends AttemptAction
{
    private const array ITEM_TABLE = [
        ItemEnum::METAL_SCRAPS => 4,
        ItemEnum::PLASTIC_SCRAPS => 3,
        GameRationEnum::STANDARD_RATION => 1,
        ItemEnum::FUEL_CAPSULE => 1,
        ItemEnum::OXYGEN_CAPSULE => 1,
    ];
    protected ActionEnum $name = ActionEnum::GEN_METAL;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        RandomServiceInterface $randomService,
        private GameEquipmentServiceInterface $gameEquipmentService,
        private StatusServiceInterface $statusService,
    ) {
        parent::__construct($eventService, $actionService, $validator, $randomService);
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraints([
            new PlaceName([
                'places' => RoomEnum::getStorages(),
                'groups' => [ClassConstraint::VISIBILITY],
            ]),
            new HasStatus([
                'status' => PlayerStatusEnum::HAS_GEN_METAL,
                'contain' => false,
                'target' => HasStatus::PLAYER,
                'groups' => [ClassConstraint::EXECUTE],
                'message' => ActionImpossibleCauseEnum::DAILY_LIMIT,
            ]),
        ]);
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target === null;
    }

    protected function checkResult(): ActionResult
    {
        $result = parent::checkResult();
        if ($result->isAFail()) {
            return $result;
        }

        $item = $this->createRandomItem();

        return $result->setEquipment($item);
    }

    protected function applyEffect(ActionResult $result): void
    {
        $this->createHasGeneratedMetalStatus();
    }

    private function createRandomItem(): GameEquipment
    {
        return $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: $this->randomItemName(),
            equipmentHolder: $this->player,
            reasons: $this->getTags(),
            time: new \DateTime(),
        );
    }

    private function randomItemName(): string
    {
        return (string) $this->randomService->getSingleRandomElementFromProbaCollection(new ProbaCollection(self::ITEM_TABLE));
    }

    private function createHasGeneratedMetalStatus(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::HAS_GEN_METAL,
            holder: $this->player,
            tags: $this->getTags(),
            time: new \DateTime(),
        );
    }
}
