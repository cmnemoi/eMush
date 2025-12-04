<?php

declare(strict_types=1);

namespace Mush\User\Query;

use Doctrine\DBAL\Connection;
use Mush\User\ViewModel\UserSearchViewModel;

final readonly class SearchUsersByUsernameQueryHandler
{
    public function __construct(private Connection $connection) {}

    public function execute(SearchUsersByUsernameQuery $query): array
    {
        $sql = <<<'SQL'
            SELECT
                users.user_id AS id,
                users.username AS username,
                similarity(LOWER(users.username), LOWER(:search_term)) AS similarity_score
            FROM users
            ORDER BY similarity_score DESC, users.id ASC
            LIMIT :limit
            SQL;

        $results = $this->connection->executeQuery($sql, [
            'search_term' => $query->username,
            'limit' => $query->limit,
        ])->fetchAllAssociative();

        return array_map(
            static fn (array $row) => UserSearchViewModel::fromQueryRow($row),
            $results
        );
    }
}
