# Project eMush

[![pipeline](https://gitlab.com/eternaltwin/mush/mush/badges/develop/pipeline.svg)](https://gitlab.com/eternaltwin/mush/mush/-/pipelines?ref=develop)
[![coverage](https://gitlab.com/eternaltwin/mush/mush/badges/develop/coverage.svg?job=api-test-develop&key_text=Backend+Coverage&key_width=130)](https://gitlab.com/eternaltwin/mush/mush/-/graphs/develop/charts)
[![discord](https://user-content.gitlab-static.net/7e2a439cd72fbe75267ad51eece2abd136f004b2/68747470733a2f2f696d672e736869656c64732e696f2f646973636f72642f363933303832303131343834363834333438)](https://discord.com/channels/693082011484684348/746873392463872071)
[![localization](https://user-content.gitlab-static.net/d208b981d10933645dfa09029e4afbd7ea88b82e/68747470733a2f2f6261646765732e63726f7764696e2e6e65742f652f36626663626161663734323533613238333761646162303566613035353165332f6c6f63616c697a65642e737667)](https://eternaltwin.crowdin.com/emush)

[eMush](https://emush.eternaltwin.org/) is an open-source remake of Mush: the greatest space-opera epic of Humanity, directly in your browser!

### eMush Api

A REST Api developed using [Symfony 6.2](https://symfony.com/doc/6.2/index.html) that manages the eMush game.

Please read [API.md](./Api/README.md) for details on the API architecture.

### eMush App

A front-end developed using [VueJs 3](https://vuejs.org/guide/introduction.html).

Please read [APP.md](./App/README.md) for details on the APP architecture.

## Getting Started

### Gitpod : your development environment in the cloud

If you don't want to go through the installation process, you can try using the project's Gitpod workspace (in alpha): 

[![Open in Gitpod](https://gitpod.io/button/open-in-gitpod.svg)](https://gitpod.io/#https://gitlab.com/eternaltwin/mush/mush)

This will create a new workspace in the cloud with all the dependencies installed and the project ready to run with your prefered IDE. You need a Gitpod account to use this feature.

### Installing with Docker

#### Windows Users:

Windows users first need to install WSL2 and Docker Desktop.

Docker Desktop for Windows can be downloaded [here](https://docs.docker.com/desktop/install/windows-install/)

WSL2 should be installed by default on recent Windows 10+ versions. Try running `wsl --help` in a Powershell terminal. If it doesn't work, follow the instructions [here](https://learn.microsoft.com/fr-fr/windows/wsl/install-manual).

Install [Debian](https://apps.microsoft.com/detail/9msvkqc78pk6) with WSL2 : `wsl --install -d Debian`

Then launch it : `wsl -d Debian`

After configuring your Debian account, you can install the project following the instructions below.

#### Install build tools and Docker

- Install build tools and Git :

```bash
sudo -s
apt update -y
apt install build-essential curl git -y
```
- For native Debian-like users, install Docker and Docker Compose in command line :

```bash
install -m 0755 -d /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/debian/gpg -o /etc/apt/keyrings/docker.asc
chmod a+r /etc/apt/keyrings/docker.asc
echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.asc] https://download.docker.com/linux/debian \
  $(. /etc/os-release && echo "$VERSION_CODENAME") stable" | \
  sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
apt update -y
apt install docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin -y
exit # Quit root mode
cd ~ # Go back to your home directory
```
Then, add your user to the Docker group :

```bash
sudo groupadd docker
sudo usermod -aG docker $USER
newgrp docker
```

Run `docker run hello-world` to check if Docker is correctly installed. If not :
- try to run it in a new terminal ;
- log off and log in again ;
- restart your computer and try again.

#### Install the project

- If not done yet, generate a SSH key and add it to your GitLab profile :
  - Generate the key : `ssh-keygen -t rsa -b 2048 -C "SSH Key for eMush repository (https://gitlab.com/eternaltwin/mush/mush)"`
  - Display the key : `cat ~/.ssh/id_rsa.pub`
  - Copy the key and add it to your GitLab profile here : https://gitlab.com/-/profile/keys

- Clone the repository and move to it : `git clone git@gitlab.com:eternaltwin/mush/mush.git && cd mush`

- Build the project : `make install`

That's it! 

If everything went well you should be able to access:
  - Swagger API documentation : http://localhost:8080/swagger/
  - eMush front end : http://localhost

(If not, run `make docker-start` to be sure that all containers are running)

Use the following credentials to login (all users - named by eMush characters - have the same password):
```
username : andie
password : 1234567891
```

You should land in a fully working Daedalus!

### Installing without Docker

TODO : write a Bash and a Powershell script because I hate typing multiple commands to install a project

Clone repository https://gitlab.com/eternaltwin/mush/mush.git

Install NVM and yarn https://github.com/coreybutler/nvm-windows/releases
```
nvm install latest
nvm use latest
npm install -g yarn
```
	
Download the last version of PHP https://windows.php.net/download#php-8.3
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
Use `mush` as passphrase or update the `.env` with your passphrase


In folder `Api/`
```
cp .env.dist .env
composer update
php bin/console mush:migrate --dev
php -S localhost:8080 -t public
```
     
In folder `App/`
```
cp .env.dist .env
yarn install
yarn serve
```

In folder `EternalTwin/`
```
cp .etwin.toml.example .etwin.toml
yarn install
yarn etwin db create
yarn etwin start
```

## Contributing

Please read [CONTRIBUTING.md](./CONTRIBUTING.md) for details on our code of conduct, and the process for submitting pull requests to us.

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
docker login registry.gitlab.com -u YOUR_USERNAME -p ACCESSS_TOKEN
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
Change line 55: `- "80:5173"` by `- "new_port:5173"` where new_port is the desired port
Change the `App/.env`
`VITE_APP_URL=http://localhost` by `VITE_APP_URL=http://localhost:new_port`
Run `make docker-start` (`make gitpod-start` on Gitpod) so that the changes are taken into account

#### Changing back port:
- in docker/docker-compose.dev.yml:
Change line 8: `- "8080:80"` by `- "new_port:80"` where new_port is the desired port
- Change the `App/.env`
`VITE_APP_API_URL=http://localhost:8080/api/v1/
VITE_APP_OAUTH_URL=http://localhost:8080/oauth
` by
`VITE_APP_API_URL=http://localhost:new_port/api/v1/
VITE_APP_OAUTH_URL=http://localhost:new_port/oauth`

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

## License

The source code of this project is licensed under [AGPL-3.0-or-later](LICENSE) License.

All Motion Twin assets in `App/src/assets` are licensed under [CC-BY-NC-SA-4.0](App/src/assets/LICENSE) License.
