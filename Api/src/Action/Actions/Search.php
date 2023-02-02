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
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Event\StatusEvent;
use Mush\Status\Service\StatusServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Search extends AbstractAction
{
    protected string $name = ActionEnum::SEARCH;

    private StatusServiceInterface $statusService;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        LoggerInterface $logger,
        StatusServiceInterface $statusService
    ) {
        parent::__construct(
            $eventService,
            $actionService,
            $validator,
            $logger
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

    protected function checkResult(): ActionResult
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
                $errorMessage = "Search::checkResult() - hidden item should have an hidden status, this status should have target or the item should be hidden by a player";
                $this->logger->error($errorMessage,
                    [
                        'daedalus' => $this->player->getDaedalus()->getId(),
                        'player' => $this->player->getId(),
                        'hiddenItem' => $mostRecentHiddenItem->getId(),
                        'hiddenStatus' => $hiddenStatus->getId(),
                        'hiddenBy' => $hiddenBy->getId()
                    ]
                );
                throw new \LogicException($errorMessage);
            }

            $itemFound = $mostRecentHiddenItem;

            $success = new Success();

            return $success->setEquipment($itemFound);
        } else {
            return new Fail();
        }
    }

    protected function applyEffect(ActionResult $result): void
    {
        if ($result instanceof Fail) {
            return;
        }

        $hiddenItem = $result->getEquipment();

        if ($hiddenItem === null) {
            $errorMessage = "Search::applyEffect() - action should have an hidden item";
            $this->logger->error($errorMessage,
                [
                    'daedalus' => $this->player->getDaedalus()->getId(),
                    'player' => $this->player->getId(),
                ]
            );
            throw new \LogicException($errorMessage);
        }

        $hiddenStatus = $hiddenItem->getStatusByName(EquipmentStatusEnum::HIDDEN);

        if ($hiddenStatus === null) {
            $errorMessage = "Search::applyEffect() - hidden item should have an hidden status";
            $this->logger->error($errorMessage,
                [
                    'daedalus' => $this->player->getDaedalus()->getId(),
                    'player' => $this->player->getId(),
                    'hiddenItem' => $hiddenItem->getId(),
                ]
            );
            throw new \LogicException($errorMessage);
        }

        $hiddenBy = $hiddenStatus->getTarget();

        $statusEvent = new StatusEvent(
            $hiddenItem->getName(),
            $hiddenItem,
            $this->getAction()->getActionTags(),
            new \DateTime(),
        );
        $statusEvent->setStatusTarget($hiddenBy);
        $this->eventService->callEvent($statusEvent, StatusEvent::STATUS_REMOVED);
    }
}
