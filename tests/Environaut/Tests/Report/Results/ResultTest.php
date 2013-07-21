<?php

namespace Environaut\Tests\Report\Results;

use Environaut\Tests\BaseTestCase;

class ResultTest extends BaseTestCase
{
    public function testConstruct()
    {
        $check = new \Environaut\Checks\Configurator('foo');
        $result = new \Environaut\Report\Results\Result($check);

        $this->assertEmpty($result->getSettings());
        $this->assertEmpty($result->getMessages());
    }

    public function testAddSettings()
    {
        $check = new \Environaut\Checks\Configurator('foo');
        $result = new \Environaut\Report\Results\Result($check);

        $result->addSetting(new \Environaut\Report\Results\Settings\Setting('foo', 'bar'));
        $result->addSetting(new \Environaut\Report\Results\Settings\Setting('blub', 'blah'));

        $this->assertCount(2, $result->getSettings());
    }

    public function testGetSettingsAsArray()
    {
        $check = new \Environaut\Checks\Configurator('foo');
        $result = new \Environaut\Report\Results\Result($check);

        $result->addSetting(new \Environaut\Report\Results\Settings\Setting('foo', 'bar'));
        $result->addSetting(new \Environaut\Report\Results\Settings\Setting('blub', 'blah'));

        $this->assertCount(2, $result->getSettings());
        $this->assertEquals(
            array(
                \Environaut\Checks\ICheck::DEFAULT_GROUP_NAME => array(
                    'foo' => 'bar',
                    'blub' => 'blah'
                )
            ),
            $result->getSettingsAsArray()
        );
    }
}
