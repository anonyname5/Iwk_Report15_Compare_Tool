#!/bin/bash

echo "Testing connection from Nginx to PHP-FPM..."

# Enter Nginx container
docker exec -it iwk_finance_nginx sh -c '
echo "Testing network connectivity to PHP-FPM container..."
ping -c 3 app

echo "Testing TCP connection to PHP-FPM port..."
nc -zv app 9000

echo "Testing PHP execution via FastCGI..."
echo "<?php phpinfo(); ?>" > /var/www/html/public/test.php
curl -v localhost/test.php | head -n 20
'

echo "Testing connection from outside to Nginx..."
curl -v http://localhost/ 2>&1 | grep -i http

echo "Checking logs..."
echo "Nginx error log:"
docker exec -it iwk_finance_nginx tail -n 20 /var/log/nginx/error.log

echo "PHP-FPM logs:"
docker exec -it iwk_finance_app tail -n 20 /usr/local/var/log/php-fpm.log 2>/dev/null || echo "No PHP-FPM log found" 