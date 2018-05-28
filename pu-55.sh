#!/bin/bash

cp composer.json original-composer.json

sed '/    printerClass="Codedungeon\\PHPUnitPrettyResultPrinter\\Printer"/d' phpunit.xml > phpunit-55.xml
composer remove codedungeon/phpunit-result-printer --no-update --dev
composer require "orchestra/database:3.5.*" --no-update --dev
composer require "orchestra/testbench:3.5.*" --no-update --dev
composer require "orchestra/testbench-browser-kit:3.5.*" --no-update --dev
composer require "illuminate/cache:5.5.*" --no-update
composer require "illuminate/config:5.5.*" --no-update
composer require "illuminate/console:5.5.*" --no-update
composer require "illuminate/database:5.5.*" --no-update
composer require "illuminate/support:5.5.*" --no-update
composer require "phpunit/phpunit:6.*" --no-update --dev
composer update --prefer-source --no-interaction

rm composer.json
mv original-composer.json composer.json

mkdir -p ./build/logs
vendor/bin/phpunit --configuration phpunit-55.xml --coverage-text --coverage-clover ./build/logs/clover.xml
rm phpunit-55.xml
