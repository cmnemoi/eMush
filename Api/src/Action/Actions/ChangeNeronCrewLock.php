<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\Reach;
use Mush\Daedalus\Enum\NeronCrewLockEnum;
use Mush\Daedalus\UseCase\ChangeNeronCrewLockUseCase;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ChangeNeronCrewLock extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::CHANGE_NERON_CREW_LOCK;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        private ChangeNeronCrewLockUseCase $changeNeronCrewLock,
    ) {
        parent::__construct($eventService, $actionService, $validator);
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(
            new HasStatus([
                'status' => PlayerStatusEnum::FOCUSED,
                'target' => HasStatus::PLAYER,
                'statusTargetName' => EquipmentEnum::BIOS_TERMINAL,
                'groups' => ['visibility'],
            ])
        );
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameEquipment;
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $actionParameters = $this->getParameters();
        $newCrewLock = ($actionParameters && \array_key_exists('crewLock', $actionParameters)) ? $actionParameters['crewLock'] : NeronCrewLockEnum::NULL->value;

        $neron = $this->player->getDaedalus()->getNeron();

        $this->changeNeronCrewLock->execute($neron, NeronCrewLockEnum::fromValue($newCrewLock));
    }
}
