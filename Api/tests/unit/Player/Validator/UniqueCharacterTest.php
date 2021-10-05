<?php

namespace Mush\Test\Player\Validator;

use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Player\Entity\Dto\PlayerCreateRequest;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Player\Validator\UniqueCharacter;
use Mush\Player\Validator\UniqueCharacterValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

class UniqueCharacterTest extends TestCase
{
    private UniqueCharacterValidator $validator;
    /** @var PlayerServiceInterface|Mockery\Mock */
    private PlayerServiceInterface $playerService;

    /**
     * @before
     */
    public function before()
    {
        $this->playerService = Mockery::mock(PlayerServiceInterface::class);
        $this->validator = new UniqueCharacterValidator($this->playerService);
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
        $constraint = new UniqueCharacter();
        $playerRequest = new PlayerCreateRequest();
        $this->initValidator();

        $this->validator->validate($playerRequest, $constraint);

        $playerRequest
            ->setCharacter('character')
            ->setDaedalus(new Daedalus())
        ;
        $this->playerService->shouldReceive('findOneByCharacter')->andReturn(null);

        $this->validator->validate($playerRequest, $constraint);

        $this->assertTrue(true);
    }

    public function testNotValid()
    {
        $constraint = new UniqueCharacter();
        $playerRequest = new PlayerCreateRequest();
        $this->initValidator('This character already exist in this daedalus');

        $playerRequest
            ->setCharacter('character')
            ->setDaedalus(new Daedalus())
        ;
        $this->playerService->shouldReceive('findOneByCharacter')->andReturn(new Player());

        $this->validator->validate($playerRequest, $constraint);

        $this->assertTrue(true);
    }

    protected function initValidator(?string $expectedMessage = null)
    {
        $builder = Mockery::mock(ConstraintViolationBuilder::class);
        $context = Mockery::mock(ExecutionContext::class);

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
