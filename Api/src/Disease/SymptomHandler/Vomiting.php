<?php

namespace Mush\Disease\SymptomHandler;

use Mush\Disease\Enum\SymptomEnum;
use Mush\Player\Entity\Player;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

class Vomiting extends AbstractSymptomHandler
{
    protected string $name = SymptomEnum::VOMITING;

    private StatusServiceInterface $statusService;

    public function __construct(
        StatusServiceInterface $statusService
    ) {
        $this->statusService = $statusService;
    }

    public function applyEffects(
        Player $player,
        int $priority,
        array $tags,
        \DateTime $time
    ): void {
        if ($player->hasStatus(PlayerStatusEnum::DIRTY)) {
            return;
        }

        $this->statusService->createStatusFromName(
            PlayerStatusEnum::DIRTY,
            $player,
            $tags,
            $time
        );
    }
}
