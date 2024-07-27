<?php

namespace Mush\Tests\unit\MetaGame\Service;

use Doctrine\ORM\EntityManager;
use Mockery;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\MetaGame\Entity\ModerationSanction;
use Mush\MetaGame\Enum\ModerationSanctionEnum;
use Mush\MetaGame\Service\ModerationService;
use Mush\MetaGame\Service\ModerationServiceInterface;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ModerationServiceTest extends TestCase
{
    /** @var EventServiceInterface|Mockery\Mock */
    private EventServiceInterface $eventService;

    /** @var EntityManager|Mockery\Mock */
    private EntityManager $entityManager;

    /** @var Mockery\Mock|TranslationServiceInterface */
    private TranslationServiceInterface $translationService;

    private ModerationServiceInterface $service;

    /**
     * @before
     */
    public function before()
    {
        $this->eventService = \Mockery::mock(EventServiceInterface::class);
        $this->entityManager = \Mockery::mock(EntityManager::class);
        $this->translationService = \Mockery::mock(TranslationServiceInterface::class);

        $this->service = new ModerationService(
            $this->entityManager,
            $this->eventService,
            $this->translationService
        );
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testBan()
    {
        $user = new User();

        $this->entityManager->shouldReceive('persist')->twice();
        $this->entityManager->shouldReceive('flush')->once();

        $this->service->banUser(
            $user,
            new User(),
            new \DateInterval('P1D'),
            'reason',
            'adminMessage'
        );

        self::assertCount(1, $user->getModerationSanctions());
        self::assertTrue($user->isBanned());
    }

    public function testPermanentBan()
    {
        $user = new User();

        $this->entityManager->shouldReceive('persist')->twice();
        $this->entityManager->shouldReceive('flush')->once();

        $this->service->banUser(
            user: $user,
            author: new User(),
            duration: null,
            reason: 'reason',
            message: 'adminMessage'
        );

        self::assertCount(1, $user->getModerationSanctions());
        self::assertTrue($user->isBanned());
        $sanction = $user->getModerationSanctions()->first();
        self::assertInstanceOf(ModerationSanction::class, $sanction);
        self::assertEquals($sanction->getEndDate(), new \DateTime('99999/12/31'));
        self::assertSame($sanction->getModerationAction(), ModerationSanctionEnum::BAN_USER);
        self::assertSame($sanction->getReason(), 'reason');
        self::assertSame($sanction->getMessage(), 'adminMessage');
    }
}
