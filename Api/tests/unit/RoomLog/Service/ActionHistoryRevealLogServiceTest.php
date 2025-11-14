<?php

declare(strict_types=1);

namespace Mush\Tests\unit\RoomLog\Service;

use Mush\Action\Actions\Chitchat;
use Mush\Action\ConfigData\ActionData;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\Random\D100RollServiceInterface;
use Mush\Game\Service\Random\GetRandomIntegerServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Factory\PlayerFactory;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Repository\InMemoryRoomLogRepository;
use Mush\RoomLog\Service\ActionHistoryRevealLogService;
use Mush\RoomLog\Service\RoomLogService;
use Mush\Status\Service\StatusServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @internal
 */
final class ActionHistoryRevealLogServiceTest extends TestCase
{
    private ActionHistoryRevealLogService $service;
    private InMemoryRoomLogRepository $roomLogRepository;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->roomLogRepository = new InMemoryRoomLogRepository();

        $roomLogService = new RoomLogService(
            d100Roll: self::createStub(D100RollServiceInterface::class),
            getRandomInteger: self::createStub(GetRandomIntegerServiceInterface::class),
            roomLogRepository: $this->roomLogRepository,
            translationService: self::createStub(TranslationServiceInterface::class),
        );

        $this->service = new ActionHistoryRevealLogService(
            roomLogService: $roomLogService,
            translationService: new class implements TranslationServiceInterface {
                public function translate(string $key, array $parameters = [], string $domain = 'messages', ?string $language = null): string
                {
                    return match ($key) {
                        'graft.name' => 'Greffer : {equipment}',
                        'daunt.name' => 'Intimider',
                        default => $key,
                    };
                }
            },
        );
    }

    public function testShouldRemoveParameterPlaceholdersFromActionName(): void
    {
        $daedalus = DaedalusFactory::createDaedalus();

        $andie = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::ANDIE, $daedalus);
        $chun = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::CHUN, $daedalus);

        $chun
            ->addActionToHistory(ActionEnum::DAUNT)
            ->addActionToHistory(ActionEnum::GRAFT);

        $chichatAction = new Chitchat(
            eventService: self::createStub(EventServiceInterface::class),
            actionService: self::createStub(ActionServiceInterface::class),
            validator: self::createStub(ValidatorInterface::class),
            actionHistoryRevealLog: $this->service,
            statusService: self::createStub(StatusServiceInterface::class),
        );
        $chichatAction->loadParameters(
            actionConfig: ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::CHITCHAT)),
            actionProvider: $andie,
            player: $chun,
            target: $andie,
        );

        $this->service->generate(numberOfActions: 2, action: $chichatAction);

        $roomLog = $this->roomLogRepository->findOneByLogKey(LogEnum::CONFIDENT_ACTIONS);
        self::assertEquals(
            expected: '**Intimider**',
            actual: $roomLog->getParameters()['lastAction']
        );
        self::assertEquals(
            expected: '**Greffer**',
            actual: $roomLog->getParameters()['actions']
        );
    }
}
