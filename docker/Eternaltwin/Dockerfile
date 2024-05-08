FROM --platform=linux/amd64 node:20-slim

ARG UID=1000
ARG GID=1000

WORKDIR /www

RUN groupmod -g $GID node && usermod -u $UID -g $GID node

USER node

# switch to npm if you chose it as package manager
CMD ["yarn", "etwin", "start"]