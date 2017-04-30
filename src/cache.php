<?php
namespace p3k;

class Cache {
  private static $redis;

  public static function redis($config=false) {
    if(empty(self::$redis)) {
      if($config) {
        self::$redis = new \Predis\Client($config);
      } else {
        self::$redis = new \Predis\Client('tcp://127.0.0.1:6379');
      }
    }
    return self::$redis;
  }

  public static function reset() {
    self::$redis = null;
  }

  public static function set($key, $value, $exp=600) {
    self::redis();
    if($exp) {
      self::$redis->setex($key, $exp, json_encode($value));
    } else {
      self::$redis->set($key, json_encode($value));
    }
  }

  public static function get($key, $default=null) {
    self::redis();
    $data = self::$redis->get($key);
    if($data) {
      return json_decode($data);
    } else {
      return $default;
    }
  }

  public static function delete($key) {
    self::redis();
    return self::$redis->del($key);
  }

  public static function expire($key, $seconds=0) {
    self::redis();
    if($seconds)
      return self::$redis->expire($key, $seconds);
    else
      return self::$redis->del($key);
  }

  public static function incr($key, $value=1) {
    self::redis();
    return self::$redis->incrby($key, $value);
  }

  public static function decr($key, $value=1) {
    self::redis();
    return self::$redis->decrby($key, $value);
  }

}

