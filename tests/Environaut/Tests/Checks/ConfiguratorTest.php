<?php

namespace Environaut\Tests\Checks;

use Environaut\Report\Results\Messages\Message;
use Environaut\Tests\BaseTestCase;
use Environaut\Tests\Checks\Fixtures\TestableConfigurator;

class ConfiguratorTest extends BaseTestCase
{
    public function testConstruct()
    {
        $intro = 'some text for introductory purposes';
        $check = new \Environaut\Checks\Configurator();
        $check->setName('foo');
        $check->setParameters(new \Environaut\Config\Parameters(array('introduction' => $intro)));

        $this->assertSame('foo', $check->getName());
        $this->assertSame($check->getDefaultGroupName(), $check->getGroup());
        $this->assertSame($intro, $check->getParameters()->get('introduction'));
        $this->assertInstanceOf('\Environaut\Report\Results\IResult', $check->getResult());
        $this->assertSame(null, $check->getCommand());
    }

    public function testCorrectSetting()
    {
        $check = new TestableConfigurator();
        $check->setName('foo');
        $check->setGroup(null);
        $check->setParameters(
            new \Environaut\Config\Parameters(
                array(
                    'setting' => 'core.foo',
                    'setting_group' => 'custom',
                )
            )
        );
        $check->setInput('asdf' . PHP_EOL);
        $this->assertTrue($check->run());

        $result = $check->getResult();
        $this->assertInstanceOf('\Environaut\Report\Results\IResult', $result);
        $settings = $result->getSettings();
        $this->assertCount(1, $settings, 'expected exactly 1 setting');
        $setting =  array_shift($settings);
        $this->assertSame('core.foo', $setting->getName());
        $this->assertSame('asdf', $setting->getValue());
        $this->assertSame('custom', $setting->getGroup());
    }

    public function testCorrectSettingWithDefaultSettingGroup()
    {
        $check = new TestableConfigurator();
        $check->setName('foo');
        $check->setInput('value' . PHP_EOL);
        $this->assertTrue($check->run());

        $this->assertEquals($check::DEFAULT_CUSTOM_GROUP_NAME, $check->getGroup());
        $result = $check->getResult();
        $this->assertInstanceOf('\Environaut\Report\Results\IResult', $result);
        $settings = $result->getSettings();
        $this->assertCount(1, $settings, 'expected exactly 1 setting');
        $setting =  array_shift($settings);
        $this->assertSame('foo', $setting->getName());
        $this->assertSame('value', $setting->getValue());
        $this->assertEquals($check::DEFAULT_SETTING_GROUP_NAME, $setting->getGroup());
        $this->assertEquals('config', $setting->getGroup());
    }

    public function testCorrectSettingWithCustomGroupFromCheck()
    {
        $check = new TestableConfigurator();
        $check->setName('foo');
        $check->setGroup('muahahaha');
        $check->setInput('value' . PHP_EOL);
        $this->assertTrue($check->run());

        $this->assertEquals('muahahaha', $check->getGroup());
        $result = $check->getResult();
        $this->assertInstanceOf('\Environaut\Report\Results\IResult', $result);
        $settings = $result->getSettings();
        $this->assertCount(1, $settings, 'expected exactly 1 setting');
        $setting =  array_shift($settings);
        $this->assertSame('foo', $setting->getName());
        $this->assertSame('value', $setting->getValue());
        $this->assertEquals('muahahaha', $setting->getGroup());
    }

    public function testCorrectSettingWithCustomGroupNotFromCheck()
    {
        $check = new TestableConfigurator();
        $check->setName('foo');
        $check->setGroup('trololo');
        $check->setInput('value' . PHP_EOL);
        $check->setParameters(new \Environaut\Config\Parameters(array('setting_group' => 'muahahaha')));
        $this->assertTrue($check->run());

        $this->assertEquals('trololo', $check->getGroup());
        $result = $check->getResult();
        $this->assertInstanceOf('\Environaut\Report\Results\IResult', $result);
        $settings = $result->getSettings();
        $this->assertCount(1, $settings, 'expected exactly 1 setting');
        $setting =  array_shift($settings);
        $this->assertSame('foo', $setting->getName());
        $this->assertSame('value', $setting->getValue());
        $this->assertEquals('muahahaha', $setting->getGroup());
    }

