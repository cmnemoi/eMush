<?php

declare(strict_types=1);

namespace Mush\Player\Listener;

use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Service\ChangePlayerVariableMaximumServiceInterface;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\Event\SkillCreatedEvent;
use Mush\Skill\Event\SkillDeletedEvent;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class SkillEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ChangePlayerVariableMaximumServiceInterface $changePlayerVariableMaximum,
        private StatusServiceInterface $statusService,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            SkillCreatedEvent::class => 'onSkillCreated',
            SkillDeletedEvent::class => 'onSkillDeleted',
        ];
    }

    public function onSkillCreated(SkillCreatedEvent $event): void
    {
        $this->applyLethargyBonus($event);
    }

    public function onSkillDeleted(SkillDeletedEvent $event): void
    {
        $this->revertLethargyBonus($event);
        $this->removeBodygardStatus($event);
    }

    private function applyLethargyBonus(SkillCreatedEvent $event): void
    {
        $player = $event->skillPlayer();

        if ($event->isNotAboutLethargy() || $player->isMush()) {
            return;
        }

        $this->changePlayerVariableMaximum->execute(
            player: $player,
            variableName: PlayerVariableEnum::ACTION_POINT,
            delta: $player->getCharacterConfig()->getMaxActionPoint()
        );
    }

    private function revertLethargyBonus(SkillDeletedEvent $event): void
    {
        $player = $event->skillPlayer();

        if ($event->isNotAboutLethargy() || $player->isMush()) {
            return;
        }

        $this->changePlayerVariableMaximum->execute(
            player: $player,
            variableName: PlayerVariableEnum::ACTION_POINT,
            delta: -$player->getCharacterConfig()->getMaxActionPoint()
        );
    }

    private function removeBodygardStatus(SkillDeletedEvent $event): void
    {
        if ($event->skill()->getName() !== SkillEnum::BODYGUARD) {
            return;
        }

        $player = $event->skillPlayer();
        if ($player->hasStatus(PlayerStatusEnum::BODYGUARD_USER)) {
            $status = $player->getStatusByNameOrThrow(PlayerStatusEnum::BODYGUARD_USER);
            $vip = $status->getPlayerTargetOrThrow();

            $this->statusService->removeStatus(PlayerStatusEnum::BODYGUARD_USER, $player, $event->getTags(), $event->getTime());
            $this->statusService->removeStatus(PlayerStatusEnum::BODYGUARD_VIP, $vip, $event->getTags(), $event->getTime());
        }
    }
}
