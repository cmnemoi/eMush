<?php

namespace Mush\Disease\SymptomHandler;

use Mush\Action\Event\ApplyEffectEvent;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Disease\Enum\SymptomEnum;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;

class CatAllergy extends AbstractSymptomHandler
{
    protected string $name = SymptomEnum::CAT_ALLERGY;

    private PlayerDiseaseServiceInterface $playerDiseaseService;
    private EventServiceInterface $eventService;

    public function __construct(
        PlayerDiseaseServiceInterface $playerDiseaseService,
        EventServiceInterface $eventService
    ) {
        $this->playerDiseaseService = $playerDiseaseService;
        $this->eventService = $eventService;
    }

    public function applyEffects(
        Player $player,
        int $priority,
        array $tags,
        \DateTime $time
    ): void {
        $damageEvent = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::HEALTH_POINT,
            -6,
            [$this->name],
            $time
        );
        $this->eventService->callEvent($damageEvent, VariableEventInterface::CHANGE_VARIABLE);

        $this->playerDiseaseService->createDiseaseFromName(DiseaseEnum::QUINCKS_OEDEMA, $player, [$this->name]);
        $diseaseEvent = new ApplyEffectEvent(
            $player,
            $player,
            VisibilityEnum::PRIVATE,
            [$this->name],
            $time
        );
        $this->eventService->callEvent($diseaseEvent, ApplyEffectEvent::PLAYER_GET_SICK);
    }
}