    public function testGroupSimpleValueQuestion()
    {
        $email = "omg@example.com";
        $check = $this->runConfigurator(
            $email,
            array(
                'question' => 'Your email?',
                'setting' => 'core.email'
            ),
            'trololo'
        );

        $this->assertSame('trololo', $check->getGroup());
        $this->assertContains('Your email?', $check->getOutput());
        $this->assertCount(1, $check->getResult()->getMessages());

        $settings = $check->getResult()->getSettingsAsArray();
        $this->assertCount(1, $settings, 'expected all settings when group is not specified');
        $settings = $check->getResult()->getSettingsAsArray(\Environaut\Checks\Configurator::DEFAULT_GROUP_NAME);
        $this->assertCount(0, $settings, 'expected default group to be empty as "trololo" was the group name.');
        $settings = $check->getResult()->getSettingsAsArray(\Environaut\Checks\Configurator::DEFAULT_CUSTOM_GROUP_NAME);
        $this->assertCount(0, $settings, 'expected default custom group to be empty as "trololo" was the group name.');
        $settings = $check->getResult()->getSettingsAsArray('trololo');
        $this->assertCount(1, $settings, 'group "trololo" should contain the setting');
        $this->assertArrayHasKey('core.email', $settings);
        $this->assertSame($email, $settings['core.email']);
    }

    public function testSimpleValueQuestion()
    {
        $email = "omg@example.com";
        $check = $this->runConfigurator(
            $email,
            array(
                'question' => 'Your email?',
                'setting' => 'core.email'
            )
        );

        $this->assertContains('Your email?', $check->getOutput());
        $this->assertCount(1, $check->getResult()->getMessages());

        $settings = $check->getResult()->getSettingsAsArray($check->getDefaultSettingGroupName());
        $this->assertCount(1, $settings);
        $this->assertArrayHasKey('core.email', $settings);
        $this->assertSame($email, $settings['core.email']);
    }

    public function testSimpleValueQuestionWithFailedValidationAfterSomeAttempts()
    {
        $email = "nonworking-email";
        $this->setExpectedException('InvalidArgumentException');
        $this->runConfigurator(
            implode(
                PHP_EOL,
                array(
                    $email,
                    $email,
                    $email,
                    $email,
                    $email
                )
            ) . PHP_EOL,
            array(
                'question' => 'Your email?',
                'setting' => 'core.email',
                'validator' => 'Environaut\Checks\Validator::validEmail',
                'max_attempts' => 5
            )
        );
    }

    public function testSimpleValueQuestionWithSuccessValidationAfterSomeAttempts()
    {
        $email = "nonworking-email";

        $check = $this->runConfigurator(
            implode(
                PHP_EOL,
                array(
                    $email,
                    $email,
                    $email,
                    $email,
                    "correct@example.com"
                )
            ) . PHP_EOL,
            array(
                'question' => 'Your email?',
                'setting' => 'core.email',
                'validator' => 'Environaut\Checks\Validator::validEmail',
                'max_attempts' => 5
            )
        );

        $this->assertContains('Your email?', $check->getOutput());
        $this->assertContains('Invalid email address given', $check->getOutput());

        $messages = $check->getResult()->getMessages();
        $this->assertCount(1, $messages);

        $message = array_shift($messages);
        $this->assertInstanceOf('Environaut\Report\Results\Messages\Message', $message);
        $this->assertTrue($message->getSeverity() === Message::SEVERITY_INFO);

        $settings = $check->getResult()->getSettingsAsArray($check->getDefaultSettingGroupName());
        $this->assertCount(1, $settings);
        $this->assertArrayHasKey('core.email', $settings);
        $this->assertSame('correct@example.com', $settings['core.email']);
    }

