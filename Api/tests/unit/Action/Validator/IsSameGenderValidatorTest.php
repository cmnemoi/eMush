<?php

namespace Mush\Tests\unit\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Action\Validator\IsSameGender;
use Mush\Action\Validator\IsSameGenderValidator;
use Mush\Game\Enum\CharacterEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

class IsSameGenderValidatorTest extends TestCase
{
    private IsSameGenderValidator $validator;
    private IsSameGender $constraint;

    /**
     * @before
     */
    public function before()
    {
        $this->validator = new IsSameGenderValidator();
        $this->constraint = new IsSameGender();
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
                'getSupport' => $target,
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
                'getSupport' => $target,
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
