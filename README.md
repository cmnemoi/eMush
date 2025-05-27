# Project eMush

[![pipeline](https://gitlab.com/eternaltwin/mush/mush/badges/develop/pipeline.svg)](https://gitlab.com/eternaltwin/mush/mush/-/pipelines?ref=develop)
[![coverage](https://gitlab.com/eternaltwin/mush/mush/badges/develop/coverage.svg?job=api-test-develop&key_text=Backend+Coverage&key_width=130)](https://gitlab.com/eternaltwin/mush/mush/-/graphs/develop/charts)
[![discord](https://user-content.gitlab-static.net/7e2a439cd72fbe75267ad51eece2abd136f004b2/68747470733a2f2f696d672e736869656c64732e696f2f646973636f72642f363933303832303131343834363834333438)](https://discord.com/channels/693082011484684348/746873392463872071)
[![localization](https://user-content.gitlab-static.net/d208b981d10933645dfa09029e4afbd7ea88b82e/68747470733a2f2f6261646765732e63726f7764696e2e6e65742f652f36626663626161663734323533613238333761646162303566613035353165332f6c6f63616c697a65642e737667)](https://eternaltwin.crowdin.com/emush)

[eMush](https://emush.eternaltwin.org/) is an open source remake of Mush: the greatest space opera of Humanity, available on all your devices!

### eMush API

A REST API developed using [Symfony 6.4](https://symfony.com/doc/6.4/index.html) that manages the eMush game.

Please read [API.md](./Api/README.md) for details on the API architecture.

### eMush App

A front-end developed using [VueJs 3](https://vuejs.org/guide/introduction.html).

Please read [APP.md](./App/README.md) for details on the APP architecture.

## Getting Started

### Gitpod : your development environment in the cloud

If you don't want to go through the installation process, you can use the project's Gitpod workspace:

[![Open in Gitpod](https://gitpod.io/button/open-in-gitpod.svg)](https://gitpod.io/#https://gitlab.com/eternaltwin/mush/mush)

This will create a new workspace in the cloud with all the dependencies installed and the project ready to run with your prefered IDE. You need a Gitpod account to use this feature.

### Installing with Docker (recommended)

#### Windows

- Install [Docker Desktop](https://docs.docker.com/desktop/install/windows-install/) ;
- Install [Ubuntu](https://apps.microsoft.com/detail/9msvkqc78pk6) with WSL2 : `wsl --install -d Ubuntu` ;
  - WSL2 should be installed by default on recent Windows 10+ versions. Try running `wsl --set-default-version 2` in a Powershell terminal. If it doesn't work, follow the instructions [here](https://learn.microsoft.com/fr-fr/windows/wsl/install-manual).
- Launch it : `wsl -d Ubuntu` ;
- Run `curl -sSL https://gitlab.com/eternaltwin/mush/mush/-/raw/develop/clone_and_docker_install.sh?ref_type=heads | bash` in your WSL2 terminal.

#### Ubuntu (recommended)

Run `curl -sSL https://gitlab.com/eternaltwin/mush/mush/-/raw/develop/clone_and_docker_install.sh?ref_type=heads | bash` in your terminal.

#### MacOS

- Install `git` and `make` ;
- Install [Docker Desktop](https://docs.docker.com/desktop/install/mac-install/) ;
- Clone the repository : `git clone https://gitlab.com/eternaltwin/mush/mush.git && cd mush` ;
- Run `make install`.

#### GNU/Linux (other distributions)

Refer to detailed Docker installation instructions [here](https://gitlab.com/eternaltwin/mush/mush/-/wikis/Docker-install-detailed-instructions) and adapt to your needs.

If you enconter any issue, ask for help on [Discord](https://discord.com/channels/693082011484684348/746873392463872071).

### Installing without Docker

If you don't want to use Docker, here are two installation scripts.

#### Windows (highly experimental)

Run those commands in a Powershell terminal:

```powershell
Invoke-WebRequest -Uri "https://gitlab.com/eternaltwin/mush/mush/-/raw/develop/clone_and_install.ps1?ref_type=heads" -OutFile "clone_and_install.ps1"
.\clone_and_install.ps1
```

If you encounter any issue (very likely), refer to legacy installation instructions [here](https://gitlab.com/eternaltwin/mush/mush/-/wikis/Legacy-Windows-Install-Instructions) and ask for help on [Discord](https://discord.com/channels/693082011484684348/746873392463872071).

#### Ubuntu

Run `curl -sSL https://gitlab.com/eternaltwin/mush/mush/-/raw/develop/clone_and_install.sh?ref_type=heads | bash` in your terminal.

### Post-installation

If everything went well you should be able to access:
  - Swagger API documentation : http://localhost:8080/swagger/
  - eMush front end : http://localhost

Use the following credentials to login (all users - named by eMush characters - have the same password):
```
username : chun
password : 1234567891
```

With Docker install, you should land in a fully working Daedalus!

With non-Docker install, run `cd Api && composer fill-daedalus` to fill a Daedalus with players.

## Contributing

- If not done yet, generate a SSH key and add it to your GitLab profile :
  - Generate the key : `ssh-keygen -t rsa -b 2048 -C "SSH Key for eMush repository (https://gitlab.com/eternaltwin/mush/mush)"`
  - Display the key : `cat ~/.ssh/id_rsa.pub`
  - Copy the key and add it to your GitLab profile here : https://gitlab.com/-/user_settings/ssh_keys/

- Then use SSH remote to be able to push to the repository : `git remote set-url origin git@gitlab.com:eternaltwin/mush/mush.git` ;

- Please read [CONTRIBUTING.md](./CONTRIBUTING.md) for details on our code of conduct, and the process for submitting pull requests to us ;

- When your first Merge Request is ready, ask access to the repository by sending us your GitLab username on [Discord](https://discord.com/channels/693082011484684348/746873392463872071) :)

## Endpoints
A swagger is available that list all the available endpoints and their specifications : [Swagger](http://localhost:8080/swagger/)

To authenticate, at the moment, use the login endpoint and set the access_token returned in the swagger header to use the other endpoints

## Gitlab
This project use gitlab ci to check the merge requests

### Gitlab docker php images
This image is used for the php environment validation

The dockerfile: [Dockerfile](./docker/gitlab/Php/Dockerfile)

#### Update the container in gitlab

```
docker login registry.gitlab.com -u YOUR_USERNAME -p ACCESSS_TOKEN
docker build -t registry.gitlab.com/eternaltwin/mush/mush/api ./docker/gitlab/Php/
docker push registry.gitlab.com/eternaltwin/mush/mush/api
```
Username can be found at: https://gitlab.com/-/profile under Full Name

Access Token can be created at: https://gitlab.com/-/profile/personal_access_tokens
(you can also connect with password by using `docker login registry.gitlab.com -u YOUR_USERNAME` then entering your password)

## Troubleshooting

### Eternaltwin login / website not working (Ports are not available: listen tcp 0.0.0.0:50320: bind : An attempt was made to access a socket in a way forbidden by its access permissions).

Open Powershell as an administrator and run the following commands :
```powershell
netsh int ipv4 set dynamic tcp start=60536 num=5000
netsh int ipv6 set dynamic tcp start=60536 num=5000
```
Restart your computer, then try to run `make docker-start` again.

### ERROR: for mush-front  Cannot start service mush-front: driver failed programming external connectivity on endpoint mush-front : Error starting userland proxy: listen tcp4 0.0.0.0:80: bind: address already in use 

You need to stop the Apache instance running on port 80. For example on Ubuntu, you can run the following command: `sudo systemctl stop apache2`.

If you don't have use of this Apache server, you can disable it by running `sudo systemctl disable apache2` to avoid having to stop it manually each time you want to run the project.

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

## License

The source code of this project is licensed under [AGPL-3.0-or-later](LICENSE) License. 

> You are free to:
> - Use — run, study, and privately modify the software.
> - Share — copy and redistribute the material in any medium or format.
> - Modify — remix, transform, and build upon the material.
> 
> Under the following terms:
> 
> - Source Code Provision — You must provide access to the source code of the software when you distribute it, including any modifications or derivative works.
> - License and Copyright Notice — You must include the original copyright notice and license in any copy of the software or substantial portion of it.
> - State Changes — You must clearly mark any changes you made to the original software.
> - ShareAlike — If you modify and distribute the software, you must license the entire work under the AGPLv3 or a compatible license.
> - Network Use is Distribution — Users who interact with the software via network have the right to receive the source code.
> - No Additional Restrictions — You may not apply legal terms or technological measures that legally restrict others from doing anything the license permits.

All Motion Twin and eMush assets in [App/src/assets](App/src/assets) are licensed under [CC-BY-NC-SA-4.0](App/src/assets/LICENSE) License.

> You are free to: 
> * Share — copy and redistribute the material in any medium or format. 
> * Adapt — remix, transform, and build upon the material. 
> * The licensor cannot revoke these freedoms as long as you follow the license terms.
> 
> Under the following terms: 
> 
> * Attribution — You must give appropriate credit, provide a link to the license, and indicate if changes were made. You may do so in any reasonable manner, but not in any way that suggests the licensor endorses you or your use. 
> * NonCommercial — You may not use the material for commercial purposes. 
> * ShareAlike — If you remix, transform, or build upon the material, you must distribute your contributions under the same license as the original. 
> * No additional restrictions — You may not apply legal terms or technological measures that legally restrict others from doing anything the license permits.
> [Creative Commons](https://creativecommons.org/licenses/by-nc-sa/4.0/)

