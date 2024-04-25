<?php

namespace Mush\Action\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Action\Actions\AbstractAction;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Error;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Exploration\Service\PlanetServiceInterface;
use Mush\Hunter\Service\HunterServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Project\Entity\Project;
use Mush\RoomLog\Entity\LogParameterInterface;
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

    public function getAction(string $actionName): ?AbstractAction
    {
        if (!isset($this->actions[$actionName])) {
            return null;
        }

        return $this->actions[$actionName];
    }

    public function executeAction(Player $player, int $actionId, array $params): ActionResult
    {
        /** @var Action $action */
        $action = $this->entityManager->getRepository(Action::class)->find($actionId);

        if (!$action) {
            throw new NotFoundHttpException('This action does not exist');
        }

        $actionService = $this->getAction($action->getActionName());

        if (null === $actionService) {
            return new Error('Action do not exist');
        }

        $target = $this->loadActionTarget($params['target']);
        $actionService->loadParameters($action, $player, $target, $params);

        return $actionService->execute();
    }

    private function loadActionTarget(?array $actionTarget): ?LogParameterInterface
    {
        if ($actionTarget !== null) {
            if ($player = $this->getPlayerActionTarget($actionTarget)) {
                return $player;
            }

            if ($equipment = $this->getEquipmentActionTarget($actionTarget)) {
                return $equipment;
            }

            if ($hunter = $this->getHunterActionTarget($actionTarget)) {
                return $hunter;
            }

            if ($planet = $this->getPlanetActionTarget($actionTarget)) {
                return $planet;
            }

            if ($project = $this->getProjectActionTarget($actionTarget)) {
                return $project;
            }
        }

        return null;
    }

    private function getEquipmentActionTarget(array $actionTarget): ?LogParameterInterface
    {
        if (($equipmentId = $actionTarget['door'] ?? null)
            || ($equipmentId = $actionTarget['item'] ?? null)
            || ($equipmentId = $actionTarget['equipment'] ?? null)
            || ($equipmentId = $actionTarget['terminal'] ?? null)
        ) {
            return $this->equipmentService->findById($equipmentId);
        }

        return null;
    }

    private function getPlayerActionTarget(array $actionTarget): ?LogParameterInterface
    {
        if ($playerId = $actionTarget['player'] ?? null) {
            return $this->playerService->findById($playerId);
        }

        return null;
    }

    private function getHunterActionTarget(array $actionTarget): ?LogParameterInterface
    {
        if ($hunterId = $actionTarget['hunter'] ?? null) {
            return $this->hunterService->findById($hunterId);
        }

        return null;
    }

    private function getPlanetActionTarget(array $actionTarget): ?LogParameterInterface
    {
        if ($planetId = $actionTarget['planet'] ?? null) {
            return $this->planetService->findById($planetId);
        }

        return null;
    }

    private function getProjectActionTarget(array $actionTarget): ?LogParameterInterface
    {
        if ($projectId = $actionTarget['project'] ?? null) {
            return $this->entityManager->getRepository(Project::class)->find($projectId);
        }

        return null;
    }
}
