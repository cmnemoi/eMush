# Code style & Architecture Guidelines

- Dependency inversion principle: depend on abstractions for infrastructure concerns, not on concretions.
- Dogmatic clean code: Extract Until You Drop.
- SRP services / commands: one service / command = one responsibility with a unique `execute()` public method.
- NEVER use mocks in tests. Prefer real implementations or handwritten in-memory / fakes. Stubs are fine.

# Build & Test Commands
- ALWAYS use `docker` to run commands.
- **Test single module**: `docker exec -u dev mush-php php -d error_reporting="E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED" vendor/bin/codecept run tests/functional/[module] && docker exec -u dev mush-php php -d error_reporting="E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED" vendor/bin/codecept run tests/unit/[module]`
- **Run all tests**: `docker exec -u dev mush-php php -d error_reporting="E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED" vendor/bin/codecept run`
- **Unit tests only**: `docker exec -u dev mush-php php -d error_reporting="E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED" vendor/bin/codecept run tests/unit`
- **Functional tests**: `docker exec -u dev mush-php php -d error_reporting="E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED" vendor/bin/codecept run tests/functional`
- **Load fixtures**: `docker exec -u dev mush-php php -d error_reporting="E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED" bin/console doctrine:fixtures:load --env=test -n --verbose`
- **Lint backend**: `docker exec -u dev mush-php php -d error_reporting="E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED" vendor/bin/php-cs-fixer fix`
- **Lint frontend**: `docker exec -u node mush-front yarn lint`
- **Build frontend**: `docker exec -u node mush-front yarn build`