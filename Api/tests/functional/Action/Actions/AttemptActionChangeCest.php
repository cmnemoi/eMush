<?php

namespace Mush\Tests\functional\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\Disassemble;
use Mush\Action\Actions\Repair;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionHolderEnum;
use Mush\Action\Enum\ActionRangeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\UseCase\AddSkillToPlayerUseCase;
use Mush\Status\Entity\Attempt;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

class AttemptActionChangeCest
{
    private Repair $repairAction;
    private Disassemble $disassembleAction;

    private ActionConfig $actionRepair;
    private ActionConfig $actionDisassemble;
    private GameEquipment $gameEquipment;
    private Player $player;

    private AddSkillToPlayerUseCase $addSkillToPlayerUseCase;

    private function setup(FunctionalTester $I): void
    {
        $attemptConfig = $I->grabEntityFromRepository(ChargeStatusConfig::class, ['statusName' => StatusEnum::ATTEMPT]);

        $statusConfig = $I->grabEntityFromRepository(StatusConfig::class, ['statusName' => EquipmentStatusEnum::BROKEN]);

        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $gameConfig->setStatusConfigs(new ArrayCollection([$attemptConfig, $statusConfig]));
        $I->flushToDatabase();

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);

        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->grabEntityFromRepository(CharacterConfig::class, ['name' => 'kuan_ti']);

        $this->player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
        ]);

        $this->player->setPlayerVariables($characterConfig);
        $this->player
            ->setActionPoint(10);
        $I->flushToDatabase($this->player);

        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($this->player, $user, $characterConfig);
        $I->haveInRepository($playerInfo);
        $this->player->setPlayerInfo($playerInfo);
        $I->refreshEntities($this->player);

        $this->addSkillToPlayerUseCase->execute(
            skill: SkillEnum::TECHNICIAN,
            player: $this->player,
        );

        $this->actionRepair = new ActionConfig();
        $this->actionRepair
            ->setName(ActionEnum::REPAIR->value)
            ->setActionName(ActionEnum::REPAIR)
            ->setActionCost(1)
            ->setSuccessRate(0)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT);
        $I->haveInRepository($this->actionRepair);

        $this->actionDisassemble = new ActionConfig();
        $this->actionDisassemble
            ->setName(ActionEnum::DISASSEMBLE->value)
            ->setActionName(ActionEnum::DISASSEMBLE)
            ->setActionCost(1)
            ->setSuccessRate(0)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT);
        $I->haveInRepository($this->actionDisassemble);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, ['isBreakable' => true]);

        $equipmentConfig->setActionConfigs(new ArrayCollection([$this->actionDisassemble, $this->actionRepair]));

        $this->gameEquipment = new GameItem($room);

        $this->gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('some name');
        $I->haveInRepository($this->gameEquipment);

        $status = new Status($this->gameEquipment, $statusConfig);
        $I->haveInRepository($status);
    }

    public function _before(FunctionalTester $I)
    {
        $this->repairAction = $I->grabService(Repair::class);
        $this->disassembleAction = $I->grabService(Disassemble::class);

        $this->addSkillToPlayerUseCase = $I->grabService(AddSkillToPlayerUseCase::class);

        $this->setup($I);
    }

    public function testChangeAttemptAction(FunctionalTester $I)
    {
        $this->repairAction->loadParameters($this->actionRepair, $this->gameEquipment, $this->player, $this->gameEquipment);

        // Execute repair
        $this->repairAction->execute();

        /** @var Attempt $attemptStatus */
        $attemptStatus = $this->player->getStatusByNameOrThrow(StatusEnum::ATTEMPT);
        $I->assertEquals(ActionEnum::REPAIR->value, $attemptStatus->getAction());
        $I->assertEquals(1, $attemptStatus->getCharge());

        $this->repairAction->loadParameters($this->actionRepair, $this->gameEquipment, $this->player, $this->gameEquipment);

        // Execute repair a second time
        $this->repairAction->execute();

        $I->assertEquals(2, $attemptStatus->getCharge());

        $this->disassembleAction->loadParameters($this->actionDisassemble, $this->gameEquipment, $this->player, $this->gameEquipment);

        // Now execute the other action
        $this->disassembleAction->execute();

        /** @var Attempt $attemptStatus */
        $attemptStatus = $this->player->getStatusByNameOrThrow(StatusEnum::ATTEMPT);

        $I->assertEquals(ActionEnum::DISASSEMBLE->value, $attemptStatus->getAction());
        $I->assertEquals(1, $attemptStatus->getCharge());

        $this->disassembleAction->loadParameters($this->actionDisassemble, $this->gameEquipment, $this->player, $this->gameEquipment);
        $this->disassembleAction->execute();
        $I->assertEquals(2, $attemptStatus->getCharge());
    }

    public function testSuccessRateIsCorrectlyCapped(FunctionalTester $I)
    {
        $this->repairAction->loadParameters($this->actionRepair, $this->gameEquipment, $this->player, $this->gameEquipment);

        // Execute repair
        $this->repairAction->execute();

        /** @var Attempt $attemptStatus */
        $attemptStatus = $this->player->getStatusByNameOrThrow(StatusEnum::ATTEMPT);
        $I->assertEquals(ActionEnum::REPAIR->value, $attemptStatus->getAction());
        $I->assertEquals(1, $attemptStatus->getCharge());

        $this->repairAction->loadParameters($this->actionRepair, $this->gameEquipment, $this->player, $this->gameEquipment);

        // Execute repair a second and third time
        $this->repairAction->execute();
        $this->repairAction->execute();

        // now up the success chances
        $this->actionRepair->setSuccessRate(80);
        $this->repairAction->loadParameters($this->actionRepair, $this->gameEquipment, $this->player, $this->gameEquipment);

        $I->assertEquals(99, $this->repairAction->getSuccessRate());
    }

    public function testNormalizeAnotherAction(FunctionalTester $I)
    {
        $this->repairAction->loadParameters($this->actionRepair, $this->gameEquipment, $this->player, $this->gameEquipment);

        // Execute repair
        $this->repairAction->execute();

        /** @var Attempt $attemptStatus */
        $attemptStatus = $this->player->getStatusByNameOrThrow(StatusEnum::ATTEMPT);
        $I->assertEquals(ActionEnum::REPAIR->value, $attemptStatus->getAction());
        $I->assertEquals(1, $attemptStatus->getCharge());

        $this->repairAction->loadParameters($this->actionRepair, $this->gameEquipment, $this->player, $this->gameEquipment);
        // Execute repair a second time
        $this->repairAction->execute();
        $I->assertEquals(2, $attemptStatus->getCharge());

        $this->disassembleAction->loadParameters($this->actionDisassemble, $this->gameEquipment, $this->player, $this->gameEquipment);

        // check that the attempt status is still correctly set to repair
        /** @var Attempt $attemptStatus */
        $attemptStatus = $this->player->getStatusByNameOrThrow(StatusEnum::ATTEMPT);
        $I->assertEquals(ActionEnum::REPAIR->value, $attemptStatus->getAction());
        $I->assertEquals(2, $attemptStatus->getCharge());
    }
}
