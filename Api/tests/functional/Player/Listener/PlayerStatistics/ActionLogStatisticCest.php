<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Player\Listener\PlayerStatistics;

use Mush\Action\Actions\ConvertCat;
use Mush\Action\Actions\Delog;
use Mush\Action\Actions\GoBerserk;
use Mush\Action\Actions\MakeSick;
use Mush\Action\Actions\MixRationSpore;
use Mush\Action\Actions\TrapCloset;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Skill\Enum\SkillEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ActionLogStatisticCest extends AbstractFunctionalTest
{
    private GameEquipmentServiceInterface $gameEquipmentService;
    private PlayerServiceInterface $playerService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->playerService = $I->grabService(PlayerServiceInterface::class);

        $this->givenKuanTiIsMush($I);
    }

    public function shouldCountAsStealthyOnCovertAction(FunctionalTester $I): void
    {
        $this->whenKuanTiMakesChunSick($I);

        $this->thenKuanTiMustHaveDoneStealthyActions(1, $I);
        $this->thenKuanTiMustHaveDoneUnstealthyActions(0, $I);
    }

    public function shouldCountAsUnstealthyCovertActionWithMycoAlarm(FunctionalTester $I): void
    {
        $this->givenMycoAlarmInRoom();

        $this->whenKuanTiMakesChunSick($I);

        $this->thenKuanTiMustHaveDoneStealthyActions(0, $I);
        $this->thenKuanTiMustHaveDoneUnstealthyActions(1, $I);
    }

    public function shouldCountAsUnstealthyCovertActionWithCamera(FunctionalTester $I): void
    {
        $this->givenInstalledCameraInRoom();

        $this->whenKuanTiMakesChunSick($I);

        $this->thenKuanTiMustHaveDoneStealthyActions(0, $I);
        $this->thenKuanTiMustHaveDoneUnstealthyActions(1, $I);
    }

    public function shouldIncrementOnceCovertActionWithCameraAndMycoAlarm(FunctionalTester $I): void
    {
        $this->givenInstalledCameraInRoom();
        $this->givenMycoAlarmInRoom();

        $this->whenKuanTiMakesChunSick($I);

        $this->thenKuanTiMustHaveDoneStealthyActions(0, $I);
        $this->thenKuanTiMustHaveDoneUnstealthyActions(1, $I);
    }

    public function shouldCountAsStealthyCovertActionWithCameraMycoAlarmAndDeface(FunctionalTester $I): void
    {
        $this->givenInstalledCameraInRoom();
        $this->givenMycoAlarmInRoom();
        $this->givenKuanTiDelogsRoom($I);

        $this->whenKuanTiMakesChunSick($I);

        $this->thenKuanTiMustHaveDoneStealthyActions(1, $I);
        $this->thenKuanTiMustHaveDoneUnstealthyActions(0, $I);
    }

    public function shouldCountAsStealthySecretMushActionWithNoSurveillance(FunctionalTester $I): void
    {
        $this->givenChunDies();

        $this->whenKuanTiTrapsTheRoom($I);

        $this->thenKuanTiMustHaveDoneStealthyActions(1, $I);
        $this->thenKuanTiMustHaveDoneUnstealthyActions(0, $I);
    }

    public function shouldCountAsUnstealthySecretMushActionWithWitness(FunctionalTester $I): void
    {
        $this->whenKuanTiTrapsTheRoom($I);

        $this->thenKuanTiMustHaveDoneStealthyActions(0, $I);
        $this->thenKuanTiMustHaveDoneUnstealthyActions(1, $I);
    }

    public function shouldCountAsStealthySecretMushActionWithDefacedRoom(FunctionalTester $I): void
    {
        $this->givenKuanTiDelogsRoom($I);

        $this->whenKuanTiTrapsTheRoom($I);

        $this->thenKuanTiMustHaveDoneStealthyActions(1, $I);
        $this->thenKuanTiMustHaveDoneUnstealthyActions(0, $I);
    }

    public function shouldCountAsStealthyConvertCatActionWithoutMycoAlarm(FunctionalTester $I): void
    {
        $this->whenKuanTiConvertsCat($I);

        $this->thenKuanTiMustHaveDoneStealthyActions(1, $I);
        $this->thenKuanTiMustHaveDoneUnstealthyActions(0, $I);
    }

    public function shouldCountAsUnstealthyConvertCatActionWithMycoAlarm(FunctionalTester $I): void
    {
        $this->givenMycoAlarmInRoom();

        $this->whenKuanTiConvertsCat($I);

        $this->thenKuanTiMustHaveDoneStealthyActions(0, $I);
        $this->thenKuanTiMustHaveDoneUnstealthyActions(1, $I);
    }

    public function shouldNotIncrementStatisticWhenMutatingWithMycoAlarm(FunctionalTester $I): void
    {
        $this->givenMycoAlarmInRoom();

        $this->whenKuanTiMutates($I);

        $this->thenKuanTiMustHaveDoneStealthyActions(0, $I);
        $this->thenKuanTiMustHaveDoneUnstealthyActions(0, $I);
    }

    public function shouldCountAsStealthyInvisibleMushActionWithoutMycoAlarm(FunctionalTester $I): void
    {
        $this->givenInstalledCameraInRoom();

        $this->whenKuanTiContaminatesFood($I);

        $this->thenKuanTiMustHaveDoneStealthyActions(1, $I);
        $this->thenKuanTiMustHaveDoneUnstealthyActions(0, $I);
    }

    public function shouldCountAsStealthyInvisibleMushActionWithMycoAlarm(FunctionalTester $I): void
    {
        $this->givenMycoAlarmInRoom();

        $this->whenKuanTiContaminatesFood($I);

        $this->thenKuanTiMustHaveDoneStealthyActions(0, $I);
        $this->thenKuanTiMustHaveDoneUnstealthyActions(1, $I);
    }

    private function givenKuanTiIsMush(FunctionalTester $I): void
    {
        $this->convertPlayerToMush($I, $this->kuanTi);
    }

    private function givenMycoAlarmInRoom(): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::MYCO_ALARM,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenInstalledCameraInRoom(): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::CAMERA_EQUIPMENT,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenKuanTiDelogsRoom(FunctionalTester $I): void
    {
        $delog = $I->grabService(Delog::class);
        $delogConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::DELOG]);

        $this->addSkillToPlayer(SkillEnum::DEFACER, $I, $this->kuanTi);
        $delog->loadParameters(
            actionConfig: $delogConfig,
            actionProvider: $this->kuanTi,
            player: $this->kuanTi,
        );
        $delog->execute();
    }

    private function givenChunDies(): void
    {
        $this->playerService->killPlayer(
            player: $this->chun,
            endReason: EndCauseEnum::DEPRESSION,
        );
    }

    private function whenKuanTiMakesChunSick(FunctionalTester $I): void
    {
        $makeSick = $I->grabService(MakeSick::class);
        $makeSickConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::MAKE_SICK]);

        $this->addSkillToPlayer(SkillEnum::BACTEROPHILIAC, $I, $this->kuanTi);
        $makeSick->loadParameters(
            actionConfig: $makeSickConfig,
            actionProvider: $this->kuanTi,
            player: $this->kuanTi,
            target: $this->chun,
        );
        $makeSick->execute();
    }

    private function whenKuanTiTrapsTheRoom(FunctionalTester $I): void
    {
        $trapCloset = $I->grabService(TrapCloset::class);
        $trapClosetConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::TRAP_CLOSET]);

        $this->addSkillToPlayer(SkillEnum::TRAPPER, $I, $this->kuanTi);
        $this->kuanTi->setSpores(1);
        $trapCloset->loadParameters(
            actionConfig: $trapClosetConfig,
            actionProvider: $this->kuanTi,
            player: $this->kuanTi,
        );
        $trapCloset->execute();
    }

    private function whenKuanTiConvertsCat(FunctionalTester $I): void
    {
        $cat = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::SCHRODINGER,
            equipmentHolder: $this->kuanTi,
            reasons: [],
            time: new \DateTime(),
        );

        $convertCat = $I->grabService(ConvertCat::class);
        $convertCatConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::CONVERT_CAT]);

        $this->kuanTi->setSpores(1);
        $convertCat->loadParameters(
            actionConfig: $convertCatConfig,
            actionProvider: $cat,
            player: $this->kuanTi,
            target: $cat,
        );
        $convertCat->execute();
    }

    private function whenKuanTiMutates(FunctionalTester $I): void
    {
        $mutate = $I->grabService(GoBerserk::class);
        $mutateConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::GO_BERSERK]);

        $mutate->loadParameters(
            actionConfig: $mutateConfig,
            actionProvider: $this->kuanTi,
            player: $this->kuanTi,
        );
        $mutate->execute();
    }

    private function whenKuanTiContaminatesFood(FunctionalTester $I): void
    {
        $food = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GameRationEnum::STANDARD_RATION,
            equipmentHolder: $this->kuanTi,
            reasons: [],
            time: new \DateTime(),
        );

        $contaminate = $I->grabService(MixRationSpore::class);
        $contaminateConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::MIX_RATION_SPORE]);

        $this->addSkillToPlayer(SkillEnum::FUNGAL_KITCHEN, $I, $this->kuanTi);
        $this->kuanTi->setSpores(1);
        $contaminate->loadParameters(
            actionConfig: $contaminateConfig,
            actionProvider: $food,
            player: $this->kuanTi,
            target: $food,
        );
        $contaminate->execute();
    }

    private function thenKuanTiMustHaveDoneStealthyActions(int $quantity, FunctionalTester $I): void
    {
        $I->assertEquals($quantity, $this->kuanTi->getPlayerInfo()->getStatistics()->getStealthActionsTaken());
    }

    private function thenKuanTiMustHaveDoneUnstealthyActions(int $quantity, FunctionalTester $I): void
    {
        $I->assertEquals($quantity, $this->kuanTi->getPlayerInfo()->getStatistics()->getUnstealthActionsTaken());
    }
}
