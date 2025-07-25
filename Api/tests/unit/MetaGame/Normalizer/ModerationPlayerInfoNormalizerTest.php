<?php

declare(strict_types=1);

namespace Mush\Tests\unit\MetaGame\Normalizer;

use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\MetaGame\Normalizer\ModerationPlayerInfoNormalizer;
use Mush\Player\Factory\PlayerFactory;
use Mush\Player\Repository\InMemoryPlayerInfoRepository;
use Mush\Tests\unit\MetaGame\TestDoubles\InMemoryTokenStorage;
use Mush\User\Entity\User;
use Mush\User\Enum\RoleEnum;
use Mush\User\Factory\UserFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @internal
 */
final class ModerationPlayerInfoNormalizerTest extends TestCase
{
    private InMemoryPlayerInfoRepository $playerInfoRepository;

    private TranslationServiceInterface $translationService;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->playerInfoRepository = new InMemoryPlayerInfoRepository();
        $this->translationService = self::createStub(TranslationServiceInterface::class);
    }

    /**
     * @after
     */
    protected function tearDown(): void
    {
        $this->playerInfoRepository->clear();
    }

    public function testModeratorShouldNotSeeAPlayerInTheirOwnDaedalus(): void
    {
        // given a Daedalus
        $daedalus = DaedalusFactory::createDaedalus();

        // given a player in the Daedalus
        $player = PlayerFactory::createPlayerWithDaedalus($daedalus);

        $this->playerInfoRepository->save($player->getPlayerInfo());

        // given the user behind the player is a moderator
        $moderator = $player->getUser();
        $moderator->setRoles([RoleEnum::MODERATOR]);

        // given another player in the Daedalus
        $anotherPlayer = PlayerFactory::createPlayerWithDaedalus($daedalus);

        $this->playerInfoRepository->save($anotherPlayer->getPlayerInfo());

        // when we normalize the player info for the moderator
        $normalizer = new ModerationPlayerInfoNormalizer(
            $this->playerInfoRepository,
            $this->getTokenStorageForUser($moderator),
            $this->translationService
        );
        $normalizer->setNormalizer(self::createStub(NormalizerInterface::class));
        $result = $normalizer->normalize($anotherPlayer->getPlayerInfo());

        // then the moderator should not be able to see the other player
        self::assertNull($result);
    }

    public function testModeratorShouldSeeAPlayerInAnotherDaedalus(): void
    {
        // given a player in the Daedalus
        $player = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::CHAO, DaedalusFactory::createDaedalus());

        $this->playerInfoRepository->save($player->getPlayerInfo());

        // given the user behind the player is a moderator
        $moderator = $player->getUser();
        $moderator->setRoles([RoleEnum::MODERATOR]);

        // given another player in another Daedalus
        $anotherPlayer = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::CHAO, DaedalusFactory::createDaedalus());

        $this->playerInfoRepository->save($anotherPlayer->getPlayerInfo());

        // when we normalize the player info for the moderator
        $normalizer = new ModerationPlayerInfoNormalizer(
            $this->playerInfoRepository,
            $this->getTokenStorageForUser($moderator),
            $this->translationService
        );
        $normalizer->setNormalizer(self::createStub(NormalizerInterface::class));

        $result = $normalizer->normalize($anotherPlayer->getPlayerInfo());

        // then the moderator should be able to see the other player
        self::assertNotNull($result);
    }

    public function testModeratorShouldSeeAPlayerIfNotPlaying(): void
    {
        // given a player in the Daedalus
        $player = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::CHAO, DaedalusFactory::createDaedalus());

        $this->playerInfoRepository->save($player->getPlayerInfo());

        // given a moderator (not playing)
        $moderator = UserFactory::createModerator();

        // when we normalize the player info for the moderator
        $normalizer = new ModerationPlayerInfoNormalizer(
            $this->playerInfoRepository,
            $this->getTokenStorageForUser($moderator),
            $this->translationService
        );
        $normalizer->setNormalizer(self::createStub(NormalizerInterface::class));
        $result = $normalizer->normalize($player->getPlayerInfo());

        // then the moderator should be able to see the player
        self::assertNotNull($result);
    }

    private function getTokenStorageForUser(User $user): InMemoryTokenStorage
    {
        $tokenStorage = new InMemoryTokenStorage();
        $tokenStorage->setToken(new UsernamePasswordToken($user, 'password', $user->getRoles()));

        return $tokenStorage;
    }
}
