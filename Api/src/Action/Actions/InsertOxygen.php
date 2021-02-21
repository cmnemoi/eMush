<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameter;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Equipment\Service\GearToolServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class InsertOxygen extends AbstractAction
{
    protected string $name = ActionEnum::INSERT_OXYGEN;

    /** @var GameItem */
    protected $parameter;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private DaedalusServiceInterface $daedalusService;
    private GearToolServiceInterface $gearToolService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        GameEquipmentServiceInterface $gameEquipmentService,
        DaedalusServiceInterface $daedalusService,
        ActionServiceInterface $actionService,
        GearToolServiceInterface $gearToolService
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService
        );

        $this->gameEquipmentService = $gameEquipmentService;
        $this->daedalusService = $daedalusService;
        $this->gearToolService = $gearToolService;
    }

    protected function support(?ActionParameter $parameter): bool
    {
        return $parameter instanceof GameItem;
    }

    public function isVisible(): bool
    {
        $gameConfig = $this->player->getDaedalus()->getGameConfig();

        return $this->player->canReachEquipment($this->parameter) &&
            $this->parameter->getEquipment()->getName() === ItemEnum::OXYGEN_CAPSULE &&
            $this->gearToolService->getUsedTool($this->player, $this->action->getName()) !== null &&
            $this->player->getDaedalus()->getOxygen() < $gameConfig->getDaedalusConfig()->getMaxOxygen() &&
            parent::isVisible()
        ;
    }

    protected function applyEffects(): ActionResult
    {
        $this->parameter->setPlayer(null);

        $this->gameEquipmentService->delete($this->parameter);

        $this->daedalusService->changeOxygenLevel($this->player->getDaedalus(), 1);

        return new Success();
    }
}
