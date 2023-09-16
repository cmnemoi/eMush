<?php

namespace Mush\Tests\unit\Disease\Service;

use Mockery;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Entity\Collection\SymptomConfigCollection;
use Mush\Disease\Entity\Config\SymptomActivationRequirement;
use Mush\Disease\Entity\Config\SymptomConfig;
use Mush\Disease\Enum\SymptomActivationRequirementEnum;
use Mush\Disease\Service\SymptomActivationRequirementService;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;

class SymptomConditionServiceTest extends TestCase
{
    /** @var EventServiceInterface|Mockery\Mock */
    private EventServiceInterface $eventService;

    /** @var RandomServiceInterface|Mockery\Mock */
    private RandomServiceInterface $randomService;

    private SymptomActivationRequirementService $service;

    /**
     * @before
     */
    public function before()
    {
        $this->eventService = \Mockery::mock(EventServiceInterface::class);
        $this->randomService = \Mockery::mock(RandomServiceInterface::class);

        $this->service = new SymptomActivationRequirementService(
            $this->eventService,
            $this->randomService,
        );
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testItemInRoomSymptomActivationRequirement()
    {
        $daedalus = new Daedalus();

        $room = new Place();

        $document = new GameItem($room);
        $document->setName(ItemEnum::DOCUMENT);

        $player = new Player();
        $player->setDaedalus($daedalus);
        $player->setPlace($room);

        $symptomCondtion = new SymptomActivationRequirement(SymptomActivationRequirementEnum::ITEM_IN_ROOM);
        $symptomCondtion->setActivationRequirement(ItemEnum::DOCUMENT);

        $symptomConfig = new SymptomConfig('disease');
        $symptomConfig->setTrigger(ActionEvent::POST_ACTION);
        $symptomConfig->addSymptomActivationRequirement($symptomCondtion);

        $symptomConfigs = new SymptomConfigCollection([$symptomConfig]);

        $result = $this->service->getActiveSymptoms($symptomConfigs, $player, ['reason']);
        $this->assertEquals($result, $symptomConfigs);

        $room->removeEquipment($document);

        $result = $this->service->getActiveSymptoms($symptomConfigs, $player, ['reason']);
        $this->assertEmpty($result);
    }

    public function testRandomSymptomActivationRequirement()
    {
        $player = new Player();

        $symptomCondtion = new SymptomActivationRequirement(SymptomActivationRequirementEnum::RANDOM);
        $symptomCondtion->setValue(50);

        $symptomConfig = new SymptomConfig('disease');
        $symptomConfig->setTrigger(EventEnum::NEW_CYCLE);
        $symptomConfig->addSymptomActivationRequirement($symptomCondtion);

        $symptomConfigs = new SymptomConfigCollection([$symptomConfig]);

        $this->randomService->shouldReceive('isSuccessful')->with(50)->once()->andReturn(true);
        $result = $this->service->getActiveSymptoms($symptomConfigs, $player, ['reason']);
        $this->assertEquals($result, $symptomConfigs);

        $this->randomService->shouldReceive('isSuccessful')->with(50)->once()->andReturn(false);
        $result = $this->service->getActiveSymptoms($symptomConfigs, $player, ['reason']);
        $this->assertEmpty($result);
    }

    public function testReasonSymptomActivationRequirement()
    {
        $player = new Player();

        $symptomCondtion = new SymptomActivationRequirement(SymptomActivationRequirementEnum::REASON);
        $symptomCondtion->setActivationRequirement(ActionEnum::MOVE);

        $symptomConfig = new SymptomConfig('disease');
        $symptomConfig->setTrigger(ActionEvent::POST_ACTION);
        $symptomConfig->addSymptomActivationRequirement($symptomCondtion);

        $symptomConfigs = new SymptomConfigCollection([$symptomConfig]);

        $result = $this->service->getActiveSymptoms($symptomConfigs, $player, [ActionEnum::MOVE]);
        $this->assertEquals($result, $symptomConfigs);

        $result = $this->service->getActiveSymptoms($symptomConfigs, $player, [ActionEnum::DROP]);
        $this->assertEmpty($result);
    }

    public function testPlayerEquipmentSymptomActivationRequirement()
    {
        $daedalus = new Daedalus();

        $room = new Place();

        $player = new Player();
        $player->setDaedalus($daedalus);
        $player->setPlace($room);

        $document = new GameItem($player);
        $document->setName(ItemEnum::DOCUMENT);

        $symptomCondtion = new SymptomActivationRequirement(SymptomActivationRequirementEnum::PLAYER_EQUIPMENT);
        $symptomCondtion->setActivationRequirement(ItemEnum::DOCUMENT);

        $symptomConfig = new SymptomConfig('disease');
        $symptomConfig->setTrigger(ActionEvent::POST_ACTION);
        $symptomConfig->addSymptomActivationRequirement($symptomCondtion);

        $symptomConfigs = new SymptomConfigCollection([$symptomConfig]);

        $result = $this->service->getActiveSymptoms($symptomConfigs, $player, ['reason']);
        $this->assertEquals($result, $symptomConfigs);

        $player->removeEquipment($document);

        $result = $this->service->getActiveSymptoms($symptomConfigs, $player, ['reason']);
        $this->assertEmpty($result);
    }

    public function testMushInRoomSymptomActivationRequirement()
    {
        $daedalus = new Daedalus();

        $room = new Place();
        $otherRoom = new Place();

        $human = new Player();
        $playerInfo = new PlayerInfo($human, new User(), new CharacterConfig());
        $human
            ->setDaedalus($daedalus)
            ->setPlace($room)
            ->setPlayerInfo($playerInfo)
        ;

        $mush = new Player();
        $mushPlayerInfo = new PlayerInfo($mush, new User(), new CharacterConfig());
        $mush
            ->setDaedalus($daedalus)
            ->setPlace($room)
            ->setPlayerInfo($mushPlayerInfo)
        ;

        $statusConfig = new StatusConfig();
        $statusConfig->setStatusName(PlayerStatusEnum::MUSH);
        $mushStatus = new Status($mush, $statusConfig);

        $mush->addStatus($mushStatus);

        $symptomCondtion = new SymptomActivationRequirement(SymptomActivationRequirementEnum::PLAYER_IN_ROOM);
        $symptomCondtion->setActivationRequirement(SymptomActivationRequirementEnum::MUSH_IN_ROOM);

        $symptomConfig = new SymptomConfig('disease');
        $symptomConfig->setTrigger(ActionEvent::POST_ACTION);
        $symptomConfig->addSymptomActivationRequirement($symptomCondtion);

        $symptomConfigs = new SymptomConfigCollection([$symptomConfig]);

        $result = $this->service->getActiveSymptoms($symptomConfigs, $human, ['reason']);
        $this->assertEquals($result, $symptomConfigs);

        $mush->changePlace($otherRoom);

        $result = $this->service->getActiveSymptoms($symptomConfigs, $human, ['reason']);
        $this->assertEmpty($result);
    }

    public function testNotAloneSymptomActivationRequirement()
    {
        $daedalus = new Daedalus();

        $room = new Place();
        $otherRoom = new Place();

        $player = new Player();
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());
        $player
            ->setDaedalus($daedalus)
            ->setPlace($room)
            ->setPlayerInfo($playerInfo)
        ;

        $otherPlayer = new Player();
        $otherPlayerInfo = new PlayerInfo($otherPlayer, new User(), new CharacterConfig());
        $otherPlayer
            ->setDaedalus($daedalus)
            ->setPlace($room)
            ->setPlayerInfo($otherPlayerInfo)
        ;

        $symptomCondtion = new SymptomActivationRequirement(SymptomActivationRequirementEnum::PLAYER_IN_ROOM);
        $symptomCondtion->setActivationRequirement(SymptomActivationRequirementEnum::NOT_ALONE);

        $symptomConfig = new SymptomConfig('disease');
        $symptomConfig->setTrigger(ActionEvent::POST_ACTION);
        $symptomConfig->addSymptomActivationRequirement($symptomCondtion);

        $symptomConfigs = new SymptomConfigCollection([$symptomConfig]);

        $result = $this->service->getActiveSymptoms($symptomConfigs, $player, ['reason']);
        $this->assertEquals($result, $symptomConfigs);

        $otherPlayer->changePlace($otherRoom);

        $result = $this->service->getActiveSymptoms($symptomConfigs, $player, ['reason']);
        $this->assertEmpty($result);
    }
}
