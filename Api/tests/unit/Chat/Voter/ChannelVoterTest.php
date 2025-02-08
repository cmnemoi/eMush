<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Chat\Voter;

use Mush\Chat\Entity\Channel;
use Mush\Chat\Enum\ChannelScopeEnum;
use Mush\Chat\Services\ChannelServiceInterface;
use Mush\Chat\Services\MessageServiceInterface;
use Mush\Chat\Voter\ChannelVoter;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Repository\PlayerInfoRepositoryInterface;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * @internal
 */
final class ChannelVoterTest extends TestCase
{
    /** @var ChannelServiceInterface|Mockery\mock */
    private ChannelServiceInterface $channelService;

    /** @var MessageServiceInterface|Mockery\mock */
    private MessageServiceInterface $messageService;

    /** @var Mockery\mock|PlayerInfoRepositoryInterface */
    private PlayerInfoRepositoryInterface $playerInfoRepository;

    private ChannelVoter $channelVoter;

    /**
     * @before
     */
    public function before(): void
    {
        $this->channelService = \Mockery::mock(ChannelServiceInterface::class);
        $this->channelService->shouldIgnoreMissing();

        $this->messageService = \Mockery::mock(MessageServiceInterface::class);
        $this->playerInfoRepository = \Mockery::mock(PlayerInfoRepositoryInterface::class);

        $this->channelVoter = new ChannelVoter($this->channelService, $this->messageService, $this->playerInfoRepository);
    }

    /**
     * @after
     */
    public function after(): void
    {
        \Mockery::close();
    }

    public function testCanViewFavoritesChannel(): void
    {
        // given an in-game player
        $user = new User();
        $player = $this->setUpInGamePlayer($user);

        // given a favorites channel
        $channel = $this->setUpChannel($player->getDaedalus(), ChannelScopeEnum::FAVORITES);

        // then the player can view the channel
        $this->testVote(ChannelVoter::VIEW, $channel, $user, ChannelVoter::ACCESS_GRANTED);
    }

    public function testCanPostInFavoritesChannel(): void
    {
        // given an in-game player
        $user = new User();
        $player = $this->setUpInGamePlayer($user);

        // given a favorites channel
        $channel = $this->setUpChannel($player->getDaedalus(), ChannelScopeEnum::FAVORITES);

        // given the player can communicate
        $this->channelService->shouldReceive('canPlayerCommunicate')->with($player)->andReturn(true)->once();

        // given the player has no disease preventing them from posting
        $this->messageService->shouldReceive('canPlayerPostMessage')->with($player, $channel)->andReturn(true)->twice();

        // then the player can post in the channel
        $this->testVote(ChannelVoter::POST, $channel, $user, ChannelVoter::ACCESS_GRANTED);
    }

    public function testCannotPostInFavoritesChannel(): void
    {
        // given an in-game player
        $user = new User();
        $player = $this->setUpInGamePlayer($user);

        // given a favorites channel
        $channel = $this->setUpChannel($player->getDaedalus(), ChannelScopeEnum::FAVORITES);

        // given the player cannot communicate
        $this->channelService->shouldReceive('canPlayerCommunicate')->with($player)->andReturn(false)->once();

        // given the player has no disease preventing them from posting
        $this->messageService->shouldReceive('canPlayerPostMessage')->with($player, $channel)->andReturn(true)->twice();

        // then the player cannot post in the channel
        $this->testVote(ChannelVoter::POST, $channel, $user, ChannelVoter::ACCESS_DENIED);
    }

    private function setUpInGamePlayer(User $user): Player
    {
        $daedalus = new Daedalus();
        new DaedalusInfo($daedalus, new GameConfig(), new LocalizationConfig());
        $player = new Player();
        $player->setDaedalus($daedalus);
        $playerInfo = new PlayerInfo($player, $user, new CharacterConfig());

        $this->playerInfoRepository
            ->shouldReceive('getCurrentPlayerInfoForUserOrNull')
            ->with($user)
            ->andReturn($playerInfo)
            ->once();

        return $player;
    }

    private function setUpChannel(Daedalus $daedalus, string $scope): Channel
    {
        $channel = new Channel();
        $channel->setScope($scope);
        $channel->setDaedalus($daedalus->getDaedalusInfo());

        return $channel;
    }

    private function testVote(
        string $attribute,
        Channel $channel,
        User $user,
        $expectedVote
    ) {
        $token = new UsernamePasswordToken(
            $user,
            'credentials',
            []
        );

        self::assertEquals(
            $expectedVote,
            $this->channelVoter->vote($token, $channel, [$attribute]),
            'Voter should return ' . $expectedVote . ' for ' . $attribute . ' attribute'
        );
    }
}
