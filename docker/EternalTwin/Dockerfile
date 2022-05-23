FROM node:16

#ARG UID=1000
#ARG GID=1000
#
#RUN groupmod -g $GID node && usermod -u $UID -g $GID node
USER node

#RUN apt-get -y update \
#	&& apt-get install -y git

#RUN mkdir -p /www
WORKDIR /www

COPY --chown=node:node ./package.json ./yarn.lock ./etwin.toml ./

RUN yarn install --pure-lockfile

EXPOSE 50320

# switch to npm if you chose it as package manager
CMD ["yarn", "etwin"]
