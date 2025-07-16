<?php
require_once __DIR__ . '/setting.php';

if (isset($_REQUEST['GLOBALS'])) {
	exit;
}

foreach(['_COOKIE', '_POST', '_GET'] as $_request) {
	foreach(${$_request} as $_key => $_value) {
		if ($_request == "_POST" && !strstr((string) $_SERVER['HTTP_REFERER'],(string) $_SERVER['SERVER_NAME'])) {
			exit("You can't post");
		}
		$_value = is_null($_value) ? '' : $_value;
		if ($_key[0] != '_') {
      ${$_key} = $_value;
  }
	}
}

$memcachein = 1;

function tripping($trip): string {
	$trip = substr(base64_encode(sha1((string) $trip)),1,8);
	return substr(crypt($trip, $trip), -10);
}

function namenohtml($name): string {
	$name = str_replace("&","&amp;",(string) $name);
	$name = str_replace("\t"," ",$name);
	$name = str_replace("<","&lt;",$name);
	$name = str_replace(">","&gt;",$name);
	return str_replace("　","_",$name);
}

//--------------------------------------------------------------------------------------------------
//資料庫連線
$db = new mysqlidb;
$db->connect($db_host, $db_uname, $db_pass, $db_name);

class mysqlidb {
	public $querynum = 0;
	public $conn;
	function connect($dbhost, $dbuser, $dbpw, $dbname = ''): void {
		if(!@$this->conn = mysqli_connect($dbhost, $dbuser, $dbpw,$dbname)) {
			echo 'Can not connect to MySQL server';
			exit;
		}
		mysqli_set_charset($this->conn,'utf8mb4');
	}

	function query($query, $type = ''): bool|\mysqli_result {
		$func = $type == 'UNBUFFERED' && @function_exists('mysqli_unbuffered_query') ?
						 'mysqli_unbuffered_query' : 'mysqli_query';
		if(!($query = $func($this->conn, $query)) && $type != 'SILENT') {
			return false;
		}
		$this->querynum++;
		return $query;
	}

	function num_rows($query) {
		return mysqli_num_rows($query);
	}

	function fetch_array($query, $result_type = MYSQLI_ASSOC): null|array|false {
		if ($query == false) {
			return NULL;
		} else {
			return mysqli_fetch_array($query, $result_type);
		}
	}

	function fetch_assoc($query): array|false|null {
		return mysqli_fetch_assoc($query);
	}

	function fetch_row($query): array|false|null {
		return mysqli_fetch_row($query);
	}

	function free_result($query) {
		if ($query == false) {
			return NULL;
		} else {
			return mysqli_free_result($query);
		}
	}

	function result($query,$a = '0',$b = '0') {
		if ($query == false) {
			return NULL;
		} else {
			$rows = [];
			while($row = $query->fetch_row()) {
				$rows[] = $row;
			}
			return $rows[$a][$b];
		}
	}

	function begin_transaction() {
		$isok = true;
		if (!mysqli_autocommit($this->conn,false)) {
			$isok = false;
		}
		if (!mysqli_begin_transaction($this->conn)) {
			$isok = false;
		}
		return $isok;
	}
	
	function commit() {
		$isok = true;
		if (!mysqli_commit($this->conn)) {
			$isok = false;
		}
		if (!mysqli_autocommit($this->conn,true)) {
			$isok = false;
		}
		return $isok;
	}
	
	function rollback() {
		$isok = true;
		if (!mysqli_rollback($this->conn)) {
			$isok = false;
		}
		if (!mysqli_autocommit($this->conn,true)) {
			$isok = false;
		}
		return $isok;
	}

	function insert_id() {
		return ($id = mysqli_insert_id($this->conn)) >= 0 ? $id : $this->result($this->query("SELECT last_insert_id()"), 0);
	}

	function connect_error(): string {
		return mysqli_error($this->conn);
	}

	function close(): bool {
		return mysqli_close($this->conn);
	}
}

//--------------------------------------------------------------------------------------------------
//SessionIDを新しくする(PHPのバージョンが古いとこの関数が無いので定義する)
if (!function_exists('session_regenerate_id'))
{
	function session_regenerate_id(): void
	{
		$QQ = serialize($_SESSION);
		session_destroy();
		session_id(md5(uniqid(mt_rand(),1)));
		session_start();
		$_SESSION = unserialize($QQ);
	}
}

//加密
function authcode($string, $operation, $key = ''): string {
	global $authkey;
	$key = md5((string) ($key ?: $authkey));
	$key_length = strlen($key);

	$string = $operation == 'DECODE' ? base64_decode((string) $string) : substr(md5($string.$key), 0, 8).$string;
	$string_length = strlen($string);

	$rndkey = $box = [];
	$result = '';

	for($i = 0; $i <= 255; $i++) {
		$rndkey[$i] = ord($key[$i % $key_length]);
		$box[$i] = $i;
	}

	for($j = $i = 0; $i < 256; $i++) {
		$j = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}

	for($a = $j = $i = 0; $i < $string_length; $i++) {
		$a = ($a + 1) % 256;
		$j = ($j + $box[$a]) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
	}

	if($operation == 'DECODE') {
		if(substr($result, 0, 8) == substr(md5(substr($result, 8).$key), 0, 8)) {
			return substr($result, 8);
		} else {
			return '';
		}
	} else {
		return str_replace('=', '', base64_encode($result));
	}
}

?>
