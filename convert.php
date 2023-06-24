<?php
array_change_key_case($_GET);
$format = 'json';
if (isset($_GET['format'])) {
	$format = $_GET['format'];
}
$src = '°F';
if (isset($_GET['src'])) {
	$src = $_GET['src'];
}
$dst = '°C';
if (isset($_GET['dst'])) {
	$dst = $_GET['dst'];
}
$val = '1';
if (isset($_GET['val'])) {
	$val = $_GET['val'];
}
function tofloat($num) {
	$dotPos = strrpos($num, '.');
	$commaPos = strrpos($num, ',');
	$sep = (($dotPos > $commaPos) && $dotPos) ? $dotPos :
		((($commaPos > $dotPos) && $commaPos) ? $commaPos : false);
	if (!$sep) {
		return floatval(preg_replace("/[^0-9]/", "", $num));
	}
	return floatval(
		preg_replace("/[^0-9]/", "", substr($num, 0, $sep)) . '.' .
		preg_replace("/[^0-9]/", "", substr($num, $sep+1, strlen($num)))
	);
}
function sanitizeDegreeType($degreeType) {
	$degreeType = strtoupper($degreeType);
	switch ($degreeType) {
		case 'F':
		case '°F'
			return '°F';
		case 'C':
		case '°C'
			return '°C';
		default:
			http_response_code(500);
			die('Failed to sanitize input types');
	}
}
function convert($from, $to, $value) {
	$from = sanitizeDegreeType($from);
	$to = sanitizeDegreeType($to);
	switch ($from) {
		case '°C':
			switch ($to) {
				case '°C':
					break; // Do nothing because it already is correct
				case '°F':
					$value = $value * 1.8 + 32;
					break;
			}
			break;
		case '°F':
			switch ($to) {
				case '°C':
					$value = ($value - 32) * 5/9;
					break;
				case '°F':
					break; // Do nothing because it already is correct
			}
			break;
	}
	return tofloat($value);
}
switch ($format) {
	case 'json':
		header("Content-Type: application/json");
		echo json_encode([
			'source' => sanitizeDegreeType($src),
			'destination' => sanitizeDegreeType($dst),
			'value' => convert($src, $dst, $val)
		]);
		break;
	case 'text':
		header("Content-Type: text/plain");
		echo "{$val} " . sanitizeDegreeType($src) . " = " . convert($src, $dst, $val) . " " . sanitizeDegreeType($dst);
		break;
	default:
		http_status_code(400);
		header("Content-Type: application/json");
		echo json_encode([
			'error' => 400,
			'message' => 'Invalid format'
		]);
		break;
}
