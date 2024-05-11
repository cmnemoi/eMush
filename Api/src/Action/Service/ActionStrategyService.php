<?php

namespace Mush\Action\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Action\Actions\AbstractAction;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Entity\ActionProviderInterface;
use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Exploration\Entity\Planet;
use Mush\Exploration\Service\PlanetServiceInterface;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Service\HunterServiceInterface;
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

        $actionService->loadParameters($actionConfig, $actionProvider, $player, $target, $params);

        return $actionService->execute();
    }

    private function loadGameEntity(?array $entityParameters): null|ActionProviderInterface|LogParameterInterface
    {
        if ($entityParameters === null) {
            return null;
        }

        $className = $entityParameters['className'];
        $entityId = $entityParameters['id'];
        if ($entityId === null) {
            return null;
        }

        $this->entityManager->getRepository($className)->find($entityId);

            case Status::class:
                return $this->entityManager->getRepository(Status::class)->find($entityId);

            default:
                return null;
        }
    }
}
