<?php

namespace SimpleSAML\Test\Authstormpath\Auth\Source;

/**
 * Class StormpathTest
 *
 * Test the storm path module using actual integration with Stormpath
 * @package SimpleSAML\Test\Authstormpath\Auth\Source
 */
class StormpathTest extends \PHPUnit_Framework_TestCase
{
    public static $good_username;
    public static $good_password;

    public static $INI_LOADED;

    public static function setUpBeforeClass()
    {
        putenv('SIMPLESAMLPHP_CONFIG_DIR=' . dirname(dirname(dirname(__DIR__))) . '/config');

        $stormPathSettings = parse_ini_file(getenv('HOME') . '/.stormpath/ssp-authstormpath-test.ini');
        self::$INI_LOADED = ($stormPathSettings !== false);

        self::$good_username = $stormPathSettings['good_username'];
        self::$good_password = $stormPathSettings['good_password'];
    }

    protected function setUp()
    {
        if (!self::$INI_LOADED) {
            $this->markTestSkipped(
                'Local config of stormpath not found.'
            );
        }
    }


    public function testCorrectPassword()
    {
        $info = array(
            'AuthId' => 'sample_stormpath'
        );
        $config = array();
        $authSource = new \sspmod_authstormpath_Auth_Source_Stormpath($info, $config);

        $attributes = $authSource->login(self::$good_username, self::$good_password);
        $this->assertEquals(['testusername'], $attributes['username']);
        $this->assertEquals(['testuser@example.com'], $attributes['email']);
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
