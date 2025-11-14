<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\HasRole;
use Mush\Action\Validator\IsExploring;
use Mush\Exploration\Event\ExplorationEvent;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\User\Enum\RoleEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ForceExplorationStep extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::FORCE_EXPLORATION_STEP;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
    ) {
        parent::__construct($eventService, $actionService, $validator);
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new HasRole(['roles' => [RoleEnum::SUPER_ADMIN], 'groups' => ['visibility']]));
        $metadata->addConstraint(new IsExploring(['groups' => ['visibility']]));
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target === null;
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $exploration = $this->player->getExplorationOrThrow();
        $event = new ExplorationEvent($exploration, [EventEnum::NEW_CYCLE], new \DateTime(), $this->player);
        $this->eventService->callEvent($event, ExplorationEvent::EXPLORATION_NEW_CYCLE);
    }
}
