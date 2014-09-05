<?php

namespace Environaut;

use Symfony\Component\Finder\Finder;

/**
 * This class compiles all necessary files of Environaut to an executable
 * environaut.phar file that may be executed directly (run standalone).
 */
class PharCompiler
{
    /**
     * Creates a PHP archive file with all files necessary to
     * run Environaut standalone.
     *
     * @param string $phar_path full path to the php archive file to create
     */
    public function create($phar_path = 'environaut.phar')
    {
        if (file_exists($phar_path)) {
            unlink($phar_path);
        }

        $phar = new \Phar($phar_path, 0, 'environaut.phar');

        $phar->setSignatureAlgorithm(\Phar::SHA1);

        $phar->startBuffering();

        $root_dir = dirname(dirname(__DIR__));

        // add environaut files
        $finder = new Finder();

        $finder->files()->notName('PharCompiler.php')->in($root_dir . '/src');

        foreach ($finder as $file) {
            $phar->addFile($file->getRealPath(), 'src/' . $file->getRelativePathname());
        }

        // add vendor files using a whitelist with excludes
        $finder = new Finder();
        $finder->files()
            ->name('*.php')
            ->name('security\.sensiolabs\.org\.crt')
            ->path('/symfony\/console\//')
            ->path('/sensiolabs\/security-checker\//')
            ->notName('SecurityCheckerCommand.php')
            ->notPath('/(\/Tests\/|\/Tester\/)/')
            ->in($root_dir . '/vendor');

        foreach ($finder as $file) {
            $phar->addFile($file->getRealPath(), 'vendor/' . $file->getRelativePathname());
        }

        // add composer vendor autoloading
        $vroot_dir = $root_dir . '/vendor/';
        $phar->addFile($vroot_dir . 'autoload.php', 'vendor/autoload.php');
        $phar->addFile($vroot_dir . 'composer/autoload_namespaces.php', 'vendor/composer/autoload_namespaces.php');
        $phar->addFile($vroot_dir . 'composer/autoload_classmap.php', 'vendor/composer/autoload_classmap.php');
        $phar->addFile($vroot_dir . 'composer/autoload_psr4.php', 'vendor/composer/autoload_psr4.php');
        $phar->addFile($vroot_dir . 'composer/autoload_real.php', 'vendor/composer/autoload_real.php');
        $phar->addFile($vroot_dir . 'composer/include_paths.php', 'vendor/composer/include_paths.php');
        $phar->addFile($vroot_dir . 'composer/ClassLoader.php', 'vendor/composer/ClassLoader.php');

        // environaut executable
        $phar->addFile($root_dir . '/bin/environaut', 'bin/environaut');

        // additional markdown files like README.md or LICENSE.md
        $finder = new Finder();
        $finder->files()->name('*.md')->depth('== 0')->in($root_dir);
        foreach ($finder as $file) {
            $phar->addFile($file->getRealPath(), '/' . $file->getRelativePathname());
        }

        // add startup file
        $phar->setStub($this->getStub());

        $phar->stopBuffering();

        chmod($phar_path, 0755);
    }

    /**
     * @return string initial stub to startup environaut when executing the phar
     */
    protected function getStub()
    {
        $stub = <<<'EOF'
#!/usr/bin/env php
<?php

try {
    Phar::mapPhar('environaut.phar');
    require 'phar://environaut.phar/bin/environaut';
} catch (PharException $e) {
    echo $e->getTraceAsString();
    exit(1);
}

__HALT_COMPILER();
EOF;

        return $stub;
    }
}
