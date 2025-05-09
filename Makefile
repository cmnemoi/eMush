UNAME := $(shell uname -s)

bash-apache:
	docker exec -it mush_apache bash

bash-api:
	docker exec -u dev -it mush_php bash

bash-api-root:
	docker exec -it mush_php bash

bash-eternaltwin:
	docker exec -it eternaltwin bash

bash-front:
	docker exec -it mush_front bash

bash-mysql:
	docker exec -it mush_database bash

build:
	docker compose -f docker/docker-compose.yml -f docker/docker-compose.dev.yml build
	docker compose -f docker/docker-compose.yml -f docker/docker-compose.dev.yml run -u root mush_front chown -R node:node /www
	docker compose -f docker/docker-compose.yml -f docker/docker-compose.dev.yml run -u root musheternaltwin chown -R node:node /www
	docker compose -f docker/docker-compose.yml -f docker/docker-compose.dev.yml run -u root mush_php chown -R dev:dev /www
	docker compose -f docker/docker-compose.yml -f docker/docker-compose.dev.yml up --no-start --remove-orphans

docker-fresh-start: docker-stop
	docker compose -f docker/docker-compose.yml -f docker/docker-compose.dev.yml up -d --force-recreate --remove-orphans

docker-start: docker-stop
	docker compose -f docker/docker-compose.yml -f docker/docker-compose.dev.yml up -d --no-recreate --remove-orphans

docker-stop:
	docker compose -f docker/docker-compose.yml -f docker/docker-compose.dev.yml stop

docker-watch:
	docker compose -f docker/docker-compose.yml -f docker/docker-compose.dev.yml up --no-recreate --remove-orphans

fill-daedalus:
	@echo "Waiting for Eternaltwin to be ready..."
	@sleep 10
	docker compose -f docker/docker-compose.yml run -u dev mush_php php bin/console mush:fill-daedalus

install: setup-git-hooks setup-env-variables build install-api install-front install-eternaltwin setup-JWT-certificates reset-eternaltwin-database docker-start fill-daedalus
	@echo "Installation completed successfully ! You can access eMush at http://localhost/."
	@echo "You can log in with the following credentials:"
	@echo "Username: chun"
	@echo "Password: 1234567891"

install-api:
	docker compose -f docker/docker-compose.yml run -u dev mush_php composer install &&\
	docker compose -f docker/docker-compose.yml run -u dev mush_php ./reset.sh --init

install-eternaltwin:
	docker compose -f docker/docker-compose.yml run -u node musheternaltwin yarn install
	docker compose -f docker/docker-compose.yml run -u node musheternaltwin yarn etwin db reset
	docker compose -f docker/docker-compose.yml run -u node musheternaltwin yarn etwin db sync

install-front:
	docker compose -f docker/docker-compose.yml run -u node mush_front yarn install &&\
	docker compose -f docker/docker-compose.yml run -u node mush_front ./reset.sh

remove-all: #Warning, it will remove EVERY container, images, volumes and network not only emushs ones
	docker system prune --volumes -a

reset-dependencies: install-api install-front install-eternaltwin

reset-eternaltwin-database: 
	docker compose -f docker/docker-compose.yml run -u node musheternaltwin yarn etwin db reset
	docker compose -f docker/docker-compose.yml run -u node musheternaltwin yarn etwin db sync

setup-env-variables:
	cp ./Api/.env.dist ./Api/.env
	cp ./App/.env.dist ./App/.env
	cp ./Eternaltwin/eternaltwin.toml ./Eternaltwin/eternaltwin.local.toml

setup-git-hooks:
	chmod +x hooks/pre-commit
	chmod +x hooks/pre-push
	git config core.hooksPath hooks

setup-JWT-certificates:
	docker compose -f docker/docker-compose.yml run -u dev mush_php openssl genpkey -pass pass:mush -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
	docker compose -f docker/docker-compose.yml run -u dev mush_php openssl pkey -passin pass:mush -in config/jwt/private.pem -out config/jwt/public.pem -pubout
	docker compose -f docker/docker-compose.yml run -u dev mush_php chmod go+r config/jwt/private.pem

start-eternaltwin-server:
	docker compose -f docker/docker-compose.yml run -u node eternaltwin yarn etwin start

gitpod-install: setup-git-hooks gitpod-setup-env-variables gitpod-build install-api install-front install-eternaltwin setup-JWT-certificates reset-eternaltwin-database gitpod-start fill-daedalus

gitpod-build:
	docker compose -f docker/docker-compose.yml -f docker/docker-compose.gitpod.yml build
	docker compose -f docker/docker-compose.yml -f docker/docker-compose.gitpod.yml run -u root mush_front chown -R node:node /www
	docker compose -f docker/docker-compose.yml -f docker/docker-compose.gitpod.yml run -u root musheternaltwin chown -R node:node /www
	docker compose -f docker/docker-compose.yml -f docker/docker-compose.gitpod.yml run -u root mush_php chown -R dev:dev /www
	docker compose -f docker/docker-compose.yml -f docker/docker-compose.gitpod.yml up --no-start

gitpod-setup-env-variables:
	cp ./Api/.env.dist ./Api/.env
	cp ./App/.env.gitpod ./App/.env
	cp ./Eternaltwin/eternaltwin.toml ./Eternaltwin/eternaltwin.local.toml

gitpod-start: docker-stop
	docker compose -f docker/docker-compose.yml -f docker/docker-compose.gitpod.yml up -d --no-recreate --remove-orphans

gitpod-stop:
	docker compose -f docker/docker-compose.yml -f docker/docker-compose.gitpod.yml stop

gitpod-watch:
	docker compose -f docker/docker-compose.yml -f docker/docker-compose.gitpod.yml up --no-recreate --remove-orphans
