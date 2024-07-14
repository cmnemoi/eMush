<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Modifier\ModifierRequirementHandler;

use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Modifier\Entity\Config\ModifierActivationRequirement;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Modifier\ModifierRequirementHandler\RequirementProjectIsActive;
use Mush\Project\Enum\ProjectName;
use Mush\Project\Factory\ProjectFactory;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ProjectIsActiveTest extends TestCase
{
    private RequirementProjectIsActive $service;

    /**
     * @before
     */
    public function before(): void
    {
        $this->service = new RequirementProjectIsActive();
    }

    public function testShouldReturnFalseIfProjectIsNotFinished(): void
    {
        // given
        $daedalus = DaedalusFactory::createDaedalus();
        ProjectFactory::createNeronProjectByNameForDaedalus(
            name: ProjectName::PLASMA_SHIELD,
            daedalus: $daedalus
        );

        // when
        $requirement = new ModifierActivationRequirement(ModifierRequirementEnum::PROJECT_IS_ACTIVE);
        $requirement->setActivationRequirement(ProjectName::PLASMA_SHIELD->value);

        // then
        self::assertFalse($this->service->checkRequirement($requirement, $daedalus));
    }

    public function testShouldReturnFalseIfProjectIsFinishedButNotActive(): void
    {
        // given
        $daedalus = DaedalusFactory::createDaedalus();
        $plasmaShield = ProjectFactory::createNeronProjectByNameForDaedalus(
            name: ProjectName::PLASMA_SHIELD,
            daedalus: $daedalus
        );
        $plasmaShield->finish();

        // when
        $requirement = new ModifierActivationRequirement(ModifierRequirementEnum::PROJECT_IS_ACTIVE);
        $requirement->setActivationRequirement(ProjectName::PLASMA_SHIELD->value);

        // then
        self::assertFalse($this->service->checkRequirement($requirement, $daedalus));
    }

    public function testShouldReturnTrueIfProjectIsActive(): void
    {
        // given
        $daedalus = DaedalusFactory::createDaedalus();
        $plasmaShield = ProjectFactory::createNeronProjectByNameForDaedalus(
            name: ProjectName::PLASMA_SHIELD,
            daedalus: $daedalus
        );
        $plasmaShield->finish();
        $daedalus->getNeron()->togglePlasmaShield();

        // when
        $requirement = new ModifierActivationRequirement(ModifierRequirementEnum::PROJECT_IS_ACTIVE);
        $requirement->setActivationRequirement(ProjectName::PLASMA_SHIELD->value);

        // then
        self::assertTrue($this->service->checkRequirement($requirement, $daedalus));
    }

    public function testShouldThrowIfRequirementIsNotSet(): void
    {
        // given
        $daedalus = DaedalusFactory::createDaedalus();

        // when
        $requirement = new ModifierActivationRequirement(ModifierRequirementEnum::PROJECT_IS_ACTIVE);

        // then
        $this->expectException(\InvalidArgumentException::class);
        $this->service->checkRequirement($requirement, $daedalus);
    }
}
