<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Fail;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameter;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Specification\Mechanic;
use Mush\Action\Specification\Reach;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Player\Entity\Modifier;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Enum\ModifierTargetEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Shower extends AbstractAction
{
    protected string $name = ActionEnum::SHOWER;

    /** @var GameEquipment */
    protected $parameter;

    private PlayerServiceInterface $playerService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        PlayerServiceInterface $playerService,
        ActionServiceInterface $actionService
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService
        );
        $this->playerService = $playerService;
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

    public function cannotExecuteReason(): ?string
    {
        if ($this->parameter->isBroken()) {
            return ActionImpossibleCauseEnum::BROKEN_EQUIPMENT;
        }

        return parent::cannotExecuteReason();
    }

    protected function applyEffects(): ActionResult
    {
        if ($dirty = $this->player->getStatusByName(PlayerStatusEnum::DIRTY)) {
            $this->player->removeStatus($dirty);
        }

        if ($this->player->isMush()) {
            $actionModifier = new Modifier();
            $actionModifier
                ->setDelta(-3)
                ->setTarget(ModifierTargetEnum::HEALTH_POINT)
            ;
            $playerEvent = new PlayerEvent($this->player);
            $playerEvent->setReason(EndCauseEnum::CLUMSINESS);
            $playerEvent->setModifier($actionModifier);
            $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::MODIFIER_PLAYER);
        }

        $this->playerService->persist($this->player);

        //@Hack: Mush 'fails' the shower to get different log
        return $this->player->isMush() ? new Fail() : new Success();
    }
}
