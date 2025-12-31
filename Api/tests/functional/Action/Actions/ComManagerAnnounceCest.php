<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Repository\PendingStatisticRepositoryInterface;
use Mush\Action\Actions\ComManagerAnnounce;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\Enum\TitleEnum;
use Mush\Player\Enum\PlayerNotificationEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ComManagerAnnounceCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private ComManagerAnnounce $comManagerAnnouncement;
    private PendingStatisticRepositoryInterface $pendingStatisticRepository;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::COM_MANAGER_ANNOUNCEMENT]);
        $this->comManagerAnnouncement = $I->grabService(ComManagerAnnounce::class);
        $this->pendingStatisticRepository = $I->grabService(PendingStatisticRepositoryInterface::class);

        $this->givenChunIsCommsManager();
    }

    public function shouldNotBeVisibleIfPlayerIsNotCommsManager(FunctionalTester $I): void
    {
        $this->whenKuanTiTriesToMakeAGeneralAnnouncement();

        $this->thenActionIsNotVisible($I);
    }

    public function shouldCreateGeneralAnnouncementForDaedalus(FunctionalTester $I): void
    {
        $this->whenChunMakesAGeneralAnnouncement();

        $this->thenDaedalusShouldHaveGeneralAnnouncement($I);
    }

    public function shouldCreateNotificationForCommsManager(FunctionalTester $I): void
    {
        $this->whenChunMakesAGeneralAnnouncement();

        $this->thenChunShouldHaveAnnouncementCreatedNotification($I);
    }

    public function shouldCreateNotificationForRestOfTheCrew(FunctionalTester $I): void
    {
        $this->whenChunMakesAGeneralAnnouncement();

        $this->thenKuanTiShouldHaveAnnouncementReceivedNotification($I);
    }

    public function shouldIncrementUserPendingStatisticOnSuccess(FunctionalTester $I): void
    {
        $this->whenChunMakesAGeneralAnnouncement();

        $statistic = $this->pendingStatisticRepository->findByNameUserIdAndClosedDaedalusIdOrNull(
            StatisticEnum::DAILY_ORDER,
            $this->chun->getUser()->getId(),
            $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId()
        );

        $I->assertEquals(
            expected: [
                'name' => StatisticEnum::DAILY_ORDER,
                'userId' => $this->chun->getUser()->getId(),
                'closedDaedalusId' => $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId(),
                'count' => 1,
                'isRare' => false,
            ],
            actual: $statistic->toArray()
        );
    }

    public function shouldBeVisibleIfNotCommsManagerPlayerHasMegaphone(FunctionalTester $I): void
    {
        $this->createEquipment(ItemEnum::MEGAPHONE, $this->kuanTi);

        $this->whenKuanTiTriesToMakeAGeneralAnnouncement();

        $this->thenActionIsVisible($I);
    }

    private function givenChunIsCommsManager(): void
    {
        $this->chun->addTitle(TitleEnum::COM_MANAGER);
    }

    private function whenKuanTiTriesToMakeAGeneralAnnouncement(): void
    {
        $this->comManagerAnnouncement->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->kuanTi,
            player: $this->kuanTi,
            parameters: ['announcement' => 'Hello, World!'],
        );
    }

    private function whenChunMakesAGeneralAnnouncement(): void
    {
        $this->comManagerAnnouncement->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->chun,
            player: $this->chun,
            parameters: ['announcement' => 'Hello, World!'],
        );
        $this->comManagerAnnouncement->execute();
    }

    private function thenActionIsNotVisible(FunctionalTester $I): void
    {
        $I->assertFalse($this->comManagerAnnouncement->isVisible());
    }

    private function thenActionIsVisible(FunctionalTester $I): void
    {
        $I->assertTrue($this->comManagerAnnouncement->isVisible());
    }

    private function thenDaedalusShouldHaveGeneralAnnouncement(FunctionalTester $I): void
    {
        $I->assertNotEmpty($this->daedalus->getGeneralAnnouncements());
    }

    private function thenChunShouldHaveAnnouncementCreatedNotification(FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: PlayerNotificationEnum::ANNOUNCEMENT_CREATED->toString(),
            actual: $this->chun->getFirstNotificationOrThrow()->getMessage(),
        );
        $I->assertEquals(
            expected: [
                'message' => 'Hello, World!',
                'character' => 'chun',
                'day' => 1,
                'cycle' => 1,
            ],
            actual: $this->chun->getFirstNotificationOrThrow()->getParameters(),
        );
    }

    private function thenKuanTiShouldHaveAnnouncementReceivedNotification(FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: PlayerNotificationEnum::ANNOUNCEMENT_RECEIVED->toString(),
            actual: $this->kuanTi->getFirstNotificationOrThrow()->getMessage(),
        );
        $I->assertEquals(
            expected: [
                'message' => 'Hello, World!',
                'character' => 'chun',
                'day' => 1,
                'cycle' => 1,
            ],
            actual: $this->kuanTi->getFirstNotificationOrThrow()->getParameters(),
        );
    }
}
