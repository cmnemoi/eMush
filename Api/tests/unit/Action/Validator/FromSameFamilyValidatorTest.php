<?php

namespace Mush\Test\Action\Validator;

use Mockery;
use Mush\Action\Actions\AbstractAction;
use Mush\Action\Validator\FromSameFamily;
use Mush\Action\Validator\FromSameFamilyValidator;
use Mush\Game\Entity\CharacterConfig;
use Mush\Game\Enum\CharacterEnum;
use Mush\Player\Entity\Player;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

class FromSameFamilyValidatorTest extends TestCase
{
    private FromSameFamilyValidator $validator;
    private FromSameFamily $constraint;

    /**
     * @before
     */
    public function before()
    {
        $this->validator = new FromSameFamilyValidator();
        $this->constraint = new FromSameFamily();
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
        $characterConfig = new CharacterConfig();
        $characterConfig->setName(CharacterEnum::DEREK);
        $player = new Player();
        $player->setCharacterConfig($characterConfig);

        $targetPlayerConfig = new CharacterConfig();
        $targetPlayerConfig->setName(CharacterEnum::CHUN);
        $target = new Player();
        $target->setCharacterConfig($targetPlayerConfig);

        $action = Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getParameter' => $target,
                'getPlayer' => $player,
            ])
        ;

        $this->initValidator();
        $this->validator->validate($action, $this->constraint);
    }

    public function testNotValid()
    {
        $characterConfig = new CharacterConfig();
        $characterConfig->setName(CharacterEnum::PAOLA);
        $player = new Player();
        $player->setCharacterConfig($characterConfig);

        $targetPlayerConfig = new CharacterConfig();
        $targetPlayerConfig->setName(CharacterEnum::GIOELE);
        $target = new Player();
        $target->setCharacterConfig($targetPlayerConfig);

        $action = Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getParameter' => $target,
                'getPlayer' => $player,
            ])
        ;

        $this->initValidator($this->constraint->message);
        $this->validator->validate($action, $this->constraint, 'visibility');
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
