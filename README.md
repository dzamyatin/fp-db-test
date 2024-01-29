#Requirements:
1) docker compose v2
2) make (optional)

#How to run test with make:
> make test

#How to run test an alternative way:
```
docker compose -f ./docker-compose.yml up -d --remove-orphans
```

```
docker exec -ti dz-test-php sh -c 'echo "\n" && php test.php && echo "\n"'
```
