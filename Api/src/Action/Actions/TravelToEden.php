<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Validator\ClassConstraint;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\ProjectFinished;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Project\Enum\ProjectName;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\DaedalusStatusEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;

final class TravelToEden extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::TRAVEL_TO_EDEN;

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraints([
            new ProjectFinished([
                'project' => ProjectName::PILGRED,
                'mode' => 'allow',
                'groups' => [ClassConstraint::EXECUTE],
                'message' => ActionImpossibleCauseEnum::NO_PILGRED,
            ]),
            new HasStatus([
                'status' => DaedalusStatusEnum::EDEN_COMPUTED,
                'target' => HasStatus::DAEDALUS,
                'groups' => [ClassConstraint::EXECUTE],
                'message' => ActionImpossibleCauseEnum::EDEN_NOT_COMPUTED,
            ]),
        ]);
    }

    public function support(?LogParameterInterface $target, array $parameters = []): bool
    {
        return $target instanceof GameEquipment;
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $this->finishDaedalus();
    }

    private function finishDaedalus(): void
    {
        $this->eventService->callEvent(
            event: new DaedalusEvent($this->player->getDaedalus(), $this->getTags(), new \DateTime()),
            name: DaedalusEvent::FINISH_DAEDALUS
        );
    }
}
