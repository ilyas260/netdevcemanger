#!/bin/bash
echo "Starting 5 queue workers for parallel scanning..."
php artisan queue:work --queue=scan,default --sleep=3 --tries=3 --timeout=600 &
php artisan queue:work --queue=scan,default --sleep=3 --tries=3 --timeout=600 &
php artisan queue:work --queue=scan,default --sleep=3 --tries=3 --timeout=600 &
php artisan queue:work --queue=scan,default --sleep=3 --tries=3 --timeout=600 &
php artisan queue:work --queue=scan,default --sleep=3 --tries=3 --timeout=600 &
wait -n
