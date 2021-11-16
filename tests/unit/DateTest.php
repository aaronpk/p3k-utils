<?php
class DateTest extends PHPUnit_Framework_TestCase {

  public function testFormatLocalPositiveOffset() {
    $local = p3k\date\format_local('c', '2017-05-01T13:30:00+0000', 7200);
    $this->assertEquals('2017-05-01T15:30:00+02:00', $local);
  }

  public function testFormatLocalNegativeOffset() {
    $local = p3k\date\format_local('c', '2017-05-01T13:30:00+0000', -25200);
    $this->assertEquals('2017-05-01T06:30:00-07:00', $local);
  }

  public function testFormatLocalZeroOffset() {
    $local = p3k\date\format_local('c', '2017-05-01T13:30:00+0200', 0);
    $this->assertEquals('2017-05-01T11:30:00+00:00', $local);
  }

  public function testTZSecondsToTimezonePositive() {
    $tz = p3k\date\tz_seconds_to_timezone(7200);
    $this->assertInstanceOf(DateTimeZone::class, $tz);
    $this->assertEquals('+02:00', $tz->getName());
  }

  public function testTZSecondsToTimezoneNegative() {
    $tz = p3k\date\tz_seconds_to_timezone(-25200);
    $this->assertInstanceOf(DateTimeZone::class, $tz);
    $this->assertEquals('-07:00', $tz->getName());
  }

  public function testTZSecondsToTimezoneZero() {
    $tz = p3k\date\tz_seconds_to_timezone(0);
    $this->assertInstanceOf(DateTimeZone::class, $tz);
    $this->assertEquals('UTC', $tz->getName());
  }

  public function testTZOffsetToSecondsPositive() {
    $seconds = p3k\date\tz_offset_to_seconds('+02:00');
    $this->assertEquals(7200, $seconds);
    $seconds = p3k\date\tz_offset_to_seconds('+0200');
    $this->assertEquals(7200, $seconds);
  }

  public function testTZOffsetToSecondsNegative() {
    $seconds = p3k\date\tz_offset_to_seconds('-07:00');
    $this->assertEquals(-25200, $seconds);
    $seconds = p3k\date\tz_offset_to_seconds('-0700');
    $this->assertEquals(-25200, $seconds);
  }
  
  public function testTZOffsetToSecondsZero() {
    $seconds = p3k\date\tz_offset_to_seconds('+00:00');
    $this->assertEquals(0, $seconds);
    $seconds = p3k\date\tz_offset_to_seconds('+0000');
    $this->assertEquals(0, $seconds);
  }

  public function testTZOffsetToSecondsInvalid() {
    $seconds = p3k\date\tz_offset_to_seconds('foo');
    $this->assertEquals(0, $seconds);
  }

  public function testTZSecondsToOffsetPositive() {
    $offset = p3k\date\tz_seconds_to_offset(7200);
    $this->assertEquals('+02:00', $offset);
  }

  public function testTZSecondsToOffsetNegative() {
    $offset = p3k\date\tz_seconds_to_offset(-25200);
    $this->assertEquals('-07:00', $offset);
  }

  public function testTZSecondsToOffsetZero() {
    $offset = p3k\date\tz_seconds_to_offset(0);
    $this->assertEquals('+00:00', $offset);
  }

}
