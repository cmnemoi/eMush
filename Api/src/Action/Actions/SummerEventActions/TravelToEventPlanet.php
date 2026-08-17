<?php

declare(strict_types=1);

namespace Mush\Action\Actions\SummerEventActions;

use Mush\Action\Actions\AbstractAction;
use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\ArackPreventsTravel;
use Mush\Action\Entity\ActionResult\Fail;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\ClassConstraint;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\Reach;
use Mush\Chat\Enum\NeronMessageEnum;
use Mush\Chat\Services\NeronMessageServiceInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Exploration\Entity\SpaceCoordinates;
use Mush\Exploration\Enum\PlanetConfigsEnum;
use Mush\Exploration\Service\PlanetServiceInterface;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Enum\HunterEnum;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class TravelToEventPlanet extends AbstractAction
{
    public const ARACK_PREVENTS_TRAVEL = 'arack_prevents_travel';
    public const EMERGENCY_REACTOR_BROKEN = 'emergency_reactor_broken';
    public const OK = 'ok';

    private const POINT_PER_ALIVE_HUMAN_ACTIVE_PLAYER = 4;
    protected ActionEnum $name = ActionEnum::TRAVEL_TO_EVENT_PLANET;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        private StatusServiceInterface $statusService,
        private GameEquipmentServiceInterface $gameEquipmentService,
        private PlanetServiceInterface $planetService,
        private NeronMessageServiceInterface $neronMessageService
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
            new HasStatus([
                'status' => PlayerStatusEnum::FOCUSED,
                'target' => HasStatus::PLAYER,
                'statusTargetName' => EquipmentEnum::COMMAND_TERMINAL,
                'groups' => [ClassConstraint::VISIBILITY],
            ]),
            new HasStatus([
                'status' => DaedalusStatusEnum::CAN_MOVE_TO_EVENT_PLANET,
                'contain' => true,
                'target' => HasStatus::DAEDALUS,
                'groups' => [ClassConstraint::VISIBILITY],
            ]),
            new HasStatus([
                'status' => DaedalusStatusEnum::TRAVELING,
                'target' => HasStatus::DAEDALUS,
                'contain' => false,
                'groups' => ['execute'],
                'message' => ActionImpossibleCauseEnum::DAEDALUS_TRAVELING,
            ]),
        ]);
    }

    public function support(?LogParameterInterface $target, array $parameters = []): bool
    {
        return $target instanceof GameEquipment;
    }

    public static function getActionStatus(Daedalus $daedalus, GameEquipmentServiceInterface $gameEquipmentService): string
    {
        /** @var false|GameEquipment $emergencyReactor */
        $emergencyReactor = $gameEquipmentService->findEquipmentsByNameAndDaedalus(
            name: EquipmentEnum::EMERGENCY_REACTOR,
            daedalus: $daedalus,
        )->first();

        if ($emergencyReactor && $emergencyReactor->isBroken()) {
            return self::EMERGENCY_REACTOR_BROKEN;
        }
        if ($daedalus->getHuntersAroundDaedalus()->getAllHuntersByType(HunterEnum::SPIDER)->count() > 0) {
            return self::ARACK_PREVENTS_TRAVEL;
        }

        return self::OK;
    }

    protected function checkResult(): ActionResult
    {
        $actionStatus = self::getActionStatus($this->player->getDaedalus(), $this->gameEquipmentService);

        return match ($actionStatus) {
            self::ARACK_PREVENTS_TRAVEL => new ArackPreventsTravel(),
            self::EMERGENCY_REACTOR_BROKEN => new Fail(),
            default => new Success(),
        };
    }

    protected function applyEffect(ActionResult $result): void
    {
        if ($result instanceof Fail) {
            return;
        }

        $daedalus = $this->getPlayer()->getDaedalus();

        // temporary : create a normal planet
        $this->createPlanet();
        // add status for the event
        $this->createStatus();
        // remove the ability to move to the event planet
        $this->removeStatus();
        // protect the players a bit
        $this->preventDeadlyMetalPlates();
        $this->preventDiseases();
        $this->preventAnxietyAttacks();

        // add 7 * human alive incident point
        $daedalus->addIncidentPoints($daedalus->getAlivePlayers()->getHumanPlayer()->getActivePlayers()->count() * self::POINT_PER_ALIVE_HUMAN_ACTIVE_PLAYER);
        // move the ship
        $travelLaunchedEvent = new DaedalusEvent(
            daedalus: $this->player->getDaedalus(),
            tags: $this->actionConfig->getActionTags(),
            time: new \DateTime(),
        );

        $this->eventService->callEvent($travelLaunchedEvent, DaedalusEvent::TRAVEL_LAUNCHED);
        // message from neron to signal the ton of incident falling on the ship
        $this->neronMessageService->createNeronMessage(NeronMessageEnum::TRAVEL_TO_EVENT_PLANET, $this->getPlayer()->getDaedalus(), [], new \DateTime());
    }

    private function createPlanet(): void
    {
        $daedalus = $this->getPlayer()->getDaedalus();

        // remove all scanned planets to avoid going to them by accident
        $oldPlanets = $this->planetService->findAllByDaedalus($daedalus);
        $this->planetService->delete($oldPlanets->toArray());

        // make the event planet
        $planet = $this->planetService->createPlanet($this->getPlayer(), PlanetConfigsEnum::SUMMER_EVENT_1, null, 8);
        // set it's coordinates to where the daedalus is going
        $planet->setCoordinates(new SpaceCoordinates($daedalus->getOrientation(), $daedalus->getCombustionChamberFuel()));
        $this->planetService->persist([$planet]);
    }

    private function createStatus(): void
    {
        $daedalus = $this->getPlayer()->getDaedalus();
        $this->statusService->createStatusFromName(
            DaedalusStatusEnum::IN_ORBIT_OF_EVENT_PLANET,
            $daedalus,
            $this->getTags(),
            new \DateTime()
        );
        $this->statusService->createStatusFromName(
            DaedalusStatusEnum::PLANET_IMPOSSIBLE_TO_SCAN,
            $daedalus,
            $this->getTags(),
            new \DateTime()
        );
    }

    private function removeStatus(): void
    {
        $daedalus = $this->getPlayer()->getDaedalus();
        $this->statusService->removeStatus(
            DaedalusStatusEnum::CAN_MOVE_TO_EVENT_PLANET,
            $daedalus,
            $this->getTags(),
            new \DateTime()
        );
    }

    private function preventDeadlyMetalPlates(): void
    {
        $daedalus = $this->getPlayer()->getDaedalus();

        foreach ($daedalus->getAlivePlayers() as $player) {
            if ($player->getHealthPoint() < 14) {
                $status = $this->statusService->createOrIncrementChargeStatus(
                    name: PlayerStatusEnum::SELECTED_FOR_STEEL_PLATE,
                    holder: $player
                );
                $this->statusService->updateCharge(
                    chargeStatus: $status,
                    delta: 2,
                    tags: $this->getTags(),
                    time: new \DateTime(),
                    mode: VariableEventInterface::SET_VALUE
                );
            }
        }
    }

    private function preventDiseases(): void
    {
        $daedalus = $this->getPlayer()->getDaedalus();

        foreach ($daedalus->getAlivePlayers() as $player) {
            $status = $this->statusService->createOrIncrementChargeStatus(
                name: PlayerStatusEnum::SELECTED_FOR_BOARD_DISEASE,
                holder: $player
            );
            $this->statusService->updateCharge(
                chargeStatus: $status,
                delta: 2,
                tags: $this->getTags(),
                time: new \DateTime(),
                mode: VariableEventInterface::SET_VALUE
            );
        }
    }

    private function preventAnxietyAttacks(): void
    {
        $daedalus = $this->getPlayer()->getDaedalus();

        foreach ($daedalus->getAlivePlayers() as $player) {
            $status = $this->statusService->createOrIncrementChargeStatus(
                name: PlayerStatusEnum::SELECTED_FOR_ANXIETY_ATTACK,
                holder: $player
            );
            $this->statusService->updateCharge(
                chargeStatus: $status,
                delta: 2,
                tags: $this->getTags(),
                time: new \DateTime(),
                mode: VariableEventInterface::SET_VALUE
            );
        }
    }
}
