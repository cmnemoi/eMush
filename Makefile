build:
	docker-compose -f docker/docker-compose.yml build

docker-start: docker-stop
	docker-compose -f docker/docker-compose.yml up -d

docker-watch:
	docker-compose -f docker/docker-compose.yml up

docker-stop:
	docker-compose -f docker/docker-compose.yml stop

bash-api:
	docker exec -it mush_php bash

bash-api-root:
	docker exec -it mush_php bash

bash-apache:
	docker exec -it mush_apache bash

bash-front:
	docker exec -it front_mush bash

bash-mysql:
	docker exec -it mush_database bash