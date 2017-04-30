<?php
class CacheTest extends PHPUnit_Framework_TestCase {

  public function testCreateFromConfig() {
    p3k\Cache::redis('tcp://127.0.0.1:6379');
    p3k\Cache::set('foo', 'bar');
    $this->assertEquals('bar', p3k\Cache::get('foo'));
    p3k\Cache::reset();
  }

  public function testAutoCreate() {
    p3k\Cache::set('foo', 'bar');
    $this->assertEquals('bar', p3k\Cache::get('foo'));
    p3k\Cache::reset();
  }

  public function testSet() {
    p3k\Cache::set('foo', 'bar', 0);
    $this->assertEquals('bar', p3k\Cache::get('foo'));
    $redis = p3k\Cache::redis();
    $this->assertEquals(-1, $redis->ttl('foo'));
  }

  public function testSetEx() {
    p3k\Cache::set('foo', 'bar', 600);
    $this->assertEquals('bar', p3k\Cache::get('foo'));
    $redis = p3k\Cache::redis();
    $this->assertGreaterThan(500, $redis->ttl('foo'));
  }

  public function testGetExpired() {
    p3k\Cache::set('foo', 'bar', 1);
    usleep(1100000);
    $this->assertEquals('default', p3k\Cache::get('foo', 'default'));
  }

  public function testDelete() {
    p3k\Cache::set('foo', 'bar', 600);
    $this->assertEquals('bar', p3k\Cache::get('foo'));
    p3k\Cache::delete('foo');
    $this->assertEquals('default', p3k\Cache::get('foo', 'default'));
  }

  public function testExpire() {
    p3k\Cache::set('foo', 'bar', 600);
    $this->assertEquals('bar', p3k\Cache::get('foo'));
    p3k\Cache::expire('foo');
    $this->assertEquals('default', p3k\Cache::get('foo', 'default'));

    p3k\Cache::set('foo', 'bar', 600);
    $this->assertEquals('bar', p3k\Cache::get('foo'));
    p3k\Cache::expire('foo', 1);
    usleep(1100000);
    $this->assertEquals('default', p3k\Cache::get('foo', 'default'));
  }

  public function testIncr() {
    p3k\Cache::delete('test1');
    p3k\Cache::incr('test1');
    $this->assertEquals(1, p3k\Cache::get('test1'));

    p3k\Cache::set('test2', 10);
    p3k\Cache::incr('test2');
    $this->assertEquals(11, p3k\Cache::get('test2'));

    p3k\Cache::set('test3', 10);
    p3k\Cache::incr('test3', 4);
    $this->assertEquals(14, p3k\Cache::get('test3'));
  }

  public function testDecr() {
    p3k\Cache::delete('test4');
    p3k\Cache::decr('test4');
    $this->assertEquals(-1, p3k\Cache::get('test4'));

    p3k\Cache::set('test5', 10);
    p3k\Cache::decr('test5');
    $this->assertEquals(9, p3k\Cache::get('test5'));

    p3k\Cache::set('test6', 10);
    p3k\Cache::decr('test6', 4);
    $this->assertEquals(6, p3k\Cache::get('test6'));
  }

}
