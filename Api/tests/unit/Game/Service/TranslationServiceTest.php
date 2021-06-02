<?php

namespace Mush\Test\Game\Service;

use Mockery;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Service\TranslationService;
use Mush\Player\Enum\EndCauseEnum;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class TranslationServiceTest extends TestCase
{
    /** @var TranslatorInterface | Mockery\Mock */
    private TranslatorInterface $translator;

    private TranslationService $translationService;

    /**
     * @before
     */
    public function before()
    {
        $this->translator = Mockery::mock(TranslatorInterface::class);

        $this->translationService = new TranslationService(
            $this->translator,
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testGetSimpleTranslationParameters()
    {
        //test simple parameter
        $this->assertEquals(['quantity' => 1], $this->translationService->getTranslateParameters(['quantity' => 1]));
    }

    public function testGetCharacterTranslationParameters()
    {
        //test character parameter
        $this->translator
            ->shouldReceive('trans')
            ->with(CharacterEnum::ANDIE . '.name', [], 'characters')
            ->andReturn('Andie')
        ;

        $this->assertEquals(['player' => 'Andie', 'character_gender' => 'female'], $this->translationService->getTranslateParameters(['player' => 'andie']));
    }

    public function testGetTargetCharacterTranslationParameters()
    {
        //test targetPlayer parameter
        $this->translator
            ->shouldReceive('trans')
            ->with(CharacterEnum::ANDIE . '.name', [], 'characters')
            ->andReturn('Andie')
        ;

        $this->assertEquals(
            ['target_player' => 'Andie', 'target_player_gender' => 'female'],
            $this->translationService->getTranslateParameters(['targetPlayer' => CharacterEnum::ANDIE])
        );
    }

    public function testGetTargetEquipmentTranslationParameters()
    {
        //test targetEquipment parameter
        $this->translator
            ->shouldReceive('trans')
            ->with(EquipmentEnum::ANTENNA . '.short_name', [], 'equipments')
            ->andReturn('Antenne')
        ;
        $this->translator
            ->shouldReceive('trans')
            ->with(EquipmentEnum::ANTENNA . '.genre', [], 'equipments')
            ->andReturn('female')
        ;
        $this->translator
            ->shouldReceive('trans')
            ->with(EquipmentEnum::ANTENNA . '.first_Letter', [], 'equipments')
            ->andReturn('vowel')
        ;
        $this->translator
            ->shouldReceive('trans')
            ->with(EquipmentEnum::ANTENNA . '.plural_name', [], 'equipments')
            ->andReturn('Antennes')
        ;

        $this->assertEquals(
            ['target' => 'Antenne', 'target_gender' => 'female', 'target_first_letter' => 'vowel', 'target_plural' => 'Antennes'],
            $this->translationService->getTranslateParameters(['targetEquipment' => EquipmentEnum::ANTENNA])
        );
    }

    public function testGetTranslationParameters()
    {
        $this->translator
            ->shouldReceive('trans')
            ->with(EquipmentEnum::ANTENNA . '.short_name', [], 'items')
            ->andReturn('Antenne')
        ;
        $this->translator
            ->shouldReceive('trans')
            ->with(EquipmentEnum::ANTENNA . '.genre', [], 'items')
            ->andReturn('female')
        ;
        $this->translator
            ->shouldReceive('trans')
            ->with(EquipmentEnum::ANTENNA . '.first_Letter', [], 'items')
            ->andReturn('vowel')
        ;
        $this->translator
            ->shouldReceive('trans')
            ->with(EquipmentEnum::ANTENNA . '.plural_name', [], 'items')
            ->andReturn('Antennes')
        ;

        $this->translator
            ->shouldReceive('trans')
            ->with(EndCauseEnum::NO_INFIRMERY . '.name', [], 'end_cause')
            ->andReturn('Pas infirmerie')
        ;

        $this->assertEquals(
            [
                'target' => 'Antenne',
                'target_gender' => 'female',
                'target_first_letter' => 'vowel',
                'target_plural' => 'Antennes',
                'cause' => 'Pas infirmerie',
            ],
            $this->translationService->getTranslateParameters([
                'targetItem' => EquipmentEnum::ANTENNA,
                'cause' => EndCauseEnum::NO_INFIRMERY,
            ])
        );
    }
}
