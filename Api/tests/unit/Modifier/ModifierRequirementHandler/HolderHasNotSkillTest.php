<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Modifier\ModifierRequirementHandler;

use Mush\Modifier\Entity\Config\ModifierActivationRequirement;
use Mush\Modifier\Entity\ModifierHolderInterface;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Modifier\ModifierRequirementHandler\RequirementHolderHasNotSkill;
use Mush\Player\Factory\PlayerFactory;
use Mush\Skill\Entity\Skill;
use Mush\Skill\Enum\SkillEnum;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class HolderHasNotSkillTest extends TestCase
{
    private RequirementHolderHasNotSkill $service;

    /**
     * @before
     */
    public function before()
    {
        $this->service = new RequirementHolderHasNotSkill();
    }

    public function testShouldReturnTrueIfPlayerDoesNotHaveTheSkill(): void
    {
        $requirement = new ModifierActivationRequirement(ModifierRequirementEnum::HOLDER_HAS_NOT_SKILL);
        $requirement->setActivationRequirement(SkillEnum::SHRINK->toString());

        $player = PlayerFactory::createPlayer();

        $result = $this->service->checkRequirement($requirement, $player);
        self::assertTrue($result);
    }

    public function testShouldReturnFalseIfPlayerHasTheSkill(): void
    {
        $requirement = new ModifierActivationRequirement(ModifierRequirementEnum::HOLDER_HAS_NOT_SKILL);
        $requirement->setActivationRequirement(SkillEnum::SHRINK->toString());

        $player = PlayerFactory::createPlayer();
        Skill::createByNameForPlayer(SkillEnum::SHRINK, $player);

        $result = $this->service->checkRequirement($requirement, $player);
        self::assertFalse($result);
    }

    public function testShouldThrowExceptionIfModifierHolderIsNotAPlayer(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $requirement = new ModifierActivationRequirement(ModifierRequirementEnum::HOLDER_HAS_NOT_SKILL);
        $requirement->setActivationRequirement(SkillEnum::SHRINK->toString());

        $holder = $this->createStub(ModifierHolderInterface::class);

        $this->service->checkRequirement($requirement, $holder);
    }
}
