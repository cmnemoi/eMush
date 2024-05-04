<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Fail;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\PlaceType;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Search extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::SEARCH;

    private StatusServiceInterface $statusService;

    public function __construct(
        EventServiceInterface $eventService,
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

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new PlaceType(['groups' => ['execute'], 'type' => 'room', 'message' => ActionImpossibleCauseEnum::NOT_A_ROOM]));
    }

    protected function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target === null;
    }

    protected function checkResult(): ActionResult
    {
        $hiddenItems = $this->player
            ->getPlace()
            ->getEquipments()
            ->filter(
                static fn (GameEquipment $gameEquipment) => ($gameEquipment->getStatusByName(EquipmentStatusEnum::HIDDEN) !== null)
            );

        if (!$hiddenItems->isEmpty()) {
            /** @var GameItem $mostRecentHiddenItem */
            $mostRecentHiddenItem = $this->statusService
                ->getMostRecent(EquipmentStatusEnum::HIDDEN, $hiddenItems);

            if (!($hiddenStatus = $mostRecentHiddenItem->getStatusByName(EquipmentStatusEnum::HIDDEN))
                || !($hiddenBy = $hiddenStatus->getTarget())
                || !$hiddenBy instanceof Player
            ) {
                throw new \LogicException('invalid hidden status');
            }

            $itemFound = $mostRecentHiddenItem;

            $success = new Success();

            return $success->setEquipment($itemFound);
        }

        return new Fail();
    }

    protected function applyEffect(ActionResult $result): void
    {
        if ($result instanceof Fail) {
            return;
        }

        $hiddenItem = $result->getEquipment();

        if ($hiddenItem === null) {
            throw new \LogicException('action should have an hidden item');
        }

        /** @var Status $hiddenStatus */
        $hiddenStatus = $hiddenItem->getStatusByName(EquipmentStatusEnum::HIDDEN);

        $this->statusService->removeStatus(
            EquipmentStatusEnum::HIDDEN,
            $hiddenItem,
            $this->getActionConfig()->getActionTags(),
            new \DateTime(),
        );
    }
}
