<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameter;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Specification\Mechanic;
use Mush\Action\Specification\Reach;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Book;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ReadBook extends AbstractAction
{
    protected string $name = ActionEnum::READ_BOOK;

    /** @var GameItem */
    protected $parameter;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private PlayerServiceInterface $playerService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        GameEquipmentServiceInterface $gameEquipmentService,
        PlayerServiceInterface $playerService,
        ActionServiceInterface $actionService
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService
        );

        $this->gameEquipmentService = $gameEquipmentService;
        $this->playerService = $playerService;
    }

    protected function support(?ActionParameter $parameter): bool
    {
        return $parameter instanceof GameItem;
    }

    protected function getVisibilitySpecifications(): array
    {
        return [
            Mechanic::class => [Mechanic::PARAMETER_KEY => EquipmentMechanicEnum::BOOK],
            Reach::class => null,
        ];
    }

    public function cannotExecuteReason(): ?string
    {
        //@TODO add conditions player already have the skill and player already read a book

        return parent::cannotExecuteReason();
    }

    protected function applyEffects(): ActionResult
    {
        /** @var Book $bookType */
        $bookType = $this->parameter->getEquipment()->getMechanicByName(EquipmentMechanicEnum::BOOK);
        $this->player->addSkill($bookType->getSkill());

        $equipmentEvent = new EquipmentEvent($this->parameter, VisibilityEnum::HIDDEN);
        $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);

        $this->playerService->persist($this->player);

        return new Success();
    }
}
