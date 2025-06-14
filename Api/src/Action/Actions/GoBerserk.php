<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\PlaceType;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Modifier\Service\ModifierCreationServiceInterface;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class GoBerserk extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::GO_BERSERK;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        private GameEquipmentServiceInterface $gameEquipmentService,
        private ModifierCreationServiceInterface $modifierCreationService,
        private PlayerDiseaseServiceInterface $playerDiseaseService,
        private StatusServiceInterface $statusService,
    ) {
        parent::__construct($eventService, $actionService, $validator);
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(
            new PlaceType([
                'type' => PlaceTypeEnum::ROOM,
                'groups' => ['execute'],
                'message' => ActionImpossibleCauseEnum::NOT_A_ROOM,
            ])
        );
        $metadata->addConstraint(
            new HasStatus([
                'status' => PlayerStatusEnum::MUSH,
                'target' => HasStatus::PLAYER,
                'groups' => ['visibility'],
            ])
        );
        $metadata->addConstraint(
            new HasStatus([
                'status' => PlayerStatusEnum::BERZERK,
                'target' => HasStatus::PLAYER,
                'contain' => false,
                'groups' => ['visibility'],
            ])
        );
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target === null;
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $this->exitTerminal();
        $this->dropAllItems();
        $this->removePersonalTraits();
        $this->removeAllMedicalConditions();
        $this->healBy($this->getOutputQuantity());
        $this->applyBerzerkStatus();
    }

    private function exitTerminal(): void
    {
        $this->statusService->removeStatus(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->player,
            tags: $this->getTags(),
            time: new \DateTime(),
        );
    }

    private function dropAllItems(): void
    {
        $playerEquipment = $this->player->getEquipments();

        foreach ($playerEquipment as $item) {
            $this->gameEquipmentService->moveEquipmentTo(
                equipment: $item,
                newHolder: $this->player->getPlace(),
                tags: $this->getTags(),
                time: new \DateTime(),
            );
        }
    }

    private function removePersonalTraits(): void
    {
        $personalTraits = $this->player->getCharacterConfig()->getInitStatuses();
        $time = new \DateTime();

        foreach ($personalTraits as $trait) {
            // @var StatusConfig $trait
            $this->statusService->removeStatus(
                statusName: $trait->getStatusName(),
                holder: $this->player,
                tags: $this->getTags(),
                time: $time,
            );
        }
    }

    private function removeAllMedicalConditions(): void
    {
        $medicalConditions = $this->player->getMedicalConditions();

        foreach ($medicalConditions as $disease) {
            $this->playerDiseaseService->removePlayerDisease(
                $disease,
                $this->getTags(),
                new \DateTime(),
                VisibilityEnum::HIDDEN,
                $this->player,
            );
        }
    }

    private function healBy(int $quantity): void
    {
        $playerModifierEvent = new PlayerVariableEvent(
            $this->player,
            PlayerVariableEnum::HEALTH_POINT,
            $quantity,
            $this->getActionConfig()->getActionTags(),
            new \DateTime(),
        );
        $playerModifierEvent->setVisibility(VisibilityEnum::HIDDEN);
        $this->eventService->callEvent($playerModifierEvent, VariableEventInterface::CHANGE_VARIABLE);
    }

    private function applyBerzerkStatus(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::BERZERK,
            holder: $this->player,
            tags: $this->getTags(),
            time: new \DateTime(),
        );
    }
}
