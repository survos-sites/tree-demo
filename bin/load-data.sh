#dbname=jstree
#echo "drop database if exists $dbname; create database $dbname; grant all privileges on database $dbname to main; " | sudo -u postgres psql
#echo "database $dbname now ready for migrations or restore"

#bin/console doctrine:query:sql "drop table if exists doctrine_migration_versions"
#bin/console doctrine:d:drop --force --if-exists
#bin/console doctrine:d:create

#bin/console make:migration
#git clean -f migrations && bin/console doctrine:migrations:migrate -n --allow-no-migration && bin/console make:migration

#bin/console doctrine:migrations:migrate -n

#git clean -f migrations/V*.php
#bin/console doctrine:migrations:migrate -n --allow-no-migration

#bin/console doctrine:schema:update --dump-sql --force
#bin/console make:migration
#bin/console doctrine:migrations:migrate -n
bin/console doctrine:fixtures:load -n
bin/console app:load-directory-files

bin/console app:import-topics

#bin/create-admins.sh


