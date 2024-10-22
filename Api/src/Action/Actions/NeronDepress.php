<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Daedalus\Entity\Neron;
use Mush\Daedalus\Enum\DaedalusStatusEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class NeronDepress extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::NERON_DEPRESS;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        private StatusServiceInterface $statusService,
    ) {
        parent::__construct($eventService, $actionService, $validator);
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
        $neron = $this->neron();
        $this->statusService->createStatusFromName(
            DaedalusStatusEnum::NERON_DEPRESSION,
            $neron,
            $this->getTags(),
            new \DateTime(),
        );
    }

    private function neron(): Neron
    {
        return $this->player->getDaedalus()->getNeron();
    }
}
