<?php

namespace Mush\Tests\functional\Communication\Listener;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Enum\ActionEnum;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\Message;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Communication\Enum\MushMessageEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Disease\Entity\Config\DiseaseCauseConfig;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

class PlayerContaminationCest
{
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I)
    {
        $this->eventService = $I->grabService(EventServiceInterface::class);
    }

    public function testDispatchInfection(FunctionalTester $I)
    {
        $mushStatusConfig = new ChargeStatusConfig();
        $mushStatusConfig
            ->setStatusName(PlayerStatusEnum::MUSH)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($mushStatusConfig);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setDiseaseName(DiseaseEnum::FUNGIC_INFECTION)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($diseaseConfig);

        $diseaseCause = new DiseaseCauseConfig();
        $diseaseCause
            ->setCauseName(DiseaseCauseEnum::INFECTION)
            ->setDiseases([
                DiseaseEnum::FUNGIC_INFECTION => 1,
            ])
            ->buildName(GameConfigENum::TEST)
        ;
        $I->haveInRepository($diseaseCause);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, [
            'statusConfigs' => new ArrayCollection([$mushStatusConfig]),
            'diseaseCauseConfig' => new ArrayCollection([$diseaseCause]),
            'diseaseConfig' => new ArrayCollection([$diseaseConfig]),
        ]);

        /** @var User $user */
        $user = $I->have(User::class);
        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class, ['characterName' => 'andie', 'name' => 'communication_conversion_test_andie']);
        /** @var CharacterConfig $characterConfig2 */
        $characterConfig2 = $I->have(CharacterConfig::class, ['characterName' => 'ian', 'name' => 'communication_conversion_test_ian']);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        $mushChannel = new Channel();
        $mushChannel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::MUSH)
        ;
        $I->haveInRepository($mushChannel);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room]);
        $player->setPlayerVariables($characterConfig);
        $player->setSpores(0);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        /** @var Player $player2 */
        $player2 = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room]);
        $player2->setPlayerVariables($characterConfig2);
        $player2->setSpores(0);
        $playerInfo2 = new PlayerInfo($player2, $user, $characterConfig2);

        $I->haveInRepository($playerInfo2);
        $player2->setPlayerInfo($playerInfo2);
        $I->refreshEntities($player2);

        $playerEvent = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::SPORE,
            1,
            [ActionEnum::INFECT],
            new \DateTime()
        );
        $playerEvent->setAuthor($player2)->setTags([ActionEnum::INFECT]);

        $this->eventService->callEvent($playerEvent, VariableEventInterface::CHANGE_VARIABLE);

        $I->assertCount(0, $player->getStatuses());
        $I->assertEquals(1, $player->getSpores());
        $I->assertEquals($room, $player->getPlace());

        $message = $I->grabEntityFromRepository(Message::class, [
            'channel' => $mushChannel,
            'message' => MushMessageEnum::INFECT_ACTION,
        ]);

        $I->assertEquals($message->getTranslationParameters(), ['quantity' => 1, 'character' => 'ian', 'target_character' => 'andie']);
        $I->assertCount(0, $mushChannel->getParticipants());
    }

    public function testDispatchContamination(FunctionalTester $I)
    {
        $mushStatusConfig = new ChargeStatusConfig();
        $mushStatusConfig
            ->setStatusName(PlayerStatusEnum::MUSH)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($mushStatusConfig);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setDiseaseName(DiseaseEnum::FUNGIC_INFECTION)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($diseaseConfig);

        $diseaseCause = new DiseaseCauseConfig();
        $diseaseCause
            ->setCauseName(DiseaseCauseEnum::INFECTION)
            ->setDiseases([
                DiseaseEnum::FUNGIC_INFECTION => 1,
            ])
            ->buildName(GameConfigENum::TEST)
        ;
        $I->haveInRepository($diseaseCause);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, [
            'statusConfigs' => new ArrayCollection([$mushStatusConfig]),
            'diseaseCauseConfig' => new ArrayCollection([$diseaseCause]),
            'diseaseConfig' => new ArrayCollection([$diseaseConfig]),
        ]);

        /** @var User $user */
        $user = $I->have(User::class);
        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class, ['characterName' => 'andie', 'name' => 'communication_conversion_test_andie']);
        /** @var CharacterConfig $characterConfig2 */
        $characterConfig2 = $I->have(CharacterConfig::class, ['characterName' => 'ian', 'name' => 'communication_conversion_test_ian']);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        $mushChannel = new Channel();
        $mushChannel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::MUSH)
        ;
        $I->haveInRepository($mushChannel);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room]);
        $player->setPlayerVariables($characterConfig);
        $player->setSpores(0);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        /** @var Player $player2 */
        $player2 = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room]);
        $player2->setPlayerVariables($characterConfig2);
        $player2->setSpores(0);
        $playerInfo2 = new PlayerInfo($player2, $user, $characterConfig2);

        $I->haveInRepository($playerInfo2);
        $player2->setPlayerInfo($playerInfo2);
        $I->refreshEntities($player2);

        $playerEvent = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::SPORE,
            3,
            [ActionEnum::INFECT],
            new \DateTime()
        );
        $playerEvent->setAuthor($player2)->setTags([ActionEnum::INFECT]);

        $this->eventService->callEvent($playerEvent, VariableEventInterface::CHANGE_VARIABLE);

        $I->assertCount(1, $player->getStatuses());
        $I->assertEquals(0, $player->getSpores());
        $I->assertEquals($room, $player->getPlace());

        $message = $I->grabEntityFromRepository(Message::class, [
            'channel' => $mushChannel,
            'message' => MushMessageEnum::INFECT_ACTION,
        ]);
        $I->assertEquals($message->getTranslationParameters(), ['quantity' => 3, 'character' => 'ian', 'target_character' => 'andie']);

        $I->assertCount(1, $mushChannel->getParticipants());

        $message = $I->grabEntityFromRepository(Message::class, [
            'channel' => $mushChannel,
            'message' => MushMessageEnum::MUSH_CONVERT_EVENT,
        ]);
    }
}
