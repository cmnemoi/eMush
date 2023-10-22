<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\HasRole;
use Mush\Exploration\Service\ExplorationServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\User\Enum\RoleEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class Teleport extends AbstractAction
{
    protected string $name = 'teleport';

    private ExplorationServiceInterface $explorationService;
    private PlayerServiceInterface $playerService;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        ExplorationServiceInterface $explorationService,
        PlayerServiceInterface $playerService
    ) {
        parent::__construct(
            $eventService,
            $actionService,
            $validator
        );
        $this->explorationService = $explorationService;
        $this->playerService = $playerService;
    }

    protected function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target === null;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new HasRole(['roles' => [RoleEnum::SUPER_ADMIN, RoleEnum::ADMIN], 'groups' => ['visibility']]));
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $exploration = $this->player->getExploration();
        if ($exploration === null) {
            throw new \RuntimeException('You need to be in an exploration to teleport to Icarus Bay');
        }

        $this->explorationService->closeExploration(
            exploration: $exploration,
            reasons: $this->action->getActionTags()
        );
    }
}
