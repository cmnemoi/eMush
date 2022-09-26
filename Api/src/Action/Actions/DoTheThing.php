<?php

namespace Mush\Action\Actions;

use Error;
use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\EmptyBedInRoom;
use Mush\Action\Validator\FlirtedAlready;
use Mush\Action\Validator\HasEquipment;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\IsSameGender;
use Mush\Action\Validator\NumberPlayersInRoom;
use Mush\Action\Validator\Reach;
use Mush\Disease\Entity\Collection\PlayerDiseaseCollection;
use Mush\Disease\Entity\Config\DiseaseCauseConfig;
use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Disease\Repository\DiseaseCausesConfigRepository;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Event\Service\EventService;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\AbstractQuantityEvent;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Player\Service\PlayerVariableServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\StatusHolderInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\StatusEvent;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DoTheThing extends AbstractAction
{
    public const BASE_CONFORT = 2;
    public const PREGNANCY_RATE = 8;
    public const STD_TRANSMISSION_RATE = 5;

    protected string $name = ActionEnum::DO_THE_THING;

    private DiseaseCausesConfigRepository $diseaseCausesConfigRepository;
    private StatusServiceInterface $statusService;
    private PlayerDiseaseServiceInterface $playerDiseaseService;
    private PlayerVariableServiceInterface $playerVariableService;
    private RandomServiceInterface $randomService;
    private RoomLogServiceInterface $roomLogService;

    public function __construct(
        EventService $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        DiseaseCausesConfigRepository $diseaseCausesConfigRepository,
        PlayerDiseaseServiceInterface $playerDiseaseService,
        PlayerVariableServiceInterface $playerVariableService,
        RandomServiceInterface $randomService,
        RoomLogServiceInterface $roomLogService,
        StatusServiceInterface $statusService
    ) {
        parent::__construct(
            $eventService,
            $actionService,
            $validator
        );
        $this->diseaseCausesConfigRepository = $diseaseCausesConfigRepository;
        $this->playerDiseaseService = $playerDiseaseService;
        $this->playerVariableService = $playerVariableService;
        $this->randomService = $randomService;
        $this->roomLogService = $roomLogService;
        $this->statusService = $statusService;
    }

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter instanceof Player;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));

        $metadata->addConstraint(new IsSameGender(['groups' => ['visibility']]));

        $metadata->addConstraint(new EmptyBedInRoom(['groups' => ['visibility']]));

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

        $metadata->addConstraint(new NumberPlayersInRoom([
            'number' => 2,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::DO_THE_THING_WITNESS,
        ]));
    }

    protected function applyEffects(): ActionResult
    {
        /** @var Player $parameter */
        $parameter = $this->parameter;
        /** @var Player $player */
        $player = $this->player;

        // @TODO add confirmation pop up

        // give two moral points, or max morale if it is their first time
        $maxMoralePoint = $this->playerVariableService->getMaxPlayerVariable($player, PlayerVariableEnum::MORAL_POINT);

        $this->addMoralPoints($player, $maxMoralePoint);
        $this->addMoralPoints($parameter, $maxMoralePoint);

        // if one is mush and has a spore, infect other player
        if ($player->isMush() && !$parameter->isMush()) {
            $this->infect($player, $parameter);
        }
        if ($parameter->isMush() && !$player->isMush()) {
            $this->infect($parameter, $player);
        }

        // may become pregnant
        $becomePregnant = $this->randomService->isSuccessful(self::PREGNANCY_RATE);
        if ($becomePregnant) {
            $this->addPregnantStatus($player, $parameter);
        }

        // may transmit an STD
        $transmitStd = $this->randomService->isSuccessful(self::STD_TRANSMISSION_RATE);
        if ($transmitStd) {
            $playerStds = $this->getPlayerStds($player);
            $parameterStds = $this->getPlayerStds($parameter);

            if ($playerStds->count() > 0) {
                $this->transmitStd($playerStds, $parameter);
            } elseif ($parameterStds->count() > 0) {
                $this->transmitStd($parameterStds, $player);
            }
        }

        // add did_the_thing status until the end of the day
        $this->addDidTheThingStatus($player);
        $this->addDidTheThingStatus($parameter);

        return new Success();
    }

    private function addMoralPoints(Player $player, int $maxMoralePoint): void
    {
        $firstTimeStatus = $player->getStatusByName(PlayerStatusEnum::FIRST_TIME);
        $moralePoints = $firstTimeStatus ? $maxMoralePoint : self::BASE_CONFORT;

        $playerModifierEvent = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::MORAL_POINT,
            $moralePoints,
            $this->getActionName(),
            new \DateTime()
        );
        $this->eventService->callEvent($playerModifierEvent, AbstractQuantityEvent::CHANGE_VARIABLE);

        if ($firstTimeStatus) {
            $player->removeStatus($firstTimeStatus);
        }
    }

    private function addDidTheThingStatus(Player $player): void
    {
        $statusEvent = new StatusEvent(
            PlayerStatusEnum::DID_THE_THING,
            $player,
            $this->getActionName(),
            new \DateTime()
        );

        $this->eventService->callEvent($statusEvent, StatusEvent::STATUS_APPLIED);
    }

    private function addPregnantStatus(Player $player, Player $parameter): void
    {
        /** @var StatusHolderInterface * */
        $femalePlayer = CharacterEnum::isMale($player->getCharacterConfig()->getName()) ? $parameter : $player;
        $pregnantStatus = new StatusEvent(
            PlayerStatusEnum::PREGNANT,
            $femalePlayer,
            $this->getActionName(),
            new \DateTime()
        );
        $pregnantStatus->setVisibility(VisibilityEnum::PRIVATE);

        $this->eventService->callEvent($pregnantStatus, StatusEvent::STATUS_APPLIED);
    }

    private function getPlayerStds(Player $player): PlayerDiseaseCollection
    {
        /** @var DiseaseCauseConfig $sexDiseaseCauseConfig */
        $sexDiseaseCauseConfig = $this->diseaseCausesConfigRepository->findBy([
            'causeName' => DiseaseCauseEnum::SEX,
        ])[0];

        $stds = array_keys($sexDiseaseCauseConfig->getDiseases());

        $playerStds = $player->getMedicalConditions()->getActiveDiseases()->filter(
            function ($disease) use ($stds) { return in_array($disease->getDiseaseConfig()->getName(), $stds); }
        );

        return $playerStds;
    }

    private function infect(Player $mush, Player $target)
    {
        /** @var ?ChargeStatus $sporeStatus */
        $sporeStatus = $mush->getStatusByName(PlayerStatusEnum::SPORES);

        if ($sporeStatus === null) {
            throw new Error('Player should have a spore status');
        }

        if ($sporeStatus->getCharge() > 0) {
            $playerEvent = new PlayerEvent($target, $this->getActionName(), new \DateTime());
            $this->eventService->callEvent($playerEvent, PlayerEvent::INFECTION_PLAYER);

            $sporeStatus->addCharge(-1);
            $this->statusService->persist($sporeStatus);
        }
    }

    private function transmitStd(PlayerDiseaseCollection $stds, Player $target): void
    {
        $draw = $this->randomService->getRandomElements($stds->toArray(), 1);
        $std = reset($draw)->getDiseaseConfig();

        $this->createDiseaseBySexLog($target, $std->getName());

        $this->playerDiseaseService->createDiseaseFromName($std->getName(), $target, $this->getActionName());
    }

    private function createDiseaseBySexLog(PLayer $player, string $disease): void
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
