<?php

namespace Mush\Test\Game\Service;

use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Game\Entity\CharacterConfig;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Service\RandomService;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;
use Mush\Room\Entity\Room;
use PHPUnit\Framework\TestCase;

class RandomServiceTest extends TestCase
{
    private RandomService $service;

    /**
     * @before
     */
    public function before()
    {
        $this->service = new RandomService();
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testRandom()
    {
        for ($i = 1; $i <= 50; ++$i) {
            $this->assertGreaterThan(-1, $this->service->random(0, 10));
            $this->assertLessThan(11, $this->service->random(0, 10));
        }
        $this->assertIsInt($this->service->random(0, 10));
        $this->assertEquals(10, $this->service->random(10, 10));
    }

    public function testRandomPercent()
    {
        for ($i = 1; $i <= 50; ++$i) {
            $this->assertGreaterThan(0, $this->service->randomPercent());
            $this->assertLessThan(101, $this->service->randomPercent());
        }
        $this->assertIsInt($this->service->randomPercent());
    }

    public function testIsSuccessfull()
    {
        $this->assertIsBool($this->service->isSuccessfull(50));
        $this->assertTrue($this->service->isSuccessfull(100));
        $this->assertFalse($this->service->isSuccessfull(0));
    }

    public function testGetRandomPlayer()
    {
        $playerCollection = new PlayerCollection();

        $playerConfig1 = new CharacterConfig();
        $playerConfig1->setName('player1');
        $player1 = new Player();
        $player1->setCharacterConfig($playerConfig1);

        $playerCollection->add($player1);

        $this->assertEquals($player1, $this->service->getRandomPlayer($playerCollection));

        $player2 = new Player();
        $playerConfig2 = new CharacterConfig();
        $playerConfig2->setName('player2');
        $player2->setCharacterConfig($playerConfig2);
        $playerCollection->add($player2);

        $player3 = new Player();
        $playerConfig3 = new CharacterConfig();
        $playerConfig3->setName('player3');
        $player3->setCharacterConfig($playerConfig3);
        $playerCollection->add($player3);

        $player4 = new Player();
        $playerConfig4 = new CharacterConfig();
        $playerConfig4->setName('player4');
        $player4->setCharacterConfig($playerConfig4);
        $playerCollection->add($player4);

        $player5 = new Player();
        $playerConfig5 = new CharacterConfig();
        $playerConfig5->setName('player5');
        $player5->setCharacterConfig($playerConfig5);
        $playerCollection->add($player5);

        $nbPlayer1 = 0;
        $nbPlayer2 = 0;
        $nbPlayer3 = 0;
        $nbPlayer4 = 0;
        $nbPlayer5 = 0;
        for ($i = 1; $i <= 500; ++$i) {
            $randomPlayer = $this->service->getRandomPlayer($playerCollection);
            switch ($randomPlayer) {
                case $player1:
                    $nbPlayer1 = $nbPlayer1 + 1;
                    break;
                case $player2:
                    $nbPlayer2 = $nbPlayer2 + 1;
                    break;
                case $player3:
                    $nbPlayer3 = $nbPlayer3 + 1;
                    break;
                case $player4:
                    $nbPlayer4 = $nbPlayer4 + 1;
                    break;
                case $player5:
                    $nbPlayer5 = $nbPlayer5 + 1;
                    break;
            }
        }

        //Xi2 law with 5 degrees of freedom and 95% confidence is 20.52
        $xiTwo = ($nbPlayer1 - 100) * ($nbPlayer1 - 100) / 100 +
            ($nbPlayer2 - 100) * ($nbPlayer2 - 100) / 100 +
            ($nbPlayer3 - 100) * ($nbPlayer3 - 100) / 100 +
            ($nbPlayer4 - 100) * ($nbPlayer4 - 100) / 100 +
            ($nbPlayer5 - 100) * ($nbPlayer5 - 100) / 100;

        $this->assertLessThan(20.52, $xiTwo);
    }

    public function testGetPlayerInRoom()
    {
        $room = new Room();
        $player1 = new Player();
        $player2 = new Player();
        $room
            ->addPlayer($player1)
            ->addPlayer($player2)
        ;

        $this->assertInstanceOf(Player::class, $this->service->getPlayerInRoom($room));
    }

    public function testGetAlivePlayerInDaedalus()
    {
        $player1 = new Player();
        $player2 = new Player();
        $player1
            ->setGameStatus(GameStatusEnum::CURRENT)
        ;
        $player2
            ->setGameStatus(GameStatusEnum::FINISHED)
        ;

        $daedalus = new Daedalus();
        $daedalus
            ->addPlayer($player2)
            ->addPlayer($player1)
        ;

        for ($i = 1; $i <= 10; ++$i) {
            $this->assertEquals($player1, $this->service->getAlivePlayerInDaedalus($daedalus));
        }
    }

    public function testGetItemInRoom()
    {
        $room = new Room();
        $equipment = new GameEquipment();
        $item = new GameItem();
        $room
            ->addEquipment($equipment)
            ->addEquipment($item)
        ;

        for ($i = 1; $i <= 10; ++$i) {
            $this->assertInstanceOf(GameItem::class, $this->service->getItemInRoom($room));
            $this->assertEquals($item, $this->service->getItemInRoom($room));
        }
    }

    public function testGetRandomElements()
    {
        $players = ['player1'];
        $this->assertEquals(['player1'], $this->service->getRandomElements($players));

        $players = ['player1', 'player2'];
        $result = $this->service->getRandomElements($players, 2);
        $this->assertContains('player1', $result);
        $this->assertContains('player2', $result);

        $players = ['player1', 'player2', 'player3', 'player4', 'player5'];
        $nbPlayer1 = 0;
        $nbPlayer2 = 0;
        $nbPlayer3 = 0;
        $nbPlayer4 = 0;
        $nbPlayer5 = 0;
        for ($i = 1; $i <= 500; ++$i) {
            $randomPlayer = $this->service->getRandomElements($players);
            switch ($randomPlayer) {
                case [0 => 'player1']:
                    $nbPlayer1 = $nbPlayer1 + 1;
                    break;
                case [1 => 'player2']:
                    $nbPlayer2 = $nbPlayer2 + 1;
                    break;
                case [2 => 'player3']:
                    $nbPlayer3 = $nbPlayer3 + 1;
                    break;
                case [3 => 'player4']:
                    $nbPlayer4 = $nbPlayer4 + 1;
                    break;
                case [4 => 'player5']:
                    $nbPlayer5 = $nbPlayer5 + 1;
                    break;
            }
        }

        //Xi2 law with 5 degrees of freedom and 99.9% confidence is 20.52
        $xiTwo = ($nbPlayer1 - 100) * ($nbPlayer1 - 100) / 100 +
            ($nbPlayer2 - 100) * ($nbPlayer2 - 100) / 100 +
            ($nbPlayer3 - 100) * ($nbPlayer3 - 100) / 100 +
            ($nbPlayer4 - 100) * ($nbPlayer4 - 100) / 100 +
            ($nbPlayer5 - 100) * ($nbPlayer5 - 100) / 100;

        $this->assertLessThan(20.52, $xiTwo);
    }

    public function testGetSingleRandomElementFromProbaArray()
    {
        $players = ['player1' => 1];
        $this->assertEquals('player1', $this->service->getSingleRandomElementFromProbaArray($players));

        $players = ['player1' => 1, 'player2' => 0];
        $this->assertEquals('player1', $this->service->getSingleRandomElementFromProbaArray($players));

        $players = ['player1' => 5, 'player2' => 1, 'player3' => 2, 'player4' => 2, 'player5' => 0];
        $nbPlayer1 = 0;
        $nbPlayer2 = 0;
        $nbPlayer3 = 0;
        $nbPlayer4 = 0;

        for ($i = 1; $i <= 500; ++$i) {
            $randomPlayer = $this->service->getSingleRandomElementFromProbaArray($players);
            $this->assertNotEquals('player5', $randomPlayer);

            switch ($randomPlayer) {
                case 'player1':
                    $nbPlayer1 = $nbPlayer1 + 1;
                    break;
                case 'player2':
                    $nbPlayer2 = $nbPlayer2 + 1;
                    break;
                case 'player3':
                    $nbPlayer3 = $nbPlayer3 + 1;
                    break;
                case 'player4':
                    $nbPlayer4 = $nbPlayer4 + 1;
                    break;
            }
        }

        //Xi2 law with 4 degrees of freedom and 99.9% confidence is 18.47
        $xiTwo = ($nbPlayer1 - 250) * ($nbPlayer1 - 250) / 250 +
            ($nbPlayer2 - 50) * ($nbPlayer2 - 50) / 50 +
            ($nbPlayer3 - 100) * ($nbPlayer3 - 100) / 100 +
            ($nbPlayer4 - 100) * ($nbPlayer4 - 100) / 100;

        $this->assertLessThan(18.47, $xiTwo);
    }

    public function testGetRandomElementsFromProbaArray()
    {
        $players = ['player1' => 1];
        $this->assertEquals(['player1'], $this->service->getRandomElementsFromProbaArray($players, 1));

        $players = ['player1' => 25, 'player2' => 5, 'player3' => 10, 'player4' => 10, 'player5' => 0];

        for ($i = 1; $i <= 10; ++$i) {
            $randomPlayer = $this->service->getRandomElementsFromProbaArray($players, 2);
            $this->assertNotContains('player5', $randomPlayer);
        }
    }
}
