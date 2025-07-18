# diam-wolf
鑽石人狼伺服器 原始碼公開版本<br />
安裝難度有點高不提供任何技術支援<br />
<br />
data.sql<br />
資料庫<br />
<br />
announcement.txt<br />
公告檔案，可隨意編輯<br />
<br />
setting.php<br />
//資料庫使用者名稱<br />
$db_uname = '';<br />

//資料庫使用者密碼<br />
$db_pass = '';<br />

//資料庫名稱<br />
$db_name = '';<br />

//管理密碼<br />
$system_password = '';<br />

//網域<br />
$domain_name = "diam.ngct.net";<br />

//key 16bit for 傳遞用<br />
$authkey = '1qaz2wsx3edc4rfv';<br />

socket/websockets.php<br />
這兩個改成自己的SSL憑證絕對路徑<br />
'ssl_cert_file' => "fullchain.pem",<br />
'ssl_key_file'  => "privkey.pem",<br />
然後用PHP讓他掛背景跑，這是WebSocket用的<br />
不設定會無法自動更新<br />
<br />
<br />
設定網站socket目錄不准許讀取不然很危險<br />
以下範例(nginx)<br />
location ^~ /socket/ {<br />
	rewrite ^ https://diam.ngct.net permanent;<br />
}<br />
<br />
設定proxy到8000(WebSocket)<br />
不設定會無法自動更新<br />
以下範例(nginx)<br />
location /wss/ {<br />
	access_log off;<br />
	proxy_pass http://0.0.0.0:8000;<br />
	X-Real-IP $remote_addr;<br />
	proxy_set_header Host $host;<br />
	proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;<br />
	proxy_http_version 1.1;<br />
	proxy_set_header Upgrade $http_upgrade;<br />
	proxy_set_header Connection "Upgrade";<br />
	proxy_connect_timeout 2s;<br />
	proxy_read_timeout 1d;<br />
	fastcgi_param SCRIPT_NAME "";<br />
}<br />
