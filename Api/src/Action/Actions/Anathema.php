<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class Anathema extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::ANATHEMA;

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
        return $target instanceof Player;
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $this->createPariahStatus();
    }

    private function createPariahStatus(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::PARIAH,
            holder: $this->target(),
            tags: $this->getTags(),
            time: new \DateTime(),
            target: $this->player,
        );
    }

    private function target(): Player
    {
        return $this->target instanceof Player ? $this->target : throw new \RuntimeException('Target is not a player');
    }
}
