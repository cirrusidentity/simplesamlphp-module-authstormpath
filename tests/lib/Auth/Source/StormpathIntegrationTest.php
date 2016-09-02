<?php

namespace SimpleSAML\Test\Authstormpath\Auth\Source;

/**
 * Class StormpathTest
 *
 * Test the storm path module using actual integration with Stormpath
 * @package SimpleSAML\Test\Authstormpath\Auth\Source
 */
class StormpathIntegrationTest extends \PHPUnit_Framework_TestCase
{
    public static $good_username;
    public static $good_password;
    public static $good_account_store;
    public static $incorrect_account_store;

    public static $applicationHref;
    public static $apiKeyFileLocation;

    public static $INI_LOADED;

    public static function setUpBeforeClass()
    {
        putenv('SIMPLESAMLPHP_CONFIG_DIR=' . dirname(dirname(dirname(__DIR__))) . '/config');

        $stormPathSettings = parse_ini_file(getenv('HOME') . '/.stormpath/ssp-authstormpath-test.ini');
        self::$INI_LOADED = ($stormPathSettings !== false);

        self::$good_username = $stormPathSettings['good_username'];
        self::$good_password = $stormPathSettings['good_password'];
        self::$applicationHref = $stormPathSettings['applicationHref'];
        self::$apiKeyFileLocation = $stormPathSettings['apiKeyFileLocation'];
        self::$good_account_store = $stormPathSettings['good_account_store'];
        self::$incorrect_account_store = $stormPathSettings['incorrect_account_store'];

    }

    protected function setUp()
    {
        if (!self::$INI_LOADED) {
            $this->markTestSkipped(
                'Local config of stormpath not found.'
            );
        }
    }


    /**
     * @dataProvider correctAuthenticationProvider
     * @param the account store to authentication against. null means all accountstores
     */
    public function testCorrectPasswordAndAttributeMapping($accountStore)
    {
        $info = array(
            'AuthId' => 'sample_stormpath'
        );
        $config = array(
            'applicationHref' => self::$applicationHref,
            'apiKeyFileLocation' => self::$apiKeyFileLocation,
            'accountStore' => $accountStore
        );
        $authSource = new \sspmod_authstormpath_Auth_Source_Stormpath($info, $config);

        $attributes = $authSource->login(self::$good_username, self::$good_password);
        $this->assertEquals(['testusername'], $attributes['username']);
        $this->assertEquals(['testuser@example.com'], $attributes['email']);
        $this->assertEquals(['Test'], $attributes['givenName']);
        $this->assertEquals(['User'], $attributes['surname']);
        $this->assertEquals(['4438ea02-5791-4fdb-983b-b35d69eb4c31'], $attributes['customAttribute']);
        $this->assertEquals(['a', 'b', 'c'], $attributes['customArray']);
        $this->assertEquals([true], $attributes['customBoolean']);
        $this->assertEquals([null], $attributes['customNull']);
        $this->assertEquals([42], $attributes['customNumber']);
        $this->assertArrayNotHasKey('customComplex', $attributes, "TBD: how to encode complex attributes");




        $this->assertFalse(array_key_exists('httpStatus', $attributes), 'Operations attributes should be filtered');

    }

    public function correctAuthenticationProvider()
    {
        return array(
            'no account store' => array(null),
            'org account store' => array(self::$good_account_store)
        );
    }

    /**
     * @dataProvider badUserPasswordProvider
     * @param $username username to check
     * @param $password password to check
     * @throws \SimpleSAML_Error_Error expected error
     */
    public function testIncorrectPasswordUsername($username, $password, $accountStore)
    {
        $this->expectException(\SimpleSAML_Error_Error::class);
        $info = array(
            'AuthId' => 'sample_stormpath'
        );
        $config = array(
            'applicationHref' => self::$applicationHref,
            'apiKeyFileLocation' => self::$apiKeyFileLocation,
            'accountStore' => $accountStore

        );
        $authSource = new \sspmod_authstormpath_Auth_Source_Stormpath($info, $config);

        // Testing 'authenticate()' method is complicated since it doesn't return. Instead we relax permissions on login
        $authSource->login($username, $password);
    }

    public function badUserPasswordProvider()
    {
        return array(
            array(self::$good_username, null, self::$good_account_store),
            array(self::$good_username, '', self::$good_account_store),
            array(self::$good_username, 'wrongpassword', self::$good_account_store),
            array(null, self::$good_password, self::$good_account_store),
            array('', self::$good_password, self::$good_account_store),
            array('nosuchuser', self::$good_password, null),
            // Correct credentials, but wrong account store
            array(self::$good_username, self::$good_password, self::$incorrect_account_store),

        );
    }
}
