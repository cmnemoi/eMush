<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameter;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Specification\Mechanic;
use Mush\Action\Specification\Reach;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Repair extends AttemptAction
{
    protected string $name = ActionEnum::REPAIR;

    /** @var GameEquipment */
    protected $parameter;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private PlayerServiceInterface $playerService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        GameEquipmentServiceInterface $gameEquipmentService,
        PlayerServiceInterface $playerService,
        RandomServiceInterface $randomService,
        ActionServiceInterface $actionService
    ) {
        parent::__construct(
            $randomService,
            $eventDispatcher,
            $actionService
        );

        $this->gameEquipmentService = $gameEquipmentService;
        $this->playerService = $playerService;
        $this->randomService = $randomService;
    }

    protected function support(?ActionParameter $parameter): bool
    {
        return $parameter instanceof GameEquipment;
    }

    protected function getVisibilitySpecifications(): array
    {
        return [
            Reach::class => null,
        ];
    }

    public function isVisible(): bool
    {
        return parent::isVisible() &&
            $this->parameter->isBroken()
            ;
    }

    protected function applyEffects(): ActionResult
    {
        $response = $this->makeAttempt();

        if ($response instanceof Success &&
            ($brokenStatus = $this->parameter->getStatusByName(EquipmentStatusEnum::BROKEN))
        ) {
            $this->parameter->removeStatus($brokenStatus);
            $this->gameEquipmentService->persist($this->parameter);
        }

        $this->playerService->persist($this->player);

        return $response;
    }
}
