<?php
class UtilsTest extends PHPUnit_Framework_TestCase {

  public function testRandomString() {
    $str1 = p3k\random_string(20);
    $this->assertEquals(20, strlen($str1));
    $str2 = p3k\random_string(20);
    $this->assertEquals(20, strlen($str2));
    $this->assertNotEquals($str1, $str2);
  }

  public function testEndsWith() {
    $this->assertFalse(p3k\str_ends_with('abcdefg', ''));
    $this->assertFalse(p3k\str_ends_with('', 'abcdefg'));
    $this->assertTrue(p3k\str_ends_with('abcdefg', 'efg'));
    $this->assertTrue(p3k\str_ends_with('abcdefg', 'abcdefg'));
    $this->assertTrue(p3k\str_ends_with('abcdefg', 'g'));
    $this->assertFalse(p3k\str_ends_with('abcdefg', 'abc'));
  }

  /*
   * These are failing in php nightly with the error:
   * session_name(): Cannot change session name when headers already sent
   * despite not trying to change the session name, just read it.

  public function testSessionSetupNoCreate() {
    // no session already, so this should not create one
    p3k\session_setup();
    $this->assertFalse(isset($_SESSION));
  }

  public function testSessionSetupCreateFromCookie() {
    // there is a session cookie, so this should initialize the session
    $_COOKIE[session_name()] = '12345';
    p3k\session_setup();
    $this->assertTrue(isset($_SESSION));
  }
  */

  public function testSessionAccess() {
    $_SESSION = [];
    $this->assertNull(p3k\session('foo'));
    $_SESSION = [];
    $this->assertEquals('default', p3k\session('foo', 'default'));
    $_SESSION = ['foo'=>'bar'];
    $this->assertEquals('bar', p3k\session('foo'));
  }

  public function testFlash() {
    $_SESSION = [];
    $this->assertNull(p3k\flash('foo'));
    $_SESSION = ['foo'=>'bar'];
    $this->assertEquals('bar', p3k\flash('foo'));
    $this->assertNull(p3k\flash('foo'));
  }

  public function testE() {
    $html = p3k\e('<b>test</b>');
    $this->assertEquals('&lt;b&gt;test&lt;/b&gt;', $html);
  }

  public function testK() {
    $this->assertEquals('b', p3k\k(['a'=>'b'], 'a'));
    $this->assertEquals('default', p3k\k(['a'=>'b'], 'z', 'default'));
    $obj = new StdClass;
    $obj->a = 'b';
    $this->assertEquals('b', p3k\k($obj, 'a'));
    $this->assertEquals('default', p3k\k($obj, 'z', 'default'));

    $keys = ['a','b','c'];
    $values = ['a'=>true, 'b'=>true, 'c'=>true];
    $this->assertTrue(p3k\k($values, $keys));

    $keys = ['a','b','c'];
    $values = ['a'=>true, 'c'=>true];
    $this->assertFalse(p3k\k($values, $keys));
  }

  public function testHTTPHeaderCase() {
    $name = p3k\http_header_case('header-name');
    $this->assertEquals('Header-Name', $name);
    $name = p3k\http_header_case('HEADER-NAME');
    $this->assertEquals('Header-Name', $name);
    $name = p3k\http_header_case('hEaDeR-nAmE');
    $this->assertEquals('Header-Name', $name);
    $name = p3k\http_header_case('host');
    $this->assertEquals('Host', $name);
    $name = p3k\http_header_case('x-header-name');
    $this->assertEquals('X-Header-Name', $name);
  }

  public function testHTMLToDomDocument() {
    $doc = p3k\html_to_dom_document('<html><head><title>Title</title></head><body>Hello World</body></html>');
    $this->assertEquals('DOMDocument', get_class($doc));
    $this->assertEmpty(libxml_get_errors());
    $doc = p3k\html_to_dom_document("\0this is not HTML");
    $this->assertEquals('DOMDocument', get_class($doc));
    $this->assertEmpty(libxml_get_errors());
  }

  public function testXMLToDomDocument() {
    $doc = p3k\xml_to_dom_document('<html><head><title>Title</title></head><body>Hello World</body></html>');
    $this->assertEquals('DOMDocument', get_class($doc));
    $this->assertEmpty(libxml_get_errors());
    $doc = p3k\xml_to_dom_document('<html><title>Title</title></head><body>Hello World</html>');
    $this->assertEquals('DOMDocument', get_class($doc));
    $this->assertEmpty(libxml_get_errors());
    $doc = p3k\xml_to_dom_document("\0this is not XML");
    $this->assertEquals('DOMDocument', get_class($doc));
    $this->assertEmpty(libxml_get_errors());
  }

  public function testBase60() {
    $this->assertEquals('BBBB', p3k\b10to60(p3k\b60to10('BBBB')));
    $this->assertEquals('ABCD_efg', p3k\b10to60(p3k\b60to10('ABCD_efg')));
    $this->assertEquals('Z111000', p3k\b10to60(p3k\b60to10('ZIl1O0O')));
    $this->assertEquals('0', p3k\b10to60(p3k\b60to10(',<.')));
  }

  public function testCreatesRedis() {
    p3k\redis();
    $this->assertEquals('Predis\Client', get_class(p3k\redis()));
  }

  public function testBuildQuery() {
    $params = ['foo'=>['bar','baz']];
    $body = p3k\http_build_query($params);
    $this->assertEquals('foo%5B%5D=bar&foo%5B%5D=baz', $body);

    $params = ['a','b','c'];
    $body = p3k\http_build_query($params);
    $this->assertEquals('0=a&1=b&2=c', $body);

    $params = ['a'=>'A','b'=>'B'];
    $body = p3k\http_build_query($params);
    $this->assertEquals('a=A&b=B', $body);
  }

}
