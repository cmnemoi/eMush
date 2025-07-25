<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\AllPlanetSectorsVisited;
use Mush\Action\Validator\ExplorationAlreadyOngoing;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\NeronCrewLock;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\SpaceShip;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Exploration\Service\ExplorationServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\DaedalusStatusEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TakeoffToPlanet extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::TAKEOFF_TO_PLANET;

    private ExplorationServiceInterface $explorationService;
    private RandomServiceInterface $randomService;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        ExplorationServiceInterface $explorationService,
        RandomServiceInterface $randomService
    ) {
        parent::__construct($eventService, $actionService, $validator);
        $this->explorationService = $explorationService;
        $this->randomService = $randomService;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new HasStatus([
            'status' => DaedalusStatusEnum::IN_ORBIT,
            'target' => HasStatus::DAEDALUS,
            'contain' => true,
            'groups' => ['visibility'],
        ]));
        $metadata->addConstraint(new HasStatus([
            'status' => DaedalusStatusEnum::TRAVELING,
            'target' => HasStatus::DAEDALUS,
            'contain' => false,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::DAEDALUS_TRAVELING,
        ]));
        $metadata->addConstraint(new AllPlanetSectorsVisited([
            'message' => ActionImpossibleCauseEnum::EXPLORE_NOTHING_LEFT,
            'groups' => ['execute'],
        ]));
        $metadata->addConstraint(new ExplorationAlreadyOngoing([
            'message' => ActionImpossibleCauseEnum::EXPLORATION_ALREADY_ONGOING,
            'groups' => ['execute'],
        ]));
        $metadata->addConstraint(new NeronCrewLock([
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::TERMINAL_NERON_LOCK,
        ]));
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof SpaceShip;
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        /** @var SpaceShip $explorationShip */
        $explorationShip = $this->target;

        // draw explorators from the players in exploration craft place to avoid all crewmates
        // to participate and be overpowered
        $playersInRoom = $explorationShip->getPlace()->getPlayers()->getPlayerAlive();

        $explorators = $this->randomService->getRandomElements(
            $playersInRoom->toArray(),
            min($this->getOutputQuantity(), $explorationShip->getPlace()->getNumberOfPlayersAlive())
        );

        $this->explorationService->createExploration(
            players: new PlayerCollection($explorators),
            explorationShip: $explorationShip,
            numberOfSectorsToVisit: $explorationShip->getEquipment()->getNumberOfExplorationSteps(),
            reasons: $this->actionConfig->getActionTags(),
        );
    }
}
