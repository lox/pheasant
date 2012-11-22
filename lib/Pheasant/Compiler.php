<?php

namespace Pheasant;

/**
 * Compiles a pheasant.phar file for easy use
 */
class Compiler
{
  public function compile($pharFile='pheasant.phar')
  {
    $basedir = realpath(__DIR__.'/../../');

    if (file_exists($pharFile))
      unlink($pharFile);

    $phar = new \Phar($pharFile);
    $phar->setSignatureAlgorithm(\Phar::SHA1);
    $phar->startBuffering();

    foreach ($this->getFiles($basedir) as $file)
    {
      $content = file_get_contents($basedir . "/$file");
      $phar->addFromString($file, $content);
    }

    $stub = <<<ENDSTUB
<?php
Phar::mapPhar('pheasant.phar');
require_once('phar://pheasant.phar/autoload.php');
__HALT_COMPILER();
ENDSTUB;

    $phar->setStub($stub);
    $phar->stopBuffering();
  }

  private function getFiles($dir)
  {
    $files = array();
    $iterator = new \RecursiveIteratorIterator(
      new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
      \RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($iterator as $fileinfo)
      if($fileinfo->isFile())
        $files[] = substr($fileinfo->getRealPath(), strlen($dir)+1);

    return preg_grep('/^(LICENSE|lib|autoload.php)/', $files);
  }
}

