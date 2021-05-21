<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameter;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\Fuel;
use Mush\Action\Validator\ParameterName;
use Mush\Action\Validator\Reach;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class InsertFuel extends AbstractAction
{
    protected string $name = ActionEnum::INSERT_FUEL;
    private GameEquipmentServiceInterface $gameEquipmentService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        GameEquipmentServiceInterface $gameEquipmentService,
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService,
            $validator
        );

        $this->gameEquipmentService = $gameEquipmentService;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraints([
            new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]),
            new ParameterName(['name' => ItemEnum::FUEL_CAPSULE, 'groups' => ['visibility']]),
            new Fuel(['retrieve' => false, 'groups' => ['visibility']]),
        ]);
    }

    protected function support(?ActionParameter $parameter): bool
    {
        return $parameter instanceof GameItem;
    }

    protected function applyEffects(): ActionResult
    {
        /** @var GameItem $item */
        $item = $this->getParameter();

        $item->setPlayer(null);
        $this->gameEquipmentService->delete($item);

        $daedalusEvent = new DaedalusEvent($this->player->getDaedalus(), new \DateTime());
        $daedalusEvent->setQuantity(1);
        $this->eventDispatcher->dispatch($daedalusEvent, DaedalusEvent::CHANGE_FUEL);

        return new Success();
    }
}
