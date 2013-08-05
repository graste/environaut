<?php

namespace Environaut\Tests\Checks\Fixtures;

use Environaut\Checks\Configurator;

use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Output\StreamOutput;

class TestableConfigurator extends Configurator
{
    protected $input;
    protected $output_stream;

    /**
     * @return \Symfony\Component\Console\Helper\DialogHelper
     */
    public function getDialogHelper()
    {
        $dialog = new \Symfony\Component\Console\Helper\DialogHelper();
        $helperSet = new HelperSet(array(new FormatterHelper()));
        $dialog->setHelperSet($helperSet);

        $dialog->setInputStream($this->getInputStream());

        return $dialog;
    }

    public function setInput($input)
    {
        $this->input = $input;
    }

    public function getOutput()
    {
        rewind($this->output_stream->getStream());
        return stream_get_contents($this->output_stream->getStream());
    }

    protected function getInputStream()
    {
        $stream = fopen('php://memory', 'r+', false);
        fputs($stream, $this->input);
        rewind($stream);

        return $stream;
    }

    public function getOutputStream()
    {
        if (!$this->output_stream) {
            $this->output_stream = new StreamOutput(fopen('php://memory', 'r+', false));
        }

        return $this->output_stream;
    }
}
