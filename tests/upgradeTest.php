<?php declare(strict_types=1);
require('settings.php');

require(dirname(__DIR__).'/upgrade.php');

use PHPUnit\Framework\TestCase;

final class upgradeTest extends TestCase {
  public function testUpgradeLibraryComplete(): void {
    $this->assertTrue(function_exists('XLtrace\Hades\backup'));
    $this->assertTrue(function_exists('XLtrace\Hades\restore'));
    $this->assertTrue(function_exists('XLtrace\Hades\patch'));
    $this->assertTrue(function_exists('XLtrace\Hades\slaves_file'));
    $this->assertTrue(function_exists('XLtrace\Hades\run_slaves'));
    $this->assertTrue(function_exists('XLtrace\Hades\build_url'));
    $this->assertTrue(function_exists('XLtrace\Hades\current_URI'));
    $this->assertTrue(function_exists('XLtrace\Hades\file_get_json'));
    $this->assertTrue(function_exists('XLtrace\Hades\file_put_json'));
    $this->assertTrue(function_exists('XLtrace\Hades\composer'));
    $this->assertTrue(function_exists('XLtrace\Hades\touch'));
    $this->assertTrue(function_exists('XLtrace\Hades\upgrade_json'));
    $this->assertTrue(function_exists('XLtrace\Hades\upgrade'));
    $this->assertTrue(function_exists('XLtrace\Hades\pcl'));
  }
  public function testSlaves_file(): void {
    $this->assertTrue(is_string(\XLtrace\Hades\slaves_file()));
  }
  public function testBuild_url(): void {
    $url = 'http://localhost/some/file.php';
    $this->assertSame($url, \XLtrace\Hades\build_url(parse_url($url)));
  }
  public function testFile_get_json(): void {
    $file = dirname(__DIR__).'/composer.json';
    $this->assertSame(json_decode(file_get_contents($file), TRUE), \XLtrace\Hades\file_get_json($file, TRUE, array()));
  }
  public function testComposer(): void {
    $this->assertTrue(TRUE);
  }
  public function testTouch(): void {
    $this->assertTrue(TRUE);
  }
  public function testUpgrade(): void {
    $this->assertTrue(TRUE);
  }
  public function testUpgrade_json(): void {
    $this->assertSame(\XLtrace\Hades\upgrade_json(NULL, FALSE), \XLtrace\Hades\upgrade_json('upgrade.json', TRUE));
    $data = array();
    $this->assertSame(\XLtrace\Hades\upgrade_json($data, FALSE), $data);
  }
}
?>
