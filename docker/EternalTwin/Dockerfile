FROM --platform=amd64 node:16

WORKDIR /www

COPY ./package.json ./yarn.lock ./etwin.toml ./

RUN yarn install --pure-lockfile

EXPOSE 50320

# switch to npm if you chose it as package manager
CMD ["yarn", "etwin"]
