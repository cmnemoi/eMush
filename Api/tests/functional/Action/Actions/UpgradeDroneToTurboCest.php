<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\UpgradeDroneToTurbo;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Tests\AbstractUpgradeDroneCest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class UpgradeDroneToTurboCest extends AbstractUpgradeDroneCest
{
    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::UPGRADE_DRONE_TO_TURBO->value]);
        $this->upgradeDrone = $I->grabService(UpgradeDroneToTurbo::class);
    }
}