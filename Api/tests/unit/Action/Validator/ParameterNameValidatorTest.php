<?php

namespace Mush\Test\Action\Validator;

use Mockery;
use Mush\Action\Actions\AbstractAction;
use Mush\Action\Validator\ParameterName;
use Mush\Action\Validator\ParameterNameValidator;
use Mush\Equipment\Entity\Config\GameItem;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Enum\GameFruitEnum;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

class ParameterNameValidatorTest extends TestCase
{
    private ParameterNameValidator $validator;
    private ParameterName $constraint;

    /**
     * @before
     */
    public function before()
    {
        $this->validator = new ParameterNameValidator();
        $this->constraint = new ParameterName();
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testValid()
    {
        $this->constraint->name = GameFruitEnum::ANEMOLE;

        $itemConfig = new ItemConfig();
        $itemConfig->setName(GameFruitEnum::ANEMOLE);

        $gameItem = new GameItem();
        $gameItem->setEquipment($itemConfig);

        $action = Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getParameter' => $gameItem,
            ])
        ;

        $this->initValidator();
        $this->validator->validate($action, $this->constraint);

        $this->assertTrue(true);
    }

    public function testNotValid()
    {
        $this->constraint->name = GameFruitEnum::PLOSHMINA;

        $itemConfig = new ItemConfig();
        $itemConfig->setName(GameFruitEnum::ANEMOLE);

        $gameItem = new GameItem();
        $gameItem->setEquipment($itemConfig);

        $action = Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getParameter' => $gameItem,
            ])
        ;

        $this->initValidator($this->constraint->message);
        $this->validator->validate($action, $this->constraint);

        $this->assertTrue(true);
    }

    protected function initValidator(?string $expectedMessage = null)
    {
        $builder = Mockery::mock(ConstraintViolationBuilder::class);
        $context = Mockery::mock(ExecutionContext::class);

        if ($expectedMessage) {
            $builder->shouldReceive('addViolation')->andReturn($builder)->once();
            $context->shouldReceive('buildViolation')->with($expectedMessage)->andReturn($builder)->once();
        } else {
            $context->shouldReceive('buildViolation')->never();
        }

        /* @var ExecutionContext $context */
        $this->validator->initialize($context);

        return $this->validator;
    }
}
