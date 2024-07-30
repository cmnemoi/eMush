<?php

declare(strict_types=1);

namespace Mush\tests\unit\Modifier\ModifierRequirementHandler;

use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Game\Enum\SkillEnum;
use Mush\Modifier\Entity\Config\ModifierActivationRequirement;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Modifier\ModifierRequirementHandler\RequirementSkillHolderIsAlive;
use Mush\Player\Factory\PlayerFactory;
use Mush\Status\Factory\StatusFactory;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class SkillHolderIsAliveTest extends TestCase
{
    public function testShouldReturnTrueIfSkillHolderIsAlive(): void
    {
        $modifierRequirement = new ModifierActivationRequirement(ModifierRequirementEnum::SKILL_HOLDER_IS_ALIVE);
        $modifierRequirement->setActivationRequirement(SkillEnum::MANKIND_ONLY_HOPE);

        $service = new RequirementSkillHolderIsAlive();

        $daedalus = DaedalusFactory::createDaedalus();
        $mankindOnlyHopeHolder = PlayerFactory::createPlayerWithDaedalus($daedalus);
        StatusFactory::createStatusByNameForHolder(SkillEnum::MANKIND_ONLY_HOPE, $mankindOnlyHopeHolder);

        $result = $service->checkRequirement(modifierRequirement: $modifierRequirement, holder: $daedalus);
        self::assertTrue($result);
    }

    public function testShouldReturnFalseIfSkillHolderIsDead(): void
    {
        $modifierRequirement = new ModifierActivationRequirement(ModifierRequirementEnum::SKILL_HOLDER_IS_ALIVE);
        $modifierRequirement->setActivationRequirement(SkillEnum::MANKIND_ONLY_HOPE);

        $service = new RequirementSkillHolderIsAlive();

        $daedalus = DaedalusFactory::createDaedalus();
        $mankindOnlyHopeHolder = PlayerFactory::createPlayerWithDaedalus($daedalus);
        StatusFactory::createStatusByNameForHolder(SkillEnum::MANKIND_ONLY_HOPE, $mankindOnlyHopeHolder);
        $mankindOnlyHopeHolder->kill();

        $result = $service->checkRequirement(modifierRequirement: $modifierRequirement, holder: $daedalus);
        self::assertFalse($result);
    }
}
