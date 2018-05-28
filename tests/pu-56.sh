#!/bin/bash

cp composer.json original-composer.json

composer require codedungeon/phpunit-result-printer --no-update --dev
composer require "orchestra/database:3.6.*" --no-update --dev
composer require "orchestra/testbench:3.6.*" --no-update --dev
composer require "orchestra/testbench-browser-kit:3.6.*" --no-update --dev
composer require "illuminate/cache:5.6.*" --no-update
composer require "illuminate/config:5.6.*" --no-update
composer require "illuminate/console:5.6.*" --no-update
composer require "illuminate/database:5.6.*" --no-update
composer require "illuminate/support:5.6.*" --no-update
composer require "phpunit/phpunit:7.*" --no-update --dev
composer update --prefer-source --no-interaction

rm composer.json
mv original-composer.json composer.json

mkdir -p ./build/logs
php -n vendor/bin/phpunit --configuration phpunit.xml --coverage-text --coverage-clover ./build/logs/clover.xml
