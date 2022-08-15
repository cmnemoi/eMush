<?php

namespace Mush\Communication\Services;

use Mush\Player\Entity\Player;

interface DiseaseMessageServiceInterface
{
    public function applyDiseaseEffects(string $message, Player $player): string;
}
