<?php

declare(strict_types=1);

namespace Mush\Action\Actions\SummerEventActions;

use Mush\Action\Actions\AbstractAction;
use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\ActionProviderIsInPlayerInventory;
use Mush\Action\Validator\ClassConstraint;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Service\DeleteEquipmentServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class FeedPet extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::FEED_THE_PET;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        private readonly DeleteEquipmentServiceInterface $deleteEquipmentServiceInterface,
        private readonly StatusServiceInterface $statusService,
    ) {
        parent::__construct($eventService, $actionService, $validator);
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraints([
            new Reach([
                'reach' => ReachEnum::ROOM,
                'groups' => [ClassConstraint::VISIBILITY],
            ]),
            new ActionProviderIsInPlayerInventory([
                'groups' => ['visibility'],
            ]),
        ]);
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameItem && $target->getName() === ItemEnum::TREASURE_HUNT_PET;
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $this->handleOldStatus();
        $this->createNewStatus();

        if ($this->isInfected($this->getGameEquipmentActionProvider())) {
            $this->infectPet();
        }
        $this->destroyItemm();
    }

    private function isInfected(GameEquipment $food): bool
    {
        return $food->hasStatus(EquipmentStatusEnum::CONTAMINATED);
    }

    private function handleOldStatus(): void
    {
        $status = $this->statusService->getByTargetAndName($this->gameItemTarget(), PlayerStatusEnum::PROTECTED_BY_PET);
        if ($status) {
            $this->statusService->removeStatus(
                PlayerStatusEnum::PROTECTED_BY_PET,
                $status->getOwner(),
                $this->getTags(),
                new \DateTime()
            );
        }
    }

    private function createNewStatus(): void
    {
        $this->statusService->createStatusFromName(
            PlayerStatusEnum::PROTECTED_BY_PET,
            $this->getPlayer(),
            $this->getTags(),
            new \DateTime(),
            $this->gameItemTarget()
        );
    }

    private function infectPet(): void
    {
        $this->statusService->createStatusFromName(
            EquipmentStatusEnum::BABY_SKINNER_INFECTED,
            $this->gameEquipmentTarget(),
            $this->getTags(),
            new \DateTime(),
            $this->getGameEquipmentActionProvider()->getStatusByNameOrThrow(EquipmentStatusEnum::CONTAMINATED)->getPlayerTargetOrThrow()
        );
    }

    private function destroyItemm(): void
    {
        $this->deleteEquipmentServiceInterface->execute($this->getGameEquipmentActionProvider());
    }
}
