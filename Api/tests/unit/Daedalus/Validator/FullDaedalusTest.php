<?php

namespace Mush\Test\Daedalus\Validator;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Validator\FullDaedalus;
use Mush\Daedalus\Validator\FullDaedalusValidator;
use Mush\Game\Entity\CharacterConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\GameConfigService;
use Mush\Player\Entity\Player;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

class FullDaedalusTest extends TestCase
{
    private FullDaedalusValidator $validator;
    /** @var GameConfigService | Mockery\Mock */
    private GameConfigService $gameConfigService;

    /**
     * @before
     */
    public function before()
    {
        $this->gameConfigService = Mockery::mock(GameConfigService::class);
        $this->validator = new FullDaedalusValidator($this->gameConfigService);
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
        $constraint = new FullDaedalus();
        $daedalus = new Daedalus();
        $this->initValidator();

        $daedalus
            ->setPlayers(new ArrayCollection([
                    new Player(),
                ]));

        $gameConfig = new GameConfig();
        $gameConfig->setCharactersConfig(new ArrayCollection([new CharacterConfig(), new CharacterConfig()]));

        $this->gameConfigService->shouldReceive('getConfig')->andReturn($gameConfig);

        $this->validator->validate($daedalus, $constraint);

        $this->assertTrue(true);
    }

    public function testNotValid()
    {
        $constraint = new FullDaedalus();
        $daedalus = new Daedalus();
        $this->initValidator('This daedalus is full');

        $daedalus
            ->setPlayers(new ArrayCollection([
                    new Player(),
                ]));

        $gameConfig = new GameConfig();
        $gameConfig->setCharactersConfig(new ArrayCollection([new CharacterConfig()]));

        $this->gameConfigService->shouldReceive('getConfig')->andReturn($gameConfig);

        $this->validator->validate($daedalus, $constraint);

        $this->assertTrue(true);
    }

    protected function initValidator(?string $expectedMessage = null)
    {
        $builder = Mockery::mock(ConstraintViolationBuilder::class);
        $context = Mockery::mock(ExecutionContext::class);

        if ($expectedMessage) {
            $builder->shouldReceive('addViolation')->andReturn($builder)->once();
            $builder->shouldReceive('setCode')->andReturn($builder)->once();
            $context->shouldReceive('buildViolation')->with($expectedMessage)->andReturn($builder)->once();
        } else {
            $context->shouldReceive('buildViolation')->never();
        }

        /* @var ExecutionContext $context */
        $this->validator->initialize($context);

        return $this->validator;
    }
}
