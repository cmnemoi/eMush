<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\PlaceType;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Write extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::WRITE;
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

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new HasStatus([
            'status' => PlayerStatusEnum::FOCUSED,
            'target' => HasStatus::PLAYER,
            'statusTargetName' => ToolItemEnum::BLOCK_OF_POST_IT,
            'groups' => ['visibility'],
        ]));
        $metadata->addConstraint(new PlaceType(['groups' => ['execute'], 'type' => 'planet', 'allowIfTypeMatches' => false, 'message' => ActionImpossibleCauseEnum::ON_PLANET]));
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameItem;
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $postIt = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::POST_IT,
            equipmentHolder: $this->player,
            reasons: $this->getActionConfig()->getActionTags(),
            time: new \DateTime(),
            visibility: VisibilityEnum::HIDDEN
        );

        $this->statusService->createContentStatus(
            content: $this->getPostItContent(),
            holder: $postIt,
            tags: $this->getActionConfig()->getActionTags(),
        );
    }

    private function getPostItContent(): string
    {
        $params = $this->getParameters();

        return ($params && \array_key_exists('content', $params)) ? $params['content'] : '';
    }
}
