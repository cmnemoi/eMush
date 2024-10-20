<?php

declare(strict_types=1);

namespace Mush\tests\functional\Skill;

use Mush\Action\Actions\InstallCamera;
use Mush\Action\Actions\RemoveCamera;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class NimbleFingersCest extends AbstractFunctionalTest
{
    private ActionConfig $installCameraConfig;
    private InstallCamera $installCamera;
    private ActionConfig $removeCameraConfig;
    private RemoveCamera $removeCamera;
    private GameEquipment $camera;
    private GameEquipmentServiceInterface $gameEquipmentService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->installCameraConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::INSTALL_CAMERA->value]);
        $this->installCamera = $I->grabService(InstallCamera::class);
        $this->removeCameraConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::REMOVE_CAMERA->value]);
        $this->removeCamera = $I->grabService(RemoveCamera::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);

        $this->addSkillToPlayer(SkillEnum::NIMBLE_FINGERS, $I);
        $this->player2->changePlace($this->daedalus->getPlaceByNameOrThrow(RoomEnum::SPACE));
    }

    public function shouldPrintSecretLogWhenInstallingCamera(FunctionalTester $I): void
    {
        $this->givenCameraInPlayerInventory();

        $this->whenPlayerInstallsCamera();

        $I->grabEntityFromRepository(
            entity: RoomLog::class,
            params: [
                'log' => ActionLogEnum::INSTALL_CAMERA,
                'visibility' => VisibilityEnum::SECRET,
            ]
        );
    }

    public function shouldPrintSecretLogWhenRemovingCamera(FunctionalTester $I): void
    {
        $this->givenCameraInPlayerRoom();

        $this->whenPlayerRemovesCamera();

        $I->grabEntityFromRepository(
            entity: RoomLog::class,
            params: [
                'log' => ActionLogEnum::REMOVE_CAMERA,
                'visibility' => VisibilityEnum::SECRET,
            ]
        );
    }

    private function givenCameraInPlayerInventory(): void
    {
        $this->camera = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::CAMERA_ITEM,
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime()
        );
    }

    private function givenCameraInPlayerRoom(): void
    {
        $this->camera = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::CAMERA_EQUIPMENT,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime()
        );
    }

    private function whenPlayerInstallsCamera(): void
    {
        $this->installCamera->loadParameters(
            actionConfig: $this->installCameraConfig,
            actionProvider: $this->camera,
            player: $this->player,
            target: $this->camera,
        );
        $this->installCamera->execute();
    }

    private function whenPlayerRemovesCamera(): void
    {
        $this->removeCamera->loadParameters(
            actionConfig: $this->removeCameraConfig,
            actionProvider: $this->camera,
            player: $this->player,
            target: $this->camera,
        );
        $this->removeCamera->execute();
    }
}
