<?php

declare(strict_types=1);

namespace Mush\Skill\Listener;

use Mush\Action\Enum\ActionEnum;
use Mush\Game\Enum\EventPriorityEnum;
use Mush\Game\Enum\TitleEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Service\ChangePlayerVariableMaximumServiceInterface;
use Mush\Skill\Entity\Skill;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\Handler\ColdBloodedHandler;
use Mush\Skill\Handler\OpportunistHandler;
use Mush\Skill\Service\DeletePlayerSkillService;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class PlayerEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ChangePlayerVariableMaximumServiceInterface $changePlayerVariableMaximum,
        private ColdBloodedHandler $coldBloodedHandler,
        private DeletePlayerSkillService $deletePlayerSkill,
        private OpportunistHandler $opportunistHandler,
        private StatusServiceInterface $statusService
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            PlayerEvent::CONVERSION_PLAYER => 'onConversionPlayer',
            PlayerEvent::DEATH_PLAYER => ['onDeathPlayer', EventPriorityEnum::LOW],
            PlayerEvent::TITLE_ATTRIBUTED => 'onTitleAttributed',
        ];
    }

    public function onConversionPlayer(PlayerEvent $event): void
    {
        $player = $event->getPlayer();

        if ($player->hasSkill(SkillEnum::LETHARGY)) {
            $this->revertLethargyBonus($player);
        }
        if ($event->hasTag(ActionEnum::EXCHANGE_BODY->value)) {
            $this->deletePlayerHumanSkills($player);
        }
    }

    public function onDeathPlayer(PlayerEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $coldBloodedPlayers = $daedalus
            ->getAlivePlayers()
            ->getHumanPlayer()
            ->getPlayersWithSkill(SkillEnum::COLD_BLOODED);

        $coldBloodedPlayers->map(fn (Player $player) => $this->coldBloodedHandler->execute($player));
    }

    public function onTitleAttributed(PlayerEvent $event): void
    {
        $title = $event->mapLog(TitleEnum::TITLES_MAP);
        if ($title === null) {
            throw new \LogicException('Player needs a specific title to gain');
        }
        $associatedStatus = PlayerStatusEnum::TITLES_OPPORTUNIST_STATUSES_MAP[$title];
        $player = $event->getPlayer();

        if (
            $player->hasSkill(SkillEnum::OPPORTUNIST)
            && $player->doesNotHaveStatus($associatedStatus)
            && $player->isHuman()
        ) {
            $this->opportunistHandler->execute($event->getPlayer(), $title);
        }
    }

    private function deletePlayerHumanSkills(Player $player): void
    {
        $player
            ->getHumanSkills()
            ->map(fn (Skill $skill) => $this->deletePlayerSkill->execute($skill->getName(), $player));
    }

    private function revertLethargyBonus(Player $player): void
    {
        $this->changePlayerVariableMaximum->execute(
            player: $player,
            variableName: PlayerVariableEnum::ACTION_POINT,
            delta: -$player->getCharacterConfig()->getMaxActionPoint()
        );
    }
}
