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
        $this->assertEquals('192.168.0.1', Validator::validIp('192.168.0.1'), 'ipv4 address');
        $this->assertEquals('2001:db8:0:8d3:0:8a2e:70:7344', Validator::validIp('2001:db8:0:8d3:0:8a2e:70:7344'), 'ipv6 address');
        $this->assertEquals('2001:db8::1428:57ab', Validator::validIp('2001:db8::1428:57ab'), 'blocks with 0 may be omitted in ipv6 addresses');
        $this->assertEquals('::ffff:7f00:1', Validator::validIp('::ffff:7f00:1'), 'embedded ipv4 in ipv6 address');
        $this->assertEquals('::ffff:127.0.0.1', Validator::validIp('::ffff:127.0.0.1'), 'embedded ipv4 in ipv6 address alternative');
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
        $this->assertEquals('https://sub.domain.co.uk:8080/foo/bar?foo=bar#baz', Validator::validUrl('https://sub.domain.co.uk:8080/foo/bar?foo=bar#baz'));
    }

    public function testValidIpv6Url()
    {
        $this->assertEquals('http://[2001:0db8:85a3:08d3:1319:8a2e:0370:7344]:8080/', Validator::validUrl('http://[2001:0db8:85a3:08d3:1319:8a2e:0370:7344]:8080/'));
    }

    public function testInvalidIpv6Url()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->assertEquals('http://2001:0db8:85a3:08d3:1319:8a2e:0370:7344:8080/', Validator::validUrl('http://2001:0db8:85a3:08d3:1319:8a2e:0370:7344:8080/'));
    }

    public function testValidEmails()
    {
        $this->assertEquals('user@example.com', Validator::validEmail('user@example.com'), 'user@example.com');
        $this->assertEquals('user+folder@example.com', Validator::validEmail('user+folder@example.com'), 'user+folder@example.com');
        $this->assertEquals('someone@example.business', Validator::validEmail('someone@example.business'), 'someone@example.business');
        $this->assertEquals('new-asdf@trololo.co.uk', Validator::validEmail('new-asdf@trololo.co.uk'), 'new-asdf@trololo.co.uk');
        $this->assertEquals('omg@nsfw.xxx', Validator::validEmail('omg@nsfw.xxx'), 'omg@nsfw.xxx');
        $this->assertEquals('A-Za-z0-9.!#$%&*+-/=?^_`{|}~@example.com', Validator::validEmail('A-Za-z0-9.!#$%&*+-/=?^_`{|}~@example.com'), 'A lot of special characters should be valid in the local part of email addresses');
        $this->assertEquals("o'hare@example.com", Validator::validEmail("o'hare@example.com"), "Single quotes are not working");
        $this->assertEquals("o'hare@xn--mller-kva.example", Validator::validEmail("o'hare@xn--mller-kva.example"), "International domains should be supported via Punycode ACE strings");
        $this->assertEquals('user@example123example123example123example123example123example123456.com', Validator::validEmail('user@example123example123example123example123example123example123456.com'), '63 characters long domain names should be valid');
        $this->assertEquals('user@example123example123example123example123example123example123456.co.nz', Validator::validEmail('user@example123example123example123example123example123example123456.co.nz'), '63 characters long domain names with top level domain "co.nz" should be valid');
        $this->assertEquals('example123example123example123example123example123example1234567@example.com', Validator::validEmail('example123example123example123example123example123example1234567@example.com'), '64 characters are valid according to SMTP in the local part');

        // this should be valid, but is not according to PHPs email filter:
        //$this->assertEquals('"foo bar"@example.com', Validator::validEmail('"foo bar"@example.com'), 'Spaces in email addresses should be allowed when they are in duoble quotes');
        //$this->assertEquals('user@localhost', Validator::validEmail('user@localhost'), 'user@localhost');

        // TODO add other tests for length constraints - 320 octets overall, 64 for local part according to SMTP, 254 chars overall if you combine RFCs etc.
    }

    public function testInvalidEmails()
    {
        $this->setExpectedException('InvalidArgumentException');
        Validator::validEmail('müller@example.com'); // Umlauts in the local part are not allowed
    }

    public function testInvalidEmails1()
    {
        $this->setExpectedException('InvalidArgumentException');
        Validator::validEmail('umlaut@müller.com'); // Umlauts etc. in the domain part should only be accepted punycode encoded
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
        Validator::validEmail('user@example123example123example123example123example123example1234567.com'); // Domain names longer than 63 characters are invalid
    }

    public function testInvalidEmails13()
    {
        $this->setExpectedException('InvalidArgumentException');
        Validator::validEmail('example123example123example123example123example123example123example123example123example123example123example123example123example123example123example123example123example123example123456789012@example1example.example123example123example123example123example123.example123example123example123example123example123example123.com'); // 320 octets/bytes are the maximum allowed length according to RFC 5322 and RFC 5321 valid emails
    }

    public function testInvalidEmails14()
    {
        $this->setExpectedException('InvalidArgumentException');
        Validator::validEmail('Someone other <someone@example.com>'); // Display names with email addresses may be valid, but are not support by us
    }

    public function testInvalidEmails15()
    {
        $this->setExpectedException('InvalidArgumentException');
        Validator::validEmail('"Someone other" <someone@example.com>'); // Quoted display names with email addresses may be valid, but are not support by us

        // this should be invalid according to SMTP, but is not according to PHPs email filter:
        // Validator::validEmail('example123example123example123example123example123example1234567@example.com'); // 64 characters are valid according to SMTP in the local part
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
