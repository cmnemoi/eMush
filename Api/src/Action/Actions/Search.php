<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Fail;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\IsRoom;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Event\Service\EventService;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Event\StatusEvent;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Search extends AbstractAction
{
    protected string $name = ActionEnum::SEARCH;

    private StatusServiceInterface $statusService;

    public function __construct(
        EventService $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        StatusServiceInterface $statusService
    ) {
        parent::__construct(
            $eventService,
            $actionService,
            $validator
        );

        $this->statusService = $statusService;
    }

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter === null;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new IsRoom(['groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::NOT_A_ROOM]));
    }

    protected function applyEffects(): ActionResult
    {
        $hiddenItems = $this->player
            ->getPlace()
            ->getEquipments()
            ->filter(
                fn (GameEquipment $gameEquipment) => ($gameEquipment->getStatusByName(EquipmentStatusEnum::HIDDEN) !== null)
            )
        ;

        if (!$hiddenItems->isEmpty()) {
            /** @var GameItem $mostRecentHiddenItem */
            $mostRecentHiddenItem = $this->statusService
                ->getMostRecent(EquipmentStatusEnum::HIDDEN, $hiddenItems)
            ;

            if (!($hiddenStatus = $mostRecentHiddenItem->getStatusByName(EquipmentStatusEnum::HIDDEN)) ||
                !($hiddenBy = $hiddenStatus->getTarget()) ||
                !$hiddenBy instanceof Player
            ) {
                throw new \LogicException('invalid hidden status');
            }

            $itemFound = $mostRecentHiddenItem;

            $statusEvent = new StatusEvent(
                $hiddenStatus->getName(),
                $itemFound,
                $this->getActionName(),
                new \DateTime()
            );
            $statusEvent->setStatusTarget($hiddenBy);
            $this->eventService->callEvent($statusEvent, StatusEvent::STATUS_REMOVED);

            $success = new Success();

            return $success->setEquipment($itemFound);
        } else {
            return new Fail();
        }
    }
}
