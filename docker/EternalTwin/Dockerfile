FROM node:18

ARG UID=1000
ARG GID=1000

RUN apt-get -y update \
	&& apt-get install -y git

WORKDIR /www

RUN groupmod -g $GID node && usermod -u $UID -g $GID node

USER node

# switch to npm if you chose it as package manager
CMD ["yarn", "etwin"]