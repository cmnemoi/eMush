<?php

namespace Mush\Communication\Services;

use Mush\Disease\Enum\SymptomEnum;
use Mush\Game\Enum\EventEnum;
use Mush\Player\Entity\Player;

class DiseaseMessageService implements DiseaseMessageServiceInterface
{
    public function applyDiseaseEffects(string $message, Player $player): string
    {
        $playerSymptoms = $player
            ->getMedicalConditions()
            ->getActiveDiseases()
            ->getAllSymptoms()
            ->getTriggeredSymptoms([EventEnum::ON_NEW_MESSAGE])
        ;

        if ($playerSymptoms->hasSymptomByName(SymptomEnum::DEAF)
        ) {
            $message = $this->applyDeafEffect($message);
        }

        return $message;
    }

    private function applyDeafEffect(string $message): string
    {
        return strtoupper($message);
    }
}
