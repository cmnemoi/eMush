<?php

declare(strict_types=1);

namespace Mush\User\Query;

use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;
use Symfony\Component\Uid\Uuid;

/**
 * @internal
 */
final class SearchUsersByUsernameQueryHandlerCest extends AbstractFunctionalTest
{
    private SearchUsersByUsernameQueryHandler $queryHandler;

    /** @var UserSearchResultDto[] */
    private array $result = [];

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->queryHandler = $I->grabService(SearchUsersByUsernameQueryHandler::class);
    }

    public function shouldReturnExactMatchFirst(FunctionalTester $I): void
    {
        $this->givenUsersWithUsernames(['John', 'Johnny', 'Jon'], $I);

        $this->whenSearchingForUsername('John');

        $this->thenResultShouldHaveCount(3, $I);
        $this->thenFirstResultUsernameShouldBe('John', $I);
        $this->thenFirstResultSimilarityScoreShouldBe(1.0, $I);
    }

    public function shouldReturnMostSimilarUsernames(FunctionalTester $I): void
    {
        $this->givenUsersWithUsernames(['Alice', 'Alex', 'Alicia', 'Bob', 'Charlie'], $I);

        $this->whenSearchingForUsername('Alec');

        $this->thenResultShouldHaveCount(3, $I);
        $this->thenFirstResultUsernameShouldBe('Alex', $I);
        $this->thenResultsShouldBeOrderedByDescendingSimilarity($I);
    }

    public function shouldBeCaseInsensitive(FunctionalTester $I): void
    {
        $this->givenUsersWithUsernames(['SMITH', 'smith', 'Smith'], $I);

        $this->whenSearchingForUsername('sMiTh');

        $this->thenResultShouldHaveCount(3, $I);
        $this->thenAllResultsShouldHaveSimilarityScore(1.0, $I);
    }

    public function shouldRespectLimitParameter(FunctionalTester $I): void
    {
        $this->givenUsersWithUsernames(['User1', 'User2', 'User3', 'User4', 'User5'], $I);

        $this->whenSearchingForUsernameWithLimit('User', 2);

        $this->thenResultShouldHaveCount(2, $I);
    }

    public function shouldHandleSpecialCharacters(FunctionalTester $I): void
    {
        $this->givenUsersWithUsernames(['Jean-Paul', 'JeanPaul', 'Jean Paul'], $I);

        $this->whenSearchingForUsername('Jean-Paul');

        $this->thenResultShouldHaveCount(3, $I);
        $this->thenFirstResultUsernameShouldBe('Jean-Paul', $I);
        $this->thenFirstResultSimilarityScoreShouldBe(1.0, $I);
    }

    public function shouldOrderByDistanceThenById(FunctionalTester $I): void
    {
        $this->givenUserWithUsername('ZZZTest1', $I);
        $this->givenUserWithUsername('ZZZTest2', $I);
        $this->givenUserWithUsername('ZZZTest3', $I);

        $this->whenSearchingForUsername('ZZZTest');

        $this->thenResultShouldHaveCount(3, $I);
        $this->thenResultAtIndexShouldHaveUsername(0, 'ZZZTest1', $I);
        $this->thenResultAtIndexShouldHaveUsername(1, 'ZZZTest2', $I);
        $this->thenResultAtIndexShouldHaveUsername(2, 'ZZZTest3', $I);
    }

    public function shouldHandleAccentedCharacters(FunctionalTester $I): void
    {
        $this->givenUsersWithUsernames(['José', 'Jose', 'Josè'], $I);

        $this->whenSearchingForUsername('Jose');

        $this->thenResultShouldHaveCount(3, $I);
        $this->thenFirstResultUsernameShouldBe('Jose', $I);
    }

    private function givenUsersWithUsernames(array $usernames, FunctionalTester $I): void
    {
        foreach ($usernames as $username) {
            $this->givenUserWithUsername($username, $I);
        }
    }

    private function givenUserWithUsername(string $username, FunctionalTester $I): User
    {
        $user = new User();
        $user
            ->setUserId(Uuid::v4()->toRfc4122())
            ->setUserName($username);
        $I->haveInRepository($user);

        return $user;
    }

    private function whenSearchingForUsername(string $username): void
    {
        $this->whenSearchingForUsernameWithLimit($username, 3);
    }

    private function whenSearchingForUsernameWithLimit(string $username, int $limit): void
    {
        $query = new SearchUsersByUsernameQuery(username: $username, limit: $limit);
        $this->result = $this->queryHandler->execute($query);
    }

    private function thenResultShouldHaveCount(int $count, FunctionalTester $I): void
    {
        $I->assertCount($count, $this->result);
    }

    private function thenFirstResultUsernameShouldBe(string $username, FunctionalTester $I): void
    {
        $I->assertEquals($username, $this->result[0]->username);
    }

    private function thenFirstResultSimilarityScoreShouldBe(float $score, FunctionalTester $I): void
    {
        $I->assertEquals($score, $this->result[0]->similarityScore);
    }

    private function thenResultsShouldBeOrderedByDescendingSimilarity(FunctionalTester $I): void
    {
        $I->assertGreaterThanOrEqual($this->result[1]->similarityScore, $this->result[0]->similarityScore);
        $I->assertGreaterThanOrEqual($this->result[2]->similarityScore, $this->result[1]->similarityScore);
    }

    private function thenAllResultsShouldHaveSimilarityScore(float $score, FunctionalTester $I): void
    {
        foreach ($this->result as $user) {
            $I->assertEquals($score, $user->similarityScore);
        }
    }

    private function thenResultAtIndexShouldHaveUsername(int $index, string $username, FunctionalTester $I): void
    {
        $I->assertEquals($username, $this->result[$index]->username);
    }
}
