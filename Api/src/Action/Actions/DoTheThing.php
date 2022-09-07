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
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ReachEnum;
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
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\StatusEvent;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DoTheThing extends AbstractAction
{
    public const BASE_CONFORT = 2;
    public const PREGNANCY_RATE = 8;

    protected string $name = ActionEnum::DO_THE_THING;

    private StatusServiceInterface $statusService;
    private PlayerVariableServiceInterface $playerVariableService;
    private RandomServiceInterface $randomService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        PlayerVariableServiceInterface $playerVariableService,
        RandomServiceInterface $randomService,
        StatusServiceInterface $statusService
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService,
            $validator
        );

        $this->playerVariableService = $playerVariableService;
        $this->statusService = $statusService;
        $this->randomService = $randomService;
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

        // @TODO if one is sick (GastroEntérite, Eruption cutanée ou Grippe ), give sickness

        // may become pregnant
        $becomePregnant = $this->randomService->isSuccessful(self::PREGNANCY_RATE);
        if ($becomePregnant) {
            $femalePlayer = CharacterEnum::isMale($player->getCharacterConfig()->getName()) ? $parameter : $player;
            $pregnantStatus = new StatusEvent(
                PlayerStatusEnum::PREGNANT,
                $femalePlayer,
                $this->getActionName(),
                new \DateTime()
            );
            $pregnantStatus->setVisibility(VisibilityEnum::PRIVATE);

            $this->eventDispatcher->dispatch($pregnantStatus, StatusEvent::STATUS_APPLIED);
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
        $this->eventDispatcher->dispatch($playerModifierEvent, AbstractQuantityEvent::CHANGE_VARIABLE);

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

        $this->eventDispatcher->dispatch($statusEvent, StatusEvent::STATUS_APPLIED);
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
            $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::INFECTION_PLAYER);

            $sporeStatus->addCharge(-1);
            $this->statusService->persist($sporeStatus);
        }
    }
}
