# Project eMush

### Mush Api

A REST Api developed using Symfony 5.1 that manage the Mush game. Creating new Daedalus and manage the players actions

### Mush App
A front-end developed using VueJs


## Getting Started

These instructions will get you a copy of the project up and running on your local machine for development and testing purposes.
See endpoints for information on the different endpoints available.

### Prerequisites

To have a working development environment you will need to install:
* [Docker](https://docs.docker.com/get-docker/)
  * _(Windows)_ during installation follow instruction to install WSL2
* [Docker-compose](https://docs.docker.com/compose/install/)

Optional:
* [Postman](https://docs.docker.com/get-docker/) - Postman requests are exported in this file : mush.postman_collection.json

#### Windows Users:
* Start powershell and create a new Debian distrib
	```powershell
	wsl --install -d Debian
	```
* WSL2 linux distro (tested on Debian: https://www.microsoft.com/en-us/p/debian/9msvkqc78pk6)
  * Enable Docker's WSL integration (Settings -> Resources -> WSL Integration)
  * install build tools:
    ```bash
    > wsl -d Debian
    $ sudo -s
    $ apt-get update
    $ apt-get install build-essential curl git
    ```
  * install docker and docker-compose
	```bash
	$ apt-get install lsb-release
	$ mkdir -m 0755 -p /etc/apt/keyrings
	$ curl -fsSL https://download.docker.com/linux/debian/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg
	$ echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.comlinux/debian (lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
	$ apt-get update
	$ apt-get install docker docker-compose docker-compose-plugin
	```

Although, it is possible to run application checked out in Windows and mounted to WSL2, it will be very slow, so I recommend checking out repo inside WSL and then work with sources either by vscode's WSL remote https://marketplace.visualstudio.com/items?itemName=ms-vscode-remote.remote-wsl or accessing files through SMB (e.g. `\\wsl$\Debian\root\mush`).


### Installing

To have a working dev environment, follow the following steps

Generate SSH Key and add it to your gitlab profile
```bash
ssh-keygen -t rsa -b 2048 -C "SSH Key for mush repository (https://gitlab.com/eternaltwin/mush/mush)"
```
On your real machine (windows) go to the folder \\wsl$\Debian\root\.ssh and copy the content of id_rsa.pub then past it in the SSH Keys settings of your gitlab profile

Clone the project
```bash
$ git clone git@gitlab.com:eternaltwin/mush/mush.git
```
Checkout to develop:
```bash
$ git checkout develop
```

Copy the .env.dist file (and change environment variables if required):

```bash
$ cp ./Api/.env.dist ./Api/.env
$ cp ./App/.env.dist ./App/.env
```

Copy the Eternal-Twin config:
```bash
$ cp ./EternalTwin/etwin.toml.example ./EternalTwin/etwin.toml
```

Start docker service
```
service docker start
```

Build the docker containers:
```bash
$ make install
```

Start the docker container
```bash
$ make docker-watch (make docker-start if you don't mind the compilation outputs)
```

Go in the Api container:
```bash
$ make bash-api
```

Create the JWT certificates (https://github.com/lexik/LexikJWTAuthenticationBundle):
```bash
openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout
chmod go+r config/jwt/private.pem
```
Use mush as passphrase or update the .env with your passphrase

If everything went well you should be able to access:
  - Swagger : http://localhost:8080/swagger/
  - Front end : http://localhost

`make install` creates 18 Eternaltwin accounts named by eMush characters with the password `1234567891`.

For example you can use the following credentials to login :
```
username : andie
password : 1234567891
```
## Installing without Docker
Clone repository https://gitlab.com/eternaltwin/mush/mush.git

Install NVM and yarn https://github.com/coreybutler/nvm-windows/releases
```
nvm install latest
nvm use latest
npm install -g yarn
```
	
Download the last version of PHP https://windows.php.net/download#php-8.2
Add the folder containing php.exe to PATH
In php.ini
    activate extension=pdo_pgsql
    activate extension=intl

Download Composer https://getcomposer.org/download/
Add the fold containing composer.bat to PATH

Download and install Postgresql https://www.enterprisedb.com/downloads/postgres-postgresql-downloads
Create new user identified by mysql with password password
Create database mush with user mysql as owner
Create database etwin.dev with user mysql as owner

Create the JWT certificates (https://github.com/lexik/LexikJWTAuthenticationBundle):
```bash
openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout
chmod go+r config/jwt/private.pem
```
Use mush as passphrase or update the .env with your passphrase


In folder Api/
```
cp .env.dist .env
composer update
php bin/console mush:migrate --dev
php -S localhost:8080 -t public
```
     
In folder App/
```
cp .env.dist .env
yarn install
yarn serve
```

In folder EternalTwin/
```
$ cp .etwin.toml.example .etwin.toml
yarn install
yarn etwin db create
yarn etwin start
```

## Endpoints
A swagger is available that list all the available endpoints and their specifications [Swagger](http://localhost:8080/swagger/)
To authenticate, at the moment, use the login endpoint and set the access_token returned in the swagger header to use the other endpoints

## Gitlab
This project use gitlab ci to check the merge requests

### Gitlab docker php images
This image is used for the php environment validation

The dockerfile: [Dockerfile](./docker/gitlab/Php/Dockerfile)

#### Update the container in gitlab

```
$ docker login registry.gitlab.com -u YOUR_USERNAME -p ACCESSS_TOKEN
$docker build -t registry.gitlab.com/eternaltwin/mush/mush/api ./docker/gitlab/Php/
$docker push registry.gitlab.com/eternaltwin/mush/mush/api
```
Username can be found at: https://gitlab.com/-/profile under Full Name

Access Token can be created at: https://gitlab.com/-/profile/personal_access_tokens
(you can also connect with password by using `docker login registry.gitlab.com -u YOUR_USERNAME` then entering your password)

## Troubleshooting

### Use different ports
To use different port modify the docker/docker-compose.dev.yml file

#### Changing front port:
in docker/docker-compose.dev.yml
Change line 55: `- "80:8080"` by `- "new_port:8080"` where new_port is the desired port
Change the `App/.env`
`VUE_APP_URL=http://localhost` by `VUE_APP_URL=http://localhost:new_port`
#### Changing back port:
- in docker/docker-compose.dev.yml:
Change line 8: `- "8080:80"` by `- "new_port:80"` where new_port is the desired port
- Change the `App/.env`
`VUE_APP_API_URL=http://localhost:8080/api/v1/
VUE_APP_OAUTH_URL=http://localhost:8080/oauth
` by
`VUE_APP_API_URL=http://localhost:new_port/api/v1/
VUE_APP_OAUTH_URL=http://localhost:new_port/oauth`

- Change `Api.env`:
`OAUTH_CALLBACK="'http://localhost:8080/oauth/callback'"`
by
`OAUTH_CALLBACK="'http://localhost:new_port/oauth/callback'"`

- Change `EternalTwin/etwin.toml`
line 82: `callback_uri = "http://localhost:8080/oauth/callback"`
by `callback_uri = "http://localhost:new_port/oauth/callback"`

## Permission issues

If for some reason your user id (`id -u`) and group id (`id -g`) aren't 1000 then you can specify them in docker/docker-compose.dev.yml

``` yaml
mush_php:
 build:
  args:
  - UID=1000
  - GID=1000
```

## Contributing

Please read [CONTRIBUTING.md](./CONTRIBUTING.md) for details on our code of conduct, and the process for submitting pull requests to us.

Please read [API.md](./Api/README.md) for details on the API architecture
Please read [APP.md](./APP/README.md) for details on the APP architecture

