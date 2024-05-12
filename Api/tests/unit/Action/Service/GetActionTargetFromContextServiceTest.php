<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Action\Service;

use Mush\Action\Service\GetActionTargetFromContextService;
use Mush\Equipment\Entity\Drone;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Factory\GameEquipmentFactory;
use Mush\Equipment\Factory\GameItemFactory;
use Mush\Player\Entity\Player;
use Mush\Project\Entity\Project;
use Mush\Project\Factory\ProjectFactory;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class GetActionTargetFromContextServiceTest extends TestCase
{
    public function testShouldLoadTerminalFromContext(): void
    {
        $expectedActionTarget = GameEquipmentFactory::createPilgredEquipment();
        $context = [
            'currentPlayer' => new Player(),
            'terminal' => $expectedActionTarget,
        ];

        $service = new GetActionTargetFromContextService();
        $loadedActionTarget = $service->execute($context);

        self::assertSame($expectedActionTarget, $loadedActionTarget);
    }

    public function testShouldLoadProjectFromContext(): void
    {
        $expectedActionTarget = ProjectFactory::createPilgredProject();
        $context = [
            'currentPlayer' => new Player(),
            'terminal' => GameEquipmentFactory::createPilgredEquipment(),
            Project::class => $expectedActionTarget,
        ];

        $service = new GetActionTargetFromContextService();
        $loadedActionTarget = $service->execute($context);

        self::assertSame($expectedActionTarget, $loadedActionTarget);
    }

    public function testShouldLoadNullFromEmptyContext(): void
    {
        $context = [
            'currentPlayer' => new Player(),
        ];

        $service = new GetActionTargetFromContextService();
        $loadedActionTarget = $service->execute($context);

        self::assertNull($loadedActionTarget);
    }

    public function testShouldLoadTabulatrixFromContext(): void
    {
        $expectedActionTarget = GameEquipmentFactory::createEquipmentByName(EquipmentEnum::TABULATRIX);
        $context = [
            'currentPlayer' => new Player(),
            GameEquipment::class => $expectedActionTarget,
        ];

        $service = new GetActionTargetFromContextService();
        $loadedActionTarget = $service->execute($context);

        self::assertSame($expectedActionTarget, $loadedActionTarget);
    }

    public function testShouldLoadTerminalForGameItemFromContext(): void
    {
        $expectedActionTarget = GameItemFactory::createBlockOfPostIt();
        $context = [
            'currentPlayer' => new Player(),
            'terminalItem' => $expectedActionTarget,
        ];

        $service = new GetActionTargetFromContextService();
        $loadedActionTarget = $service->execute($context);

        self::assertSame($expectedActionTarget, $loadedActionTarget);
    }

    public function testShouldLoadDroneFromContext(): void
    {
        $expectedActionTarget = GameEquipmentFactory::createDroneForHolder(new Player());
        $context = [
            'currentPlayer' => new Player(),
            Drone::class => $expectedActionTarget,
        ];

        $service = new GetActionTargetFromContextService();
        $loadedActionTarget = $service->execute($context);

        self::assertSame($expectedActionTarget, $loadedActionTarget);
    }
}
