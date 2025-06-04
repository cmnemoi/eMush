<?php

namespace Mush\Tests\unit\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Action\Validator\IsSameGender;
use Mush\Action\Validator\IsSameGenderValidator;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\CharacterEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

/**
 * @internal
 */
final class IsSameGenderValidatorTest extends TestCase
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

    public function testAlwaysValid()
    {
        $daedalusConfig = new DaedalusConfig();
        $daedalusConfig->setFreeLove(false);
        $gameConfig = new GameConfig();
        $gameConfig->setDaedalusConfig($daedalusConfig);
        $daedalus = new Daedalus();
        $daedalus->setDaedalusVariables($daedalusConfig);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, new LocalizationConfig());

        $characterConfig = new CharacterConfig();
        $characterConfig->setCharacterName(CharacterEnum::DEREK);
        $player = new Player();
        $playerInfo = new PlayerInfo(
            $player,
            new User(),
            $characterConfig
        );
        $player->setPlayerInfo($playerInfo);
        $player->setDaedalus($daedalus);

        $targetPlayerConfig = new CharacterConfig();
        $targetPlayerConfig->setCharacterName(CharacterEnum::CHUN);
        $target = new Player();
        $targetPlayerInfo = new PlayerInfo(
            $target,
            new User(),
            $targetPlayerConfig
        );
        $target->setPlayerInfo($targetPlayerInfo);
        $target->setDaedalus($daedalus);

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getTarget' => $target,
                'getPlayer' => $player,
            ]);

        $this->initValidator();
        $this->validator->validate($action, $this->constraint);
    }

    public function testNoFreeLoveNotValid()
    {
        $daedalusConfig = new DaedalusConfig();
        $daedalusConfig->setFreeLove(false);
        $gameConfig = new GameConfig();
        $gameConfig->setDaedalusConfig($daedalusConfig);
        $daedalus = new Daedalus();
        $daedalus->setDaedalusVariables($daedalusConfig);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, new LocalizationConfig());

        $characterConfig = new CharacterConfig();
        $characterConfig->setCharacterName(CharacterEnum::PAOLA);
        $player = new Player();
        $playerInfo = new PlayerInfo(
            $player,
            new User(),
            $characterConfig
        );
        $player->setPlayerInfo($playerInfo);
        $player->setDaedalus($daedalus);

        $targetPlayerConfig = new CharacterConfig();
        $targetPlayerConfig->setCharacterName(CharacterEnum::CHUN);
        $target = new Player();
        $targetPlayerInfo = new PlayerInfo(
            $target,
            new User(),
            $targetPlayerConfig
        );
        $target->setPlayerInfo($targetPlayerInfo);
        $target->setDaedalus($daedalus);

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getTarget' => $target,
                'getPlayer' => $player,
            ]);

        $this->initValidator($this->constraint->message);
        $this->validator->validate($action, $this->constraint, 'visibility');
    }

    public function testFreeLoveValid()
    {
        $daedalusConfig = new DaedalusConfig();
        $daedalusConfig->setFreeLove(true);
        $gameConfig = new GameConfig();
        $gameConfig->setDaedalusConfig($daedalusConfig);
        $daedalus = new Daedalus();
        $daedalus->setDaedalusVariables($daedalusConfig);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, new LocalizationConfig());

        $characterConfig = new CharacterConfig();
        $characterConfig->setCharacterName(CharacterEnum::PAOLA);
        $player = new Player();
        $playerInfo = new PlayerInfo(
            $player,
            new User(),
            $characterConfig
        );
        $player->setPlayerInfo($playerInfo);
        $player->setDaedalus($daedalus);

        $targetPlayerConfig = new CharacterConfig();
        $targetPlayerConfig->setCharacterName(CharacterEnum::CHUN);
        $target = new Player();
        $targetPlayerInfo = new PlayerInfo(
            $target,
            new User(),
            $targetPlayerConfig
        );
        $target->setPlayerInfo($targetPlayerInfo);
        $target->setDaedalus($daedalus);

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getTarget' => $target,
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
