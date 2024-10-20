<?php

declare(strict_types=1);

namespace Mush\Action\Actions;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class DoorSabotage extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::DOOR_SABOTAGE;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        private RandomServiceInterface $randomService,
        private StatusServiceInterface $statusService,
    ) {
        parent::__construct($eventService, $actionService, $validator);
    }

    public function support(\Mush\RoomLog\Entity\LogParameterInterface|null $target, array $parameters): bool
    {
        return $target === null;
    }

    protected function checkResult(): \Mush\Action\Entity\ActionResult\ActionResult
    {
        return new Success();
    }

    protected function applyEffect(\Mush\Action\Entity\ActionResult\ActionResult $result): void
    {
        $this->breakRandomDoor();
    }

    private function breakRandomDoor(): void
    {
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::BROKEN,
            holder: $this->randomDoor(),
            tags: $this->getTags(),
            time: new \DateTime(),
        );
    }

    private function randomDoor(): Door
    {
        $doors = $this->player->getPlace()->getOperationalDoors();
        if ($doors->isEmpty()) {
            throw new \RuntimeException('There should be at least one door to break');
        }

        return $this->randomService->getRandomElement($doors->toArray());
    }
}