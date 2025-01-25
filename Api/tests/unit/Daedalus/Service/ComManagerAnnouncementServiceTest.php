<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Daedalus\Service;

use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Daedalus\Repository\InMemoryComManagerAnnouncementRepository;
use Mush\Daedalus\Service\ComManagerAnnouncementService;
use Mush\Game\Enum\CharacterEnum;
use Mush\Player\Factory\PlayerFactory;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ComManagerAnnouncementServiceTest extends TestCase
{
    private InMemoryComManagerAnnouncementRepository $comManagerAnnouncementRepository;
    private ComManagerAnnouncementService $ComManagerAnnouncementService;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->comManagerAnnouncementRepository = new InMemoryComManagerAnnouncementRepository();
        $this->ComManagerAnnouncementService = new ComManagerAnnouncementService($this->comManagerAnnouncementRepository);
    }

    /**
     * @after
     */
    protected function tearDown(): void
    {
        $this->comManagerAnnouncementRepository->clear();
    }

    public function testShouldAddGeneralAnnouncementToDaedalus(): void
    {
        $daedalus = DaedalusFactory::createDaedalus();
        $commsManager = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::PAOLA, $daedalus);

        // when I create a general announcement
        $this->ComManagerAnnouncementService->execute(
            comManager: $commsManager,
            announcement: 'test',
        );

        // then I should have a general announcement
        $announcements = $this->comManagerAnnouncementRepository->findByComManagerAndAnnouncement(
            $commsManager->getId(),
            'test',
        );

        self::assertCount(1, $announcements);
        self::assertCount(1, $daedalus->getGeneralAnnouncements());
    }
}
