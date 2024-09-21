<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\HasRole;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\User\Enum\RoleEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Allow admins to kill a player.
 */
final class KillPlayer extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::KILL_PLAYER;
    protected GameEquipmentServiceInterface $gameEquipmentService;
    protected RandomServiceInterface $randomService;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validationService,
        private PlayerServiceInterface $playerService,
    ) {
        parent::__construct($eventService, $actionService, $validationService);
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new HasRole(['roles' => [RoleEnum::SUPER_ADMIN], 'groups' => ['visibility']]));
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof Player;
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $this->playerService->killPlayer(
            player: $this->playerTarget(),
            endReason: EndCauseEnum::QUARANTINE,
            time: new \DateTime()
        );
    }
}
