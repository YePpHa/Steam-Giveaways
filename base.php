<?php
/* Use openssl_random_pseudo_bytes if supported otherwise fallback to mt_rand */
function crypto_rand_secure($min, $max) {
  if (function_exists("openssl_random_pseudo_bytes")) {
    $range = 1 + $max - $min;
    if ($range <= 0) return $min; // not so random...
    $log = log($range, 2);
    $bytes = (int) ($log / 8) + 1; // length in bytes
    $bits = (int) $log + 1; // length in bits
    $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
    do {
      $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
      $rnd = $rnd & $filter; // discard irrelevant bits
    } while ($rnd >= $range);
    return $min + $rnd;
  } else {
    return mt_rand($min, $max);
  }
}

function getToken($length = 64) {
  $token = "";
  $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_";
  $charsLength = strlen($chars) - 1;
  for ($i = 0; $i < $length; $i++){
    $token .= $chars[crypto_rand_secure(0, $charsLength)];
  }
  return $token;
}

/* Config */
define("DB_HOST", "REDACTED");
define("DB_USER", "REDACTED");
define("DB_PASS", "REDACTED");
define("DB_DATABASE", "REDACTED");
define("RECAPTCHA_PUBLIC_KEY", "REDACTED");
define("RECAPTCHA_PRIVATE_KEY", "REDACTED");

/* Database */
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_DATABASE);

if($db->connect_errno > 0){
  die('Unable to connect to database [' . $db->connect_error . ']');
}
?>