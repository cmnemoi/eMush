<?php

namespace Mush\Test\Communication\Voter;

use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\ChannelPlayer;
use Mush\Communication\Entity\Message;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Communication\Voter\MessageVoter;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Player\Entity\Player;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class MessageVoterTest extends TestCase
{
    private Voter $voter;

    /**
     * @before
     */
    public function before()
    {
        $this->voter = new MessageVoter();
    }

    public function testCanView()
    {
        $user = new User();
        $player = new Player();
        $user->setCurrentGame($player);

        yield 'anonymous cannot edit' => [
            MessageVoter::VIEW,
            new Message(),
            null,
            Voter::ACCESS_DENIED,
        ];

        $this->testVote(MessageVoter::VIEW, new Message(), null, Voter::ACCESS_DENIED);
        $this->testVote(MessageVoter::VIEW, new Message(), $user, Voter::ACCESS_GRANTED);
    }

    public function testCanCreateInPublicChannel()
    {
        $user = new User();
        $channel = new Channel();
        $player = new Player();
        $user->setCurrentGame($player);

        $this->testVote(MessageVoter::CREATE, new Message(), null, Voter::ACCESS_DENIED);

        $message = new Message();
        $message->setChannel($channel);
        $player->setGameStatus(GameStatusEnum::CURRENT);

        $this->testVote(MessageVoter::CREATE, $message, $user, Voter::ACCESS_GRANTED);

        $player->setGameStatus(GameStatusEnum::FINISHED);

        $this->testVote(MessageVoter::CREATE, $message, $user, Voter::ACCESS_DENIED);
    }

    public function testCanCreateInPrivateChannel()
    {
        $user = new User();
        $channel = new Channel();
        $channel->setScope(ChannelScopeEnum::PRIVATE);
        $player = new Player();
        $user->setCurrentGame($player);

        $this->testVote(MessageVoter::CREATE, new Message(), null, Voter::ACCESS_DENIED);

        $message = new Message();
        $message->setChannel($channel);
        $player->setGameStatus(GameStatusEnum::CURRENT);

        $this->testVote(MessageVoter::CREATE, $message, $user, Voter::ACCESS_DENIED);

        $channelPlayer = new ChannelPlayer();
        $channelPlayer
            ->setChannel($channel)
            ->setParticipant($player)
        ;
        $channel->addParticipant($channelPlayer);

        $this->testVote(MessageVoter::CREATE, $message, $user, Voter::ACCESS_GRANTED);

        $player->setGameStatus(GameStatusEnum::FINISHED);

        $this->testVote(MessageVoter::CREATE, $message, $user, Voter::ACCESS_DENIED);
    }

    private function testVote(
        string $attribute,
        Message $message,
        ?User $user,
        $expectedVote
    ) {
        $token = new AnonymousToken('secret', 'anonymous');
        if ($user) {
            $token = new UsernamePasswordToken(
                $user, 'credentials', 'memory'
            );
        }

        $this->assertEquals(
            $expectedVote,
            $this->voter->vote($token, $message, [$attribute])
        );
    }
}
