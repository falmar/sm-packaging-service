### init
- `printf "UID=$(id -u)\nGID=$(id -g)" > .env`
- `printf "BINPACK_USERNAME=YOUR_USERNAME\nBINPACK_API_KEY=YOUR_API_KEY" >> .env`
- `docker-compose up -d`
- `docker-compose run shipmonk-packing-app bash`
- `composer install && php bin/doctrine orm:schema-tool:create && php bin/doctrine dbal:run-sql "$(cat data/packaging-data.sql)"`

### run
- `php run.php "$(cat sample.json)"`

### adminer
- Open `http://localhost:8080/?server=mysql&username=root&db=packing`
- Password: secret
