################### list ###################
list: 			# display this list
	@cat Makefile \
		| grep "^[a-z0-9_]\+:" \
		| sed -r "s/:[^#]*?#?(.*)?/\r\t\t\t\t-\1/" \
		| sed "s/^/ • make /" \
		| sort

################### Colors ###################

GREEN=\033[0;32m
RED=\033[0;31m
BLUE=\033[0;34m
END_COLORING=\033[0m

################### Variables ###################

BIN=api/vendor/bin/
MND=$(BIN)phpmnd
STAN=$(BIN)phpstan
BEHAT=$(BIN)behat
PHP=$(BIN)php
UNIT=$(BIN)phpunit
METRICS=$(BIN)phpmetrics
CBF=$(BIN)phpcbf
REC=$(BIN)rector
INFECTION=$(BIN)infection

SRC=./src
TESTS=./tests

DOCKER_EXEC_TOOLS_APP=docker exec -it $(CONTAINER_NAME) sh
NODE_INSTALL="npm i"
SERVER_RUN="npm run dev"

###### set ENV ######

ENV ?= dev

ifeq ($(ENV),dev)
    COMPOSE_FILE := docker-compose.yml
    PORT := 8000
    MODE := development
else ifeq ($(ENV),staging)
    COMPOSE_FILE := docker-compose.staging.yml
    PORT := 8001
    MODE := staging
endif

################## docker client dev ##################

CONTAINER_NAME=norsk-client-$(ENV)


vbuild:		# build vue
	docker compose build norsk-client
	@echo "$(GREEN)Done building vue-project by vite$(END_COLORING)"

vinstall:	# vue install
	$(DOCKER_EXEC_TOOLS_APP) -c $(NODE_INSTALL)
	@echo "$(GREEN)Done installing vue-project by vite$(END_COLORING)"

vup:  		# start vite container with correct API host by ENV
	docker compose -f $(COMPOSE_FILE) up -d norsk-client
	@echo "$(GREEN)Starting vite container for $(ENV)$(END_COLORING)"

vrun:		# run vite development or staging server by ENV
	docker exec $(CONTAINER_NAME) sh -c "npm run dev -- --mode=$(MODE)"
	@echo "$(GREEN)Vite is running at $(PORT)$(END_COLORING)"

vinit:		# init vue-project by vite
	make vbuild
	make vinstall
	make vrun

vite: 		# build and up vue-project by vite
	make vup
	make vrun

jsbuild: 	# build javascript by vite
	make vup
	$(DOCKER_EXEC_TOOLS_APP) -c "npx vue-tsc && npx vite build"

vunit: 		# run unit tests and coverage
	make vup
	$(DOCKER_EXEC_TOOLS_APP)  -c "npm run test:coverage"
	@echo "$(GREEN)finished unit tests$(END_COLORING)"


##################### Php ######################

################### Composer ###################

ci:			# composer install dev|staging
	CMD=install docker compose -f $(COMPOSE_FILE) up composer
	@echo "$(GREEN)Done starting for $(COMPOSE_FILE) composer install$(END_COLORING)"

cu:			# composer  update dev|staging
	CMD=update docker compose -f $(COMPOSE_FILE) up composer
	@echo "$(GREEN)Done starting for $(COMPOSE_FILE) composer update$(END_COLORING)"

ca:			# composer dump-autoload dev|staging
	CMD=dump-autoload docker compose -f $(COMPOSE_FILE) up composer
	@echo "$(GREEN)Done starting for $(COMPOSE_FILE) composer dump-autoload$(END_COLORING)"

################### Dev-Work ###################

dev:		# checking git working and up-to-date, container and tests working
	git status
	git pull origin
	make cu
	make php
	make test
	@echo "$(GREEN)You're all set and ready to dev$(END_COLORING)"

################## Php helper ###################
behat:			# run behat alone
	docker exec php8-norsk-dev $(BEHAT) --config tools/behat.yml -n --colors
	@echo "$(GREEN)Done bdd$(END_COLORING)"

bdd:			# prepare and run behat
	make pup
	make behat

bdd-add:		# add behat cases
	@echo "$(CONTEXT)"
	@echo "or do in container: vendor/bin/behat --snippets-for=$(CONTEXT) --append-snippets + press Context-Number"
	docker exec php8-norsk-dev sh -c "$(BEHAT) --config=./tools/behat.yml --snippets-for=$(CONTEXT) --append-snippets"

analyse:		# mnd + stan
	docker exec php8-norsk-dev $(MND) ./api/src --progress --extensions=default_parameter,-return,argument
	docker exec php8-norsk-dev $(STAN) analyse -c ./tools/phpstan.neon --memory-limit 1G
	@echo "$(GREEN)Done static analysis$(END_COLORING)"

mutation:		# infection
	docker exec php8-norsk-dev $(INFECTION) --configuration=tools/infection.json5 -s --only-covered
	@echo "$(GREEN)Done mutation testing$(END_COLORING)"

report:			# phpMetrics report to e.g. http://localhost:63342/norsk/tools/reports/29-11-23-09-59-53/index.html
	docker exec php8-norsk-dev $(METRICS) --report-html=./tools/reports/`date +'%d-%m-%y-%H-%M-%S'` ./
	@echo "$(GREEN)Done metrics report$(END_COLORING)"

cs:				# sniffer + beautifier
	docker exec php8-norsk-dev $(CBF) --standard=./tools/sniffs.xml ./api/src
	docker exec php8-norsk-dev $(CBF) --standard=./tools/sniffs.xml ./api/tests
	@echo "$(GREEN)Done cleaning$(END_COLORING)"

rector-dr: 		# rector dry-run
	docker exec php8-norsk-dev $(REC) process --dry-run --config ./tools/rector.php

rector:			# process rector
	docker exec php8-norsk-dev $(REC) process --config ./tools/rector.php


################## docker dev ##################

pbu:			# building php container
	docker compose build php8-norsk
	@echo "$(GREEN)Done building php$(END_COLORING)"

pup:			# starting php container
	docker compose up php8-norsk -d
	@echo "$(GREEN)Done starting php$(END_COLORING)"

php:			# build and start php and database container
	docker compose build
	docker compose up -d
	@echo "$(GREEN)Done building and starting php and database$(END_COLORING)"

docDev:			# build container for development
	make php
	make ci

test:			# run unit & system tests
	make docDev
	docker exec php8-norsk-dev $(UNIT) --configuration=./tools/phpunit.xml --testsuite "Api UnitTest Suite","Api SystemTest Suite"

tests:			# run all tests: unit & system tests, bdd, mutation
	make test
	make behat
	make mutation

################## docker staging API ##################

stage:			# building and starting staging php + db container
	docker compose -f $(COMPOSE_FILE) build
	docker compose -f $(COMPOSE_FILE) up -d
	make vite > vite.log 2>&1 & # start vite in background
	@echo "$(GREEN)Started environment: $(ENV) ($(COMPOSE_FILE)) => go to http://localhost:$(PORT)/ $(END_COLORING)"

stageDb:		# building and starting staging php + db container with importing db input
	make stage ENV=staging
	docker exec norsk-api-staging php /var/www/html/norsk/api/scripts/importStagingDb/importStagingDb.php
	@echo "$(GREEN)Done building and starting staging container with imported database $(END_COLORING)"
