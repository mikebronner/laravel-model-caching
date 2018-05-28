#!/bin/bash

sed '/    printerClass="Codedungeon\\PHPUnitPrettyResultPrinter\\Printer"/d' phpunit.xml > phpunit-54.xml
composer remove codedungeon/phpunit-result-printer --no-update --dev
composer require "orchestra/database:3.4.*" --no-update --dev
composer require "orchestra/testbench:3.4.*" --no-update --dev
composer require "orchestra/testbench-browser-kit:3.4.*" --no-update --dev
composer require "illuminate/cache:5.4.*" --no-update
composer require "illuminate/config:5.4.*" --no-update
composer require "illuminate/console:5.4.*" --no-update
composer require "illuminate/database:5.4.*" --no-update
composer require "illuminate/support:5.4.*" --no-update
composer require "phpunit/phpunit:5.7.*" --no-update --dev
composer update --prefer-source --no-interaction

mkdir -p ./build/logs
vendor/bin/phpunit --configuration phpunit-54.xml --coverage-text --coverage-clover ./build/logs/clover.xml
