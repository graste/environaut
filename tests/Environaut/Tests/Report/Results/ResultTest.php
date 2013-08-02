<?php

namespace Environaut\Tests\Report\Results;

use Environaut\Checks\Configurator;
use Environaut\Checks\ICheck;
use Environaut\Report\Results\Result;
use Environaut\Report\Results\Settings\ISetting;
use Environaut\Report\Results\Settings\Setting;
use Environaut\Tests\BaseTestCase;

class ResultTest extends BaseTestCase
{
    public function testConstruct()
    {
        $check = new Configurator('foo');
        $result = new Result($check);

        $this->assertEmpty($result->getCachableSettings());
        $this->assertEmpty($result->getSettings());
        $this->assertEmpty($result->getMessages());
    }

    public function testAddSettings()
    {
        $check = new Configurator('foo');
        $result = new Result($check);

        $result->addSetting(new Setting('foo', 'bar'));
        $result->addSetting(new Setting('blub', 'blah'), false);

        $this->assertCount(2, $result->getSettings());
        $this->assertCount(1, $result->getCachableSettings());
    }

    public function testGetSettingsAsArray()
    {
        $check = new Configurator('foo');
        $result = new Result($check);

        $result->addSetting(new Setting('foo', 'bar'));
        $result->addSetting(new Setting('blub', 'blah'));
        $result->addSetting(new Setting('yes', 'no'), false); // non-cachable

        $this->assertCount(2, $result->getCachableSettingsAsArray());
        $this->assertSame(
            array(
                array(
                    'name' => 'foo',
                    'value' => 'bar',
                    'group' => ICheck::DEFAULT_GROUP_NAME,
                    'flag' => ISetting::NORMAL
                ),
                array(
                    'name' => 'blub',
                    'value' => 'blah',
                    'group' => ICheck::DEFAULT_GROUP_NAME,
                    'flag' => ISetting::NORMAL
                ),
            ),
            $result->getCachableSettingsAsArray()
        );

        $this->assertCount(3, $result->getSettingsAsArray());
        $this->assertSame(
            array(
                array(
                    'name' => 'foo',
                    'value' => 'bar',
                    'group' => ICheck::DEFAULT_GROUP_NAME,
                    'flag' => ISetting::NORMAL
                ),
                array(
                    'name' => 'blub',
                    'value' => 'blah',
                    'group' => ICheck::DEFAULT_GROUP_NAME,
                    'flag' => ISetting::NORMAL
                ),
                array(
                    'name' => 'yes',
                    'value' => 'no',
                    'group' => ICheck::DEFAULT_GROUP_NAME,
                    'flag' => ISetting::NORMAL
                ),
            ),
            $result->getSettingsAsArray()
        );
    }
}
