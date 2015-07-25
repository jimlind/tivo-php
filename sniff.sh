vendor/bin/phpcs src --standard=vendor/escapestudios/symfony2-coding-standard/Symfony2/ruleset.xml -p --colors
vendor/bin/phpcs tests --standard=vendor/escapestudios/symfony2-coding-standard/Symfony2/ruleset.xml -p --colors

vendor/bin/phpcs src --standard=PSR2 -p --colors
vendor/bin/phpcs tests --standard=PSR2 -p --colors