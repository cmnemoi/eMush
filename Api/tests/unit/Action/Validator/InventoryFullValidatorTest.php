<?php

namespace Mush\Test\Action\Validator;

use Mockery;
use Mush\Action\Actions\AbstractAction;
use Mush\Action\Validator\InventoryFull;
use Mush\Action\Validator\InventoryFullValidator;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\GameItem;
use Mush\Game\Entity\GameConfig;
use Mush\Player\Entity\Player;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

class InventoryFullValidatorTest extends TestCase
{
    private InventoryFullValidator $validator;
    private InventoryFull $constraint;

    /**
     * @before
     */
    public function before()
    {
        $this->validator = new InventoryFullValidator();
        $this->constraint = new InventoryFull();
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
        $gameConfig = new GameConfig();
        $gameConfig->setMaxItemInInventory(2);

        $daedalus = new Daedalus();
        $daedalus->setGameConfig($gameConfig);

        $player = new Player();
        $player->setDaedalus($daedalus);
        $player->addEquipment(new GameItem());

        $action = Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
            ])
        ;

        $this->initValidator();
        $this->validator->validate($action, $this->constraint);

        $this->assertTrue(true);
    }

    public function testNotValid()
    {
        $gameConfig = new GameConfig();
        $gameConfig->setMaxItemInInventory(1);

        $daedalus = new Daedalus();
        $daedalus->setGameConfig($gameConfig);

        $player = new Player();
        $player->setDaedalus($daedalus);
        $player->addEquipment(new GameItem());

        $action = Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
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
