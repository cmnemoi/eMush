<?php

declare(strict_types=1);

namespace Mush\tests\api\Chat;

use Mush\Chat\Entity\Channel;
use Mush\Chat\Services\ChannelServiceInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Tests\ApiTester;
use Mush\User\Entity\User;

final class CreateMessageValidationCest
{
    private DaedalusServiceInterface $daedalusService;
    private PlayerServiceInterface $playerService;
    private ChannelServiceInterface $channelService;
    private GameConfig $gameConfig;
    private User $user;
    private Daedalus $daedalus;
    private Player $player;
    private Channel $channel;

    public function _before(ApiTester $I): void
    {
        $this->user = $I->loginUser('default');
        $this->daedalusService = $I->grabService(DaedalusServiceInterface::class);
        $this->playerService = $I->grabService(PlayerServiceInterface::class);
        $this->channelService = $I->grabService(ChannelServiceInterface::class);
        $this->gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);

        $this->daedalus = $this->daedalusService->createDaedalus($this->gameConfig, 'test_daedalus', LanguageEnum::FRENCH);
        $this->player = $this->playerService->createPlayer($this->daedalus, $this->user, CharacterEnum::CHUN);
        $this->channel = $this->channelService->getPublicChannel($this->daedalus->getDaedalusInfo());
    }

    public function shouldAcceptMessageWithLessThan4096Character(ApiTester $I): void
    {
        $message = 'Hello, World!';

        $I->sendPostRequest("/channel/{$this->channel->getId()}/message", [
            'message' => $message,
            'playerId' => $this->player->getId(),
        ]);

        $I->seeResponseCodeIs(200);
    }

    public function shouldAcceptMessageWithExactly4096Characters(ApiTester $I): void
    {
        $message = str_repeat('a', 4096);

        $I->sendPostRequest("/channel/{$this->channel->getId()}/message", [
            'message' => $message,
            'playerId' => $this->player->getId(),
        ]);

        $I->seeResponseCodeIs(200);
    }

    public function shouldRejectMessageWithMoreThan4096Characters(ApiTester $I): void
    {
        $message = str_repeat('a', 4097);

        $I->sendPostRequest("/channel/{$this->channel->getId()}/message", [
            'message' => $message,
        ]);

        $I->seeResponseCodeIs(422);
        $I->seeResponseContainsJson([
            'violations' => [
                [
                    'propertyPath' => 'message',
                    'title' => 'The message cannot be longer than 4096 characters',
                ],
            ],
        ]);
    }
}
