<?php
require_once('vendor/xkamui/saveurl.php');
$config = json_decode(file_get_contents('config.json'),false);
if ($config === null) die ('Ada kesalahan pada config');

if (!isset($_GET['feed'])) {
    echo "Welcome";
    die();
}
$feedParam = $_GET['feed'];
$feed = $config -> generate -> $feedParam;
if ($feed === null) die ('Generate gak ketemu');
//print_r($feed);

$url = $feed -> url;
$filename = $feed -> target;
echo "Generating " . $url . " to " . $filename . "<br>";

if (saveUrl($url,$filename)) {
    echo "Berhasil";
}
else {
    echo "Gagal"; 
}
?>