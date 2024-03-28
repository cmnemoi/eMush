<?php

namespace Mush\Tests\unit\MetaGame\Service;

use Doctrine\ORM\EntityManager;
use Mockery;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionVariableEvent;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Exploration\Repository\PlanetRepository;
use Mush\Exploration\Service\PlanetService;
use Mush\Game\Entity\DifficultyConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\DifficultyEnum;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\MetaGame\Entity\ModerationSanction;
use Mush\MetaGame\Service\ModerationService;
use Mush\MetaGame\Service\ModerationServiceInterface;
use Mush\Modifier\Entity\Config\EventModifierConfig;
use Mush\Modifier\Entity\Config\TriggerEventModifierConfig;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Modifier\Enum\ModifierPriorityEnum;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Enum\PlayerVariableEnum;
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
