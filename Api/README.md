# Mush Api

A REST Api that manage the Mush game. Creating new Daedalus and manage the players actions

## Getting Started

These instructions will get you a copy of the project up and running on your local machine for development and testing purposes.
See endpoints for information on the different endpoints available.

### Prerequisites

To have a working devlopment environment you will need to install:
* [Docker](https://docs.docker.com/get-docker/) 
* [Docker-compose](https://docs.docker.com/compose/install/) 

Optional:
* [Postman](https://www.postman.com/) - Postman requests are exported in this file : mush.postman_collection.json

### Installing

To have a working dev environment, follow the following steps

Clone the project
```
git clone git@gitlab.com:eternal-twin/mush.git
```
Go to the Api directory:
```
cd mush/Api
```
Checkout to develop:
```
git checkout develop
```
copy the .env.dist file (and change environment variables if required):
```
cp .env.dist .env
```
Build the docker containers:
```
make build
Or 
docker-compose -f docker/docker-compose.yml build
```
Start the docker container
```
make docker-watch (make docker-start if you don't mind the compilation outputs)
Or 
docker exec -it mush_api bash
```
Compile the Api
```
npm run compile
```
Run the migrations
```
npm run run-migration
```
If everything went well you should be able to access: http://localhost:8080/swagger/

## Running the tests
If you need you can create a .env.test for specific variable environment for test purpose (having a test database for instance)
Run the tests
```
npm run test
```

## Endpoints
A swagger is available that list all the available endpoints and their specifications [Swagger](http://localhost:8080/swagger/) 
To authenticate, at the moment, use the login endpoint and set the access_token returned in the swagger header to use the other endpoints

## Contributing

Please read [CONTRIBUTING.md](./docs/CONTRIBUTING.md) for details on our code of conduct, and the process for submitting pull requests to us.

Please read [ARCHITECTURE.md](./docs/ARCHITECTURE.md) for details on the architecture
