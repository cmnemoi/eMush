<?php

namespace Mush\Disease\SymptomHandler;

use Mush\Action\Event\ApplyEffectEvent;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Disease\Enum\SymptomEnum;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Game\Entity\Collection\EventChain;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;

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
    ): EventChain {
        $damageEvent = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::HEALTH_POINT,
            -6,
            [$this->name],
            $time
        );
        $damageEvent
            ->setPriority($priority)
            ->setEventName(VariableEventInterface::CHANGE_VARIABLE)
        ;

        $this->playerDiseaseService->createDiseaseFromName(DiseaseEnum::QUINCKS_OEDEMA, $player, [$this->name]);
        $diseaseEvent = new ApplyEffectEvent(
            $player,
            $player,
            VisibilityEnum::PRIVATE,
            [$this->name],
            $time
        );
        $diseaseEvent
            ->setPriority($priority)
            ->setEventName(ApplyEffectEvent::PLAYER_GET_SICK)
        ;

        return new EventChain([$damageEvent, $diseaseEvent]);
    }
}
