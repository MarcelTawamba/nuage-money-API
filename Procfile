release: composer heroku-release && php artisan banks:sync
web: vendor/bin/heroku-php-apache2 public/
worker: php artisan queue:work --tries=3
