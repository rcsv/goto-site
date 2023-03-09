FROM phpswoole/swoole


WORKDIR /var/www/app/
CMD ["php", "/var/www/app/server.php"]
