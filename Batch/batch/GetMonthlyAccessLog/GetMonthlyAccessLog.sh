#!/bin/sh

cd /var/www/landclass_batch/batch/GetMonthlyAccessLog
last_month=`date -d '1 month ago' +'%Y%m'`

php GetMonthlyAccessLog.php $last_month
