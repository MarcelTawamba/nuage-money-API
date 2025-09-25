release: php artisan migrate --force
web: vendor/bin/heroku-php-apache2 public/
worker: php artisan queue:work --tries=3
scheduler: php artisan schedule:run --no-interaction
