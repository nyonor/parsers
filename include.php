<?php
/* ********************************************************** */
ini_set("display_errors", true);
error_reporting(E_ALL);
/* ********************************************************** */

#define('DR', $_SERVER['DOCUMENT_ROOT']);
define('DR', '/srv/www/4tochki.ru/preview/html');
define('RW', DR.'/../rw');
define('RESULTS_FILE', RW.'/rivals/'.date('Ymd').'.log');

function debug($var = false, $showHtml = false, $showFrom = true) {
	if ($showFrom) {
			$calledFrom = debug_backtrace();
			echo '<strong>' . substr($calledFrom[0]['file'], 1) . '</strong>';
			echo ' (line <strong>' . $calledFrom[0]['line'] . '</strong>)';
	}
	echo "\n<pre class=\"debug\">\n";

	$var = print_r($var, true);
	if ($showHtml) {
		$var = str_replace('<', '&lt;', str_replace('>', '&gt;', $var));
	}
	echo $var . "\n</pre>\n\n";
}

function res_write($site, $res, $count, $date){
	//echo "$site $res $count $date\n";
	file_put_contents(RESULTS_FILE, "$site $res $count $date\n", FILE_APPEND);
}

// $DB_HOST = "4tochki.mysql.4tochki.ru"; // ����� ���� ������
// $DB_USER = "4tochki"; // ��� ������������ ��� ����������� � ����
// $DB_PASS = "S$3k*s(e"; // ������
//$DB_NAME = "4tochki"; // ��� ���� ������
/*$DB_HOST = "wr.4tochki.mysql.pwrs.ru"; // ����� ���� ������
$DB_USER = "harchenko_an"; // ��� ������������ ��� ����������� � ����
$DB_PASS = "Hs72_W@k"; // ������
$DB_NAME = "4tochki";*/ // ��� ���� ������
$DB_HOST = "10.211.55.6"; // ����� ���� ������
$DB_USER = "mysqlUser"; // ��� ������������ ��� ����������� � ����
$DB_PASS = "iddqd"; // ������
$DB_NAME = "4tochki"; // ��� ���� ������
/*var mysqlUser = 'mysqlUser';
var mysqlPassword = 'iddqd';
var mysqlHost = '10.211.55.6';
var mysqlPort = 3306;
var mysqlDatabase = '4tochki';*/
  
$db=mysql_connect($DB_HOST,$DB_USER,$DB_PASS);
mysql_select_db($DB_NAME,$db);

// �������� ������ �� �������������� � ������ DunlopJP, producers_data.id = 83
function is_it_dunlopJP($model) {
	$model = strtolower($model);
//	$query = "
//	SELECT pd.id as producer
//	FROM aliases
//	LEFT JOIN 4t ON (4t.id = aliases.id_4t)
//	LEFT JOIN tyres_data as td ON (td.model = 4t.model)
//	LEFT JOIN producers_data as pd ON (pd.id = td.producer)
//	WHERE aliases.alias = '$model'
//	OR td.model = '$model'
//	GROUP BY company
//	";
	$query = "
	SELECT td.producer
	FROM tyres_data td
	LEFT JOIN tyres_sizes ts ON (ts.`tyre_id` = td.id) 
	LEFT JOIN 4t ON (4t.`cae` = ts.cae) 
	LEFT JOIN aliases al ON (al.`id_4t` = 4t.id)
	WHERE al.alias = '$model' OR td.model = '$model'
	GROUP BY td.`producer`
	";
	$query_sql = mysql_query($query);
	if (mysql_num_rows($query_sql) == 1) {
		if (mysql_result($query_sql, 0, 'producer') == '83') $return = 1;
		elseif (mysql_result($query_sql, 0, 'producer') == '43') $return = 0;
	}
	elseif (mysql_num_rows($query_sql) == 2) $return = 2;
	else $return = 0;
	return $return;
}

// �������� ������ �� �������������� � ������ DunlopJP, producers_data.id = 83
function is_it_Nordman($model) {
	$model = strtolower($model);
	$query = "
	SELECT td.producer
	FROM tyres_data td
	LEFT JOIN tyres_sizes ts ON (ts.`tyre_id` = td.id) 
	LEFT JOIN 4t ON (4t.`cae` = ts.cae) 
	RIGHT JOIN aliases al ON (al.`id_4t` = 4t.id)
	WHERE al.alias = '$model' OR td.model = '$model'
	GROUP BY td.`producer`
	";
	// echo $query;
	$query_sql = mysql_query($query);
	if (mysql_num_rows($query_sql) == 1) {
		if (mysql_result($query_sql, 0, 'producer') == '81') $return = 1;
		elseif (mysql_result($query_sql, 0, 'producer') == '40') $return = 0;
	}
	elseif (mysql_num_rows($query_sql) == 2) $return = 2;
	else $return = 0;
	return $return;
}

// ������� IDs ������� ��� �� ����� ����������� �� ������ ��������
function failId($activeId, $site) {
	$query = "
	SELECT id
	FROM tyres
	WHERE site = '$site'
	";
	$query_sql = mysql_query($query);
	$array = array();
	while ($arr = mysql_fetch_assoc($query_sql)) {
		$array[] = $arr["id"];
	}
	$fail = array_diff($array, $activeId);
	if ($fail) {
		$fails = implode(",", $fail);
		$query = "
		DELETE FROM tyres
		WHERE id IN ($fails)
		";
		$query_sql = mysql_query($query);
	}
	return $fail;
}


set_time_limit(36000);

if(!file_exists(RESULTS_FILE)) {
	//$ourFileHandle = fopen(RESULTS_FILE, 'w') or die("can't open file");
	//fclose($ourFileHandle);
}

?>