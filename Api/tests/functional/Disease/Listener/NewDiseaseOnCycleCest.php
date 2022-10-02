<?php

namespace Mush\Tests\Disease\Event;

use App\Tests\FunctionalTester;
use DateTime;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Entity\Config\DiseaseCauseConfig;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Game\Entity\DifficultyConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Game\Service\EventServiceInterface;

class NewDiseaseOnCycleCest
{
    private EventServiceInterface $eventServiceService;

    public function _before(FunctionalTester $I)
    {
        $this->eventServiceService = $I->grabService(EventServiceInterface::class);
    }

    public function testNewCycleDisease(FunctionalTester $I)
    {
        /** @var DifficultyConfig $difficultyConfig */
        $difficultyConfig = $I->have(DifficultyConfig::class, ['cycleDiseaseRate' => 100]);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['difficultyConfig' => $difficultyConfig]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room, 'characterConfig' => $characterConfig]);

        $time = new DateTime();

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setGameConfig($gameConfig)
            ->setName(DiseaseEnum::FOOD_POISONING)
        ;
        $I->haveInRepository($diseaseConfig);

        $diseaseCause = new DiseaseCauseConfig();
        $diseaseCause
            ->setName(DiseaseCauseEnum::CYCLE)
            ->setDiseases([
                DiseaseEnum::FOOD_POISONING => 2,
            ])
            ->setGameConfig($gameConfig)
        ;
        $I->haveInRepository($diseaseCause);

        $diseaseCauseLowMorale = new DiseaseCauseConfig();
        $diseaseCauseLowMorale
            ->setName(DiseaseCauseEnum::CYCLE_LOW_MORALE)
            ->setDiseases([
                DiseaseEnum::FLU => 2,
            ])
            ->setGameConfig($gameConfig)
        ;
        $I->haveInRepository($diseaseCauseLowMorale);

        $playerEvent = new PlayerEvent($player, EndCauseEnum::CLUMSINESS, new \DateTime());
        $playerEvent->setVisibility(VisibilityEnum::PUBLIC);

        $this->eventServiceService->callEvent($playerEvent, PlayerEvent::CYCLE_DISEASE);

        $I->seeInRepository(PlayerDisease::class, [
            'player' => $player->getId(),
            'diseaseConfig' => $diseaseConfig,
        ]);
    }

    public function testNewCycleDiseaseLowMorale(FunctionalTester $I)
    {
        /** @var DifficultyConfig $difficultyConfig */
        $difficultyConfig = $I->have(DifficultyConfig::class, ['cycleDiseaseRate' => 100]);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['difficultyConfig' => $difficultyConfig]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room, 'characterConfig' => $characterConfig]);

        $statusConfig = new StatusConfig();
        $statusConfig
            ->setName(PlayerStatusEnum::DEMORALIZED)
            ->setVisibility(VisibilityEnum::PUBLIC)
        ;
        $I->haveInRepository($statusConfig);
        $status = new Status($player, $statusConfig);
        $I->haveInRepository($status);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setGameConfig($gameConfig)
            ->setName(DiseaseEnum::FLU)
        ;
        $I->haveInRepository($diseaseConfig);

        $diseaseCause = new DiseaseCauseConfig();
        $diseaseCause
            ->setName(DiseaseCauseEnum::CYCLE)
            ->setDiseases([
                DiseaseEnum::FOOD_POISONING => 2,
            ])
            ->setGameConfig($gameConfig)
        ;
        $I->haveInRepository($diseaseCause);

        $diseaseCauseLowMorale = new DiseaseCauseConfig();
        $diseaseCauseLowMorale
            ->setName(DiseaseCauseEnum::CYCLE_LOW_MORALE)
            ->setDiseases([
                DiseaseEnum::FLU => 2,
            ])
            ->setGameConfig($gameConfig)
        ;
        $I->haveInRepository($diseaseCauseLowMorale);

        $playerEvent = new PlayerEvent($player, EndCauseEnum::CLUMSINESS, new \DateTime());
        $playerEvent->setVisibility(VisibilityEnum::PUBLIC);

        $this->eventServiceService->callEvent($playerEvent, PlayerEvent::CYCLE_DISEASE);

        $I->seeInRepository(PlayerDisease::class, [
            'player' => $player->getId(),
            'diseaseConfig' => $diseaseConfig,
        ]);
    }
}
