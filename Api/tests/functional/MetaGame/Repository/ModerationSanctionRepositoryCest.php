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

    public function shouldReturnUserWarnings(FunctionalTester $I): void
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

        // when I get the user warnings from the repository
        $warnings = $this->moderationSanctionRepository->findAllUserWarnings($this->chun->getUser());

        // then I should see the user warning
        $I->assertCount(1, $warnings);
        $I->assertEquals(ModerationSanctionEnum::WARNING, $warnings[0]->getModerationAction());
        $I->assertEquals('flood', $warnings[0]->getReason());
        $I->assertEquals('hello, world!', $warnings[0]->getMessage());
        $I->assertEquals($now, $warnings[0]->getStartDate());
        $I->assertEquals($oneDayLater, $warnings[0]->getEndDate());
    }

    public function shouldNotReturnOtherUserWarnings(FunctionalTester $I): void
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

        // when I get Chun'swarnings from the repository
        $warnings = $this->moderationSanctionRepository->findAllUserWarnings($this->chun->getUser());

        // then I should not see any warning
        $I->assertCount(0, $warnings);
    }
}
