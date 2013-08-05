<?php

namespace Environaut\Tests\Checks;

use Environaut\Checks\Validator;
use Environaut\Tests\BaseTestCase;

class ValidatorTest extends BaseTestCase
{
    public function testFixPath()
    {
        $this->assertEquals('', Validator::fixPath(''));
        $this->assertEquals(null, Validator::fixPath(null));
        $this->assertEquals('/', Validator::fixPath('/'));
        $this->assertEquals('/some/dir/', Validator::fixPath('/some/dir'));
        $this->assertEquals('/some/dir/', Validator::fixPath('/some/dir/'));
        $this->assertEquals(' /', Validator::fixPath(' '));
    }

    public function testFixRelativePath()
    {
        $this->assertEquals('', Validator::fixRelativePath(''));
        $this->assertEquals('.', Validator::fixRelativePath('.'));
        $this->assertEquals('..', Validator::fixRelativePath('..'));
        $this->assertEquals('./../..', Validator::fixRelativePath('./../..'));
        $this->assertEquals('../this/not', Validator::fixRelativePath('../this/not'));
        $this->assertEquals('i/.htaccess', Validator::fixRelativePath('i/foo/../.htaccess'));
        $this->assertEquals('i/.htaccess', Validator::fixRelativePath('i/.htaccess'));
        $this->assertEquals('that/not', Validator::fixRelativePath('that/this/path/../../not'));
        $this->assertEquals('you/not', Validator::fixRelativePath('you/that/this/path/../../../not'));
        $this->assertEquals('not', Validator::fixRelativePath('that/this/path/../../../not'));
        $this->assertEquals('this/not', Validator::fixRelativePath('this/path/../not'));
        $this->assertEquals('this/path/not', Validator::fixRelativePath('this/path//not'));
        $this->assertEquals('this/path/not', Validator::fixRelativePath('this/path/./not'));
        $this->assertEquals('/this/not', Validator::fixRelativePath('/this/not'));
        $this->assertEquals('this/not', Validator::fixRelativePath('this/not'));
        $this->assertEquals('./this/not', Validator::fixRelativePath('./this/not'));
        $this->assertEquals('/this/not', Validator::fixRelativePath('/this/path/../not'));
    }

    public function testEmptyValidIp()
    {
        $this->setExpectedException('InvalidArgumentException');
        Validator::validIp('');
    }

    public function testInvalidValidIp()
    {
        $this->setExpectedException('InvalidArgumentException');
        Validator::validIp('127.0.0.256');
    }

    public function testValidValidIp()
    {
        $this->assertEquals('192.168.0.1', Validator::validIp('192.168.0.1'), 'ipv4');
        $this->assertEquals(
            '2001:db8:0:8d3:0:8a2e:70:7344',
            Validator::validIp('2001:db8:0:8d3:0:8a2e:70:7344'),
            'ipv6'
        );
        $this->assertEquals('2001:db8::1428:57ab', Validator::validIp('2001:db8::1428:57ab'), 'omit blocks with 0');
        $this->assertEquals('::ffff:7f00:1', Validator::validIp('::ffff:7f00:1'), 'embedded ipv4 in ipv6 address');
        $this->assertEquals('::ffff:127.0.0.1', Validator::validIp('::ffff:127.0.0.1'), 'embedded ipv4 in ipv6');
        $this->assertEquals('10.0.0.1', Validator::validIp('10.0.0.1'), '10.0.0.1');
        $this->assertEquals('193.137.42.15', Validator::validIp('193.137.42.15'), '193.137.42.15');
        $this->assertEquals('192.168.0.1', Validator::validIp('192.168.0.1'), 'ipv4 address');
    }

    public function testValidIpv4NotReserved()
    {
        $this->setExpectedException('InvalidArgumentException');
        Validator::validIpv4NotReserved('240.0.0.1');
    }

    public function testValidUrl()
    {
        $this->assertEquals(
            'https://sub.domain.co.uk:8080/foo/bar?foo=bar#baz',
            Validator::validUrl('https://sub.domain.co.uk:8080/foo/bar?foo=bar#baz')
        );
        $this->assertEquals('http://heise.de', Validator::validUrl('http://heise.de'));
    }

    public function testValidIpv6Url()
    {
        $this->assertEquals(
            'http://[2001:0db8:85a3:08d3:1319:8a2e:0370:7344]:8080/',
            Validator::validUrl('http://[2001:0db8:85a3:08d3:1319:8a2e:0370:7344]:8080/')
        );
    }

