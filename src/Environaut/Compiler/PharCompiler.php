<?php

namespace Environaut\Compiler;

use Symfony\Component\Finder\Finder;

/**
 * This class compiles all necessary files of Environaut to an executable
 * environaut.phar file that may be executed directly.
 */
class PharCompiler
{
    public function create($phar_path = 'environaut.phar')
    {
        if (file_exists($phar_path))
        {
            unlink($phar_path);
        }

        $phar = new \Phar($phar_path, 0, 'environaut.phar');

        $phar->setSignatureAlgorithm(\Phar::SHA1);

        $phar->startBuffering();

        $root_dir = dirname(dirname(dirname(__DIR__)));

        // add environaut files
        $finder = new Finder();
        $finder->files()->name('*.php')->notName('PharCompiler.php')->in($root_dir . '/src');
        foreach ($finder as $file)
        {
            $phar->addFile($file->getRealPath(), 'src/' . $file->getRelativePathname());
        }

        // add vendor files
        $finder = new Finder();
        $finder->files()->name('*.php')->notPath('/Tests/')->in($root_dir . '/vendor');
        foreach ($finder as $file)
        {
            $phar->addFile($file->getRealPath(), 'vendor/' . $file->getRelativePathname());
        }

        // add composer vendor autoloading
        $phar->addFile($root_dir . '/vendor/autoload.php', 'vendor/autoload.php');
        $phar->addFile($root_dir . '/vendor/composer/autoload_namespaces.php', 'vendor/composer/autoload_namespaces.php');
        $phar->addFile($root_dir . '/vendor/composer/autoload_classmap.php', 'vendor/composer/autoload_classmap.php');
        $phar->addFile($root_dir . '/vendor/composer/autoload_real.php', 'vendor/composer/autoload_real.php');
        $phar->addFile($root_dir . '/vendor/composer/ClassLoader.php', 'vendor/composer/ClassLoader.php');

        // environaut executable
        $phar->addFile($root_dir . '/bin/environaut', 'bin/environaut');

        // add startup file
        $phar->setStub($this->getStub());

        $phar->stopBuffering();

        chmod($phar_path, 0755);
    }

    protected function getStub()
    {
        $stub = <<<'EOF'
#!/usr/bin/env php
<?php

try
{
    Phar::mapPhar('environaut.phar');
    require 'phar://environaut.phar/bin/environaut';
}
catch (PharException $e)
{
    echo $e->getTraceAsString();
    exit(1);
}

__HALT_COMPILER();
EOF;

        return $stub;
    }
}

