#!/bin/bash
echo "Starting 2 queue workers for parallel scanning (to respect DB max connections)..."
php artisan queue:work --queue=scan,default --sleep=3 --tries=3 --timeout=600 &
php artisan queue:work --queue=scan,default --sleep=3 --tries=3 --timeout=600 &
wait -n
