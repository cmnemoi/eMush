<?php

namespace Mush\Action\Actions;

use Error;
use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\MushSpore;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\AbstractQuantityEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class implementing the "Phagocyte" action.
 *
 * For 0 PA, A Mush Can Consume one spore to gain 4 action points and 4 health points
 *
 * More info : http://mushpedia.com/wiki/Phagocyte
 */
class Phagocyte extends AbstractAction
{
    protected string $name = ActionEnum::PHAGOCYTE;

    private StatusServiceInterface $statusService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        StatusServiceInterface $statusService
    ) {
        parent::__construct($eventDispatcher, $actionService, $validator);

        $this->statusService = $statusService;
    }

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter === null;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new HasStatus(['status' => PlayerStatusEnum::MUSH, 'target' => HasStatus::PLAYER, 'groups' => ['visibility']]));
        $metadata->addConstraint(new MushSpore(['groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::PHAGOCYTE_NO_SPORE]));
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        /** @var ?ChargeStatus $sporeStatus */
        $sporeStatus = $this->player->getStatusByName(PlayerStatusEnum::SPORES);
        if ($sporeStatus === null) {
            throw new Error('Player should have a spore status');
        }

        // Consume one spore from the player
        $this->statusService->updateCharge($sporeStatus, -1);

        // The Player gains 4 :hp:
        $healthPointGainEvent = new PlayerVariableEvent(
            $this->player,
            PlayerVariableEnum::HEALTH_POINT,
            4,
            $this->getActionName(),
            new \DateTime()
        );
        $healthPointGainEvent->setVisibility(VisibilityEnum::PRIVATE);
        $this->eventService->dispatch($healthPointGainEvent, AbstractQuantityEvent::CHANGE_VARIABLE);

        // The Player gains 4 :pa:
        $actionPointGainEvent = new PlayerVariableEvent(
            $this->player,
            PlayerVariableEnum::ACTION_POINT,
            4,
            $this->getActionName(),
            new \DateTime()
        );
        $actionPointGainEvent->setVisibility(VisibilityEnum::PRIVATE);
        $this->eventService->dispatch($actionPointGainEvent, AbstractQuantityEvent::CHANGE_VARIABLE);
    }
}
