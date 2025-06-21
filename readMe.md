# Norsk

#### Application to learn norsk.

*Copyright: Sven Duge, 07/2019*

## Development:

### initial:

##### Database:
- start Database-Container
- log into mysql with root in container using credentials from `docker/config/database.*.env`
- import schema: `database/schemes/norsk_database.sql`
- run in container:
```
  CREATE USER IF NOT EXISTS 'norsk'@'%' IDENTIFIED BY '123test456';
  GRANT ALL PRIVILEGES ON myDatabase.* TO 'norsk'@'%';
  FLUSH PRIVILEGES;
  ```

##### Client:

for a blank projekt:

- vite: initial set up in container
  ```npm create vite@latest client -- --template vue-ts``` [https://dev.to/ysmnikhil/how-to-build-with-react-or-vue-with-vite-and-docker-1a3l]
- vite.config.ts: add port and host!
- router: npm install vue-router@4

```
npx vite --help
```

#### further:

```make vinit``` to init the client

```make vite``` for vue client-set-up to test directly in the browser

```make vunit``` to run the tests with coverage

##### API

```make dev``` for php set-up for api

There are other make commands to run reports, static code analyses or tests. Look at the make file with `make list`.

Composer if necessary:

```make ci``` for composer install

```make cu``` for composer update

```make ca``` for composer dump-autoload

##### API + Client

```make stage``` for starting staging container for api and client, but with empty database

```make stageDb``` for starting staging container for api and client, but with example data in database

---

#### Local url for api-testing (e.g. postman):

- Dev: `localhost:9999/api/v1/train/verbs`
- Staging: `localhost:9997/api/v1/train/verbs`

---

#### JWT + Auth

- https://jwt.io => create token: adding key and date on right side; base64 decoded key + checkbox unchecked
- the client sends jwt to API for user-login. API sends personal-user token back. The client uses that personal token to
  train or manage words and verbs for certain user:
  - Client Login/Registration => send username + password
  - => api convert username + password to hash + salt + config-pepper => compares with database mit bcrypt
  - => api send back token to the client
  - client requests with token; token holds username + expiration 2 hours
  - => api validates username and validity of token

---

### Deployment

Deploy to webspace:

#### Build + Deploy

- import database schema
- set variables in client/.env/production
- make jsbuild
- deploy client to server => client/dist
- deploy api to server
  - run `composer-install --no-dev`
  - move/rename configs without prd

---

### Backup

Backup after every manual editing and update in git:

*docker exec CONTAINER /usr/bin/mysqldump -u root --password=root -r DATABASE > /some/path/on/your/host/backup.sql*

```
docker exec mysql-norsk /usr/bin/mysqldump norsk -u root --password=>MYSQL_ROOT_PASSWORD< > ./backup/norsk.sql
```

----
