#!/usr/bin/env php
<?php
require_once(__DIR__.'/../functions.php');

$server = new Swoole\WebSocket\Server("0.0.0.0", 8443, SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL);
$server->set([
    'reactor_num' => 24,
    'worker_num' => 12,
    'max_request' => 10000,
    'ssl_cert_file' => "fullchain.pem",
    'ssl_key_file'  => "privkey.pem",
    'ssl_protocols' => SWOOLE_SSL_TLSv1_2 | SWOOLE_SSL_TLSv1_3 | SWOOLE_SSL_TLSv1_1 | SWOOLE_SSL_SSLv2,
    'log_level' => SWOOLE_LOG_ERROR,
    'user' => 'www-data',
    'group' => 'www-data',
]);

$table = new Swoole\Table(1024);
$table->column('fd', Swoole\Table::TYPE_INT);
$table->column('room_no', Swoole\Table::TYPE_INT);
$table->column('gameuserid', Swoole\Table::TYPE_INT);
$table->create();
$server->table = $table;

$server->on('open', function (Swoole\WebSocket\Server $_server, $request): void {
	global $server;
	[$room_no, $gameuserid] = explode("\t", authcode(str_replace("/wss/","",(string) $request->server['request_uri']), 'DECODE'));
    $server->room_no = $room_no;
    $server->table->set($request->fd, ['fd' => $request->fd, 'room_no' => $room_no, 'gameuserid' => $gameuserid]);
});

$server->on('message', function (Swoole\WebSocket\Server $_server, $frame): void {
	global $server;
	
	foreach ($server->table as $key => $value) {
		if (!$server->isEstablished($value["fd"])) {
			$server->table->del($value["fd"]);
        }
    }
	
	foreach ($server->table as $value) {
	 	 if ($value["room_no"] == $server->room_no) {
			$server->push($value["fd"], "Last updated: ".gmdate("H:i:s",time()+3600*8));
         }
	}
});

$server->on('close', function ($_server, $fd): void {
});

$server->start();
?>
