web: vendor/bin/heroku-php-apache2 -i user.ini public/
worker: php artisan queue:restart && php artisan queue:work database --tries=3 && php artisan queue:listen database --tries=3