    public function testInvalidIpv6Url()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->assertEquals(
            'http://2001:0db8:85a3:08d3:1319:8a2e:0370:7344:8080/',
            Validator::validUrl('http://2001:0db8:85a3:08d3:1319:8a2e:0370:7344:8080/')
        );
    }

    public function testValidEmails()
    {
        $emails = array(
            'user@example.com',
            'user+folder@example.com',
            'someone@example.business',
            'new-asdf@trololo.co.uk',
            'omg@nsfw.xxx',
            'A-Za-z0-9.!#$%&*+-/=?^_`{|}~@example.com',
            "o'hare@example.com",
            "o'hare@xn--mller-kva.example",
            'user@example123example123example123example123example123example123456.com',
            'user@example123example123example123example123example123example123456.co.nz',
            'example123example123example123example123example123example1234567@example.com',
        );

        foreach ($emails as $email) {
            $this->assertEquals($email, Validator::validEmail($email), $email);
        }

        // this should be valid, but is not according to PHPs email filter:
        //$this->assertEquals(
        //'"foo bar"@example.com',
        //Validator::validEmail('"foo bar"@example.com'),
        //'Spaces in email addresses should be allowed when they are in duoble quotes');
        //$this->assertEquals('user@localhost', Validator::validEmail('user@localhost'), 'user@localhost');

        // TODO add other tests for length constraints:
        // - 320 octets overall,
        // - 64 for local part according to SMTP,
        // - 254 chars overall if you combine RFCs etc.
    }

    public function testInvalidEmails()
    {
        $this->setExpectedException('InvalidArgumentException');
        Validator::validEmail('müller@example.com'); // Umlauts in the local part are not allowed
    }

    public function testInvalidEmails1()
    {
        $this->setExpectedException('InvalidArgumentException');
        Validator::validEmail('umlaut@müller.com'); // Umlauts in the domain part should be accepted punycode encoded
    }

    public function testInvalidEmails2()
    {
        $this->setExpectedException('InvalidArgumentException');
        Validator::validEmail('trololo');
    }

    public function testInvalidEmails3()
    {
        $this->setExpectedException('InvalidArgumentException');
        Validator::validEmail('');
    }

    public function testInvalidEmails4()
    {
        $this->setExpectedException('InvalidArgumentException');
        Validator::validEmail(null);
    }

    public function testInvalidEmails5()
    {
        $this->setExpectedException('InvalidArgumentException');
        Validator::validEmail(false);
    }

    public function testInvalidEmails6()
    {
        $this->setExpectedException('InvalidArgumentException');
        Validator::validEmail(true);
    }

    public function testInvalidEmails7()
    {
        $this->setExpectedException('InvalidArgumentException');
        Validator::validEmail(array());
    }

    public function testInvalidEmails8()
    {
        $this->setExpectedException('InvalidArgumentException');
        Validator::validEmail(new \stdClass());
    }

    public function testInvalidEmails9()
    {
        $this->setExpectedException('InvalidArgumentException');
        Validator::validEmail('@');
    }

    public function testInvalidEmails10()
    {
        $this->setExpectedException('InvalidArgumentException');
        Validator::validEmail('a@b');
    }

    public function testInvalidEmails11()
    {
        $this->setExpectedException('InvalidArgumentException');
        Validator::validEmail('<foo>@example.com'); // Characters < and > are not valid in email addresses
    }

    public function testInvalidEmails12()
    {
        $this->setExpectedException('InvalidArgumentException');
        // Domain names longer than 63 characters are invalid
        Validator::validEmail('user@example123example123example123example123example123example1234567.com');
    }

    public function testInvalidEmails13()
    {
        $this->setExpectedException('InvalidArgumentException');
        // 320 octets/bytes are the maximum allowed length according to RFC 5322 and RFC 5321 valid emails
        Validator::validEmail(
            'example123example123example123example123example123example123example123example123example123example123' .
            'example123example123example123example123example123example123example123example123456789012@example1' .
            'example.example123example123example123example123example123.example123example123example123example123' .
            'example123example123.com'
        );
    }

    public function testInvalidEmails14()
    {
        $this->setExpectedException('InvalidArgumentException');
        // Display names with email addresses may be valid, but are not support by us
        Validator::validEmail('Someone other <someone@example.com>');
    }

    public function testInvalidEmails15()
    {
        $this->setExpectedException('InvalidArgumentException');
        // Quoted display names with email addresses may be valid, but are not support by us
        Validator::validEmail('"Someone other" <someone@example.com>');

        // this should be invalid according to SMTP, but is not according to PHPs email filter:
        // 64 characters are valid according to SMTP in the local part
        // Validator::validEmail('example123example123example123example123example123example1234567@example.com');
    }

    public function testInvalidEmails16()
    {
        $this->setExpectedException('InvalidArgumentException');
        Validator::validEmail('example@example.com@example.com');
    }

    public function testReadableDirectory()
    {
        $this->assertEquals(__DIR__, Validator::readableDirectory(__DIR__));
        $this->assertEquals('.', Validator::readableDirectory('.'));
    }

    public function testReadableFile()
    {
        $this->assertEquals(__FILE__, Validator::readableFile(__FILE__));
    }

    public function testDirectoryIsNotAReadableFile()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->assertEquals(__DIR__, Validator::readableFile(__DIR__));
    }

    public function testNonexistingReadableDirectory()
    {
        $this->setExpectedException('InvalidArgumentException');
        Validator::readableDirectory(__DIR__ . 'wetrgfsafdadffwwrwgfasfaf');
    }

    public function testFileIsNotAReadableDirectory()
    {
        $this->setExpectedException('InvalidArgumentException');
        Validator::readableDirectory(__FILE__);
    }
}
