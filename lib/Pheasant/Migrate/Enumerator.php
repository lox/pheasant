<?php

namespace Pheasant\Migrate;

/**
 * Enumerate all domain objects in a PHP source tree
 */
class Enumerator implements \IteratorAggregate
{
    private $_dir;

    /**
     * Constructor
     */
    public function __construct($dir)
    {
        $this->_dir = $dir;
    }

    /**
     * Finds classes that extend \Pheasant\DomainObject
     */
    private function _getDomainObjectsFromFile($file)
    {
        $result = array();

        if (fnmatch("*.php", $file)) {
            foreach ($this->_getClassFiles($file) as $namespace=>$classes) {
                foreach ($classes as $class) {
                    $fullClass = empty($namespace) ? "\\$class" : "\\$namespace\\$class";

                    $reflection = new \ReflectionClass($fullClass);
                    if (is_a($fullClass, "\Pheasant\DomainObject", true) && $reflection->isInstantiable()) {
                        $result []= $fullClass;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Parses PHP and gets Namespaces and Classes
     * @see http://stackoverflow.com/questions/928928/determining-what-classes-are-defined-in-a-php-class-file
     */
    private function _getClassFiles($file)
    {
        $classes = array();
        $namespace = 0;
        $tokens = token_get_all(file_get_contents($file));
        $count = count($tokens);
        $dlm = false;
        for ($i = 2; $i < $count; $i++) {
            if ((isset($tokens[$i - 2][1]) && ($tokens[$i - 2][1] == "phpnamespace" || $tokens[$i - 2][1] == "namespace")) ||
                ($dlm && $tokens[$i - 1][0] == T_NS_SEPARATOR && $tokens[$i][0] == T_STRING)) {
                    if (!$dlm) $namespace = 0;
                    if (isset($tokens[$i][1])) {
                        $namespace = $namespace ? $namespace . "\\" . $tokens[$i][1] : $tokens[$i][1];
                        $dlm = true;
                    }
                } elseif ($dlm && ($tokens[$i][0] != T_NS_SEPARATOR) && ($tokens[$i][0] != T_STRING)) {
                $dlm = false;
            }
            if (($tokens[$i - 2][0] == T_CLASS || (isset($tokens[$i - 2][1]) && $tokens[$i - 2][1] == "phpclass"))
                && $tokens[$i - 1][0] == T_WHITESPACE && $tokens[$i][0] == T_STRING) {
                    $class_name = $tokens[$i][1];
                    if (!isset($classes[$namespace])) $classes[$namespace] = array();
                    $classes[$namespace][] = $class_name;
                }
        }

        return $classes;

    }

    public function getIterator()
    {
        $flags = \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS;
        $iterator = new \RecursiveDirectoryIterator($this->_dir, $flags);
        $iterator = new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::SELF_FIRST);
        $classes = array();

        foreach ($iterator as $file) {
            if ($file->isFile() && ($result = $this->_getDomainObjectsFromFile($file->getRealPath()))) {
                $classes = array_merge($classes, $result);
            }
        }

        return new \ArrayIterator($classes);
    }
}
