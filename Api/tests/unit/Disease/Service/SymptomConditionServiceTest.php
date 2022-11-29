<?php

namespace Mush\Test\Disease\Service;

use Mockery;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Entity\Collection\SymptomConfigCollection;
use Mush\Disease\Entity\Config\SymptomCondition;
use Mush\Disease\Entity\Config\SymptomConfig;
use Mush\Disease\Enum\SymptomConditionEnum;
use Mush\Disease\Service\SymptomConditionService;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Service\ModifierServiceInterface;
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
    /** @var ModifierServiceInterface|Mockery\Mock */
    private ModifierServiceInterface $modifierService;

    /** @var RandomServiceInterface|Mockery\Mock */
    private RandomServiceInterface $randomService;

    private SymptomConditionService $service;

    /**
     * @before
     */
    public function before()
    {
        $this->modifierService = Mockery::mock(ModifierServiceInterface::class);
        $this->randomService = Mockery::mock(RandomServiceInterface::class);

        $this->service = new SymptomConditionService(
            $this->modifierService,
            $this->randomService,
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testItemInRoomSymptomCondition()
    {
        $daedalus = new Daedalus();

        $room = new Place();

        $document = new GameItem();
        $document->setName(ItemEnum::DOCUMENT);
        $document->setHolder($room);

        $player = new Player();
        $player->setDaedalus($daedalus);
        $player->setPlace($room);

        $symptomCondtion = new SymptomCondition(SymptomConditionEnum::ITEM_IN_ROOM);
        $symptomCondtion->setCondition(ItemEnum::DOCUMENT);

        $symptomConfig = new SymptomConfig('disease');
        $symptomConfig->setTrigger(ActionEvent::POST_ACTION);
        $symptomConfig->addSymptomCondition($symptomCondtion);

        $symptomConfigs = new SymptomConfigCollection([$symptomConfig]);

        $result = $this->service->getActiveSymptoms($symptomConfigs, $player, 'reason');
        $this->assertEquals($result, $symptomConfigs);

        $room->removeEquipment($document);

        $result = $this->service->getActiveSymptoms($symptomConfigs, $player, 'reason');
        $this->assertEmpty($result);
    }

    public function testRandomSymptomCondition()
    {
        $player = new Player();

        $symptomCondtion = new SymptomCondition(SymptomConditionEnum::RANDOM);
        $symptomCondtion->setValue(50);

        $symptomConfig = new SymptomConfig('disease');
        $symptomConfig->setTrigger(EventEnum::NEW_CYCLE);
        $symptomConfig->addSymptomCondition($symptomCondtion);

        $symptomConfigs = new SymptomConfigCollection([$symptomConfig]);

        $this->randomService->shouldReceive('isSuccessful')->with(50)->once()->andReturn(true);
        $result = $this->service->getActiveSymptoms($symptomConfigs, $player, 'reason');
        $this->assertEquals($result, $symptomConfigs);

        $this->randomService->shouldReceive('isSuccessful')->with(50)->once()->andReturn(false);
        $result = $this->service->getActiveSymptoms($symptomConfigs, $player, 'reason');
        $this->assertEmpty($result);
    }

    public function testReasonSymptomCondition()
    {
        $player = new Player();

        $symptomCondtion = new SymptomCondition(SymptomConditionEnum::REASON);
        $symptomCondtion->setCondition(ActionEnum::MOVE);

        $symptomConfig = new SymptomConfig('disease');
        $symptomConfig->setTrigger(ActionEvent::POST_ACTION);
        $symptomConfig->addSymptomCondition($symptomCondtion);

        $symptomConfigs = new SymptomConfigCollection([$symptomConfig]);

        $result = $this->service->getActiveSymptoms($symptomConfigs, $player, ActionEnum::MOVE);
        $this->assertEquals($result, $symptomConfigs);

        $result = $this->service->getActiveSymptoms($symptomConfigs, $player, ActionEnum::DROP);
        $this->assertEmpty($result);
    }

    public function testPlayerEquipmentSymptomCondition()
    {
        $daedalus = new Daedalus();

        $room = new Place();

        $player = new Player();
        $player->setDaedalus($daedalus);
        $player->setPlace($room);

        $document = new GameItem();
        $document->setName(ItemEnum::DOCUMENT);
        $document->setHolder($player);

        $symptomCondtion = new SymptomCondition(SymptomConditionEnum::PLAYER_EQUIPMENT);
        $symptomCondtion->setCondition(ItemEnum::DOCUMENT);

        $symptomConfig = new SymptomConfig('disease');
        $symptomConfig->setTrigger(ActionEvent::POST_ACTION);
        $symptomConfig->addSymptomCondition($symptomCondtion);

        $symptomConfigs = new SymptomConfigCollection([$symptomConfig]);

        $result = $this->service->getActiveSymptoms($symptomConfigs, $player, 'reason');
        $this->assertEquals($result, $symptomConfigs);

        $player->removeEquipment($document);

        $result = $this->service->getActiveSymptoms($symptomConfigs, $player, 'reason');
        $this->assertEmpty($result);
    }

    public function testMushInRoomSymptomCondition()
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
        $statusConfig->setName(PlayerStatusEnum::MUSH);
        $mushStatus = new Status($mush, $statusConfig);

        $mush->addStatus($mushStatus);

        $symptomCondtion = new SymptomCondition(SymptomConditionEnum::PLAYER_IN_ROOM);
        $symptomCondtion->setCondition(SymptomConditionEnum::MUSH_IN_ROOM);

        $symptomConfig = new SymptomConfig('disease');
        $symptomConfig->setTrigger(ActionEvent::POST_ACTION);
        $symptomConfig->addSymptomCondition($symptomCondtion);

        $symptomConfigs = new SymptomConfigCollection([$symptomConfig]);

        $result = $this->service->getActiveSymptoms($symptomConfigs, $human, 'reason');
        $this->assertEquals($result, $symptomConfigs);

        $mush->changePlace($otherRoom);

        $result = $this->service->getActiveSymptoms($symptomConfigs, $human, 'reason');
        $this->assertEmpty($result);
    }

    public function testNotAloneSymptomCondition()
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

        $symptomCondtion = new SymptomCondition(SymptomConditionEnum::PLAYER_IN_ROOM);
        $symptomCondtion->setCondition(SymptomConditionEnum::NOT_ALONE);

        $symptomConfig = new SymptomConfig('disease');
        $symptomConfig->setTrigger(ActionEvent::POST_ACTION);
        $symptomConfig->addSymptomCondition($symptomCondtion);

        $symptomConfigs = new SymptomConfigCollection([$symptomConfig]);

        $result = $this->service->getActiveSymptoms($symptomConfigs, $player, 'reason');
        $this->assertEquals($result, $symptomConfigs);

        $otherPlayer->changePlace($otherRoom);

        $result = $this->service->getActiveSymptoms($symptomConfigs, $player, 'reason');
        $this->assertEmpty($result);
    }
}
