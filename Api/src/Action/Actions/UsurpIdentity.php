<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\HasRole;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Repository\PlayerRepositoryInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\User\Enum\RoleEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class UsurpIdentity extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::USURP_IDENTITY;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        private PlayerRepositoryInterface $playerRepository,
    ) {
        parent::__construct($eventService, $actionService, $validator);
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
        $target = $this->playerTarget();
        $playerUser = $this->player->getUser();
        $targetUser = $target->getUser();

        $this->player->getPlayerInfo()->updateUser($targetUser);
        $target->getPlayerInfo()->updateUser($playerUser);

        $this->playerRepository->save($this->player);
        $this->playerRepository->save($target);

        $result->addDetail('playerId', $target->getId());
    }
}
