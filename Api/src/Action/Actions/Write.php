<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\Mechanic;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Status\Entity\ContentStatus;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class Write extends AbstractAction
{
    protected string $name = ActionEnum::WRITE;
    protected GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        GameEquipmentServiceInterface $gameEquipmentService,
        StatusServiceInterface $statusService
    ) {
        parent::__construct($eventService, $actionService, $validator);

        $this->gameEquipmentService = $gameEquipmentService;
        $this->statusService = $statusService;
    }

    protected function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameItem;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new HasStatus([
            'status' => PlayerStatusEnum::FOCUSED,
            'target' => HasStatus::PLAYER,
            'statusTargetName' => ToolItemEnum::BLOCK_OF_POST_IT,
            'groups' => ['visibility'],
        ]));
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $time = new \DateTime();

        $postIt = $this->gameEquipmentService->createGameEquipmentFromName(
            ItemEnum::POST_IT,
            $this->player,
            $this->getAction()->getActionTags(),
            $time,
            VisibilityEnum::HIDDEN
        );

        $params = $this->getParameters();
        $content = array_key_exists('content', $params) ? $params['content'] : null;

        /** @var ContentStatus $contentStatus */
        $contentStatus = $this->statusService->createStatusFromName(
            EquipmentStatusEnum::DOCUMENT_CONTENT,
            $postIt,
            $this->getAction()->getActionTags(),
            new \DateTime(),
        );

        $contentStatus->setContent($content);
    }
}
