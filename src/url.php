<?php
namespace p3k\url;

function display_url($url) {
  # remove scheme and www.
  $url = preg_replace('/^https?:\/\/(www\.)?/', '', $url);
  # if the remaining string has no path components but has a trailing slash, remove the trailing slash
  $url = preg_replace('/^([^\/]+)\/$/', '$1', $url);
  return $url;
}

function add_query_params_to_url($url, $add_params) {
  $parts = parse_url($url);
  if(array_key_exists('query', $parts) && $parts['query']) {
    parse_str($parts['query'], $params);
  } else {
    $params = [];
  }

  foreach($add_params as $k=>$v) {
    $params[$k] = $v;
  }

  $parts['query'] = http_build_query($params);

  return build_url($parts);
}

function strip_tracking_params($url) {
  $parts = parse_url($url);
  
  if(!array_key_exists('query', $parts))
    return $url;

  parse_str($parts['query'], $params);

  $new_params = [];

  foreach($params as $key=>$val) {
    if(substr($key, 0, 4) != 'utm_')
      $new_params[$key] = $val;
  }

  $parts['query'] = http_build_query($new_params);

  return build_url($parts);
}

// Input: Any URL or string like "aaronparecki.com"
// Output: Normalized URL (default to http if no scheme, force "/" path)
//         or return false if not a valid URL
function normalize($url) {
  $parts = parse_url($url);

  if(array_key_exists('path', $parts) && $parts['path'] == '')
    return false;

  // parse_url returns just "path" for naked domains
  if(count($parts) == 1 && array_key_exists('path', $parts)) {
    $parts['host'] = $parts['path'];
    unset($parts['path']);
  }

  if(!array_key_exists('scheme', $parts))
    $parts['scheme'] = 'http';

  if(!array_key_exists('path', $parts))
    $parts['path'] = '/';

  // Invalid scheme
  if(!in_array($parts['scheme'], array('http','https')))
    return false;

  return build_url($parts);
}

// Inverse of parse_url()
// http://php.net/parse_url
function build_url($parsed_url) {
  $scheme   = !empty($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
  $host     = !empty($parsed_url['host']) ? $parsed_url['host'] : '';
  $port     = !empty($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
  $user     = !empty($parsed_url['user']) ? $parsed_url['user'] : '';
  $pass     = !empty($parsed_url['pass']) ? ':' . $parsed_url['pass']  : '';
  $pass     = ($user || $pass) ? "$pass@" : '';
  $path     = !empty($parsed_url['path']) ? $parsed_url['path'] : '';
  $query    = !empty($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
  $fragment = !empty($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
  return "$scheme$user$pass$host$port$path$query$fragment";
}

function host_matches($a, $b) {
  return parse_url($a, PHP_URL_HOST) == parse_url($b, PHP_URL_HOST);
}

function is_url($url) {
  return is_string($url) && preg_match('/^https?:\/\/[a-z0-9\.\-]\/?/', $url);
}

function is_public_ip($ip) {
  // http://stackoverflow.com/a/30143143

  //Private ranges...
  //http://www.iana.org/assignments/iana-ipv4-special-registry/
  $networks = array('10.0.0.0'        =>  '255.0.0.0',        //LAN.
                    '172.16.0.0'      =>  '255.240.0.0',      //LAN.
                    '192.168.0.0'     =>  '255.255.0.0',      //LAN.
                    '127.0.0.0'       =>  '255.0.0.0',        //Loopback.
                    '169.254.0.0'     =>  '255.255.0.0',      //Link-local.
                    '100.64.0.0'      =>  '255.192.0.0',      //Carrier.
                    '192.0.2.0'       =>  '255.255.255.0',    //Testing.
                    '198.18.0.0'      =>  '255.254.0.0',      //Testing.
                    '198.51.100.0'    =>  '255.255.255.0',    //Testing.
                    '203.0.113.0'     =>  '255.255.255.0',    //Testing.
                    '192.0.0.0'       =>  '255.255.255.0',    //Reserved.
                    '224.0.0.0'       =>  '224.0.0.0',        //Reserved.
                    '0.0.0.0'         =>  '255.0.0.0');       //Reserved.

  $ip = @inet_pton($ip);
  if (strlen($ip) !== 4) { return false; }

  //Is the IP in a private range?
  foreach($networks as $network_address => $network_mask) {
    $network_address   = inet_pton($network_address);
    $network_mask      = inet_pton($network_mask);
    assert(strlen($network_address)    === 4);
    assert(strlen($network_mask)       === 4);
    if (($ip & $network_mask) === $network_address)
      return false;
  }

  return true;
}

function geo_to_latlng($uri) {
  if(preg_match('/geo:([\-\+]?[0-9\.]+),([\-\+]?[0-9\.]+)/', $uri, $match)) {
    return array(
      'latitude' => (double)$match[1],
      'longitude' => (double)$match[2],
    );
  } else {
    return false;
  }
}

