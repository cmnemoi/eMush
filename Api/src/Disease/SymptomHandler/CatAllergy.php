<?php

namespace Mush\Disease\SymptomHandler;

use Mush\Action\Event\ApplyEffectEvent;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Disease\Enum\SymptomEnum;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;

class CatAllergy extends AbstractSymptomHandler
{
    protected string $name = SymptomEnum::CAT_ALLERGY;

    private EventServiceInterface $eventService;
    private PlayerDiseaseServiceInterface $playerDiseaseService;

    public function __construct(
        EventServiceInterface $eventService,
        PlayerDiseaseServiceInterface $playerDiseaseService,
    ) {
        $this->eventService = $eventService;
        $this->playerDiseaseService = $playerDiseaseService;
    }

    public function applyEffects(string $symptomName, Player $player, \DateTime $time): void
    {
        if ($symptomName !== SymptomEnum::CAT_ALLERGY) {
            return;
        }

        $logParameters = [];
        $logParameters[$player->getLogKey()] = $player->getLogName();
        $logParameters['character_gender'] = CharacterEnum::isMale($player->getName()) ? 'male' : 'female';

        $damageEvent = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::HEALTH_POINT,
            -6,
            [$symptomName],
            $time
        );

        $this->eventService->callEvent($damageEvent, VariableEventInterface::CHANGE_VARIABLE);

        $this->playerDiseaseService->createDiseaseFromName(DiseaseEnum::QUINCKS_OEDEMA, $player, [$symptomName]);

        $diseaseEvent = new ApplyEffectEvent(
            $player,
            $player,
            VisibilityEnum::PRIVATE,
            [$symptomName],
            $time
        );
        $this->eventService->callEvent($diseaseEvent, ApplyEffectEvent::PLAYER_GET_SICK);
    }
}
