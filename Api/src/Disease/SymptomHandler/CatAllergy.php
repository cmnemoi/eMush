<?php

namespace Mush\Disease\SymptomHandler;

use Mush\Disease\Enum\DiseaseEnum;
use Mush\Disease\Enum\SymptomEnum;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Player\Entity\Player;

class CatAllergy extends AbstractSymptomHandler
{
    protected string $name = SymptomEnum::CAT_ALLERGY;

    private PlayerDiseaseServiceInterface $playerDiseaseService;

    public function __construct(
        PlayerDiseaseServiceInterface $playerDiseaseService,
    ) {
        $this->playerDiseaseService = $playerDiseaseService;
    }

    public function applyEffects(
        Player $player,
        int $priority,
        array $tags,
        \DateTime $time
    ): void {
        $this->playerDiseaseService->createDiseaseFromName(DiseaseEnum::QUINCKS_OEDEMA->toString(), $player, [$this->name]);
    }
}
