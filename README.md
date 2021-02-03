# Project eMush

### Mush Api

A REST Api developed using Symfony 5.1 that manage the Mush game. Creating new Daedalus and manage the players actions

### Mush App
A front-end developed using VueJs


## Getting Started

These instructions will get you a copy of the project up and running on your local machine for development and testing purposes.
See endpoints for information on the different endpoints available.

### Prerequisites

To have a working devlopment environment you will need to install:
* [Docker](https://docs.docker.com/get-docker/) 
* [Docker-compose](https://docs.docker.com/compose/install/) 

Windows Users:
* [mingw](https://en.wikipedia.org/wiki/MinGW) - Or any tools to run unix commands

Optional:
* [Postman](https://docs.docker.com/get-docker/) - Postman requests are exported in this file : mush.postman_collection.json

### Installing

To have a working dev environment, follow the following steps

Clone the project
```
git clone git@gitlab.com:eternal-twin/mush/mush.git
```
Checkout to develop:
```
git checkout develop
```

Copy the .env.dist file (and change environment variables if required):

```
cp ./Api/.env.dist ./Api/.env
cp ./App/.env.dist ./App/.env
```

Copy the Eternal-Twin config:
```
cp ./EternalTwin/etwin.toml.example ./EternalTwin/etwin.toml
```


Build the docker containers:
```
make install
```

Start the docker container
```
make docker-watch (make docker-start if you don't mind the compilation outputs)
```

Go in the Api container:
```
make bash-api
```

Create the JWT certificates (https://github.com/lexik/LexikJWTAuthenticationBundle):
```
$ mkdir -p config/jwt
$ openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
$ openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout
$ chmod go+r config/jwt/private.pem 
```
Use mush as passphrase or update the .env with your passphrase

If everything went well you should be able to access: 
  - Swagger : http://localhost:8080/swagger/
  - Front end : http://localhost

## Endpoints
A swagger is available that list all the available endpoints and their specifications [Swagger](http://localhost:8080/swagger/) 
To authenticate, at the moment, use the login endpoint and set the access_token returned in the swagger header to use the other endpoints

## Troubleshotings

### Use different ports
Feel free to improve the docker-compose to add a local non-committed docker-compose.dev

For now you have to change the docker-compose.yml and be careful not commit it.
#### Changing front port:
in docker/docker-compose.yml
Change line 55: `- "80:8080"` by `- "new_port:8080"` where new_port is the desired port
Change the `App/.env`
`VUE_APP_URL=http://localhost` by `VUE_APP_URL=http://localhost:new_port`
#### Changing back port:
- in docker/docker-compose.yml:  
Change line 8: `- "8080:80"` by `- "new_port:80"` where new_port is the desired port  
- Change the `App/.env`  
`VUE_APP_API_URL=http://localhost:8080/api/v1/
VUE_APP_OAUTH_URL=http://localhost:8080/oauth
` by  
`VUE_APP_API_URL=http://localhost:new_port/api/v1/
VUE_APP_OAUTH_URL=http://localhost:new_port/oauth`  

- Change `Àpi.env`:  
`OAUTH_CALLBACK="'http://localhost:8080/oauth/callback'"`
by  
`OAUTH_CALLBACK="'http://localhost:new_port/oauth/callback'"`
 
- Change `EternalTwin/etwin.toml`  
line 82: `callback_uri = "http://localhost:8080/oauth/callback"`  
by `callback_uri = "http://localhost:new_port/oauth/callback"`

## Contributing

Please read [CONTRIBUTING.md](./CONTRIBUTING.md) for details on our code of conduct, and the process for submitting pull requests to us.

Please read [API.md](./Api/README.md) for details on the API architecture
Please read [APP.md](./APP/README.md) for details on the APP architecture
