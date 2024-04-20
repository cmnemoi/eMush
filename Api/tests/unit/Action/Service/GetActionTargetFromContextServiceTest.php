<?php

declare(strict_types=1);

namespace Mush\Action\Service;

use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Factory\GameEquipmentFactory;
use Mush\Player\Entity\Player;
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
            'project' => $expectedActionTarget,
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
            'item' => $expectedActionTarget,
        ];

        $service = new GetActionTargetFromContextService();
        $loadedActionTarget = $service->execute($context);

        self::assertSame($expectedActionTarget, $loadedActionTarget);
    }
}
