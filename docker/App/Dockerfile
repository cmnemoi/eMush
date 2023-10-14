FROM node:18

# not mandatory if you EXPORT a fixed port. See below.
ARG YOUR_APP_WEB_HTTP_PORT
ARG UID=1000
ARG GID=1000

RUN apt-get -y update \
	&& apt-get install -y git

WORKDIR /www

RUN apt-get autoremove -y \
    && apt-get autoclean -y \
    && apt-get clean -y \
    && rm -rf /var/lib/apt/lists/*

# Or just use EXPORT 8080
EXPOSE ${YOUR_APP_WEB_HTTP_PORT}
# If yout want use vue-cli UI you need to also EXPORT 8000

RUN groupmod -g $GID node && usermod -u $UID -g $GID node

USER node

# switch to npm if you chose it as package manager
CMD ["yarn", "serve"]