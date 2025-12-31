<?php

namespace Mush\Action\Actions;

use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\AreShowersDismantled;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class WashInSink extends AbstractWashSelfAction
{
    protected ActionEnum $name = ActionEnum::WASH_IN_SINK;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        protected StatusServiceInterface $statusService,
        private RandomServiceInterface $randomService
    ) {
        parent::__construct($eventService, $actionService, $validator, $statusService, $randomService);

        $this->statusService = $statusService;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach([
            'reach' => ReachEnum::ROOM,
            'groups' => ['visibility'],
        ]));
        $metadata->addConstraint(new AreShowersDismantled([
            'groups' => ['visibility'],
        ]));
    }
}
