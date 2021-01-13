
docker-start: docker-stop
	docker-compose -f docker/docker-compose.yml up -d

docker-watch:
	docker-compose -f docker/docker-compose.yml up

docker-stop:
	docker-compose -f docker/docker-compose.yml stop

bash-api:
	docker exec -udev -it mush_php bash

bash-api-root:
	docker exec -it mush_php bash

bash-apache:
	docker exec -it mush_apache bash

bash-front:
	docker exec -it mush_front bash

bash-mysql:
	docker exec -it mush_database bash

reset-dependencies: install-api install-front install-eternal-twin

build:
	docker-compose -f docker/docker-compose.yml build
	docker-compose -f docker/docker-compose.yml up --no-start

install: build install-api
	docker-compose -f docker/docker-compose.yml run -u node mush_front yarn install
	docker-compose -f docker/docker-compose.yml run -u node eternal_twin yarn install

remove-all: #Warning, it will remove EVERY container, images, volumes and network not only emushs ones
	docker system prune --volumes

install-eternal-twin: reset-eternal-twin-database
	docker start eternal_twin
	docker exec -i eternal_twin yarn install

install-api:
	docker start mush_php mush_database &&\
	docker exec -i mush_php composer install &&\
	docker exec -i mush_php ./reset.sh

install-front:
	docker start mush_front &&\
	docker exec -i mush_front yarn install &&\
	docker exec -i mush_front ./reset.sh

reset-eternal-twin-database:
	docker start mush_database &&\
	cat docker/EternalTwin/drop.sql | docker exec -i mush_database psql --username mysql eternal_twin &&\
	cat docker/EternalTwin/dump_12-01-2021_20_33_41.sql | docker exec -i mush_database psql --username mysql eternal_twin
