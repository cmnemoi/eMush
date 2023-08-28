<?php

namespace Mush\Test\Player\Validator;

use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Game\Enum\CharacterEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Dto\PlayerCreateRequest;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Validator\UniqueCharacter;
use Mush\Player\Validator\UniqueCharacterValidator;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

class UniqueCharacterTest extends TestCase
{
    private UniqueCharacterValidator $validator;
    /** @var DaedalusServiceInterface|Mockery\Mock */
    private DaedalusServiceInterface $daedalusService;

    /**
     * @before
     */
    public function before()
    {
        $this->daedalusService = \Mockery::mock(DaedalusServiceInterface::class);
        $this->validator = new UniqueCharacterValidator($this->daedalusService);
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
        $constraint = new UniqueCharacter();
        $playerRequest = new PlayerCreateRequest();

        $characterConfig = new CharacterConfig();
        $characterConfig->setName(CharacterEnum::CHUN);
        $player = new Player();
        $playerInfo = new PlayerInfo($player, new User(), $characterConfig);

        $this->initValidator();

        $daedalus = new Daedalus();
        $daedalus->addPlayer($player);

        $this->validator->validate($playerRequest, $constraint);

        $playerRequest
            ->setCharacter('character')
            ->setDaedalus(new Daedalus())
        ;
        $this->daedalusService->shouldReceive('findOneByCharacter')->andReturn(null);

        $this->validator->validate($playerRequest, $constraint);

        $this->assertTrue(true);
    }

    public function testNotValid()
    {
        $constraint = new UniqueCharacter();
        $playerRequest = new PlayerCreateRequest();

        $characterConfig = new CharacterConfig();
        $characterConfig->setName(CharacterEnum::CHUN);
        $player = new Player();
        $playerInfo = new PlayerInfo($player, new User(), $characterConfig);

        $this->initValidator();

        $daedalus = new Daedalus();
        $daedalus->addPlayer($player);
        $playerRequest
            ->setCharacter(CharacterEnum::CHUN)
            ->setDaedalus(new Daedalus())
        ;

        $this->validator->validate($playerRequest, $constraint);

        $this->assertTrue(true);
    }

    protected function initValidator(string $expectedMessage = null)
    {
        $builder = \Mockery::mock(ConstraintViolationBuilder::class);
        $context = \Mockery::mock(ExecutionContext::class);

        if ($expectedMessage) {
            $builder->shouldReceive('addViolation')->andReturn($builder)->once();
            $builder->shouldReceive('setParameter')->andReturn($builder)->once();
            $builder->shouldReceive('setCode')->andReturn($builder)->once();
            $builder->shouldReceive('atPath')->andReturn($builder)->once();
            $context->shouldReceive('buildViolation')->with($expectedMessage)->andReturn($builder)->once();
        } else {
            $context->shouldReceive('buildViolation')->never();
        }

        /* @var ExecutionContext $context */
        $this->validator->initialize($context);

        return $this->validator;
    }
}
