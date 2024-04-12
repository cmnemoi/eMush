<?php

namespace Mush\Tests\functional\Communication\Repository;

use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\ChannelPlayer;
use Mush\Communication\Entity\Message;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Communication\Repository\MessageRepository;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

class MessageRepositoryCest
{
    private FunctionalTester $tester;

    private MessageRepository $messageRepository;

    public function _before(FunctionalTester $I)
    {
        $this->tester = $I;

        $this->messageRepository = $I->grabService(MessageRepository::class);
    }

    public function testFindByChannel(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo->setName('daedalus');
        $I->haveInRepository($daedalusInfo);

        /** @var Daedalus $daedalus2 */
        $daedalus2 = $I->have(Daedalus::class, ['name' => 'daedalus_']);
        $daedalus2Info = new DaedalusInfo($daedalus2, $gameConfig, $localizationConfig);
        $daedalusInfo->setName('daedalus2');
        $I->haveInRepository($daedalus2Info);

        /** @var User $user */
        $user = $I->have(User::class);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
        ]);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        /** @var Player $player2 */
        $player2 = $I->have(Player::class, [
            'daedalus' => $daedalus2,
        ]);
        $player2Info = new PlayerInfo($player2, $user, $characterConfig);

        $I->haveInRepository($player2Info);
        $player2->setPlayerInfo($player2Info);
        $I->refreshEntities($player2);

        $channel1 = $this->createPrivateChannel([$playerInfo, $player2Info], $daedalus);
        $channel2 = $this->createPrivateChannel([$playerInfo, $player2Info], $daedalus);

        $currentTime = new \DateTime();

        $time1 = new \DateTime();
        $time1->sub(new \DateInterval('PT5H'));
        $message1 = new Message();
        $message1
            ->setCreatedAt($time1)
            ->setChannel($channel1)
            ->setMessage('message');
        $I->haveInRepository($message1);

        $time2 = new \DateTime();
        $time2->sub(new \DateInterval('PT25H'));
        $message2 = new Message();
        $message2
            ->setCreatedAt($time2)
            ->setChannel($channel1)
            ->setMessage('message');
        $I->haveInRepository($message2);

        $message3 = new Message();
        $message3
            ->setCreatedAt($time2)
            ->setChannel($channel2)
            ->setMessage('message');
        $I->haveInRepository($message3);

        // No time constraint
        $result = $this->messageRepository->findByChannel($channel1);
        $I->assertCount(2, $result);
        $I->assertContains($message1, $result);
        $I->assertContains($message2, $result);
        $I->assertNotContains($message3, $result);

        // The other channel
        $result = $this->messageRepository->findByChannel($channel2);
        $I->assertCount(1, $result);
        $I->assertNotContains($message1, $result);
        $I->assertNotContains($message2, $result);
        $I->assertContains($message3, $result);

        // less than 24 h
        $result = $this->messageRepository->findByChannel($channel1, new \DateInterval('PT24H'));
        $I->assertCount(1, $result);
        $I->assertContains($message1, $result);
        $I->assertNotContains($message2, $result);
        $I->assertNotContains($message3, $result);

        // less than 1 h
        $result = $this->messageRepository->findByChannel($channel1, new \DateInterval('PT1H'));
        $I->assertEmpty($result);
    }

    public function testFindByChannelCheckDuplicateThreadAnswers(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo->setName('daedalus');
        $I->haveInRepository($daedalusInfo);

        /** @var User $user */
        $user = $I->have(User::class);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
        ]);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $channel1 = $this->createPrivateChannel([$playerInfo], $daedalus);

        $currentTime = new \DateTime();

        $message1 = new Message();
        $message1
            ->setCreatedAt($currentTime)
            ->setChannel($channel1)
            ->setMessage('message1');
        $I->haveInRepository($message1);

        $message2 = new Message();
        $message2
            ->setCreatedAt($currentTime)
            ->setChannel($channel1)
            ->setMessage('message2')
            ->setParent($message1);
        $I->haveInRepository($message2);

        $result = $this->messageRepository->findByChannel($channel1);

        $I->assertCount(1, $result);
        $I->assertContains($message1, $result);
        $result1 = $result[0];
        $I->assertInstanceOf(Message::class, $result1);
        $I->assertCount(1, $result1->getChild());
        $I->assertContains($message2, $result1->getChild());
        $I->assertNotContains($message2, $result);
    }

    private function createPrivateChannel(array $users, Daedalus $daedalus): Channel
    {
        $privateChannel = new Channel();
        $privateChannel->setDaedalus($daedalus->getDaedalusInfo());
        $privateChannel->setScope(ChannelScopeEnum::PRIVATE);

        $this->tester->haveInRepository($privateChannel);

        /** @var PlayerInfo $user */
        foreach ($users as $user) {
            $participant = new ChannelPlayer();
            $participant
                ->setParticipant($user)
                ->setChannel($privateChannel);
            $this->tester->haveInRepository($participant);
        }

        return $privateChannel;
    }
}
