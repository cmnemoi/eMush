FROM --platform=amd64 node:16-slim

WORKDIR /www

ARG UID=1001
ARG GID=1001

RUN groupmod -g $GID node && usermod -u $UID -g $GID node

COPY --chown=node:node ./package.json ./yarn.lock ./etwin.toml ./

RUN yarn install --pure-lockfile

EXPOSE 50320

# switch to npm if you chose it as package manager
USER node
CMD ["yarn", "etwin"]
