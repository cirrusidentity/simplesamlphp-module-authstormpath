<?php

namespace SimpleSAML\Test\Authstormpath\Auth\Source;

class StormpathTest extends \PHPUnit_Framework_TestCase
{
    public static $good_username = 'theusername';
    public static $good_password = 'thepassword';

    public static function setUpBeforeClass()
    {
        putenv('SIMPLESAMLPHP_CONFIG_DIR=' . dirname(dirname(dirname(__DIR__))) . '/config');
    }

    public function testCorrectPassword()
    {
        $info = array(
            'AuthId' => 'sample_stormpath'
        );
        $config = array();
        $authSource = new \sspmod_authstormpath_Auth_Source_Stormpath($info, $config);

        // Testing 'authenticate()' method is complicated since it doesn't return. Instead we relax permissions on login
        $authSource->login(self::$good_username, self::$good_password);
    }

    /**
     * @dataProvider badUserPasswordProvider
     * @param $username username to check
     * @param $password password to check
     * @throws \SimpleSAML_Error_Error expected error
     */
    public function testIncorrectPasswordUsername($username, $password)
    {
        $this->expectException(\SimpleSAML_Error_Error::class);
        $info = array(
            'AuthId' => 'sample_stormpath'
        );
        $config = array();
        $authSource = new \sspmod_authstormpath_Auth_Source_Stormpath($info, $config);

        // Testing 'authenticate()' method is complicated since it doesn't return. Instead we relax permissions on login
        $authSource->login($username, $password);
    }

    public function badUserPasswordProvider()
    {
        return array(
            array(self::$good_username, null),
            array(self::$good_username, ''),
            array(self::$good_username, 'wrongpassword'),
            array(null, self::$good_password),
            array('', self::$good_password),
            array('nosuchuser', self::$good_password),
        );
    }
}
