dbname=jstree

#heroku pg:reset --confirm jufj-scorecard  && echo "CREATE EXTENSION pg_trgm; CREATE EXTENSION \"uuid-ossp\";  CREATE EXTENSION hstore;" | heroku pg:psql
#echo "create user main WITH ENCRYPTED PASSWORD 'main';" | sudo -u postgres psql

echo "drop database if exists $dbname; create database $dbname; grant all privileges on database $dbname to main; " | sudo -u postgres psql
echo "database $dbname now ready for migrations or restore"

#git clean -f migrations/app/V*.php
#bin/console doctrine:migrations:diff
#bin/console doctrine:migrations:migrate

bin/console doctrine:schema:update --dump-sql --force
#bin/console make:migration
#bin/console doctrine:migrations:migrate -n
bin/console doctrine:fixtures:load -n

bin/console app:import-topics

bin/create-admins.sh


