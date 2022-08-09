<?php

namespace unit\Player\Voter;

use Mush\Communication\Entity\Message;
use Mush\Communication\Voter\MessageVoter;
use Mush\Player\Entity\Player;
use Mush\Player\Voter\PlayerVoter;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PlayerVoterTest extends TestCase
{
    private Voter $voter;

    /**
     * @before
     */
    public function before()
    {
        $this->voter = new PlayerVoter();
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

        $this->testVote(PlayerVoter::PLAYER_VIEW, new Player(), $user, Voter::ACCESS_GRANTED);
    }

    public function testCanCreate()
    {
        $user = new User();
        $player = new Player();

        $this->testVote(PlayerVoter::PLAYER_CREATE, null, $user, Voter::ACCESS_GRANTED);

        $user->setCurrentGame($player);
        $this->testVote(PlayerVoter::PLAYER_CREATE, null, $user, Voter::ACCESS_DENIED);
    }

    public function testCanEnd()
    {
        $user = new User();
        $player = new Player();

        $this->testVote(PlayerVoter::PLAYER_END, $player, $user, Voter::ACCESS_DENIED);

        $user->setCurrentGame($player);
        $this->testVote(PlayerVoter::PLAYER_END, $player, $user, Voter::ACCESS_GRANTED);
    }

    private function testVote(
        string $attribute,
        ?Player $player,
        User $user,
        $expectedVote
    ) {
        $token = new UsernamePasswordToken(
            $user, 'credentials', []
        );

        $this->assertEquals(
            $expectedVote,
            $this->voter->vote($token, $player, [$attribute])
        );
    }
}
