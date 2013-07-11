<?php

namespace Environaut\Compiler;

/**
 * This class compiles all necessary files of Environaut to an executable
 * environaut.phar file that may be executed directly.
 */
class PharCompiler
{
    /**
     * Create a new PHP archive.
     */
    public function create($file = 'environaut.phar')
    {
        $phar = new \Phar($file, 0, 'environaut.phar');

        $phar->setSignatureAlgorithm(\Phar::SHA1);

        $phar->startBuffering();

        $phar->buildFromDirectory(dirname(dirname(dirname(__DIR__))), '/src');

        $phar->setStub($this->getStub());

        $phar->stopBuffering();

        $phar->compress(Phar::GZ);

        unset($phar);
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