    public function testSimpleChoice()
    {
        $choices = array('foo', 'bar', 'baz');

        $check = $this->runConfigurator(
            "asdf" . PHP_EOL . "1" . PHP_EOL,
            array(
                'question' => 'Take a pick',
                'setting' => 'pick',
                'choices' => $choices,
                'select' => true
            )
        );

        $this->assertContains('Take a pick', $check->getOutput());

        $messages = $check->getResult()->getMessages();
        $this->assertCount(1, $messages);

        $message = array_shift($messages);
        $this->assertInstanceOf('Environaut\Report\Results\Messages\Message', $message);
        $this->assertTrue($message->getSeverity() === Message::SEVERITY_INFO);

        $settings = $check->getResult()->getSettingsAsArray($check->getDefaultSettingGroupName());
        $this->assertCount(1, $settings);
        $this->assertArrayHasKey('pick', $settings);
        $this->assertSame('bar', $settings['pick']);
    }

    public function testSimpleDefaultValue()
    {
        $check = $this->runConfigurator(
            PHP_EOL,
            array(
                'question' => 'Type something long',
                'setting' => 'pick',
                'default' => 'hooray for default values!'
            )
        );

        $this->assertContains('Type something long', $check->getOutput());

        $messages = $check->getResult()->getMessages();
        $this->assertCount(1, $messages);

        $message = array_shift($messages);
        $this->assertInstanceOf('Environaut\Report\Results\Messages\Message', $message);
        $this->assertTrue($message->getSeverity() === Message::SEVERITY_INFO);

        $settings = $check->getResult()->getSettingsAsArray($check->getDefaultSettingGroupName());
        $this->assertCount(1, $settings);
        $this->assertArrayHasKey('pick', $settings);
        $this->assertSame('hooray for default values!', $settings['pick']);
    }

    public function testHiddenInputValue()
    {
        $check = $this->runConfigurator(
            "password" . PHP_EOL,
            array(
                'question' => 'Type something long',
                'setting' => 'pick',
                'hidden' => true
            )
        );

        $this->assertContains('Type something long', $check->getOutput());

        $messages = $check->getResult()->getMessages();
        $this->assertCount(1, $messages);

        $message = array_shift($messages);
        $this->assertInstanceOf('Environaut\Report\Results\Messages\Message', $message);
        $this->assertTrue($message->getSeverity() === Message::SEVERITY_INFO);

        $settings = $check->getResult()->getSettingsAsArray($check->getDefaultSettingGroupName());
        $this->assertCount(1, $settings);
        $this->assertArrayHasKey('pick', $settings);
        $this->assertSame('password', $settings['pick']);
    }

    public function testHiddenInputValueWithValidatorSucceeds()
    {
        $check = $this->runConfigurator(
            __DIR__ . PHP_EOL,
            array(
                'question' => 'Type something long',
                'setting' => 'pick',
                'hidden' => true,
                'validator' => 'Environaut\Checks\Validator::readableDirectory'
            )
        );

        $this->assertContains('Type something long', $check->getOutput());

        $messages = $check->getResult()->getMessages();
        $this->assertCount(1, $messages);

        $message = array_shift($messages);
        $this->assertInstanceOf('Environaut\Report\Results\Messages\Message', $message);
        $this->assertTrue($message->getSeverity() === Message::SEVERITY_INFO);

        $settings = $check->getResult()->getSettingsAsArray($check->getDefaultSettingGroupName());
        $this->assertCount(1, $settings);
        $this->assertArrayHasKey('pick', $settings);
        $this->assertSame(__DIR__, $settings['pick']);
    }

