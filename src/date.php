<?php
namespace p3k\date;

use DateTime, DateTimeZone;

// $format - one of the php.net/date format strings
// $date - a string that will be passed to DateTime()
// $offset - integer timezone offset in seconds
function format_local($format, $date, $offset) {
  if($offset != 0)
    $tz = new DateTimeZone(($offset < 0 ? '-' : '+') . sprintf('%02d:%02d', abs(floor($offset / 60 / 60)), (($offset / 60) % 60)));
  else
    $tz = new DateTimeZone('UTC');
  $d = new DateTime($date);
  $d->setTimeZone($tz);
  return $d->format($format);
}

function tz_offset_to_seconds($offset) {
  if(preg_match('/([+-])(\d{2}):?(\d{2})/', $offset, $match)) {
    $sign = ($match[1] == '-' ? -1 : 1);
    return (($match[2] * 60 * 60) + ($match[3] * 60)) * $sign;
  } else {
    return 0;
  }
}

function tz_seconds_to_offset($seconds) {
  return ($seconds < 0 ? '-' : '+') . sprintf('%02d:%02d', abs($seconds/60/60), ($seconds/60)%60);
}

function tz_seconds_to_timezone($seconds) {
  if($seconds != 0)
    $tz = new DateTimeZone(tz_seconds_to_offset($seconds));
  else
    $tz = new DateTimeZone('UTC');
  return $tz;
}

