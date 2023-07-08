
bash-apache:
	docker exec -it mush_apache bash

bash-api:
	docker exec -u dev -it mush_php bash

bash-api-root:
	docker exec -it mush_php bash

bash-eternal-twin:
	docker exec -it eternal_twin bash

bash-front:
	docker exec -it mush_front bash

bash-mysql:
	docker exec -it mush_database bash

build:
	docker compose -f docker/docker-compose.yml -f docker/docker-compose.dev.yml build
	docker compose -f docker/docker-compose.yml -f docker/docker-compose.dev.yml run -u root mush_front chown -R node:node /www
	docker compose -f docker/docker-compose.yml -f docker/docker-compose.dev.yml run -u root eternal_twin chown -R node:node /www
	docker compose -f docker/docker-compose.yml -f docker/docker-compose.dev.yml run -u root mush_php chown -R dev:dev /www
	docker compose -f docker/docker-compose.yml -f docker/docker-compose.dev.yml up --no-start

create-crew: docker-start
	@echo "Waiting for ET server to fully start..."
	sleep 10
	docker compose -f docker/docker-compose.yml run -u dev mush_php php bin/console mush:create-crew

docker-start: docker-stop
	docker compose -f docker/docker-compose.yml -f docker/docker-compose.dev.yml up -d --no-recreate --remove-orphans

docker-stop:
	docker compose -f docker/docker-compose.yml -f docker/docker-compose.dev.yml stop

docker-watch:
	docker compose -f docker/docker-compose.yml -f docker/docker-compose.dev.yml up --no-recreate --remove-orphans

fill-daedalus:
	docker compose -f docker/docker-compose.yml run -u dev mush_php php bin/console mush:fill-daedalus

gitpod-install: gitpod-setup-env-variables build install-api start-mush-database install-front install-eternal-twin setup-JWT-certificates

gitpod-setup-env-variables:
	sudo cp ./Api/.env.dist ./Api/.env
	sudo cp ./App/.env.dist ./App/.env
	sudo cp ./EternalTwin/etwin.toml.example ./EternalTwin/etwin.toml

install: setup-env-variables build install-api start-mush-database install-front install-eternal-twin setup-JWT-certificates

install-eternal-twin: start-mush-database
	docker compose -f docker/docker-compose.yml run -u node eternal_twin yarn install
	docker compose -f docker/docker-compose.yml run -u node eternal_twin yarn etwin db create

install-api:
	docker start mush_php mush_database eternal_twin &&\
	docker compose -f docker/docker-compose.yml run -u dev mush_php composer install &&\
	docker compose -f docker/docker-compose.yml run -u dev mush_php ./reset.sh --init

install-front:
	docker start mush_front &&\
	docker compose -f docker/docker-compose.yml run -u node mush_front yarn install &&\
	docker compose -f docker/docker-compose.yml run -u node mush_front ./reset.sh

remove-all: #Warning, it will remove EVERY container, images, volumes and network not only emushs ones
	docker system prune --volumes -a

reset-dependencies: install-api install-front install-eternal-twin

remove-all: #Warning, it will remove EVERY container, images, volumes and network not only emushs ones
	docker system prune --volumes -a

reset-dependencies: install-api install-front install-eternal-twin

reset-eternal-twin-database: 
	docker compose -f docker/docker-compose.yml run -u node eternal_twin yarn etwin db create

setup-env-variables:
	cp ./Api/.env.dist ./Api/.env
	cp ./App/.env.dist ./App/.env
	cp ./EternalTwin/etwin.toml.example ./EternalTwin/etwin.toml

setup-JWT-certificates:
	docker compose -f docker/docker-compose.yml run -u dev mush_php openssl genpkey -pass pass:mush -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
	docker compose -f docker/docker-compose.yml run -u dev mush_php openssl pkey -passin pass:mush -in config/jwt/private.pem -out config/jwt/public.pem -pubout
	docker compose -f docker/docker-compose.yml run -u dev mush_php chmod go+r config/jwt/private.pem

setup-env-variables:
	cp ./Api/.env.dist ./Api/.env
	cp ./App/.env.dist ./App/.env
	cp ./EternalTwin/etwin.toml.example ./EternalTwin/etwin.toml

setup-JWT-certificates:
	docker exec -it -udev mush_php openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
	docker exec -it -udev mush_php openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout
	docker exec -i -udev mush_php chmod go+r config/jwt/private.pem

start-mush-database:
	docker start mush_database