<?php

namespace Mush\Action\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Action\Actions\AbstractAction;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Entity\ActionProviderInterface;
use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Exploration\Entity\Planet;
use Mush\Exploration\Service\PlanetServiceInterface;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Service\HunterServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Project\Entity\Project;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Entity\Status;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ActionStrategyService implements ActionStrategyServiceInterface
{
    private array $actions = [];
    private PlayerServiceInterface $playerService;
    private GameEquipmentServiceInterface $equipmentService;
    private HunterServiceInterface $hunterService;
    private PlanetServiceInterface $planetService;
    private EntityManagerInterface $entityManager;

    public function __construct(
        PlayerServiceInterface $playerService,
        GameEquipmentServiceInterface $equipmentService,
        HunterServiceInterface $hunterService,
        PlanetServiceInterface $planetService,
        EntityManagerInterface $entityManager
    ) {
        $this->playerService = $playerService;
        $this->equipmentService = $equipmentService;
        $this->hunterService = $hunterService;
        $this->planetService = $planetService;
        $this->entityManager = $entityManager;
    }

    public function addAction(AbstractAction $action): void
    {
        $this->actions[$action->getActionName()] = $action;
    }

    public function getAction(ActionEnum $actionName): ?AbstractAction
    {
        if (!isset($this->actions[$actionName->value])) {
            return null;
        }

        return $this->actions[$actionName->value];
    }

    public function executeAction(Player $player, int $actionId, array $params): ActionResult
    {
        /** @var ActionConfig $actionConfig */
        $actionConfig = $this->entityManager->getRepository(ActionConfig::class)->find($actionId);

        if (!$actionConfig) {
            throw new NotFoundHttpException('This actionConfig does not exist');
        }

        $actionName = $actionConfig->getActionName();
        $actionService = $this->getAction($actionName);

        if (null === $actionService) {
            throw new \Exception("this action is not implemented ({$actionName->value})");
        }

        /** @var ?LogParameterInterface $target */
        $target = $this->loadGameEntity($params['target']);

        /** @var ActionProviderInterface $actionProvider */
        $actionProvider = $this->loadGameEntity($params['actionProvider']);

        $action = new Action();
        $action->setActionConfig($actionConfig)->setActionProvider($actionProvider);

        $actionService->loadParameters($action, $player, $target, $params);

        return $actionService->execute();
    }

    private function loadGameEntity(?array $entityParameters): null|ActionProviderInterface|LogParameterInterface
    {
        if ($entityParameters !== null) {
            if ($player = $this->getPlayerEntity($entityParameters)) {
                return $player;
            }

            if ($equipment = $this->getEquipmentEntity($entityParameters)) {
                return $equipment;
            }

            if ($hunter = $this->getHunterEntity($entityParameters)) {
                return $hunter;
            }

            if ($planet = $this->getPlanetEntity($entityParameters)) {
                return $planet;
            }

            if ($project = $this->getProjectEntity($entityParameters)) {
                return $project;
            }
            if ($status = $this->getStatusEntity($entityParameters)) {
                return $status;
            }

            if ($place = $this->getPlaceEntity($entityParameters)) {
                return $place;
            }
        }

        return null;
    }

    private function getEquipmentEntity(array $entityParameter): ?GameEquipment
    {
        if (($equipmentId = $entityParameter['door'] ?? null)
            || ($equipmentId = $entityParameter['item'] ?? null)
            || ($equipmentId = $entityParameter['equipment'] ?? null)
            || ($equipmentId = $entityParameter['terminal'] ?? null)
        ) {
            return $this->equipmentService->findById($equipmentId);
        }

        return null;
    }

    private function getPlayerEntity(array $entityParameter): ?Player
    {
        if ($playerId = $entityParameter['player'] ?? null) {
            return $this->playerService->findById($playerId);
        }

        return null;
    }

    private function getHunterEntity(array $entityParameter): ?Hunter
    {
        if ($hunterId = $entityParameter['hunter'] ?? null) {
            return $this->hunterService->findById($hunterId);
        }

        return null;
    }

    private function getPlanetEntity(array $entityParameter): ?Planet
    {
        if ($planetId = $entityParameter['planet'] ?? null) {
            return $this->planetService->findById($planetId);
        }

        return null;
    }

    private function getProjectEntity(array $entityParameter): ?Project
    {
        if ($projectId = $entityParameter['project'] ?? null) {
            return $this->entityManager->getRepository(Project::class)->find($projectId);
        }

        return null;
    }

    private function getPlaceEntity(array $entityParameter): ?Place
    {
        if ($placeId = $entityParameter['place'] ?? null) {
            return $this->entityManager->getRepository(Place::class)->find($placeId);
        }

        return null;
    }

    private function getStatusEntity(array $entityParameter): ?Status
    {
        if ($statusId = $entityParameter['status'] ?? null) {
            return $this->entityManager->getRepository(Status::class)->find($statusId);
        }

        return null;
    }
}
