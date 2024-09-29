<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\HasRole;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\User\Enum\RoleEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Allow admins to suicide themselves.
 */
class Suicide extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::SUICIDE;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        private PlayerServiceInterface $playerService,
    ) {
        parent::__construct($eventService, $actionService, $validator);
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new HasRole(['roles' => [RoleEnum::SUPER_ADMIN], 'groups' => ['visibility']]));
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
        $this->playerService->killPlayer(
            player: $this->player,
            endReason: EndCauseEnum::mapEndCause($this->getTags()),
            time: new \DateTime()
        );
    }
}
