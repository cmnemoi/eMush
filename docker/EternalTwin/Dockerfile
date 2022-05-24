FROM node:16

USER node

WORKDIR /www

COPY --chown=node:node ./package.json ./yarn.lock ./etwin.toml ./

RUN yarn install --pure-lockfile

EXPOSE 50320

# switch to npm if you chose it as package manager
CMD ["yarn", "etwin"]
