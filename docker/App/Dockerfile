FROM node:18

# not mandatory if you EXPORT a fixed port. See below.
ARG YOUR_APP_WEB_HTTP_PORT
ARG UID=1000
ARG GID=1000

WORKDIR /www

# Install git
RUN apt-get -y update \
	&& apt-get install -y git \
    && apt-get autoremove -y \
    && apt-get autoclean -y \
    && apt-get clean -y \
    && rm -rf /var/lib/apt/lists/*  \
    # Setup a non-root user
    && groupmod -g $GID node \
    && usermod -u $UID -g $GID node

# Or just use EXPORT 8080
EXPOSE ${YOUR_APP_WEB_HTTP_PORT}

USER node

# switch to npm if you chose it as package manager
CMD ["yarn", "serve"]