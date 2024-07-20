<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\ProjectFinished;
use Mush\Action\Validator\Reach;
use Mush\Daedalus\Repository\NeronRepositoryInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Project\Enum\ProjectName;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ToggleMagneticNet extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::TOGGLE_MAGNETIC_NET;

    public function __construct(
        protected EventServiceInterface $eventService,
        protected ActionServiceInterface $actionService,
        protected ValidatorInterface $validator,
        private NeronRepositoryInterface $neronRepository
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
        $metadata->addConstraint(
            new ProjectFinished([
                'project' => ProjectName::MAGNETIC_NET,
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
        $neron = $this->player->getDaedalus()->getNeron();
        $neron->toggleMagneticNet();

        $this->neronRepository->save($neron);
    }
}
