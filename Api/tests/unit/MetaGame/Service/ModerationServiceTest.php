<?php

namespace Mush\Tests\unit\MetaGame\Service;

use Doctrine\ORM\EntityManager;
use Mockery;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\MetaGame\Entity\ModerationSanction;
use Mush\MetaGame\Enum\ModerationSanctionEnum;
use Mush\MetaGame\Service\ModerationService;
use Mush\MetaGame\Service\ModerationServiceInterface;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Tests\Unit\MetaGame\TestDoubles\FakeModerationSanctionRepository;
use Mush\User\Entity\User;
use Mush\User\Factory\UserFactory;
use Mush\User\Repository\BannedIpRepositoryInterface;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ModerationServiceTest extends TestCase
{
    /** @var EntityManager|Mockery\Mock */
    private EntityManager $entityManager;

    /** @var Mockery\Mock|TranslationServiceInterface */
    private TranslationServiceInterface $translationService;

    private FakeModerationSanctionRepository $moderationSanctionRepository;

    private ModerationServiceInterface $service;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->entityManager = \Mockery::mock(EntityManager::class);
        $this->translationService = \Mockery::mock(TranslationServiceInterface::class);
        $this->moderationSanctionRepository = new FakeModerationSanctionRepository();

        $this->service = new ModerationService(
            entityManager: $this->entityManager,
            playerService: self::createStub(PlayerServiceInterface::class),
            translationService: $this->translationService,
            bannedIpRepository: self::createStub(BannedIpRepositoryInterface::class),
            moderationSanctionRepository: $this->moderationSanctionRepository
        );
    }

    /**
     * @after
     */
    protected function tearDown(): void
    {
        \Mockery::close();
    }

    public function testBan()
    {
        $user = UserFactory::createUser();

        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager->shouldReceive('flush')->once();

        $this->service->banUser(
            user: $user,
            author: new User(),
            reason: 'reason',
            message: 'adminMessage',
            duration: new \DateInterval('P3D')
        );

        dump($this->moderationSanctionRepository->findAllBansNotYetTriggeredForAll());
        self::assertCount(1, $user->getModerationSanctions());
        self::assertTrue($user->isBanned());
    }

    public function testPermanentBan()
    {
        $user = UserFactory::createUser();

        $this->entityManager->shouldReceive('persist')->once();
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
