diff:
	php bin/console doctrine:migrations:diff -n

drop:
	php bin/console doctrine:schema:drop --force --full-database

load-data:
	php bin/console mush:migrate --dev

lint:
	vendor/bin/php-cs-fixer fix

psalm:
	vendor/bin/psalm

test:
	vendor/bin/codecept run

test-functional:
	vendor/bin/codecept run tests/functional

test-unit:
	vendor/bin/codecept run tests/unit

update-schema: 
	php bin/console doctrine:migrations:migrate -n
