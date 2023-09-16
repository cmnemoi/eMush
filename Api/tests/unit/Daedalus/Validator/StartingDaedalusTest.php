<?php

namespace Mush\Tests\unit\Daedalus\Validator;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Validator\StartingDaedalus;
use Mush\Daedalus\Validator\StartingDaedalusValidator;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Service\GameConfigService;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

class StartingDaedalusTest extends TestCase
{
    private StartingDaedalusValidator $validator;
    /** @var GameConfigService|Mockery\Mock */
    private GameConfigService $gameConfigService;

    /**
     * @before
     */
    public function before()
    {
        $this->gameConfigService = \Mockery::mock(GameConfigService::class);
        $this->validator = new StartingDaedalusValidator($this->gameConfigService);
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testValid()
    {
        $constraint = new StartingDaedalus();
        $daedalus = new Daedalus();
        $this->initValidator();

        $daedalus->setPlayers(new ArrayCollection([new Player()]));

        $gameConfig = new GameConfig();
        $gameConfig->setCharactersConfig(new ArrayCollection([new CharacterConfig(), new CharacterConfig()]));

        new DaedalusInfo($daedalus, $gameConfig, new LocalizationConfig());

        $this->validator->validate($daedalus, $constraint);

        $this->assertTrue(true);
    }

    public function testNotValid()
    {
        $constraint = new StartingDaedalus();
        $daedalus = new Daedalus();
        $this->initValidator('This daedalus cannot accept new players');

        $daedalusInfo = new DaedalusInfo($daedalus, new GameConfig(), new LocalizationConfig());

        $daedalusInfo->setGameStatus(GameStatusEnum::CURRENT);

        $gameConfig = new GameConfig();
        $gameConfig->setCharactersConfig(new ArrayCollection([new CharacterConfig()]));

        $this->validator->validate($daedalus, $constraint);

        $this->assertTrue(true);
    }

    protected function initValidator(string $expectedMessage = null)
    {
        $builder = \Mockery::mock(ConstraintViolationBuilder::class);
        $context = \Mockery::mock(ExecutionContext::class);

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
