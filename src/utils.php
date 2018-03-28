<?php
namespace p3k;

use DOMDocument, IMagick, Exception;
use Config, ORM;

function redis() {
  static $client;
  if(empty($client))
    $client = new \Predis\Client(class_exists('Config') ? Config::$redis : 'tcp://127.0.0.1:6379');
  return $client;
}

function bs()
{
  static $pheanstalk;
  if(empty($pheanstalk)) {
    if(class_exists('Config'))
      $pheanstalk = new \Pheanstalk\Pheanstalk(Config::$beanstalkServer, Config::$beanstalkPort);
    else
      $pheanstalk = new \Pheanstalk\Pheanstalk('127.0.0.1', 11300);
  }
  return $pheanstalk;
}

function initdb() {
  ORM::configure('mysql:host=' . Config::$db['host'] . ';dbname=' . Config::$db['database']);
  ORM::configure('username', Config::$db['username']);
  ORM::configure('password', Config::$db['password']);
}

function e($text) {
  return htmlspecialchars($text);
}

function k($a, $k, $default=null) {
  if(is_array($k)) {
    $result = true;
    foreach($k as $key) {
      $result = $result && array_key_exists($key, $a);
    }
    return $result;
  } else {
    if(is_array($a) && array_key_exists($k, $a))
      return $a[$k];
    elseif(is_object($a) && property_exists($a, $k))
      return $a->$k;
    else
      return $default;
  }
}

function random_string($len) {
  $charset='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
  $str = '';
  $c = strlen($charset)-1;
  for($i=0; $i<$len; $i++) {
    $str .= $charset[mt_rand(0, $c)];
  }
  return $str;
}

// Returns true if $needle is the end of the $haystack
function str_ends_with($haystack, $needle) {
  if($needle == '' || $haystack == '') return false;
  return strpos(strrev($haystack), strrev($needle)) === 0;
}

// Sets up the session.
// If create is true, the session will be created even if there is no cookie yet.
// If create is false, the session will only be set up in PHP if they already have a session cookie.
function session_setup($create=false, $lifetime=2592000) {
  if($create || isset($_COOKIE[session_name()])) {
    session_set_cookie_params($lifetime);
    @session_start();
  }
}

function session($key, $default=null) {
  if(isset($_SESSION) && array_key_exists($key, $_SESSION))
    return $_SESSION[$key];
  else
    return $default;
}

function flash($key) {
  if(isset($_SESSION) && isset($_SESSION[$key])) {
    $value = $_SESSION[$key];
    unset($_SESSION[$key]);
    return $value;
  }
}

function http_header_case($str) {
  $str = str_replace('-', ' ', $str);
  $str = ucwords(strtolower($str));
  $str = str_replace(' ', '-', $str);
  return $str;
}

function http_build_query($params) {
  // PHP's built-in http_build_query function encodes arrays with numeric indexes,
  // like foo[0]=bar&foo[0]=baz
  // This function removes the numeric indexes so that it's conformant with Micropub
  return preg_replace('/%5B[0-9]+%5D/', '%5B%5D', \http_build_query($params));
}

function html_to_dom_document($html) {
  // Parse the source body as HTML
  $doc = new DOMDocument();
  libxml_use_internal_errors(true); # suppress parse errors and warnings
  $body = mb_convert_encoding($html, 'HTML-ENTITIES', mb_detect_encoding($html));
  @$doc->loadHTML($body, LIBXML_NOWARNING|LIBXML_NOERROR);
  libxml_clear_errors();
  return $doc;
}

function xml_to_dom_document($xml) {
  // Parse the source body as XML
  $doc = new DOMDocument();
  libxml_use_internal_errors(true); # suppress parse errors and warnings
  // $body = mb_convert_encoding($xml, 'HTML-ENTITIES', mb_detect_encoding($xml));
  $body = $xml;
  $doc->loadXML($body);
  libxml_clear_errors();
  return $doc;
}

// Reads the exif rotation data and actually rotates the photo.
// Only does anything if the exif library is loaded, otherwise is a noop.
function correct_photo_rotation($filename) {
  if(class_exists('IMagick')) {
    try {
      $image = new IMagick($filename);
      $orientation = $image->getImageOrientation();
      switch($orientation) {
        case IMagick::ORIENTATION_BOTTOMRIGHT:
          $image->rotateImage(new ImagickPixel('#00000000'), 180);
          break;
        case IMagick::ORIENTATION_RIGHTTOP:
          $image->rotateImage(new ImagickPixel('#00000000'), 90);
          break;
        case IMagick::ORIENTATION_LEFTBOTTOM:
          $image->rotateImage(new ImagickPixel('#00000000'), -90);
          break;
      }
      $image->setImageOrientation(IMagick::ORIENTATION_TOPLEFT);
      $image->writeImage($filename);
    } catch(Exception $e){}
  }
}

/**
 * Converts base 10 to base 60.
 * http://tantek.pbworks.com/NewBase60
 * @param int $n
 * @return string
 */
function b10to60($n)
{
  $s = "";
  $m = "0123456789ABCDEFGHJKLMNPQRSTUVWXYZ_abcdefghijkmnopqrstuvwxyz";
  if ($n==0)
    return 0;

  while ($n>0)
  {
    $d = $n % 60;
    $s = $m[$d] . $s;
    $n = ($n-$d)/60;
  }
  return $s;
}

/**
 * Converts base 60 to base 10, with error checking
 * http://tantek.pbworks.com/NewBase60
 * @param string $s
 * @return int
 */
function b60to10($s)
{
  $n = 0;
  for($i = 0; $i < strlen($s); $i++) // iterate from first to last char of $s
  {
    $c = ord($s[$i]); //  put current ASCII of char into $c
    if ($c>=48 && $c<=57) { $c=$c-48; }
    else if ($c>=65 && $c<=72) { $c-=55; }
    else if ($c==73 || $c==108) { $c=1; } // typo capital I, lowercase l to 1
    else if ($c>=74 && $c<=78) { $c-=56; }
    else if ($c==79) { $c=0; } // error correct typo capital O to 0
    else if ($c>=80 && $c<=90) { $c-=57; }
    else if ($c==95) { $c=34; } // underscore
    else if ($c>=97 && $c<=107) { $c-=62; }
    else if ($c>=109 && $c<=122) { $c-=63; }
    else { $c = 0; } // treat all other noise as 0
    $n = (60 * $n) + $c;
  }
  return $n;
}
