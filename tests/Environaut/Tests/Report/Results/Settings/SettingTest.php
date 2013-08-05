<?php

namespace Environaut\Tests\Report\Results\Settings;

use Environaut\Checks\Configurator;
use Environaut\Checks\ICheck;
use Environaut\Report\Results\Result;
use Environaut\Report\Results\Settings\ISetting;
use Environaut\Report\Results\Settings\Setting;
use Environaut\Tests\BaseTestCase;

class SettingTest extends BaseTestCase
{
    public function testConstruct()
    {
        $s = new Setting('foo', 'bar');

        $this->assertSame('foo', $s->getName());
        $this->assertSame('bar', $s->getValue());
        $this->assertSame(Setting::NORMAL, $s->getFlag());
        $this->assertSame(ICheck::DEFAULT_GROUP_NAME, $s->getGroup());
    }

    public function testGroupNamesMagic()
    {
        $this->assertSame(array('foo'), Setting::getGroupNames('foo'), 'Simple group name as string');
        $this->assertSame(array('foo'), Setting::getGroupNames(array('foo')), 'Group name as array');
        $this->assertSame(array('foo', 'bar'), Setting::getGroupNames('foo, bar'), 'Multiple group names comma separated');
        $this->assertSame(array('foo', 'bar'), Setting::getGroupNames(array('foo', 'bar')), 'Multiple group names in an array');
        $this->assertSame(array(), Setting::getGroupNames(null), 'No group name on NULL');
        $this->assertSame(array(), Setting::getGroupNames(array()), 'No group name on empty array');
        $this->assertSame(array(), Setting::getGroupNames(new \stdClass()), 'No group name on object');
    }

    public function testMatchesGroup()
    {
        $s = new Setting('foo', 'bar', 'group');

        $this->assertSame('group', $s->getGroup());

        $this->assertTrue($s->matchesGroup('group'));
        $this->assertTrue($s->matchesGroup(array('group')));
        $this->assertTrue($s->matchesGroup(null));

        $this->assertFalse($s->matchesGroup('nonexistant'));
        $this->assertFalse($s->matchesGroup(array('nonexistant')));
        $this->assertFalse($s->matchesGroup(array()));
        $this->assertFalse($s->matchesGroup(new \stdClass()));
    }
}
