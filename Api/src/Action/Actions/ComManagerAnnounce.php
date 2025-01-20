<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\ClassConstraint;
use Mush\Action\Validator\NeedTitle;
use Mush\Game\Enum\TitleEnum;
use Mush\Game\Exception\GameException;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Enum\PlayerNotificationEnum;
use Mush\Player\Service\AddComManagerAnnouncementToPlayerService;
use Mush\Player\Service\UpdatePlayerNotificationService;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ComManagerAnnounce extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::COM_MANAGER_ANNOUNCEMENT;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        private readonly AddComManagerAnnouncementToPlayerService $addComManagerAnnouncementToPlayer,
        private readonly UpdatePlayerNotificationService $updatePlayerNotification,
        private readonly StatusServiceInterface $statusService,
    ) {
        parent::__construct($eventService, $actionService, $validator);
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraints([
            new NeedTitle([
                'title' => TitleEnum::COM_MANAGER,
                'groups' => [ClassConstraint::VISIBILITY],
            ]),
        ]);
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
        $this->createAnnouncement();
        $this->createAnnouncementCreatedNotification();
        $this->createAnnouncementReceivedNotification();
    }

    private function createAnnouncement(): void
    {
        $this->addComManagerAnnouncementToPlayer->execute(
            comManager: $this->player,
            announcement: $this->announcement(),
        );
    }

    private function createAnnouncementCreatedNotification(): void
    {
        $this->updatePlayerNotification->execute(
            player: $this->player,
            message: PlayerNotificationEnum::ANNOUNCEMENT_CREATED->toString(),
        );
    }

    private function createAnnouncementReceivedNotification(): void
    {
        $recipients = $this->player->getDaedalus()->getAlivePlayers()->getAllExcept($this->player);
        foreach ($recipients as $player) {
            $this->updatePlayerNotification->execute(
                player: $player,
                message: PlayerNotificationEnum::ANNOUNCEMENT_RECEIVED->toString(),
            );
        }
    }

    private function announcement(): string
    {
        $params = $this->getParameters();
        $announcement = ($params && \array_key_exists('announcement', $params)) ? $params['announcement'] : '';

        if (!$announcement) {
            throw new GameException('Announcement cannot be empty!');
        }

        return $announcement;
    }
}
