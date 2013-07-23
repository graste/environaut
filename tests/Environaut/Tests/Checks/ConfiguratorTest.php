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
        $check = new \Environaut\Checks\Configurator('foo', array('introduction' => $intro));

        $this->assertEquals('foo', $check->getName());
        $this->assertInstanceOf('\Environaut\Report\Results\IResult', $check->getResult());
        $this->assertEquals(null, $check->getCommand());
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

        $this->assertEquals('trololo', $check->getGroup());
        $this->assertContains('Your email?', $check->getOutput());
        $this->assertCount(1, $check->getResult()->getMessages());

        $settings = $check->getResult()->getSettingsAsArray();
        $this->assertCount(1, $settings, 'expected all settings when group is not specified');
        $settings = $check->getResult()->getSettingsAsArray('default');
        $this->assertCount(0, $settings, 'expected default group to be empty/nonexisting as "trololo" was the group name.');
        $settings = $check->getResult()->getSettingsAsArray('trololo');
        $this->assertCount(1, $settings, 'group "trololo" should contain the setting');
        $this->assertArrayHasKey('core.email', $settings);
        $this->assertEquals($email, $settings['core.email']);
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

        $settings = $check->getResult()->getSettingsAsArray('default');
        $this->assertCount(1, $settings);
        $this->assertArrayHasKey('core.email', $settings);
        $this->assertEquals($email, $settings['core.email']);
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
        $email = "nonworking-email" . PHP_EOL;

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

        $settings = $check->getResult()->getSettingsAsArray('default');
        $this->assertCount(1, $settings);
        $this->assertArrayHasKey('core.email', $settings);
        $this->assertEquals('correct@example.com', $settings['core.email']);
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

        $settings = $check->getResult()->getSettingsAsArray('default');
        $this->assertCount(1, $settings);
        $this->assertArrayHasKey('pick', $settings);
        $this->assertEquals('bar', $settings['pick']);
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

        $settings = $check->getResult()->getSettingsAsArray('default');
        $this->assertCount(1, $settings);
        $this->assertArrayHasKey('pick', $settings);
        $this->assertEquals('hooray for default values!', $settings['pick']);
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

        $settings = $check->getResult()->getSettingsAsArray('default');
        $this->assertCount(1, $settings);
        $this->assertArrayHasKey('pick', $settings);
        $this->assertEquals('password', $settings['pick']);
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

        $settings = $check->getResult()->getSettingsAsArray('default');
        $this->assertCount(1, $settings);
        $this->assertArrayHasKey('pick', $settings);
        $this->assertEquals(__DIR__, $settings['pick']);
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

        $settings = $check->getResult()->getSettingsAsArray('default');
        $this->assertCount(1, $settings);
        $this->assertArrayHasKey('core.testing', $settings);
        $this->assertEquals(true, $settings['core.testing']);
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

        $settings = $check->getResult()->getSettingsAsArray('default');
        $this->assertCount(1, $settings);
        $this->assertArrayHasKey('core.testing', $settings);
        $this->assertEquals(true, $settings['core.testing']);
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

        $settings = $check->getResult()->getSettingsAsArray('default');
        $this->assertCount(1, $settings);
        $this->assertArrayHasKey('core.testing', $settings);
        $this->assertEquals(false, $settings['core.testing']);
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

        $settings = $check->getResult()->getSettingsAsArray('default');
        $this->assertCount(1, $settings);
        $this->assertArrayHasKey('core.testing', $settings);
        $this->assertEquals(false, $settings['core.testing']);
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
        $check = new TestableConfigurator('confirmation', $group, $params);

        $check->setInput($input);

        $check->run();

        return $check;
    }
}
