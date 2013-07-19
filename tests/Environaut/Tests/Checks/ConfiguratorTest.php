<?php

namespace Environaut\Tests\Checks;

use Environaut\Report\Results\Messages\Message;
use Environaut\Tests\BaseTestCase;
use Environaut\Tests\Checks\TestableConfigurator;

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

    public function testSimpleValueQuestion()
    {
        $email = "omg@example.com";
        $check = $this->runConfigurator(
            $email,
            array(
                'question' => 'Your email?',
                'setting_name' => 'core.email'
            )
        );

        $this->assertContains('Your email?', $check->getOutput());
        $this->assertCount(1, $check->getResult()->getMessages());

        $settings = $check->getResult()->getSettings();
        $this->assertCount(1, $settings);
        $this->assertArrayHasKey('core.email', $settings);
        $this->assertEquals($email, $settings['core.email']);
    }

    public function testSimpleValueQuestionWithFailedValidationAfterSomeAttempts()
    {
        $email = "nonworking-email";
        $this->setExpectedException('InvalidArgumentException');
        $this->runConfigurator(
            $email . "\n" . $email . "\n" . $email . "\n" . $email . "\n" . $email . "\n",
            array(
                'question' => 'Your email?',
                'setting_name' => 'core.email',
                'validator' => 'Environaut\Checks\Validator::validEmail',
                'max_attempts' => 5
            )
        );
    }

    public function testSimpleValueQuestionWithSuccessValidationAfterSomeAttempts()
    {
        $email = "nonworking-email\n";

        $check = $this->runConfigurator(
            $email . $email . $email . $email . "correct@example.com\n",
            array(
                'question' => 'Your email?',
                'setting_name' => 'core.email',
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

        $settings = $check->getResult()->getSettings();
        $this->assertCount(1, $settings);
        $this->assertArrayHasKey('core.email', $settings);
        $this->assertEquals('correct@example.com', $settings['core.email']);
    }

    public function testSimpleChoice()
    {
        $choices = array('foo', 'bar', 'baz');

        $check = $this->runConfigurator(
            "asdf\n1\n",
            array(
                'question' => 'Take a pick',
                'setting_name' => 'pick',
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

        $settings = $check->getResult()->getSettings();
        $this->assertCount(1, $settings);
        $this->assertArrayHasKey('pick', $settings);
        $this->assertEquals('bar', $settings['pick']);
    }

    public function testSimpleDefaultValue()
    {
        $check = $this->runConfigurator(
            "\n",
            array(
                'question' => 'Type something long',
                'setting_name' => 'pick',
                'default' => 'hooray for default values!'
            )
        );

        $this->assertContains('Type something long', $check->getOutput());

        $messages = $check->getResult()->getMessages();
        $this->assertCount(1, $messages);

        $message = array_shift($messages);
        $this->assertInstanceOf('Environaut\Report\Results\Messages\Message', $message);
        $this->assertTrue($message->getSeverity() === Message::SEVERITY_INFO);

        $settings = $check->getResult()->getSettings();
        $this->assertCount(1, $settings);
        $this->assertArrayHasKey('pick', $settings);
        $this->assertEquals('hooray for default values!', $settings['pick']);
    }

    public function testHiddenInputValue()
    {
        $check = $this->runConfigurator(
            "password\n",
            array(
                'question' => 'Type something long',
                'setting_name' => 'pick',
                'hidden' => true
            )
        );

        $this->assertContains('Type something long', $check->getOutput());

        $messages = $check->getResult()->getMessages();
        $this->assertCount(1, $messages);

        $message = array_shift($messages);
        $this->assertInstanceOf('Environaut\Report\Results\Messages\Message', $message);
        $this->assertTrue($message->getSeverity() === Message::SEVERITY_INFO);

        $settings = $check->getResult()->getSettings();
        $this->assertCount(1, $settings);
        $this->assertArrayHasKey('pick', $settings);
        $this->assertEquals('password', $settings['pick']);
    }

    public function testHiddenInputValueWithValidatorSucceeds()
    {
        $check = $this->runConfigurator(
            __DIR__ . "\n",
            array(
                'question' => 'Type something long',
                'setting_name' => 'pick',
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

        $settings = $check->getResult()->getSettings();
        $this->assertCount(1, $settings);
        $this->assertArrayHasKey('pick', $settings);
        $this->assertEquals(__DIR__, $settings['pick']);
    }

    public function testHiddenInputValueWithValidatorFails()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->runConfigurator(
            __DIR__ . "nonexisting\n",
            array(
                'question' => 'Type something long',
                'setting_name' => 'pick',
                'hidden' => true,
                'validator' => 'Environaut\Checks\Validator::readableDirectory',
                'max_attempts' => 1
            )
        );
    }

    public function testSimpleConfirmation()
    {
        $check = $this->runConfigurator(
            "\n",
            array(
                'question' => 'Do you like testing?',
                'confirm' => true,
                'setting_name' => 'core.testing'
            )
        );

        $this->assertContains('Do you like testing?', $check->getOutput());
        $this->assertCount(1, $check->getResult()->getMessages());

        $settings = $check->getResult()->getSettings();
        $this->assertCount(1, $settings);
        $this->assertArrayHasKey('core.testing', $settings);
        $this->assertEquals(true, $settings['core.testing']);
    }

    public function testSimpleYesConfirmation()
    {
        $check = $this->runConfigurator(
            "y\n",
            array(
                'question' => 'Do you like testing?',
                'confirm' => true,
                'setting_name' => 'core.testing'
            )
        );

        $this->assertContains('Do you like testing?', $check->getOutput());
        $this->assertCount(1, $check->getResult()->getMessages());

        $settings = $check->getResult()->getSettings();

        $this->assertCount(1, $settings);
        $this->assertArrayHasKey('core.testing', $settings);
        $this->assertEquals(true, $settings['core.testing']);
    }

    public function testSimpleNoConfirmation()
    {
        $check = $this->runConfigurator(
            "n\n",
            array(
                'question' => 'Do you like testing?',
                'confirm' => true,
                'setting_name' => 'core.testing'
            )
        );

        $this->assertContains('Do you like testing?', $check->getOutput());
        $this->assertCount(1, $check->getResult()->getMessages());

        $settings = $check->getResult()->getSettings();

        $this->assertCount(1, $settings);
        $this->assertArrayHasKey('core.testing', $settings);
        $this->assertEquals(false, $settings['core.testing']);
    }

    public function testSimpleNoConfirmationViaDefault()
    {
        $check = $this->runConfigurator(
            "\n",
            array(
                'question' => 'Do you like testing?',
                'confirm' => true,
                'default' => false,
                'setting_name' => 'core.testing'
            )
        );

        $this->assertContains('Do you like testing?', $check->getOutput());
        $this->assertContains('default=n', $check->getOutput());

        $this->assertCount(1, $check->getResult()->getMessages());

        $settings = $check->getResult()->getSettings();
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
            "\n",
            array(
                'introduction' => $intro,
                'question' => 'Do you like testing?',
                'confirm' => true,
                'setting_name' => 'core.testing'
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
            "\n",
            array(
                'introduction' => $intro,
                'question' => 'Do you like testing?',
                'confirm' => true,
                'setting_name' => 'core.testing'
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
    protected function runConfigurator($input, array $params = array())
    {
        $check = new TestableConfigurator('confirmation', $params);

        $check->setInput($input);

        $check->run();

        return $check;
    }
}
