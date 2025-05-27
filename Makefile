.PHONY: bash-apache
bash-apache:
	docker exec -it mush-apache bash

.PHONY: bash-api
bash-api:
	docker exec -u dev -it mush-php bash

.PHONY: bash-api-root
bash-api-root:
	docker exec -it mush-php bash

.PHONY: bash-eternaltwin
bash-eternaltwin:
	docker exec -it mush-eternaltwin bash

.PHONY: bash-front
bash-front:
	docker exec -it mush-front bash

.PHONY: bash-mysql
bash-mysql:
	docker exec -it mush-database bash

.PHONY: build
build:
	docker compose -f docker/docker-compose.yml -f docker/docker-compose.dev.yml build
	docker compose -f docker/docker-compose.yml -f docker/docker-compose.dev.yml run -u root mush-front chown -R node:node /www
	docker compose -f docker/docker-compose.yml -f docker/docker-compose.dev.yml run -u root mush-eternaltwin chown -R node:node /www
	docker compose -f docker/docker-compose.yml -f docker/docker-compose.dev.yml run -u root mush-php chown -R dev:dev /www
	docker compose -f docker/docker-compose.yml -f docker/docker-compose.dev.yml up --no-start --remove-orphans

.PHONY: docker-fresh-start
docker-fresh-start: docker-stop
	docker compose -f docker/docker-compose.yml -f docker/docker-compose.dev.yml up -d --remove-orphans

.PHONY: docker-start
docker-start: docker-stop
	docker compose -f docker/docker-compose.yml -f docker/docker-compose.dev.yml up -d --no-recreate --remove-orphans

.PHONY: docker-stop
docker-stop:
	docker compose -f docker/docker-compose.yml -f docker/docker-compose.dev.yml stop

.PHONY: docker-watch
docker-watch:
	docker compose -f docker/docker-compose.yml -f docker/docker-compose.dev.yml up --no-recreate --remove-orphans

.PHONY: fill-daedalus
fill-daedalus:
	@echo "Waiting for Eternaltwin to be ready..."
	@sleep 10
	docker compose -f docker/docker-compose.yml run -u dev mush-php php bin/console mush:fill-daedalus

.PHONY: install
install: setup-git-hooks setup-env-variables build install-api install-front install-eternaltwin setup-JWT-certificates reset-eternaltwin-database docker-start fill-daedalus
	@echo "Installation completed successfully ! You can access eMush at http://localhost/."
	@echo "You can log in with the following credentials:"
	@echo "Username: chun"
	@echo "Password: 1234567891"

.PHONY: install-api
install-api:
	docker compose -f docker/docker-compose.yml run -u dev mush-php composer install &&\
	docker compose -f docker/docker-compose.yml run -u dev mush-php ./reset.sh --init

.PHONY: install-eternaltwin
.PHONY: install-eternaltwin
install-eternaltwin:
	docker compose -f docker/docker-compose.yml run -u node mush-eternaltwin yarn install
	docker compose -f docker/docker-compose.yml run -u node mush-eternaltwin yarn etwin db reset
	docker compose -f docker/docker-compose.yml run -u node mush-eternaltwin yarn etwin db sync

.PHONY: install-front
install-front:
	docker compose -f docker/docker-compose.yml run -u node mush-front yarn install &&\
	docker compose -f docker/docker-compose.yml run -u node mush-front ./reset.sh

.PHONY: remove-all
remove-all: #Warning, it will remove EVERY container, images, volumes and network not only emushs ones
	docker system prune --volumes -a

.PHONY: reset-dependencies
reset-dependencies: install-api install-front install-eternaltwin

.PHONY: reset-eternaltwin-database
reset-eternaltwin-database: 
	docker compose -f docker/docker-compose.yml run -u node mush-eternaltwin yarn etwin db reset
	docker compose -f docker/docker-compose.yml run -u node mush-eternaltwin yarn etwin db sync

.PHONY: setup-env-variables
setup-env-variables:
	cp ./Api/.env.dist ./Api/.env.local
	cp ./Api/.env.test ./Api/.env.test.local
	cp ./App/.env.dist ./App/.env
	cp ./Eternaltwin/eternaltwin.toml ./Eternaltwin/eternaltwin.local.toml

.PHONY: setup-git-hooks
setup-git-hooks:
	chmod +x hooks/pre-commit
	chmod +x hooks/pre-push
	git config core.hooksPath hooks

.PHONY: setup-JWT-certificates
setup-JWT-certificates:
	docker compose -f docker/docker-compose.yml run -u dev mush-php openssl genpkey -pass pass:mush -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
	docker compose -f docker/docker-compose.yml run -u dev mush-php openssl pkey -passin pass:mush -in config/jwt/private.pem -out config/jwt/public.pem -pubout
	docker compose -f docker/docker-compose.yml run -u dev mush-php chmod go+r config/jwt/private.pem

.PHONY: start-eternaltwin-server
start-eternaltwin-server:
	docker compose -f docker/docker-compose.yml run -u node mush-eternaltwin yarn etwin start

.PHONY: gitpod-install
gitpod-install: setup-git-hooks gitpod-setup-env-variables gitpod-build install-api install-front install-eternaltwin setup-JWT-certificates reset-eternaltwin-database gitpod-start fill-daedalus

.PHONY: gitpod-build
gitpod-build:
	docker compose -f docker/docker-compose.yml -f docker/docker-compose.gitpod.yml build
	docker compose -f docker/docker-compose.yml -f docker/docker-compose.gitpod.yml run -u root mush-front chown -R node:node /www
	docker compose -f docker/docker-compose.yml -f docker/docker-compose.gitpod.yml run -u root mush-eternaltwin chown -R node:node /www
	docker compose -f docker/docker-compose.yml -f docker/docker-compose.gitpod.yml run -u root mush-php chown -R dev:dev /www
	docker compose -f docker/docker-compose.yml -f docker/docker-compose.gitpod.yml up --no-start

.PHONY: gitpod-setup-env-variables
gitpod-setup-env-variables:
	cp ./Api/.env.dist ./Api/.env
	cp ./App/.env.gitpod ./App/.env
	cp ./Eternaltwin/eternaltwin.toml ./Eternaltwin/eternaltwin.local.toml

.PHONY: gitpod-start
gitpod-start: docker-stop
	docker compose -f docker/docker-compose.yml -f docker/docker-compose.gitpod.yml up -d --no-recreate --remove-orphans

.PHONY: gitpod-stop
gitpod-stop:
	docker compose -f docker/docker-compose.yml -f docker/docker-compose.gitpod.yml stop

.PHONY: gitpod-watch
gitpod-watch:
	docker compose -f docker/docker-compose.yml -f docker/docker-compose.gitpod.yml up --no-recreate --remove-orphans

.PHONY: bare-metal-install
bare-metal-install: setup-git-hooks
	chmod +x install.sh
	chmod +x uninstall.sh
	chmod +x start.sh
	chmod +x stop.sh
	bash install.sh

.PHONY: uninstall
uninstall:
	bash uninstall.sh

.PHONY: start
start:
	bash start.sh

.PHONY: stop
stop:
	bash stop.sh