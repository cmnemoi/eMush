<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\ClassConstraint;
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
 * Class implementing the "Infectious Carress" action (converting the cat to the Mush).
 * This action is granted by Schrodinger.
 *
 * For 1 PA and 1 spore, "Infectious Caress" gives Schrodinger the Infected status
 * which means all future random chance injuries involving petting or picking him up
 * will infect human crewmembers with one spore.
 *
 * More info : http://www.mushpedia.com/wiki/Schr%C3%B6dinger
 */
class ConvertCat extends AbstractAction
{
    private const int SPORE_COST = 1;
    protected ActionEnum $name = ActionEnum::CONVERT_CAT;

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
        $metadata->addConstraint(new HasStatus([
            'status' => EquipmentStatusEnum::CAT_INFECTED,
            'contain' => false,
            'groups' => [ClassConstraint::EXECUTE],
            'message' => ActionImpossibleCauseEnum::CAT_ALREADY_CONVERTED]));
        $metadata->addConstraint(new GameVariableLevel([
            'target' => GameVariableLevel::PLAYER,
            'variableName' => PlayerVariableEnum::SPORE,
            'checkMode' => GameVariableLevel::IS_MIN,
            'groups' => [ClassConstraint::EXECUTE],
            'message' => ActionImpossibleCauseEnum::INFECT_CAT_NO_SPORE]));
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
        $this->statusService->createStatusFromName(
            EquipmentStatusEnum::CAT_INFECTED,
            $this->gameEquipmentTarget(),
            $this->getActionConfig()->getActionTags(),
            new \DateTime(),
        );

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
