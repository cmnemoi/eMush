<?php

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\ShootHunter;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Event\HunterPoolEvent;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\UseCase\AddSkillToPlayerUseCase;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class SkillPointCest extends AbstractFunctionalTest
{
    private ShootHunter $shootHunterAction;
    private ActionConfig $action;

    private AddSkillToPlayerUseCase $addSkillToPlayerUseCase;
    private EventServiceInterface $eventService;

    private Player $chao;

    private GameEquipment $turret;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->addSkillToPlayerUseCase = $I->grabService(AddSkillToPlayerUseCase::class);
        $this->eventService = $I->grabService(EventServiceInterface::class);

        $this->action = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::SHOOT_HUNTER]);
        $this->action->setDirtyRate(0)->setSuccessRate(100);

        $I->haveInRepository($this->action);

        $frontAlphaTurret = $this->createExtraPlace(RoomEnum::FRONT_ALPHA_TURRET, $I, $this->daedalus);
        $this->chao = $this->addPlayerByCharacter($I, $this->daedalus, 'chao');
        $this->chao->changePlace($frontAlphaTurret);

        $event = new HunterPoolEvent(
            $this->daedalus,
            ['test'],
            new \DateTime()
        );
        $this->eventService->callEvent($event, HunterPoolEvent::UNPOOL_HUNTERS);

        $turretConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['name' => 'turret_command_default']);
        $this->turret = new GameEquipment($this->daedalus->getPlaceByName(RoomEnum::FRONT_ALPHA_TURRET));
        $this->turret
            ->setName('turret')
            ->setEquipment($turretConfig);
        $I->haveInRepository($this->turret);

        $turretChargeStatusConfig = $I->grabEntityFromRepository(ChargeStatusConfig::class, ['name' => 'electric_charges_turret_command_default']);

        /** @var StatusServiceInterface $statusService */
        $statusService = $I->grabService(StatusServiceInterface::class);
        $statusService->createStatusFromConfig(
            $turretChargeStatusConfig,
            $this->turret,
            [],
            new \DateTime()
        );

        $this->shootHunterAction = $I->grabService(ShootHunter::class);
    }

    public function testShootWithGunmanSkillPoints(FunctionalTester $I)
    {
        /** @var Hunter $hunter */
        $hunter = $this->daedalus->getAttackingHunters()->first();

        $this->addSkillToPlayerUseCase->execute(
            skill: SkillEnum::SHOOTER,
            player: $this->chao
        );

        $shooterSkill = $this->chao->getSkillByNameOrThrow(SkillEnum::SHOOTER);
        $I->assertEquals(
            4,
            $shooterSkill->getSkillPoints()
        );

        // check the action cost
        $this->shootHunterAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $this->turret,
            player: $this->chao,
            target: $hunter
        );
        $I->assertTrue($this->shootHunterAction->isVisible());
        $I->assertEquals(0, $this->shootHunterAction->getActionPointCost());
        $I->assertEquals(
            4,
            $shooterSkill->getSkillPoints()
        );

        // Now execute the action
        $this->shootHunterAction->execute();
        $I->assertNotEquals($hunter->getHunterConfig()->getInitialHealth(), $hunter->getHealth());
        $I->assertEquals(
            $this->chao->getActionPoint(),
            $this->chao->getPlayerInfo()->getCharacterConfig()->getInitActionPoint()
        );

        $I->assertEquals(
            3,
            $shooterSkill->getSkillPoints()
        );
        $I->seeInRepository(RoomLog::class, [
            'place' => RoomEnum::FRONT_ALPHA_TURRET,
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->chao->getPlayerInfo(),
            'log' => ActionLogEnum::SHOOT_HUNTER_SUCCESS,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }
}
