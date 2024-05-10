<?php

declare(strict_types=1);

namespace Mush\tests\functional\MetaGame\Repository;

use Mush\MetaGame\Enum\ModerationSanctionEnum;
use Mush\MetaGame\Repository\ModerationSanctionRepository;
use Mush\MetaGame\Service\ModerationService;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ModerationSanctionRepositoryCest extends AbstractFunctionalTest
{
    private ModerationSanctionRepository $moderationSanctionRepository;

    private ModerationService $moderationService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->moderationSanctionRepository = $I->grabService(ModerationSanctionRepository::class);

        $this->moderationService = $I->grabService(ModerationService::class);
    }

    public function shouldReturnUserActiveBansAndWarnings(FunctionalTester $I): void
    {
        $now = new \DateTime();
        $oneDayLater = (clone $now)->add(new \DateInterval('P1D'));

        // given Chun's user is warned
        $this->moderationService->warnUser(
            user: $this->chun->getUser(),
            duration: new \DateInterval('P1D'),
            reason: 'flood',
            message: 'hello, world!',
            startingDate: $now
        );

        // given Chun's user is banned
        $this->moderationService->banUser(
            user: $this->chun->getUser(),
            duration: new \DateInterval('P1D'),
            reason: 'flood',
            message: 'hello, world!',
            startingDate: $now
        );

        // given Chun is put in quarantine
        $this->moderationService->quarantinePlayer(
            player: $this->chun,
            reason: 'flood',
            message: 'hello, world!',
        );

        // when I get the user sanctions from the repository
        $sanctions = $this->moderationSanctionRepository->findAllUserActiveBansAndWarnings($this->chun->getUser());

        // then I should see the user sanction
        $I->assertCount(2, $sanctions);
        $I->assertEquals(ModerationSanctionEnum::WARNING, $sanctions[0]->getModerationAction());
        $I->assertEquals('flood', $sanctions[0]->getReason());
        $I->assertEquals('hello, world!', $sanctions[0]->getMessage());
        $I->assertEquals($now, $sanctions[0]->getStartDate());
        $I->assertEquals($oneDayLater, $sanctions[0]->getEndDate());
    }

    public function shouldNotReturnOtherUserActiveBansAndWarnings(FunctionalTester $I): void
    {
        $now = new \DateTime();

        // given KT's user is warned
        $this->moderationService->warnUser(
            user: $this->kuanTi->getUser(),
            duration: new \DateInterval('P1D'),
            reason: 'flood',
            message: 'hello, world!',
            startingDate: $now
        );

        // when I get Chun's sanctions from the repository
        $sanctions = $this->moderationSanctionRepository->findAllUserActiveBansAndWarnings($this->chun->getUser());

        // then I should not see any sanction
        $I->assertCount(0, $sanctions);
    }

    public function shouldNotReturnUserInActiveBansAndWarnings(FunctionalTester $I): void
    {
        $yesterday = (new \DateTime())->sub(new \DateInterval('P1D'));

        // given Chun's user is warned
        $this->moderationService->warnUser(
            user: $this->chun->getUser(),
            duration: new \DateInterval('PT1S'),
            reason: 'flood',
            message: 'hello, world!',
            startingDate: $yesterday
        );

        // when I get the user sanctions from the repository
        $sanctions = $this->moderationSanctionRepository->findAllUserActiveBansAndWarnings($this->chun->getUser());

        // then I should not see the user sanction
        $I->assertCount(0, $sanctions);
    }
}
