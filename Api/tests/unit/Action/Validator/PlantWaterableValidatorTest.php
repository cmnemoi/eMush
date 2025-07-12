<?php

namespace Mush\Tests\unit\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Action\Validator\PlantWaterable;
use Mush\Action\Validator\PlantWaterableValidator;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\BreakableTypeEnum;
use Mush\Place\Entity\Place;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

/**
 * @internal
 */
final class PlantWaterableValidatorTest extends TestCase
{
    private PlantWaterableValidator $validator;
    private PlantWaterable $constraint;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->validator = new PlantWaterableValidator();
        $this->constraint = new PlantWaterable();
    }

    /**
     * @after
     */
    protected function tearDown(): void
    {
        \Mockery::close();
    }

    public function testValid()
    {
        $itemConfig = new ItemConfig();
        $itemConfig->setBreakableType(BreakableTypeEnum::BREAKABLE);

        $target = new GameItem(new Place());
        $target->setEquipment($itemConfig);

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getTarget' => $target,
            ]);

        $statusConfig = new StatusConfig();
        $statusConfig->setStatusName(EquipmentStatusEnum::PLANT_THIRSTY);
        $status = new Status($target, $statusConfig);

        $this->initValidator();
        $this->validator->validate($action, $this->constraint);

        $target = new GameItem(new Place());
        $target->setEquipment($itemConfig);

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getTarget' => $target,
            ]);

        $statusConfig = new StatusConfig();
        $statusConfig->setStatusName(EquipmentStatusEnum::PLANT_DRY);
        $status = new Status($target, $statusConfig);

        $this->initValidator();
        $this->validator->validate($action, $this->constraint);

        self::assertTrue(true);
    }

    public function testNotValid()
    {
        $target = new GameItem(new Place());

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getTarget' => $target,
            ]);

        $this->initValidator($this->constraint->message);
        $this->validator->validate($action, $this->constraint);

        $statusConfig = new StatusConfig();
        $statusConfig->setStatusName(PlayerStatusEnum::GUARDIAN);
        $status = new Status($target, $statusConfig);

        $this->initValidator($this->constraint->message);
        $this->validator->validate($action, $this->constraint);

        self::assertTrue(true);
    }

    protected function initValidator(?string $expectedMessage = null)
    {
        $builder = \Mockery::mock(ConstraintViolationBuilder::class);
        $context = \Mockery::mock(ExecutionContext::class);

        if ($expectedMessage) {
            $builder->shouldReceive('addViolation')->andReturn($builder)->once();
            $context->shouldReceive('buildViolation')->with($expectedMessage)->andReturn($builder)->once();
        } else {
            $context->shouldReceive('buildViolation')->never();
        }

        // @var ExecutionContext $context
        $this->validator->initialize($context);

        return $this->validator;
    }
}
