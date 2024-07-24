<?php

declare(strict_types=1);

namespace Mush\Skill\UseCase;

use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Repository\PlayerRepositoryInterface;
use Mush\Skill\Entity\Skill;
use Mush\Skill\Enum\SkillName;
use Mush\Skill\Event\SkillAddedToPlayerEvent;

final class AddSkillToPlayerUseCase
{
    public function __construct(
        private EventServiceInterface $eventService,
        private PlayerRepositoryInterface $playerRepository
    ) {}

    public function execute(SkillName $skillName, Player $player): void
    {
        $skillConfig = $player->getSkillConfigByNameOrThrow($skillName);
        $skill = new Skill($skillConfig, $player);

        $this->playerRepository->save($player);

        $this->eventService->callEvent(
            event: new SkillAddedToPlayerEvent($skill),
            name: SkillAddedToPlayerEvent::class
        );
    }
}
