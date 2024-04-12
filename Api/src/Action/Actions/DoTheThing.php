<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Fail;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\EmptyOperationalBedInRoom;
use Mush\Action\Validator\FlirtedAlready;
use Mush\Action\Validator\HasEquipment;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\IsSameGender;
use Mush\Action\Validator\NumberPlayersAliveInRoom;
use Mush\Action\Validator\Reach;
use Mush\Disease\Entity\Collection\PlayerDiseaseCollection;
use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Disease\Service\DiseaseCauseServiceInterface;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Entity\StatusHolderInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DoTheThing extends AbstractAction
{
    public const PREGNANCY_RATE = 8;
    public const STD_TRANSMISSION_RATE = 5;
    public const TOO_PASSIONATE_ACT_RATE = 5;

    protected string $name = ActionEnum::DO_THE_THING;

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
        $metadata->addConstraint(new IsSameGender(['groups' => ['visibility']]));
        $metadata->addConstraint(new EmptyOperationalBedInRoom(['groups' => ['visibility']]));
        $metadata->addConstraint(new FlirtedAlready([
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
            'message' => ActionImpossibleCauseEnum::DO_THE_THING_ASLEEP,
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
            'message' => ActionImpossibleCauseEnum::DO_THE_THING_CAMERA,
        ]));
        $metadata->addConstraint(new NumberPlayersAliveInRoom([
            'mode' => NumberPlayersAliveInRoom::GREATER_THAN,
            'number' => 2,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::DO_THE_THING_WITNESS,
        ]));
    }

    protected function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof Player;
    }

    protected function checkResult(): ActionResult
    {
        $actIsTooPassionate = $this->randomService->isSuccessful(self::TOO_PASSIONATE_ACT_RATE);
        $sofaInRoom = $this->player->getPlace()->getEquipmentByName(EquipmentEnum::SWEDISH_SOFA);

        if ($actIsTooPassionate && $sofaInRoom) {
            $this->statusService->createStatusFromName(
                statusName: EquipmentStatusEnum::BROKEN,
                holder: $sofaInRoom,
                tags: $this->action->getActionTags(),
                time: new \DateTime(),
                visibility: VisibilityEnum::PUBLIC,
            );

            return new Fail();
        }

        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        /** @var Player $target */
        $target = $this->target;
        $player = $this->player;

        /** @TODO add confirmation pop up */

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

        // may become pregnant
        $becomePregnant = $this->randomService->isSuccessful(self::PREGNANCY_RATE);
        if ($becomePregnant) {
            $this->addPregnantStatus($player, $target);
        }

        // may transmit an STD
        $transmitStd = $this->randomService->isSuccessful(self::STD_TRANSMISSION_RATE);
        if ($transmitStd) {
            $playerStds = $this->getPlayerStds($player);
            $parameterStds = $this->getPlayerStds($target);

            if ($playerStds->count() > 0) {
                $this->transmitStd($playerStds, $target);
            } elseif ($parameterStds->count() > 0) {
                $this->transmitStd($parameterStds, $player);
            }
        }

        // add did_the_thing status until the end of the day
        $this->addDidTheThingStatus($player);
        $this->addDidTheThingStatus($target);
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
            $this->getAction()->getActionTags(),
            new \DateTime(),
        );
        $this->eventService->callEvent($playerModifierEvent, VariableEventInterface::CHANGE_VARIABLE);

        if ($firstTimeStatus) {
            $this->statusService->removeStatus(
                PlayerStatusEnum::FIRST_TIME,
                $player,
                $this->getAction()->getActionTags(),
                new \DateTime(),
            );
        }
    }

    private function addDidTheThingStatus(Player $player): void
    {
        $this->statusService->createStatusFromName(
            PlayerStatusEnum::DID_THE_THING,
            $player,
            $this->getAction()->getActionTags(),
            new \DateTime(),
        );
    }

    private function addPregnantStatus(Player $player, Player $target): void
    {
        $playerName = $player->getPlayerInfo()->getCharacterConfig()->getCharacterName();
        $targetName = $target->getPlayerInfo()->getCharacterConfig()->getCharacterName();

        // Won't make male characters pregnant
        if (CharacterEnum::isMale($playerName) && CharacterEnum::isMale($targetName)) {
            return;
        }

        // won't make female characters pregnant between themselves
        if (!CharacterEnum::isMale($playerName) && !CharacterEnum::isMale($targetName)) {
            return;
        }

        /** @var StatusHolderInterface $femalePlayer */
        $femalePlayer = CharacterEnum::isMale($playerName) ? $target : $player;

        $this->statusService->createStatusFromName(
            PlayerStatusEnum::PREGNANT,
            $femalePlayer,
            $this->getAction()->getActionTags(),
            new \DateTime(),
            null,
            VisibilityEnum::PRIVATE
        );
    }

    private function getPlayerStds(Player $player): PlayerDiseaseCollection
    {
        $sexDiseaseCauseConfig = $this->diseaseCauseService->findCauseConfigByDaedalus(DiseaseCauseEnum::SEX, $player->getDaedalus());

        $stds = array_keys($sexDiseaseCauseConfig->getDiseases()->toArray());

        return $player->getMedicalConditions()->getActiveDiseases()->filter(
            static function ($disease) use ($stds) { return \in_array($disease->getDiseaseConfig()->getName(), $stds, true); }
        );
    }

    private function infect(Player $mush, Player $target)
    {
        $sporeNumber = $mush->getVariableValueByName(PlayerVariableEnum::SPORE);
        if ($sporeNumber > 0) {
            $removeSporeEvent = new PlayerVariableEvent(
                $mush,
                PlayerVariableEnum::SPORE,
                -1,
                $this->getAction()->getActionTags(),
                new \DateTime()
            );
            $addSporesEvent = new PlayerVariableEvent(
                $target,
                PlayerVariableEnum::SPORE,
                1,
                $this->getAction()->getActionTags(),
                new \DateTime()
            );
            $addSporesEvent->setAuthor($mush);
            $this->eventService->callEvent($removeSporeEvent, VariableEventInterface::CHANGE_VARIABLE);
            $this->eventService->callEvent($addSporesEvent, VariableEventInterface::CHANGE_VARIABLE);
        }
    }

    private function transmitStd(PlayerDiseaseCollection $stds, Player $target): void
    {
        $draw = $this->randomService->getRandomElements($stds->toArray(), 1);
        $std = reset($draw)->getDiseaseConfig();

        $this->createDiseaseBySexLog($target, $std->getName());

        $this->playerDiseaseService->createDiseaseFromName($std->getName(), $target, $this->getAction()->getActionTags());
    }

    private function createDiseaseBySexLog(Player $player, string $disease): void
    {
        $this->roomLogService->createLog(
            'disease_by_sex',
            $player->getPlace(),
            VisibilityEnum::PRIVATE,
            'event_log',
            $player,
            ['disease' => $disease],
            new \DateTime()
        );
    }
}
