<?php

namespace Mush\Tests\unit\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Action\Validator\ForbiddenLove;
use Mush\Action\Validator\ForbiddenLoveValidator;
use Mush\Game\Enum\CharacterEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

class FromSameFamilyValidatorTest extends TestCase
{
    private ForbiddenLoveValidator $validator;
    private ForbiddenLove $constraint;

    /**
     * @before
     */
    public function before()
    {
        $this->validator = new ForbiddenLoveValidator();
        $this->constraint = new ForbiddenLove();
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
        $characterConfig = new CharacterConfig();
        $characterConfig->setCharacterName(CharacterEnum::DEREK);
        $player = new Player();

        $playerInfo = new PlayerInfo(
            $player,
            new User(),
            $characterConfig
        );
        $player->setPlayerInfo($playerInfo);

        $targetPlayerConfig = new CharacterConfig();
        $targetPlayerConfig->setCharacterName(CharacterEnum::CHUN);
        $target = new Player();
        $targetPlayerInfo = new PlayerInfo(
            $target,
            new User(),
            $targetPlayerConfig
        );
        $target->setPlayerInfo($targetPlayerInfo);

        $action = \Mockery::mock(AbstractAction::class);
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
        $characterConfig->setCharacterName(CharacterEnum::PAOLA);
        $player = new Player();
        $playerInfo = new PlayerInfo(
            $player,
            new User(),
            $characterConfig
        );
        $player->setPlayerInfo($playerInfo);

        $targetPlayerConfig = new CharacterConfig();
        $targetPlayerConfig->setCharacterName(CharacterEnum::GIOELE);
        $target = new Player();
        $targetPlayerInfo = new PlayerInfo(
            $target,
            new User(),
            $targetPlayerConfig
        );
        $target->setPlayerInfo($targetPlayerInfo);

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getParameter' => $target,
                'getPlayer' => $player,
            ])
        ;

        $this->initValidator($this->constraint->message);
        $this->validator->validate($action, $this->constraint, 'visibility');
    }

    protected function initValidator(string $expectedMessage = null)
    {
        $builder = \Mockery::mock(ConstraintViolationBuilder::class);
        $context = \Mockery::mock(ExecutionContext::class);

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