    public function testHiddenInputValueWithValidatorFails()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->runConfigurator(
            __DIR__ . "nonexisting" . PHP_EOL,
            array(
                'question' => 'Type something long',
                'setting' => 'pick',
                'hidden' => true,
                'validator' => 'Environaut\Checks\Validator::readableDirectory',
                'max_attempts' => 1
            )
        );
    }

    public function testSimpleConfirmation()
    {
        $check = $this->runConfigurator(
            PHP_EOL,
            array(
                'question' => 'Do you like testing?',
                'confirm' => true,
                'setting' => 'core.testing'
            )
        );

        $this->assertContains('Do you like testing?', $check->getOutput());
        $this->assertCount(1, $check->getResult()->getMessages());

        $settings = $check->getResult()->getSettingsAsArray($check->getDefaultSettingGroupName());
        $this->assertCount(1, $settings);
        $this->assertArrayHasKey('core.testing', $settings);
        $this->assertSame(true, $settings['core.testing']);
    }

    public function testSimpleYesConfirmation()
    {
        $check = $this->runConfigurator(
            "y" . PHP_EOL,
            array(
                'question' => 'Do you like testing?',
                'confirm' => true,
                'setting' => 'core.testing'
            )
        );

        $this->assertContains('Do you like testing?', $check->getOutput());
        $this->assertCount(1, $check->getResult()->getMessages());

        $settings = $check->getResult()->getSettingsAsArray($check->getDefaultSettingGroupName());
        $this->assertCount(1, $settings);
        $this->assertArrayHasKey('core.testing', $settings);
        $this->assertSame(true, $settings['core.testing']);
    }

    public function testSimpleNoConfirmation()
    {
        $check = $this->runConfigurator(
            "n" . PHP_EOL,
            array(
                'question' => 'Do you like testing?',
                'confirm' => true,
                'setting' => 'core.testing'
            )
        );

        $this->assertContains('Do you like testing?', $check->getOutput());
        $this->assertCount(1, $check->getResult()->getMessages());

        $settings = $check->getResult()->getSettingsAsArray($check->getDefaultSettingGroupName());
        $this->assertCount(1, $settings);
        $this->assertArrayHasKey('core.testing', $settings);
        $this->assertSame(false, $settings['core.testing']);
    }

    public function testSimpleNoConfirmationViaDefault()
    {
        $check = $this->runConfigurator(
            PHP_EOL,
            array(
                'question' => 'Do you like testing?',
                'confirm' => true,
                'default' => false,
                'setting' => 'core.testing'
            )
        );

        $this->assertContains('Do you like testing?', $check->getOutput());
        $this->assertContains('default=n', $check->getOutput());

        $this->assertCount(1, $check->getResult()->getMessages());

        $settings = $check->getResult()->getSettingsAsArray($check->getDefaultSettingGroupName());
        $this->assertCount(1, $settings);
        $this->assertArrayHasKey('core.testing', $settings);
        $this->assertSame(false, $settings['core.testing']);
    }

    public function testIntroductionIsDisplayed()
    {
        $intro = <<<EOT
some multiline
    text for
introductory purposes
EOT;
        $check = $this->runConfigurator(
            PHP_EOL,
            array(
                'introduction' => $intro,
                'question' => 'Do you like testing?',
                'confirm' => true,
                'setting' => 'core.testing'
            )
        );

        $this->assertContains($intro, $check->getOutput());
    }

    public function testIntroductionAsArrayIsDisplayed()
    {
        $intro = array(
            "some multiline",
            "    text for",
            "introductory purposes"
        );

        $check = $this->runConfigurator(
            PHP_EOL,
            array(
                'introduction' => $intro,
                'question' => 'Do you like testing?',
                'confirm' => true,
                'setting' => 'core.testing'
            )
        );

        $expected = <<<EOT
some multiline
    text for
introductory purposes
EOT;
        $this->assertContains($expected, $check->getOutput());
    }

    /**
     * Runs a TestableConfigurator instance with the given input
     * and parameters and returns the instance afterwards.
     *
     * @param type $input
     * @param array $params
     *
     * @return \Environaut\Tests\Checks\TestableConfigurator
     */
    protected function runConfigurator($input, array $params = array(), $group = 'default')
    {
        $check = new TestableConfigurator();
        $check->setName('confirmation');
        $check->setGroup($group);
        $check->setParameters(new \Environaut\Config\Parameters($params));
        $check->setInput($input);

        $check->run();

        return $check;
    }
}
