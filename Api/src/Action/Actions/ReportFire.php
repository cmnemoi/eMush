<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameter;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ReportEvent;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\IsReported;
use Mush\Action\Validator\Status;
use Mush\Status\Enum\StatusEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ReportFire extends AbstractAction
{
    protected string $name = ActionEnum::REPORT_FIRE;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService,
            $validator
        );
    }

    protected function support(?ActionParameter $parameter): bool
    {
        return $parameter === null;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Status(['status' => StatusEnum::FIRE, 'target' => Status::PLAYER_ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new IsReported(['groups' => ['visibility']]));
    }

    protected function applyEffects(): ActionResult
    {
        $reportEvent = new ReportEvent($this->player);
        $reportEvent->setPlace($this->player->getPlace());

        $this->eventDispatcher->dispatch($reportEvent, ReportEvent::REPORT_FIRE);

        return new Success();
    }
}
