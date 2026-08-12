<?php

declare(strict_types=1);

namespace Mush\MetaGame\Query;

use Mush\MetaGame\ViewModel\GameCountersViewModel;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final readonly class CachedGetGameCountersQueryHandler
{
    private const string CACHE_KEY = 'meta_game.game_counters';
    private const int CACHE_TTL_IN_SECONDS = 24 * 60 * 60;

    public function __construct(
        private GetGameCountersQueryHandler $inner,
        private CacheInterface $cache,
    ) {}

    public function execute(GetGameCountersQuery $query): GameCountersViewModel
    {
        return $this->cache->get(
            self::CACHE_KEY,
            function (ItemInterface $item) use ($query): GameCountersViewModel {
                $item->expiresAfter(self::CACHE_TTL_IN_SECONDS);

                return $this->inner->execute($query);
            },
        );
    }
}
