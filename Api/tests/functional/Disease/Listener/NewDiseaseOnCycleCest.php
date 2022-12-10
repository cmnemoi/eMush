<?php

namespace Mush\Tests\Disease\Event;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Disease\Entity\Config\DiseaseCauseConfig;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Game\Entity\DifficultyConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\User\Entity\User;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class NewDiseaseOnCycleCest
{
    private EventDispatcherInterface $eventDispatcher;

    public function _before(FunctionalTester $I)
    {
        $this->eventDispatcher = $I->grabService(EventDispatcherInterface::class);
    }

    public function testNewCycleDisease(FunctionalTester $I)
    {
        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setName(DiseaseEnum::FOOD_POISONING)
        ;
        $I->haveInRepository($diseaseConfig);
        $diseaseCause = new DiseaseCauseConfig();
        $diseaseCause
            ->setName(DiseaseCauseEnum::CYCLE)
            ->setDiseases([
                DiseaseEnum::FOOD_POISONING => 2,
            ])
        ;
        $I->haveInRepository($diseaseCause);
        $diseaseCauseLowMorale = new DiseaseCauseConfig();
        $diseaseCauseLowMorale
            ->setName(DiseaseCauseEnum::CYCLE_LOW_MORALE)
            ->setDiseases([
                DiseaseEnum::FLU => 2,
            ])
        ;
        $I->haveInRepository($diseaseCauseLowMorale);

        /** @var DifficultyConfig $difficultyConfig */
        $difficultyConfig = $I->have(DifficultyConfig::class, ['cycleDiseaseRate' => 100]);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, [
            'difficultyConfig' => $difficultyConfig,
            'diseaseCauseConfig' => new ArrayCollection([$diseaseCauseLowMorale, $diseaseCause]),
            'diseaseConfig' => new ArrayCollection([$diseaseConfig]),
        ]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room, 'characterConfig' => $characterConfig]);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $time = new \DateTime();

        $playerEvent = new PlayerEvent($player, EndCauseEnum::CLUMSINESS, new \DateTime());
        $playerEvent->setVisibility(VisibilityEnum::PUBLIC);

        $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::CYCLE_DISEASE);

        $I->seeInRepository(PlayerDisease::class, [
            'player' => $player->getId(),
            'diseaseConfig' => $diseaseConfig,
        ]);
    }

    public function testNewCycleDiseaseLowMorale(FunctionalTester $I)
    {
        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setName(DiseaseEnum::FLU)
        ;
        $I->haveInRepository($diseaseConfig);
        $diseaseCause = new DiseaseCauseConfig();
        $diseaseCause
            ->setName(DiseaseCauseEnum::CYCLE)
            ->setDiseases([
                DiseaseEnum::FOOD_POISONING => 2,
            ])
        ;
        $I->haveInRepository($diseaseCause);
        $diseaseCauseLowMorale = new DiseaseCauseConfig();
        $diseaseCauseLowMorale
            ->setName(DiseaseCauseEnum::CYCLE_LOW_MORALE)
            ->setDiseases([
                DiseaseEnum::FLU => 2,
            ])
        ;
        $I->haveInRepository($diseaseCauseLowMorale);

        /** @var DifficultyConfig $difficultyConfig */
        $difficultyConfig = $I->have(DifficultyConfig::class, ['cycleDiseaseRate' => 100]);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, [
            'difficultyConfig' => $difficultyConfig,
            'diseaseCauseConfig' => new ArrayCollection([$diseaseCauseLowMorale, $diseaseCause]),
            'diseaseConfig' => new ArrayCollection([$diseaseConfig]),
        ]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room, 'characterConfig' => $characterConfig]);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $statusConfig = new StatusConfig();
        $statusConfig
            ->setName(PlayerStatusEnum::DEMORALIZED)
            ->setVisibility(VisibilityEnum::PUBLIC)
        ;
        $I->haveInRepository($statusConfig);
        $status = new Status($player, $statusConfig);
        $I->haveInRepository($status);

        $playerEvent = new PlayerEvent($player, EndCauseEnum::CLUMSINESS, new \DateTime());
        $playerEvent->setVisibility(VisibilityEnum::PUBLIC);

        $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::CYCLE_DISEASE);

        $I->seeInRepository(PlayerDisease::class, [
            'player' => $player->getId(),
            'diseaseConfig' => $diseaseConfig,
        ]);
    }
}
