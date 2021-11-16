<?php
class URLTest extends PHPUnit_Framework_TestCase {

  public function testDisplayURL() {
    $url = p3k\url\display_url('http://example.com');
    $this->assertEquals('example.com', $url);
    $url = p3k\url\display_url('http://example.com/');
    $this->assertEquals('example.com', $url);
    $url = p3k\url\display_url('example.com');
    $this->assertEquals('example.com', $url);
    $url = p3k\url\display_url('http://example.com/foo');
    $this->assertEquals('example.com/foo', $url);
    $url = p3k\url\display_url('http://example.com/foo/');
    $this->assertEquals('example.com/foo/', $url);
    $url = p3k\url\display_url('http://www.example.com/foo/');
    $this->assertEquals('example.com/foo/', $url);
    $url = p3k\url\display_url('https://www.example.com/foo/');
    $this->assertEquals('example.com/foo/', $url);
    $url = p3k\url\display_url('https://example.www.example.com/foo/');
    $this->assertEquals('example.www.example.com/foo/', $url);
  }

  public function testAddQueryParamsToURLNoExistingParams() {
    $url = p3k\url\add_query_params_to_url('http://example.com', ['q'=>1]);
    $this->assertEquals('http://example.com?q=1', $url);
    $url = p3k\url\add_query_params_to_url('http://example.com/', ['q'=>1]);
    $this->assertEquals('http://example.com/?q=1', $url);
    $url = p3k\url\add_query_params_to_url('http://example.com/foo', ['q'=>1]);
    $this->assertEquals('http://example.com/foo?q=1', $url);
    $url = p3k\url\add_query_params_to_url('http://example.com/foo#fragment', ['q'=>1]);
    $this->assertEquals('http://example.com/foo?q=1#fragment', $url);
  }

  public function testAddQueryParamsToURLWithExistingParams() {
    $url = p3k\url\add_query_params_to_url('http://example.com?a=b', ['q'=>1]);
    $this->assertEquals('http://example.com?a=b&q=1', $url);
    $url = p3k\url\add_query_params_to_url('http://example.com/?a=b', ['q'=>1]);
    $this->assertEquals('http://example.com/?a=b&q=1', $url);
    $url = p3k\url\add_query_params_to_url('http://example.com/foo?a=b', ['q'=>1]);
    $this->assertEquals('http://example.com/foo?a=b&q=1', $url);
    $url = p3k\url\add_query_params_to_url('http://example.com/foo?a=b#fragment', ['q'=>1]);
    $this->assertEquals('http://example.com/foo?a=b&q=1#fragment', $url);
  }

  public function testStripTrackingParams() {
    $url = p3k\url\strip_tracking_params('http://example.com/');
    $this->assertEquals('http://example.com/', $url);
    $url = p3k\url\strip_tracking_params('http://example.com/?utm_source=foo');
    $this->assertEquals('http://example.com/', $url);
    $url = p3k\url\strip_tracking_params('http://example.com/?foo=bar');
    $this->assertEquals('http://example.com/?foo=bar', $url);
    $url = p3k\url\strip_tracking_params('http://example.com/?foo=bar&utm_source=froogle');
    $this->assertEquals('http://example.com/?foo=bar', $url);
  }

  public function testNormalizeURL() {
    $url = p3k\url\normalize('http://example.com/');
    $this->assertEquals('http://example.com/', $url);
    $url = p3k\url\normalize('http://example.com');
    $this->assertEquals('http://example.com/', $url);
    $url = p3k\url\normalize('example.com');
    $this->assertEquals('http://example.com/', $url);
    $url = p3k\url\normalize('mailto:user@example.com');
    $this->assertEquals(false, $url);
  }

  public function testBuildURL() {
    $parts = p3k\url\build_url(parse_url('http://example.com'));
    $this->assertEquals('http://example.com', $parts);
    $parts = p3k\url\build_url(parse_url('http://example.com/'));
    $this->assertEquals('http://example.com/', $parts);
    $parts = p3k\url\build_url(parse_url('https://example.com/?'));
    $this->assertEquals('https://example.com/', $parts);
    $parts = p3k\url\build_url(parse_url('https://example.com/?foo=bar'));
    $this->assertEquals('https://example.com/?foo=bar', $parts);
    $parts = p3k\url\build_url(parse_url('https://example.com?foo=bar'));
    $this->assertEquals('https://example.com?foo=bar', $parts);
    $parts = p3k\url\build_url(parse_url('https://user:pass@example.com/?foo=bar'));
    $this->assertEquals('https://user:pass@example.com/?foo=bar', $parts);
    $parts = p3k\url\build_url(parse_url('https://user:pass@example.com:3000/?foo=bar#f'));
    $this->assertEquals('https://user:pass@example.com:3000/?foo=bar#f', $parts);
    $parts = p3k\url\build_url(parse_url('https://user@example.com/?foo=bar'));
    $this->assertEquals('https://user@example.com/?foo=bar', $parts);
    $parts = p3k\url\build_url(parse_url('https://user:@example.com/?foo=bar'));
    $this->assertEquals('https://user@example.com/?foo=bar', $parts);
  }

  public function testHostMatches() {
    $this->assertTrue(p3k\url\host_matches('http://example.com/', 'https://example.com/foo'));
    $this->assertFalse(p3k\url\host_matches('http://example.com/', 'https://subdomain.example.com/foo'));
  }

  public function testIsURL() {
    $this->assertTrue(p3k\url\is_url('http://example.com/'));
    $this->assertTrue(p3k\url\is_url('http://example'));
    $this->assertTrue(p3k\url\is_url('https://example.com/foo?a=b#f'));
    $this->assertFalse(p3k\url\is_url('mailto:user@example.com'));
    $this->assertFalse(p3k\url\is_url('geo:45.5,-122.6'));
  }

  public function testIsPublicIP() {
    $this->assertTrue(p3k\url\is_public_ip('45.1.200.42'));
    $this->assertFalse(p3k\url\is_public_ip('192.168.200.1'));
    $this->assertFalse(p3k\url\is_public_ip('127.0.0.1'));
    $this->assertFalse(p3k\url\is_public_ip('0.10.0.0'));
    $this->assertFalse(p3k\url\is_public_ip('10.10.0.0'));
  }

  public function testGeoToLatLng() {
    $coords = p3k\url\geo_to_latlng('geo:45.521296,-122.626412');
    $this->assertEquals(['latitude'=>45.521296, 'longitude'=>-122.626412], $coords);
    $coords = p3k\url\geo_to_latlng('geo:45.521296,-122.626412;u=35');
    $this->assertEquals(['latitude'=>45.521296, 'longitude'=>-122.626412], $coords);
    $coords = p3k\url\geo_to_latlng('http://example.com/');
    $this->assertEquals(false, $coords);
  }

}
