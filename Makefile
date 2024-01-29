#!make
include .env

build:
	docker compose -f ./docker-compose.yml build
up:
	docker compose -f ./docker-compose.yml up -d --remove-orphans
down:
	docker compose -f ./docker-compose.yml down
run:
	docker exec -ti ${PROJECT_NAME}-php sh -c 'echo "\n" && php test.php && echo "\n"'
test:
	@make build
	@make up
	@make run
