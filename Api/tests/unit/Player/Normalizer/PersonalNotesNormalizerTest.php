<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Player\Normalizer;

use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Factory\GameItemFactory;
use Mush\Player\Entity\PersonalNotesTab;
use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;
use Mush\Player\Normalizer\PersonalNotesNormalizer;
use Mush\Player\Normalizer\PersonalNotesTabNormalizer;
use Mush\Tests\unit\Player\TestDoubles\FakeTranslationService;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class PersonalNotesNormalizerTest extends TestCase
{
    private PersonalNotesNormalizer $normalizer;
    private Player $player;

    protected function setUp(): void
    {
        $this->normalizer = new PersonalNotesNormalizer(
            new FakeTranslationService(),
            new PersonalNotesTabNormalizer()
        );
        $this->player = PlayerFactory::createPlayerWithDaedalus(DaedalusFactory::createDaedalus());
        $this->setPersonalNotesTabsId($this->player->getPersonalNotes()->getTabs()->first(), 1);
    }

    public function testHasAccessIsTrueWhenPlayerHasTalkieInInventory(): void
    {
        // given a talkie is in the player's inventory
        $this->player->addEquipment(GameItemFactory::createEquipmentByName(ItemEnum::WALKIE_TALKIE));
        $personalNotes = $this->player->getPersonalNotes();

        // when personal notes are normalized
        $personalNotes->getTabs()->first()->setContent('This is a test');
        $normalized = $this->normalizer->normalize($personalNotes);

        // then hasAccess should be true and notes should be available
        self::assertTrue(
            $normalized['hasAccess'],
            'Personal notes hasAccess should be true when player has a talkie in inventory'
        );
        self::assertTrue(
            $normalized['tabs'][0]['content'] === 'This is a test',
            'Personal notes content should be available when player has a talkie in inventory'
        );
    }

    public function testHasAccessIsTrueWhenTalkieIsInRoom(): void
    {
        // given a talkie is in the player's room
        $talkie = GameItemFactory::createEquipmentByName(ItemEnum::WALKIE_TALKIE);
        $talkie->setOwner($this->player);
        $this->player->addEquipment($talkie);
        $talkie->setHolder($this->player->getPlace());
        $personalNotes = $this->player->getPersonalNotes();

        // when personal notes are normalized
        $personalNotes->getTabs()->first()->setContent('This is a test');
        $normalized = $this->normalizer->normalize($personalNotes);

        // then hasAccess should be true and notes should be available
        self::assertTrue(
            $normalized['hasAccess'],
            'Personal notes hasAccess should be true when a talkie is in the player\'s room'
        );
        self::assertTrue(
            $normalized['tabs'][0]['content'] === 'This is a test',
            'Personal notes content should be available when a talkie is in the player\'s room'
        );
    }

    public function testHasAccessIsFalseWhenNoTalkieAvailable(): void
    {
        // given player has no talkie available
        $personalNotes = $this->player->getPersonalNotes();

        // when personal notes are normalized
        $personalNotes->getTabs()->first()->setContent('This is a test');
        $normalized = $this->normalizer->normalize($personalNotes);

        // then hasAccess should be false and notes should not be available
        self::assertFalse(
            $normalized['hasAccess'],
            'Personal notes hasAccess should be false when player cannot reach a talkie'
        );
        self::assertTrue(
            $normalized['tabs'][0]['content'] === '',
            'Personal notes content should not be available when player cannot reach a talkie'
        );
    }

    private function setPersonalNotesTabsId(PersonalNotesTab $personalNotesTab, int $id): void
    {
        (new \ReflectionProperty(PersonalNotesTab::class, 'id'))->setValue($personalNotesTab, $id);
    }
}
