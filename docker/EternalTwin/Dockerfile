FROM node:14

RUN apt-get -y update \
	&& apt-get install -y git

WORKDIR /www

# switch to npm if you chose it as package manager
CMD ["yarn", "etwin"]