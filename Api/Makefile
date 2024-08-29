create-daedalus:
	php bin/console mush:create-daedalus --dev

diff:
	php bin/console doctrine:migrations:diff -n

drop:
	php bin/console doctrine:schema:drop --force --full-database

fill-daedalus-all:
	php bin/console mush:fill-daedalus

fill-daedalus-two:
	php bin/console mush:fill-daedalus --number 2

load-data:
	php bin/console mush:load-configs-data

load-fixtures:
	php bin/console doctrine:database:create --env=test --if-not-exists
	php bin/console doctrine:schema:drop --full-database --env=test -f
	php bin/console doctrine:schema:update --complete --env=test -f
	php bin/console doctrine:fixtures:load --env=test -n --verbose

lint:
	vendor/bin/php-cs-fixer fix

psalm:
	vendor/bin/psalm

reset: load-fixtures
	php bin/console doctrine:schema:drop --force --full-database
	php bin/console mush:migrate --dev

test:
	vendor/bin/codecept run

test-functional:
	vendor/bin/codecept run tests/functional

test-unit:
	vendor/bin/codecept run tests/unit

update-schema: 
	php bin/console doctrine:migrations:migrate -n

validate-crew-death:
	php bin/console mush:validate-crew-death
