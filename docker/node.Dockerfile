FROM node:latest

WORKDIR /srv/app

COPY package*.json ./

RUN npm install

COPY . .

CMD ["sh"]
