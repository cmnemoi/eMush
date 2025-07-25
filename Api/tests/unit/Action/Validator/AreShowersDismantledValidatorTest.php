<?php

namespace Mush\Tests\unit\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Action\Validator\AreShowersDismantled;
use Mush\Action\Validator\AreShowersDismantledValidator;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

/**
 * @internal
 */
final class AreShowersDismantledValidatorTest extends TestCase
{
    private AreShowersDismantledValidator $validator;
    private AreShowersDismantled $constraint;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->validator = new AreShowersDismantledValidator();
        $this->constraint = new AreShowersDismantled();
    }

    /**
     * @after
     */
    protected function tearDown(): void
    {
        \Mockery::close();
    }

    public function testNotValid()
    {
        $daedalus = new Daedalus();
        $place = new Place();

        $equipment = new GameEquipment($place);
        $equipment->setName(EquipmentEnum::SHOWER);
        $place->addEquipment($equipment);

        $daedalus->addPlace($place);

        $characterConfig = new CharacterConfig();
        $player = new Player();
        $player->setDaedalus($daedalus);

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getTarget' => null,
                'getPlayer' => $player,
            ]);

        $this->initValidator($this->constraint->message);
        $this->validator->validate($action, $this->constraint, 'visibility');
    }

    public function testValid()
    {
        $daedalus = new Daedalus();

        $characterConfig = new CharacterConfig();
        $player = new Player();
        $player->setDaedalus($daedalus);

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getTarget' => null,
                'getPlayer' => $player,
            ]);

        $this->initValidator();
        $this->validator->validate($action, $this->constraint);
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
