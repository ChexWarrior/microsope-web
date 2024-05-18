# Microsope Web

## Local Setup
This project runs with [DDEV](https://ddev.readthedocs.io/en/stable/) locally, install this first.

Run these steps to setup the local environment for the firs time:
1. Start the DDEV environment with `ddev start`.
2. Install packages with `ddev composer install`.
3. Create the database: `ddev php bin/console doctrine:database:create`.
4. Run the latest migration: `ddev php bin/console doctrine:migrations:migrate`.
5. Run a fixture for initial data: `ddev php bin/console doctrine:fixtures:load --group=main`.

## Tests
Run the following commands to create the test database:
```bash
ddev php bin/console  --env=test doctrine:database:create
ddev php bin/console  --env=test doctrine:schema:create
```

To run the tests: `ddev php ./bin/phpunit`