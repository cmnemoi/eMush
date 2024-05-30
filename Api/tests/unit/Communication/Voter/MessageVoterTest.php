<?php

namespace Mush\Tests\unit\Communication\Voter;

use Mockery;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\ChannelPlayer;
use Mush\Communication\Entity\Message;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Communication\Services\ChannelServiceInterface;
use Mush\Communication\Voter\MessageVoter;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Repository\PlayerInfoRepository;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @internal
 */
final class MessageVoterTest extends TestCase
{
    /** @var ChannelServiceInterface|Mockery\mock */
    private ChannelServiceInterface $channelService;

    /** @var Mockery\mock|PlayerInfoRepository */
    private PlayerInfoRepository $playerInfoRepository;

    private Voter $voter;

    /**
     * @before
     */
    public function before()
    {
        $this->channelService = \Mockery::mock(ChannelServiceInterface::class);
        $this->playerInfoRepository = \Mockery::mock(PlayerInfoRepository::class);

        $this->voter = new MessageVoter($this->channelService, $this->playerInfoRepository);
    }

    public function testCanView()
    {
        $user = new User();
        $player = new Player();
        $playerInfo = new PlayerInfo($player, $user, new CharacterConfig());
        $player->setPlayerInfo($playerInfo);

        $this->playerInfoRepository
            ->shouldReceive('getCurrentPlayerInfoForUserOrNull')
            ->with($user)
            ->andReturn($playerInfo)
            ->once();

        yield 'anonymous cannot edit' => [
            MessageVoter::VIEW,
            new Message(),
            null,
            Voter::ACCESS_DENIED,
        ];

        $this->testVote(MessageVoter::VIEW, new Message(), $user, Voter::ACCESS_GRANTED);
    }

    public function testCanCreateInPublicChannel()
    {
        $user = new User();
        $channel = new Channel();
        $player = new Player();
        $playerInfo = new PlayerInfo($player, $user, new CharacterConfig());
        $player->setPlayerInfo($playerInfo);

        $message = new Message();
        $message->setChannel($channel);

        $this->playerInfoRepository
            ->shouldReceive('getCurrentPlayerInfoForUserOrNull')
            ->with($user)
            ->andReturn($playerInfo)
            ->once();
        $this->channelService->shouldReceive('getPiratedPlayer')->with($player)->andReturn(null)->once();
        $this->channelService->shouldReceive('canPlayerCommunicate')->with($player)->andReturn(true)->once();

        $this->testVote(MessageVoter::CREATE, $message, $user, Voter::ACCESS_GRANTED);

        $playerInfo->setGameStatus(GameStatusEnum::FINISHED);

        $this->playerInfoRepository
            ->shouldReceive('getCurrentPlayerInfoForUserOrNull')
            ->with($user)
            ->andReturn($playerInfo)
            ->once();
        $this->channelService->shouldReceive('getPiratedPlayer')->with($player)->andReturn(null)->once();
        $this->channelService->shouldReceive('canPlayerCommunicate')->with($player)->andReturn(true)->once();

        $this->testVote(MessageVoter::CREATE, $message, $user, Voter::ACCESS_DENIED);
    }

    public function testCanCreateInPrivateChannel()
    {
        $user = new User();
        $channel = new Channel();
        $channel->setScope(ChannelScopeEnum::PRIVATE);
        $player = new Player();
        $playerInfo = new PlayerInfo($player, $user, new CharacterConfig());
        $player->setPlayerInfo($playerInfo);

        $message = new Message();
        $message->setChannel($channel);

        $this->playerInfoRepository
            ->shouldReceive('getCurrentPlayerInfoForUserOrNull')
            ->with($user)
            ->andReturn($playerInfo)
            ->once();
        $this->channelService->shouldReceive('getPiratedPlayer')->with($player)->andReturn(null)->once();
        $this->channelService->shouldReceive('canPlayerCommunicate')->with($player)->andReturn(true)->once();

        $this->testVote(MessageVoter::CREATE, $message, $user, Voter::ACCESS_DENIED);

        $channelPlayer = new ChannelPlayer();
        $channelPlayer
            ->setChannel($channel)
            ->setParticipant($playerInfo);
        $channel->addParticipant($channelPlayer);

        $this->playerInfoRepository
            ->shouldReceive('getCurrentPlayerInfoForUserOrNull')
            ->with($user)
            ->andReturn($playerInfo)
            ->once();
        $this->channelService->shouldReceive('getPiratedPlayer')->with($player)->andReturn(null)->once();
        $this->channelService->shouldReceive('canPlayerCommunicate')->with($player)->andReturn(true)->once();

        $this->testVote(MessageVoter::CREATE, $message, $user, Voter::ACCESS_GRANTED);

        $playerInfo->setGameStatus(GameStatusEnum::FINISHED);

        $this->playerInfoRepository
            ->shouldReceive('getCurrentPlayerInfoForUserOrNull')
            ->with($user)
            ->andReturn($playerInfo)
            ->once();
        $this->channelService->shouldReceive('getPiratedPlayer')->with($player)->andReturn(null)->once();
        $this->channelService->shouldReceive('canPlayerCommunicate')->with($player)->andReturn(true)->once();

        $this->testVote(MessageVoter::CREATE, $message, $user, Voter::ACCESS_DENIED);
    }

    private function testVote(
        string $attribute,
        Message $message,
        User $user,
        $expectedVote
    ) {
        $token = new UsernamePasswordToken(
            $user,
            'credentials',
            []
        );

        self::assertSame(
            $expectedVote,
            $this->voter->vote($token, $message, [$attribute])
        );
    }
}
