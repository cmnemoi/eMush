<?php

namespace Mush\Test\Action\Validator;

use Mockery;
use Mush\Action\Actions\AbstractAction;
use Mush\Action\Validator\FullHull;
use Mush\Action\Validator\FullHullValidator;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Player\Entity\Player;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

class FullHullValidatorTest extends TestCase
{
    private FullHullValidator $validator;
    private FullHull $constraint;

    /**
     * @before
     */
    public function before()
    {
        $this->validator = new FullHullValidator();
        $this->constraint = new FullHull();
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
        $daedalusConfig = new DaedalusConfig();
        $daedalusConfig->setMaxHull(100);

        $gameConfig = new GameConfig();
        $gameConfig->setDaedalusConfig($daedalusConfig);
        $daedalus = new Daedalus();
        $daedalus->setHull(99);
        new DaedalusInfo($daedalus, $gameConfig, new LocalizationConfig());
        $player = new Player();
        $player
            ->setDaedalus($daedalus)
        ;

        $action = Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
            ])
        ;

        $this->initValidator();
        $this->validator->validate($action, $this->constraint);
    }

    public function testNotValid()
    {
        $daedalusConfig = new DaedalusConfig();
        $daedalusConfig->setMaxHull(100);

        $gameConfig = new GameConfig();
        $gameConfig->setDaedalusConfig($daedalusConfig);
        $daedalus = new Daedalus();
        $daedalus->setHull(100);
        new DaedalusInfo($daedalus, $gameConfig, new LocalizationConfig());
        $player = new Player();
        $player
            ->setDaedalus($daedalus)
        ;

        $action = Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
            ])
        ;

        $this->initValidator($this->constraint->message);
        $this->validator->validate($action, $this->constraint);
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
