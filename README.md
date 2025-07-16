# diam-wolf
鑽石人狼伺服器 原始碼公開版本
安裝難度有點高請不提供任何技術支援

announcement.txt
公告檔案，可隨意編輯

setting.php
//資料庫使用者名稱
$db_uname = '';

//資料庫使用者密碼
$db_pass = '';

//資料庫名稱
$db_name = '';

//管理密碼
$system_password = '';

//網域
$domain_name = "diam.ngct.net";

//key 16bit for 傳遞用
$authkey = '1qaz2wsx3edc4rfv';

socket/websockets.php
這兩個改成自己的SSL憑證絕對路徑
'ssl_cert_file' => "fullchain.pem",
'ssl_key_file'  => "privkey.pem",


設定網站socket目錄不准許讀取不然很危險
以下範例(nginx)
location ^~ /socket/ {
	rewrite ^ https://diam.ngct.net permanent;
}

設定proxy到8000(WebSocket)
不設定會無法自動更新
以下範例(nginx)
location /wss/ {
	access_log off;
	proxy_pass http://0.0.0.0:8000;
	X-Real-IP $remote_addr;
	proxy_set_header Host $host;
	proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
	proxy_http_version 1.1;
	proxy_set_header Upgrade $http_upgrade;
	proxy_set_header Connection "Upgrade";
	proxy_connect_timeout 2s;
	proxy_read_timeout 1d;
	fastcgi_param SCRIPT_NAME "";
}
