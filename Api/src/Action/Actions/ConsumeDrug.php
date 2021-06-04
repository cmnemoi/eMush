<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameter;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\Status;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Drug;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Service\EquipmentEffectServiceInterface;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ConsumeDrug extends Consume
{
    protected string $name = ActionEnum::CONSUME_DRUG;

    private StatusServiceInterface $statusService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        PlayerServiceInterface $playerService,
        EquipmentEffectServiceInterface $equipmentServiceEffect,
        StatusServiceInterface $statusService
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService,
            $validator,
            $playerService,
            $equipmentServiceEffect,
        );
        $this->statusService = $statusService;
    }

    protected function support(?ActionParameter $parameter): bool
    {
        return $parameter instanceof GameItem;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Status([
            'status' => PlayerStatusEnum::DRUG_EATEN,
            'contain' => false,
            'target' => Status::PLAYER,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::CONSUME_DRUG_TWICE,
        ]));
    }

    protected function applyEffects(): ActionResult
    {
        /** @var GameItem $parameter */
        $parameter = $this->parameter;

        /** @var Drug $drugMechanic */
        $drugMechanic = $parameter->getEquipment()->getMechanicByName(EquipmentMechanicEnum::DRUG);

        if (null === $drugMechanic) {
            throw new \Exception('Cannot consume this equipment');
        }

        // @TODO add disease, cures and extra effects
        $equipmentEffect = $this->equipmentServiceEffect->getConsumableEffect($drugMechanic, $this->player->getDaedalus());

        if (!$this->player->isMush()) {
            $this->dispatchConsumableEffects($equipmentEffect);
            $this->statusService
                ->createChargeStatus(
                    PlayerStatusEnum::DRUG_EATEN,
                    $this->player,
                    ChargeStrategyTypeEnum::CYCLE_DECREMENT,
                    null,
                    VisibilityEnum::HIDDEN,
                    VisibilityEnum::HIDDEN,
                    1,
                    0,
                    true
                );
        } else {
            $this->dispatchMushEffect();
        }

        $this->playerService->persist($this->player);

        // if no charges consume equipment
        $equipmentEvent = new EquipmentEvent($parameter, VisibilityEnum::HIDDEN, new \DateTime());
        $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);

        return new Success();
    }
}
