<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\ClassConstraint;
use Mush\Action\Validator\EquipmentInfected;
use Mush\Action\Validator\GameVariableLevel;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class implementing the multiple actions that infect a pet. You need one spore and then the pet behavior change to help the mush team.
 */
final class ConvertPet extends AbstractAction
{
    private const int SPORE_COST = 1;
    protected ActionEnum $name = ActionEnum::CONVERT_PET;

    protected StatusServiceInterface $statusService;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        StatusServiceInterface $statusService
    ) {
        parent::__construct($eventService, $actionService, $validator);

        $this->statusService = $statusService;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach([
            'reach' => ReachEnum::INVENTORY,
            'groups' => [ClassConstraint::VISIBILITY]]));
        $metadata->addConstraint(new HasStatus([
            'status' => PlayerStatusEnum::MUSH,
            'target' => HasStatus::PLAYER,
            'groups' => [ClassConstraint::VISIBILITY]]));
        $metadata->addConstraint(new EquipmentInfected([
            'groups' => [ClassConstraint::EXECUTE],
            'message' => ActionImpossibleCauseEnum::PET_ALREADY_CONVERTED]));
        $metadata->addConstraint(new GameVariableLevel([
            'target' => GameVariableLevel::PLAYER,
            'variableName' => PlayerVariableEnum::SPORE,
            'checkMode' => GameVariableLevel::IS_MIN,
            'value' => self::SPORE_COST,
            'groups' => [ClassConstraint::EXECUTE],
            'message' => ActionImpossibleCauseEnum::INFECT_PET_NO_SPORE]));
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameItem;
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $this->createInfectedStatus();

        $this->removeSporeFromPlayer();
    }

    private function createInfectedStatus(): void
    {
        $equipment = $this->gameEquipmentTarget();
        foreach (EquipmentStatusEnum::getPetInfectedStatus() as $itemName => $statusName) {
            if ($equipment->getName() === $itemName) {
                $this->statusService->createStatusFromName(
                    $statusName,
                    $equipment,
                    $this->getActionConfig()->getActionTags(),
                    new \DateTime(),
                    $this->player,
                );

                break;
            }
        }
    }

    private function removeSporeFromPlayer(): void
    {
        $playerModifierEvent = new PlayerVariableEvent(
            $this->player,
            PlayerVariableEnum::SPORE,
            -self::SPORE_COST,
            $this->getTags(),
            new \DateTime(),
        );
        $this->eventService->callEvent($playerModifierEvent, VariableEventInterface::CHANGE_VARIABLE);
    }
}
