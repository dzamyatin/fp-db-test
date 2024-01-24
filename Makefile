#!make
include .env

build:
	docker compose -f ./docker-compose.yml build
up:
	docker compose -f ./docker-compose.yml up -d --remove-orphans
down:
	docker compose -f ./docker-compose.yml down
test:
	@make build
	@make up
	docker exec -ti ${PROJECT_NAME}-php sh -c "php test.php"
