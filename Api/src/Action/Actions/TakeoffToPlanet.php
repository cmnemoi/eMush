<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\AllPlanetSectorsVisited;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Exploration\Service\ExplorationServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\DaedalusStatusEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class TakeoffToPlanet extends AbstractAction
{
    protected string $name = ActionEnum::TAKEOFF_TO_PLANET;

    private const NUMBER_OF_EXPLORATORS = 4;
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

    protected function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameEquipment;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new HasStatus([
            'status' => DaedalusStatusEnum::TRAVELING,
            'target' => HasStatus::DAEDALUS,
            'contain' => false,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::DAEDALUS_TRAVELING,
        ]));
        $metadata->addConstraint(new HasStatus([
            'status' => DaedalusStatusEnum::IN_ORBIT,
            'target' => HasStatus::DAEDALUS,
            'contain' => true,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::EXPLORE_NOT_IN_ORBIT,
        ]));
        $metadata->addConstraint(new AllPlanetSectorsVisited([
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::EXPLORE_NOTHING_LEFT,
        ]));
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        /** @var GameEquipment $icarus */
        $icarus = $this->target;

        // draw explorators from the players in exploration craft place to avoid all crewmates
        // to participate and be overpowered
        $explorators = $this->randomService->getRandomElements(
            $icarus->getPlace()->getPlayers()->getPlayerAlive()->toArray(),
            self::NUMBER_OF_EXPLORATORS
        );

        $this->explorationService->createExploration(
            players: new PlayerCollection($explorators),
            explorationShip: $icarus,
            numberOfSectorsToVisit: $this->getOutputQuantity(),
            reasons: $this->action->getActionTags(),
        );
    }
}
