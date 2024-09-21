<?php

declare(strict_types=1);

namespace Mush\Disease\SymptomHandler;

use Mush\Disease\Enum\SymptomEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Service\PlayerServiceInterface;

final class Septicemia extends AbstractSymptomHandler
{
    protected string $name = SymptomEnum::SEPTICEMIA;

    public function __construct(private PlayerServiceInterface $playerService) {}

    public function applyEffects(
        Player $player,
        int $priority,
        array $tags,
        \DateTime $time
    ): void {
        $this->playerService->killPlayer(player: $player, endReason: EndCauseEnum::INFECTION, time: $time);
    }
}
