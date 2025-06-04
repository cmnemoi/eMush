<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\Surgery;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Disease\Enum\InjuryEnum;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class SurgeryCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private Surgery $surgery;
    private PlayerDiseaseServiceInterface $playerDiseaseService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::SURGERY->value]);
        $this->surgery = $I->grabService(Surgery::class);
        $this->playerDiseaseService = $I->grabService(PlayerDiseaseServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->addSkillToPlayer(SkillEnum::MEDIC, $I, player: $this->chun);
    }

    public function shouldNotBeVisibleIfPlayerNotInMedlab(FunctionalTester $I): void
    {
        $this->givenKuanTiIsInjured();

        $this->givenKuanTiIsLaidDown();

        $this->whenChunTriesASurgeryOnKuanTi();

        $this->thenActionShouldNotBeVisible($I);
    }

    public function shouldNotVisibleIfPlayerHasMedikit(FunctionalTester $I): void
    {
        $this->givenChunHasMedikit();

        $this->givenKuanTiIsInjured();

        $this->givenKuanTiIsLaidDown();

        $this->whenChunTriesASurgeryOnKuanTi();

        $this->thenActionShouldVisible($I);
    }

    public function shouldNotBeVisibleIfTargetIsNotInjured(FunctionalTester $I): void
    {
        $this->givenChunHasMedikit();

        $this->givenKuanTiIsLaidDown();

        $this->whenChunTriesASurgeryOnKuanTi();

        $this->thenActionShouldNotBeVisible($I);
    }

    public function shouldNotBeExecutableIfPlayerIsNotLaidDown(FunctionalTester $I): void
    {
        $this->givenChunHasMedikit();

        $this->givenKuanTiIsInjured();

        $this->whenChunTriesASurgeryOnKuanTi();

        $this->thenActionShouldNotBeExecutableWithMessage(ActionImpossibleCauseEnum::SURGERY_NOT_LYING_DOWN, $I);
    }

    public function shouldRemoveInjuryOnSuccess(FunctionalTester $I): void
    {
        $this->givenChunHasMedikit();

        $this->givenKuanTiIsInjured();

        $this->givenKuanTiIsLaidDown();

        $this->givenSurgeryFailRateIs(0);

        $this->givenSurgeryCriticalRateIs(0);

        $this->whenChunMakesASurgeryOnKuanTi();

        $this->thenKuanTiShouldNotHaveAnyInjury($I);
    }

    public function shouldNotRemoveInjuryOnFail(FunctionalTester $I): void
    {
        $this->givenChunHasMedikit();

        $this->givenKuanTiIsInjured();

        $this->givenKuanTiIsLaidDown();

        $this->givenSurgeryFailRateIs(100);

        $this->givenSurgeryCriticalRateIs(0);

        $this->whenChunMakesASurgeryOnKuanTi();

        $this->thenKuanTiShouldHaveAnInjury($I);
    }

    public function shouldCreateSepsisOnFail(FunctionalTester $I): void
    {
        $this->givenChunHasMedikit();

        $this->givenKuanTiIsInjured();

        $this->givenKuanTiIsLaidDown();

        $this->givenSurgeryFailRateIs(100);

        $this->givenSurgeryCriticalRateIs(0);

        $this->whenChunMakesASurgeryOnKuanTi();

        $this->thenKuanTiShouldHaveASepsis($I);
    }

    public function shouldGiveTriumphOnCriticalSuccess(FunctionalTester $I): void
    {
        $this->givenChunHasMedikit();

        $this->givenKuanTiIsInjured();

        $this->givenKuanTiIsLaidDown();

        $this->givenSurgeryFailRateIs(0);

        $this->givenSurgeryCriticalRateIs(100);

        $this->whenChunMakesASurgeryOnKuanTi();

        $this->thenPlayerShouldHaveTriumph(5, $this->chun, $I);

        $this->thenPlayerShouldHaveTriumph(0, $this->kuanTi, $I);
    }

    private function givenKuanTiIsInjured(): void
    {
        $this->playerDiseaseService->createDiseaseFromName(
            diseaseName: InjuryEnum::BROKEN_FINGER,
            player: $this->kuanTi,
        );
    }

    private function givenKuanTiIsLaidDown(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::LYING_DOWN,
            holder: $this->kuanTi,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenChunHasMedikit(): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ToolItemEnum::MEDIKIT,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime()
        );
    }

    private function givenSurgeryFailRateIs(int $failRate): void
    {
        $this->actionConfig->setOutputQuantity($failRate);
    }

    private function givenSurgeryCriticalRateIs(int $criticalRate): void
    {
        $this->actionConfig->setCriticalRate($criticalRate);
    }

    private function whenChunTriesASurgeryOnKuanTi(): void
    {
        $this->surgery->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->chun,
            player: $this->chun,
            target: $this->kuanTi
        );
    }

    private function whenChunMakesASurgeryOnKuanTi(): void
    {
        $this->whenChunTriesASurgeryOnKuanTi();
        $this->surgery->execute();
    }

    private function thenActionShouldNotBeVisible(FunctionalTester $I): void
    {
        $I->assertFalse($this->surgery->isVisible());
    }

    private function thenActionShouldVisible(FunctionalTester $I): void
    {
        $I->assertTrue($this->surgery->isVisible());
    }

    private function thenActionShouldNotBeExecutableWithMessage(string $message, FunctionalTester $I): void
    {
        $I->assertEquals($message, $this->surgery->cannotExecuteReason());
    }

    private function thenKuanTiShouldNotHaveAnyInjury(FunctionalTester $I): void
    {
        $I->assertNull($this->kuanTi->getMedicalConditionByName(InjuryEnum::BROKEN_FINGER));
    }

    private function thenKuanTiShouldHaveAnInjury(FunctionalTester $I): void
    {
        $I->assertNotNull($this->kuanTi->getMedicalConditionByName(InjuryEnum::BROKEN_FINGER));
    }

    private function thenKuanTiShouldHaveASepsis(FunctionalTester $I): void
    {
        $I->assertNotNull($this->kuanTi->getMedicalConditionByName(DiseaseEnum::SEPSIS), 'Kuan Ti should have sepsis');
    }

    private function thenPlayerShouldHaveTriumph(int $quantity, Player $player, FunctionalTester $I): void
    {
        $I->assertEquals($quantity, $player->getTriumph());
    }
}
