<?php

namespace Mush\Tests\unit\MetaGame\Service;

use Doctrine\ORM\EntityManager;
use Mockery;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\MetaGame\Service\ModerationService;
use Mush\MetaGame\Service\ModerationServiceInterface;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;

class ModerationServiceTest extends TestCase
{
    /** @var EventServiceInterface|Mockery\Mock */
    private EventServiceInterface $eventService;
    /** @var EntityManager|Mockery\Mock */
    private EntityManager $entityManager;
    /** @var TranslationServiceInterface|Mockery\Mock */
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

    public function testBan()
    {
        $user = new User();

        $this->entityManager->shouldReceive('persist')->twice();
        $this->entityManager->shouldReceive('flush')->once();

        $this->service->banUser($user, new \DateInterval('P1D'), 'reason', 'adminMessage');

        $this->assertCount(1, $user->getModerationSanctions());
        $this->assertTrue($user->isBanned());
    }
}
