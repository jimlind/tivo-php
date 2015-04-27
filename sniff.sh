vendor/bin/phpcs src --standard=vendor/leaphub/phpcs-symfony2-standard/leaphub/phpcs/Symfony2/ruleset.xml -p --colors
vendor/bin/phpcs tests --standard=vendor/leaphub/phpcs-symfony2-standard/leaphub/phpcs/Symfony2/ruleset.xml -p --colors

vendor/bin/phpcs src --standard=PSR2 -p --colors
vendor/bin/phpcs tests --standard=PSR2 -p --colors