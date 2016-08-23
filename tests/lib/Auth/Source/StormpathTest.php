<?php

namespace SimpleSAML\Test\Authstormpath\Auth\Source;

use Stormpath\Resource\CustomData;
use Stormpath\Resource\Error;
use Stormpath\Stormpath;

/**
 * Class StormpathTest
 * Test the Stormpath authe module using stubs for the Stormpath API.
 * @package SimpleSAML\Test\Authstormpath\Auth\Source
 */
class StormpathTest extends \PHPUnit_Framework_TestCase
{
    public static $good_username = 'theusername';
    public static $good_password = 'thepassword';

    private $defaultConfig = [
        'applicationHref' => 'myAppHref',
    ];

    public static function setUpBeforeClass()
    {
        putenv('SIMPLESAMLPHP_CONFIG_DIR=' . dirname(dirname(dirname(__DIR__))) . '/config');
    }


    public function testCorrectPassword()
    {
        $info = array(
            'AuthId' => 'sample_stormpath'
        );
        $authSource = new \sspmod_authstormpath_Auth_Source_Stormpath($info, $this->defaultConfig);

        $account = new \Stormpath\Resource\Account();
        $account->setUsername('abc');
        $account->setEmail('abc@example.com');

        $provider = $this->setupMockStormpath($account);
        $authSource->setResourceProvider($provider);

        // Testing 'authenticate()' method is complicated since it doesn't return. Instead we relax permissions on login
        $attributes = $authSource->login(self::$good_username, self::$good_password);
        $this->assertEquals(['abc'], $attributes['username']);
        $this->assertEquals(['abc@example.com'], $attributes['email']);
        //TODO: fill out rest of attribute assertion
    }



    /**
     * @dataProvider authenticationErrorProvider
     * @param $error Stormpath error
     * @throws \SimpleSAML_Error_Error expected error
     */
    public function testAuthenticationError(\Stormpath\Resource\Error $error)
    {
        //TODO: confirm the error conten
        $this->expectException(\SimpleSAML_Error_Error::class);

        $info = array(
            'AuthId' => 'sample_stormpath'
        );
        $authSource = new \sspmod_authstormpath_Auth_Source_Stormpath($info, $this->defaultConfig);
        $exception = new \Stormpath\Resource\ResourceError($error);
        $provider = $this->setupMockStormpathWithException($exception);
        $authSource->setResourceProvider($provider);

        // Testing 'authenticate()' method is complicated since it doesn't return. Instead we relax permissions on login
        $authSource->login('username', 'password');
    }

    public function authenticationErrorProvider()
    {
        //TODO: figure out additional error conditions
        $err1 = new \stdClass();
        $err1->status = 'No such user';
        return array(
            array(new Error(new \stdClass())),
        );
    }

    /**
     * Setup Mock Stormpath resources that will return the give account when authenticated.
     * @param \Stormpath\Resource\Account $account
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function setupMockStormpath(\Stormpath\Resource\Account $account)
    {

        //TODO: figure out how to add customdata to the account
        $stubAuthenticationResult = $this->getMockBuilder('\Stormpath\Resource\AuthenticationResult')
            ->getMock();

        $stubAuthenticationResult->method('getAccount')
            ->willReturn($account);

        $stubApplication = $this->getMockBuilder('\Stormpath\Resource\Application')
            ->getMock();
        $stubApplication->method('authenticateAccount')
            ->with($this->callback(function(\Stormpath\Authc\UsernamePasswordRequest $request){
                return $request->getPrincipals() === self::$good_username &&
                $request->getCredentials() === str_split(self::$good_password);
            }))
//            ->with(self::$good_username, self::$good_password)
            ->willReturn($stubAuthenticationResult);

        $stubDataStore = $this->getMockBuilder('\Stormpath\DataStore\DataStore')
            ->getMock();

        $stubDataStore->method('getResource')
            ->with('myAppHref', \Stormpath\Stormpath::APPLICATION)
            ->willReturn($stubApplication);

        $stubClient = $this->getMockBuilder('\Stormpath\Client')
            ->disableOriginalConstructor()// Client requires specific constructor args
            ->getMock();

        $stubClient->method('getDataStore')
            ->willReturn($stubDataStore);
        // stub the API
        $stub = $this->getMockBuilder('sspmod_authstormpath_Auth_Source_StormpathResourceProvider')
            ->getMock();
        $stub->method('getClient')
            ->willReturn($stubClient);

        return $stub;
    }

    /**
     * Setup Mock Stormpath resources that will throw the given exception when authenticated.
     * @param $exception
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function setupMockStormpathWithException(\Exception $exception)
    {


        $stubApplication = $this->getMockBuilder('\Stormpath\Resource\Application')
            ->getMock();
        $stubApplication->method('authenticate')
            ->willThrowException($exception);

        //TODO: consolidate the stub creation with the other method
        $stubDataStore = $this->getMockBuilder('\Stormpath\DataStore\DataStore')
            ->getMock();

        $stubDataStore->method('getResource')
            ->willReturn($stubApplication);

        $stubClient = $this->getMockBuilder('\Stormpath\Client')
            ->disableOriginalConstructor()// Client requires specific constructor args
            ->getMock();

        $stubClient->method('getDataStore')
            ->willReturn($stubDataStore);
        // stub the API
        $stub = $this->getMockBuilder('sspmod_authstormpath_Auth_Source_StormpathResourceProvider')
            ->getMock();
        $stub->method('getClient')
            ->willReturn($stubClient);

        return $stub;
    }
}
