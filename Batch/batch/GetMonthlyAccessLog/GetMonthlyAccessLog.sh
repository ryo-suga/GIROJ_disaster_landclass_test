#!/bin/sh

cd /var/www/web/batch/www/root/php-work/batch/GetMonthlyAccessLog
last_month=`date -d '1 month ago' +'%Y%m'`
php GetMonthlyAccessLog.php $last_month
