<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\UsurpIdentity;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Entity\ActionResult\Error;
use Mush\Action\Enum\ActionEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\User\Enum\RoleEnum;

/**
 * @internal
 */
final class UsurpIdentityCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private UsurpIdentity $usurpIdentity;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::USURP_IDENTITY]);
        $this->usurpIdentity = $I->grabService(UsurpIdentity::class);
    }

    public function shouldNotBeExecutableByNonSuperAdmin(FunctionalTester $I): void
    {
        $sourceUser = $this->player->getUser();
        $targetUser = $this->player2->getUser();
        $this->loadAction();

        $I->assertFalse($this->usurpIdentity->isVisible());
        $result = $this->usurpIdentity->execute();

        $I->assertInstanceOf(Error::class, $result);
        $I->assertSame('user does not have the role to do this action', $result->getMessage());
        $I->assertSame($sourceUser, $this->player->getUser());
        $I->assertSame($targetUser, $this->player2->getUser());
    }

    public function shouldExchangeOnlyCharacterOwnership(FunctionalTester $I): void
    {
        $sourceUser = $this->player->getUser();
        $targetUser = $this->player2->getUser();
        $this->player->getUser()->setRoles([RoleEnum::SUPER_ADMIN]);
        $this->convertPlayerToMush($I, $this->player2);
        $this->player->setActionPoint(2)->setTriumph(3);
        $this->player2->setActionPoint(8)->setTriumph(13);

        $this->loadAction();
        $I->assertTrue($this->usurpIdentity->isVisible());
        $result = $this->usurpIdentity->execute();

        $I->assertSame($targetUser, $this->player->getUser());
        $I->assertSame($sourceUser, $this->player2->getUser());
        $I->assertSame(2, $this->player->getActionPoint());
        $I->assertSame(8, $this->player2->getActionPoint());
        $I->assertSame(3, $this->player->getTriumph());
        $I->assertSame(13, $this->player2->getTriumph());
        $I->assertFalse($this->player->hasStatus(PlayerStatusEnum::MUSH));
        $I->assertTrue($this->player2->hasStatus(PlayerStatusEnum::MUSH));
        $I->assertSame($this->player2->getId(), $result->getDetails()['playerId']);
    }

    private function loadAction(): void
    {
        $this->usurpIdentity->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->player,
            player: $this->player,
            target: $this->player2,
        );
    }
}
