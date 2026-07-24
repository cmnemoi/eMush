<?php

declare(strict_types=1);

namespace Mush\tests\api\Chat;

use Mush\Chat\Entity\Channel;
use Mush\Chat\Entity\Dto\CreateMessage;
use Mush\Chat\Entity\Message;
use Mush\Chat\Services\ChannelServiceInterface;
use Mush\Chat\Services\MessageServiceInterface;
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
use Mush\User\Enum\RoleEnum;

final class FavoriteMessageCest
{
    private ChannelServiceInterface $channelService;
    private MessageServiceInterface $messageService;
    private PlayerServiceInterface $playerService;
    private DaedalusServiceInterface $daedalusService;
    private GameConfig $gameConfig;
    private Daedalus $daedalus;
    private User $chunUser;
    private User $kuanTiUser;
    private Player $chun;
    private Message $message;

    public function _before(ApiTester $I): void
    {
        $this->channelService = $I->grabService(ChannelServiceInterface::class);
        $this->messageService = $I->grabService(MessageServiceInterface::class);
        $this->playerService = $I->grabService(PlayerServiceInterface::class);
        $this->daedalusService = $I->grabService(DaedalusServiceInterface::class);
        $this->gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);

        $this->chunUser = $I->loginUser(RoleEnum::USER);
        $this->kuanTiUser = $I->loginUser(RoleEnum::USER);
        $this->daedalus = $this->daedalusService->createDaedalus($this->gameConfig, 'favorite_message_test_daedalus', LanguageEnum::FRENCH);
        $this->chun = $this->playerService->createPlayer($this->daedalus, $this->chunUser, CharacterEnum::CHUN);
        $this->playerService->createPlayer($this->daedalus, $this->kuanTiUser, CharacterEnum::KUAN_TI);
    }

    public function kuanTiShouldNotFavoriteChunsPrivateMessage(ApiTester $I): void
    {
        $this->givenChunWroteAPrivateMessage();

        $this->whenKuanTiFavoritesTheMessage($I);

        $this->thenFavoriteShouldBeRejected($I);
    }

    public function chunShouldNotFavoriteHerOwnPrivateMessage(ApiTester $I): void
    {
        $this->givenChunWroteAPrivateMessage();

        $this->whenChunFavoritesTheMessage($I);

        $this->thenFavoriteShouldBeRejected($I);
    }

    public function kuanTiShouldFavoriteChunsPublicMessage(ApiTester $I): void
    {
        $this->givenChunWroteAPublicMessage();

        $this->whenKuanTiFavoritesTheMessage($I);

        $this->thenFavoriteShouldBeAccepted($I);
    }

    private function givenChunWroteAPrivateMessage(): void
    {
        $privateChannel = $this->channelService->createPrivateChannel($this->chun);
        $this->message = $this->chunWritesMessageIn($privateChannel, 'Private message from Chun');
    }

    private function givenChunWroteAPublicMessage(): void
    {
        $publicChannel = $this->channelService->getPublicChannel($this->daedalus->getDaedalusInfo());
        $this->message = $this->chunWritesMessageIn($publicChannel, 'Public message from Chun');
    }

    private function whenKuanTiFavoritesTheMessage(ApiTester $I): void
    {
        $this->favoriteMessageAs($I, $this->kuanTiUser);
    }

    private function whenChunFavoritesTheMessage(ApiTester $I): void
    {
        $this->favoriteMessageAs($I, $this->chunUser);
    }

    private function thenFavoriteShouldBeRejected(ApiTester $I): void
    {
        $I->seeResponseCodeIs(403);
    }

    private function thenFavoriteShouldBeAccepted(ApiTester $I): void
    {
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['detail' => 'Message marked as favorites successfully']);
    }

    private function favoriteMessageAs(ApiTester $I, User $user): void
    {
        $I->loginUser($user);
        $I->sendPostRequest("/channel/favorite-message/{$this->message->getId()}");
    }

    private function chunWritesMessageIn(Channel $channel, string $content): Message
    {
        $createMessage = new CreateMessage();
        $createMessage
            ->setChannel($channel)
            ->setMessage($content)
            ->setPlayerId($this->chun->getId());

        return $this->messageService->createPlayerMessage($this->chun, $createMessage);
    }
}
