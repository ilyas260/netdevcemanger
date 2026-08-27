#!/bin/bash
echo "Starting Laravel Scheduler..."
php artisan schedule:work &

echo "Starting Laravel Queue Worker..."
php artisan queue:work --queue=scan,default --sleep=3 --tries=3 --timeout=600 &

# Wait for any process to exit
wait -n
  
# Exit with status of process that exited first
exit $?
