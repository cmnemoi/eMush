<?php

namespace Mush\Test\Game\Service;

use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
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

    public function randomTest()
    {
        for ($i = 1; $i <= 50; ++$i) {
            $this->assertGreaterThan(0, $this->service->random(0, 10));
            $this->assertLessThan(10, $this->service->random(0, 10));
        }
        $this->assertInstanceOf(int::class, $this->service->random(0, 10));
        $this->assertEquals(10, $this->service->random(10, 10));
    }

    public function randomPercentTest()
    {
        for ($i = 1; $i <= 50; ++$i) {
            $this->assertGreaterThan(0, $this->service->randomPercent());
            $this->assertLessThan(100, $this->service->randomPercent());
        }
        $this->assertInstanceOf(int::class, $this->service->randomPercent());
    }

    public function isSuccessfullTest()
    {
        $this->assertInstanceOf(bool::class, $this->service->isSuccessfull(50));
        $this->assertTrue($this->service->isSuccessfull(100));
        $this->assertFalse($this->service->isSuccessfull(0));
    }

    public function getRandomPlayerTest()
    {
        $playerCollection = new PlayerCollection();
        $player1 = new Player();
        $playerCollection->add($player1);

        $this->assertEquals($player1, $this->service->getRandomPlayer($playerCollection));

        $player2 = new Player();
        $playerCollection->add($player2);
        $player3 = new Player();
        $playerCollection->add($player3);
        $player4 = new Player();
        $playerCollection->add($player4);
        $player5 = new Player();
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

        //Xi2 law with 5 degrees of freedom and 95% confidence is 15.09
        $xiTwo = ($nbPlayer1 - 100) * ($nbPlayer1 - 100) / 100 +
            ($nbPlayer2 - 100) * ($nbPlayer2 - 100) / 100 +
            ($nbPlayer3 - 100) * ($nbPlayer3 - 100) / 100 +
            ($nbPlayer4 - 100) * ($nbPlayer4 - 100) / 100 +
            ($nbPlayer5 - 100) * ($nbPlayer5 - 100) / 100;

        $this->assertLessThan(15.09, $xiTwo);
    }

    public function getPlayerInRoomTest()
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

    public function getAlivePlayerInDaedalusTest()
    {
        $room = new Room();
        $greatBeyond = new Room();
        $daedalus = new Daedalus();
        $daedalus
            ->addRoom($room)
            ->addRoom($greatBeyond)
        ;

        $player1 = new Player();
        $player2 = new Player();
        $room
            ->addPlayer($player1)
        ;
        $greatBeyond
            ->addPlayer($player2)
        ;

        $player1->setGameStatus(GameStatusEnum::CURRENT);
        $player2->setGameStatus(GameStatusEnum::FINISHED);

        for ($i = 1; $i <= 10; ++$i) {
            $this->assertEquals($player1, $this->service->getAlivePlayerInDaedalus($daedalus));
        }
    }

    public function getItemInRoomTest()
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

    public function getRandomElementsTest()
    {
        $players = ['player1'];
        $this->assertEquals(['player1'], $this->service->getRandomElements($players));

        $players = ['player1', 'player2'];
        $this->assertEquals(['player1', 'player2'], $this->service->getRandomElements($players, 2));

        $players = ['player1', 'player2', 'player3', 'player4', 'player5'];
        $nbPlayer1 = 0;
        $nbPlayer2 = 0;
        $nbPlayer3 = 0;
        $nbPlayer4 = 0;
        $nbPlayer5 = 0;
        for ($i = 1; $i <= 500; ++$i) {
            $randomPlayer = $this->service->getRandomElements($players, 1);
            switch ($randomPlayer) {
                case ['player1']:
                    $nbPlayer1 = $nbPlayer1 + 1;
                    break;
                case ['player2']:
                    $nbPlayer2 = $nbPlayer2 + 1;
                    break;
                case ['player3']:
                    $nbPlayer3 = $nbPlayer3 + 1;
                    break;
                case ['player4']:
                    $nbPlayer4 = $nbPlayer4 + 1;
                    break;
                case ['player5']:
                    $nbPlayer5 = $nbPlayer5 + 1;
                    break;
            }
        }

        //Xi2 law with 5 degrees of freedom and 95% confidence is 15.09
        $xiTwo = ($nbPlayer1 - 100) * ($nbPlayer1 - 100) / 100 +
            ($nbPlayer2 - 100) * ($nbPlayer2 - 100) / 100 +
            ($nbPlayer3 - 100) * ($nbPlayer3 - 100) / 100 +
            ($nbPlayer4 - 100) * ($nbPlayer4 - 100) / 100 +
            ($nbPlayer5 - 100) * ($nbPlayer5 - 100) / 100;

        $this->assertLessThan(15.09, $xiTwo);
    }

    public function getSingleRandomElementFromProbaArrayTest()
    {
        $players = ['player1' => 1];
        $this->assertEquals(['player1'], $this->service->getSingleRandomElementFromProbaArray($players));

        $players = ['player1' => 1, 'player2' => 0];
        $this->assertEquals(['player1'], $this->service->getSingleRandomElementFromProbaArray($players));

        $players = ['player1' => 2.5, 'player2' => 0.5, 'player3' => 1, 'player4' => 1, 'player5' => 0];
        $nbPlayer1 = 0;
        $nbPlayer2 = 0;
        $nbPlayer3 = 0;
        $nbPlayer4 = 0;

        for ($i = 1; $i <= 400; ++$i) {
            $randomPlayer = $this->service->getSingleRandomElementFromProbaArray($players);
            $this->assertNotEquals(['player5'], $randomPlayer);

            switch ($randomPlayer) {
                case ['player1']:
                    $nbPlayer1 = $nbPlayer1 + 1;
                    break;
                case ['player2']:
                    $nbPlayer2 = $nbPlayer2 + 1;
                    break;
                case ['player3']:
                    $nbPlayer3 = $nbPlayer3 + 1;
                    break;
                case ['player4']:
                    $nbPlayer4 = $nbPlayer4 + 1;
                    break;
            }
        }

        //Xi2 law with 4 degrees of freedom and 95% confidence is 9.49
        $xiTwo = ($nbPlayer1 - 250) * ($nbPlayer1 - 250) / 200 +
            ($nbPlayer2 - 50) * ($nbPlayer2 - 50) / 100 +
            ($nbPlayer3 - 100) * ($nbPlayer3 - 100) / 100 +
            ($nbPlayer4 - 100) * ($nbPlayer4 - 100) / 100;

        $this->assertLessThan(9.49, $xiTwo);
    }

    public function getRandomElementsFromProbaArrayTest()
    {
        $players = ['player1' => 1];
        $this->assertEquals(['player1'], $this->service->getRandomElementsFromProbaArray($players, 1));

        $players = ['player1' => 2.5, 'player2' => 0.5, 'player3' => 1, 'player4' => 1, 'player5' => 0];
        $nbPlayer1 = 0;
        $nbPlayer2 = 0;
        $nbPlayer3 = 0;
        $nbPlayer4 = 0;

        for ($i = 1; $i <= 10; ++$i) {
            $randomPlayer = $this->service->getRandomElementsFromProbaArray($players, 2);
            $this->assertNotContains('player5', $randomPlayer);
        }
    }
}
