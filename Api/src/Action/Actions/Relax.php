<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\BondedAlready;
use Mush\Action\Validator\EmptyPlaceToSit;
use Mush\Action\Validator\HasEquipment;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\NumberPlayersAliveInRoom;
use Mush\Action\Validator\Reach;
use Mush\Disease\Service\DiseaseCauseServiceInterface;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Relax extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::RELAX;

    private DiseaseCauseServiceInterface $diseaseCauseService;
    private PlayerDiseaseServiceInterface $playerDiseaseService;
    private RandomServiceInterface $randomService;
    private RoomLogServiceInterface $roomLogService;
    private StatusServiceInterface $statusService;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        DiseaseCauseServiceInterface $diseaseCauseService,
        PlayerDiseaseServiceInterface $playerDiseaseService,
        RandomServiceInterface $randomService,
        RoomLogServiceInterface $roomLogService,
        StatusServiceInterface $statusService
    ) {
        parent::__construct(
            $eventService,
            $actionService,
            $validator
        );
        $this->diseaseCauseService = $diseaseCauseService;
        $this->playerDiseaseService = $playerDiseaseService;
        $this->randomService = $randomService;
        $this->roomLogService = $roomLogService;
        $this->statusService = $statusService;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new EmptyPlaceToSit(['groups' => ['visibility']]));
        $metadata->addConstraint(new BondedAlready([
            'groups' => ['execute'],
            'expectedValue' => true,
            'initiator' => false,
            'message' => ActionImpossibleCauseEnum::DO_THE_THING_NOT_INTERESTED,
        ]));
        $metadata->addConstraint(new HasStatus([
            'status' => PlayerStatusEnum::LYING_DOWN,
            'contain' => false,
            'target' => HasStatus::PARAMETER,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::RELAX_ASLEEP,
        ]));
        $metadata->addConstraint(new HasStatus([
            'status' => PlayerStatusEnum::DID_THE_THING,
            'contain' => false,
            'target' => HasStatus::PARAMETER,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::DO_THE_THING_ALREADY_DONE,
        ]));
        $metadata->addConstraint(new HasStatus([
            'status' => PlayerStatusEnum::DID_THE_THING,
            'contain' => false,
            'target' => HasStatus::PLAYER,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::DO_THE_THING_ALREADY_DONE,
        ]));
        $metadata->addConstraint(new HasEquipment([
            'reach' => ReachEnum::SHELVE,
            'equipments' => [EquipmentEnum::CAMERA_EQUIPMENT],
            'contains' => false,
            'checkIfOperational' => true,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::RELAX_CAMERA,
        ]));
        $metadata->addConstraint(new NumberPlayersAliveInRoom([
            'mode' => NumberPlayersAliveInRoom::GREATER_THAN,
            'number' => 2,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::RELAX_WITNESS,
        ]));
        $metadata->addConstraint(new HasStatus([
            'status' => PlayerStatusEnum::INACTIVE,
            'contain' => false,
            'target' => HasStatus::PLAYER,
            'groups' => ['visibility'],
        ]));
        $metadata->addConstraint(new HasStatus([
            'status' => PlayerStatusEnum::HIGHLY_INACTIVE,
            'contain' => false,
            'target' => HasStatus::PLAYER,
            'groups' => ['visibility'],
        ]));
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof Player;
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        /** @var Player $target */
        $target = $this->target;
        $player = $this->player;

        // give two moral points, or max morale if it is their first time
        $moralePoints = $this->getOutputQuantity();
        $this->addMoralPoints($player, $moralePoints);
        $this->addMoralPoints($target, $moralePoints);

        // if one is mush and has a spore, infect other player
        if ($player->isMush() && !$target->isMush()) {
            $this->infect($player, $target);
        }
        if ($target->isMush() && !$player->isMush()) {
            $this->infect($target, $player);
        }

        // add did_the_thing status until the end of the day
        $this->addDidTheThingStatus($player);
        $this->addDidTheThingStatus($target);

        // add target as a bonded player if not already the case. (To allow Raluca to be flirted with)
        if ($player->hasBondeddWith($target) === false) {
            $player->addBond($target);
        }
    }

    private function addMoralPoints(Player $player, int $moralePoints): void
    {
        $maxMoralePoint = $this->player->getVariableByName(PlayerVariableEnum::MORAL_POINT)->getMaxValue();

        if ($maxMoralePoint === null) {
            throw new \Exception('moralPoints should have a maximum value');
        }

        $firstTimeStatus = $player->getStatusByName(PlayerStatusEnum::FIRST_TIME);
        $moralePoints = $firstTimeStatus ? $maxMoralePoint : $moralePoints;

        $playerModifierEvent = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::MORAL_POINT,
            $moralePoints,
            $this->getActionConfig()->getActionTags(),
            new \DateTime(),
        );
        $this->eventService->callEvent($playerModifierEvent, VariableEventInterface::CHANGE_VARIABLE);

        if ($firstTimeStatus) {
            $this->statusService->removeStatus(
                PlayerStatusEnum::FIRST_TIME,
                $player,
                $this->getActionConfig()->getActionTags(),
                new \DateTime(),
            );
        }
    }

    private function addDidTheThingStatus(Player $player): void
    {
        $this->statusService->createStatusFromName(
            PlayerStatusEnum::DID_THE_THING,
            $player,
            $this->getActionConfig()->getActionTags(),
            new \DateTime(),
        );
    }

    private function infect(Player $mush, Player $target): void
    {
        $sporeNumber = $mush->getVariableValueByName(PlayerVariableEnum::SPORE);
        if ($sporeNumber > 0) {
            $removeSporeEvent = new PlayerVariableEvent(
                $mush,
                PlayerVariableEnum::SPORE,
                -1,
                $this->getActionConfig()->getActionTags(),
                new \DateTime()
            );
            $addSporesEvent = new PlayerVariableEvent(
                $target,
                PlayerVariableEnum::SPORE,
                1,
                $this->getActionConfig()->getActionTags(),
                new \DateTime()
            );
            $addSporesEvent->setAuthor($mush);
            $this->eventService->callEvent($removeSporeEvent, VariableEventInterface::CHANGE_VARIABLE);
            $this->eventService->callEvent($addSporesEvent, VariableEventInterface::CHANGE_VARIABLE);
        }
    }
}
