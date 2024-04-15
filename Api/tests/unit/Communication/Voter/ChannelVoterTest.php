<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Communication\Voter;

use Mush\Communication\Entity\Channel;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Communication\Services\ChannelServiceInterface;
use Mush\Communication\Services\MessageServiceInterface;
use Mush\Communication\Voter\ChannelVoter;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Repository\PlayerInfoRepository;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

final class ChannelVoterTest extends TestCase
{
    /** @var ChannelServiceInterface|Mockery\mock */
    private ChannelServiceInterface $channelService;
    /** @var MessageServiceInterface|Mockery\mock */
    private MessageServiceInterface $messageService;
    /** @var PlayerInfoRepository|Mockery\mock */
    private PlayerInfoRepository $playerInfoRepository;

    private ChannelVoter $channelVoter;

    /**
     * @before
     */
    public function before(): void
    {
        $this->channelService = \Mockery::mock(ChannelServiceInterface::class);
        $this->channelService->shouldIgnoreMissing();

        $this->messageService = \Mockery::mock(MessageServiceInterface::class);
        $this->playerInfoRepository = \Mockery::mock(PlayerInfoRepository::class);

        $this->channelVoter = new ChannelVoter($this->channelService, $this->messageService, $this->playerInfoRepository);
    }

    public function testCanViewFavoritesChannel(): void
    {
        // given an in-game player
        $user = new User();
        $player = $this->setUpInGamePlayer($user);

        // given a favorites channel
        $channel = $this->setUpChannel($player->getDaedalus(), ChannelScopeEnum::FAVORITES);

        // given the player can communicate
        $this->channelService->shouldReceive('canPlayerCommunicate')->with($player)->andReturn(true)->once();

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
        $this->messageService->shouldReceive('canPlayerPostMessage')->with($player, $channel)->andReturn(true)->once();

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
        $this->messageService->shouldReceive('canPlayerPostMessage')->with($player, $channel)->andReturn(true)->once();

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
            ->shouldReceive('findCurrentGameByUser')
            ->with($user)
            ->andReturn($playerInfo)
            ->once()
        ;

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
            $user, 'credentials', []
        );

        $this->assertEquals(
            $expectedVote,
            $this->channelVoter->vote($token, $channel, [$attribute]),
            'Voter should return ' . $expectedVote . ' for ' . $attribute . ' attribute'
        );
    }
}
