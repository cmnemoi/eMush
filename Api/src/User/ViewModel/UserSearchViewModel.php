<?php

declare(strict_types=1);

namespace Mush\User\ViewModel;

use Mush\Game\ViewModel\ViewModelInterface;

final readonly class UserSearchViewModel implements ViewModelInterface
{
    public function __construct(
        public string $userId,
        public string $username,
        public float $similarityScore,
    ) {}

    public static function fromQueryRow(array $row): self
    {
        return new self(
            (string) $row['id'],
            (string) $row['username'],
            (float) $row['similarity_score'],
        );
    }
}
