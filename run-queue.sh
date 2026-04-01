pm2 start queue/queue-worker.sh --name laravel-queue-pharma
phan-quyen.sh Modules/Website/database/Seeders/menu.json
echo "
    Câu lệnh quản lý pm2 \n
    pm2 start queue-worker.sh	Khởi động \n
    hoặc chạy nền: ./pm2queue.sh \n
    pm2 stop laravel-queue	Dừng \n
    pm2 restart laravel-queue	Khởi động lại \n
    pm2 delete laravel-queue	Xóa tiến trình \n
    pm2 logs laravel-queue	Xem log \n
    pm2 flush laravel-queue // xóa các logs \n
"
