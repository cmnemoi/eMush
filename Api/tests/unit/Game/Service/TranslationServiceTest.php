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
    /** @var TranslatorInterface|Mockery\Mock */
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
        $this->translator->shouldReceive('trans')
            ->with('key', ['quantity' => 1], 'domain')
            ->andReturn('translated message')
            ->once()
        ;
        $this->translationService->translate('key', ['quantity' => 1], 'domain');
    }

    public function testGetCharacterTranslationParameters()
    {
        //test character parameter
        $this->translator
            ->shouldReceive('trans')
            ->with(CharacterEnum::PAOLA . '.name', [], 'characters')
            ->andReturn('Andie')
        ;

        $this->translator->shouldReceive('trans')
            ->with('key', ['character' => 'Andie', 'character_gender' => 'female'], 'domain')
            ->andReturn('translated message')
            ->once()
        ;
        $this->translationService->translate('key', ['character' => CharacterEnum::PAOLA], 'domain');
    }

    public function testGetTargetCharacterTranslationParameters()
    {
        //test targetPlayer parameter
        $this->translator
            ->shouldReceive('trans')
            ->with(CharacterEnum::PAOLA . '.name', [], 'characters')
            ->andReturn('Andie')
        ;

        $this->translator->shouldReceive('trans')
            ->with('key', ['target_character' => 'Andie', 'target_character_gender' => 'female'], 'domain')
            ->andReturn('translated message')
            ->once()
        ;
        $this->translationService->translate('key', ['target_character' => CharacterEnum::PAOLA], 'domain');
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
            ->with(EquipmentEnum::ANTENNA . '.first_letter', [], 'equipments')
            ->andReturn('vowel')
        ;
        $this->translator
            ->shouldReceive('trans')
            ->with(EquipmentEnum::ANTENNA . '.plural_name', [], 'equipments')
            ->andReturn('Antennes')
        ;

        $this->translator->shouldReceive('trans')
            ->with('key', ['target_equipment' => 'Antenne', 'target_equipment_gender' => 'female', 'target_equipment_first_letter' => 'vowel', 'target_equipment_plural' => 'Antennes'], 'domain')
            ->andReturn('translated message')
            ->once()
        ;
        $this->translationService->translate('key', ['target_equipment' => EquipmentEnum::ANTENNA], 'domain');
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
            ->with(EquipmentEnum::ANTENNA . '.first_letter', [], 'items')
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

        $translatedParameters = [
            'target_equipment' => 'Antenne',
            'target_equipment_gender' => 'female',
            'target_equipment_first_letter' => 'vowel',
            'target_equipment_plural' => 'Antennes',
            'cause' => 'Pas infirmerie',
        ];

        $initialParameters = [
            'target_item' => EquipmentEnum::ANTENNA,
            'cause' => EndCauseEnum::NO_INFIRMERY,
        ];

        $this->translator->shouldReceive('trans')
            ->with('key', $translatedParameters, 'domain')
            ->andReturn('translated message')
            ->once()
        ;
        $this->translationService->translate('key', $initialParameters, 'domain');
    }
}
