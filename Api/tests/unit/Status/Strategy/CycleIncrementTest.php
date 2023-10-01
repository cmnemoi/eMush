<?php

namespace Mush\Tests\unit\Status\Strategy;

use Mockery;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\EventEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Status\ChargeStrategies\AbstractChargeStrategy;
use Mush\Status\ChargeStrategies\CycleIncrement;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;

class CycleIncrementTest extends TestCase
{
    /** @var StatusServiceInterface|Mockery\Mock */
    private StatusServiceInterface $statusService;
    private AbstractChargeStrategy $strategy;

    /**
     * @before
     */
    public function before()
    {
        $this->statusService = \Mockery::mock(StatusServiceInterface::class);

        $this->strategy = new CycleIncrement($this->statusService);
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testIncrement()
    {
        $status = $this->createStatus();
        $time = new \DateTime();

        $this->statusService->shouldReceive('updateCharge')->with($status, 1, [EventEnum::NEW_CYCLE], $time)->once();

        $this->strategy->execute($status, [EventEnum::NEW_CYCLE], $time);
    }

    private function createStatus(): ChargeStatus
    {
        $place = new Place();
        $place->setType(PlaceTypeEnum::ROOM);
        $player = new Player();
        $player->setPlace($place);
        $user = new User();
        $characterConfig = new CharacterConfig();
        $characterConfig->setCharacterName(CharacterEnum::CHAO);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);
        $statusConfig = new ChargeStatusConfig();
        $statusConfig
            ->setChargeStrategy(ChargeStrategyTypeEnum::CYCLE_INCREMENT)
            ->setMaxCharge(10)
            ->setStatusName('status')
        ;
        $status = new ChargeStatus($player, $statusConfig);
        $status->getVariableByName($status->getName())->setValue(0);

        return $status;
    }
}
