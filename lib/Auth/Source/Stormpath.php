<?php

class sspmod_authstormpath_Auth_Source_Stormpath extends sspmod_core_Auth_UserPassBase
{
    /**
     * @var SimpleSAML_Configuration
     */
    private $config;

    private $resourceProvider;

    private $applicationHref;

    public function __construct($info, &$config)
    {
        parent::__construct($info, $config);

        $this->config = \SimpleSAML_Configuration::loadFromArray($config);

        $this->applicationHref = $this->config->getString('applicationHref');

        //FIXME: add config validation tests


        //FIXME: base class on property in config
        $this->resourceProvider = new sspmod_authstormpath_Auth_Source_RestStormpathResourceProvider($this->config);
    }

    /**
     * Relaxed visibility for testability
     * @param string $username the username to check
     * @param string $password the user's password
     * @return array the users attributes
     * @throws SimpleSAML_Error_Error if user/password is incorrect, or some other error
     */
    public function login($username, $password)
    {
        $client = $this->resourceProvider->getClient();
        $application = $client->getDataStore()->getResource($this->applicationHref, \Stormpath\Stormpath::APPLICATION);

        try {
            //TODO: set the organization to authentiate against (per
            // https://docs.stormpath.com/rest/product-guide/latest/multitenancy.html#authenticating-an-account-against-an-organization)
            // rather than authing against all Account Stores
            //$accountStore = $anAccountStoreMapping->getAccountStore();
            //$authenticationRequest = new UsernamePasswordRequest('usernameOrEmail', 'password',
            //   array('accountStore' => $accountStore));
            //$result = $application->authenticateAccount($authenticationRequest);

            $result = $application->authenticate($username, $password);
            $account = $result->getAccount();
            //SimpleSAML_Logger::info('account ' . var_dump($account, true));
            return array(
                'username' => array($account->username),
                'email' => array($account->email),
                'fullName' => array($account->fullName),
                'givenName' => array($account->givenName),
                'sn' => array($account->surname),
            );
        } catch (\Stormpath\Resource\ResourceError $re) {
            SimpleSAML_Logger::info("Stormpath Auth error {$re->getStatus()}, code {$re->getErrorCode()} ," .
                "msg {$re->getMessage()} devmsg {$re->getDeveloperMessage()}");
            //TODO: distinguish between error types
            throw new SimpleSAML_Error_Error('WRONGUSERPASS');
        }
    }

    /**
     * Set the provider of Stormpath clients
     * @param sspmod_authstormpath_Auth_Source_StormpathResourceProvider $resourceProvider
     */
    public function setResourceProvider(sspmod_authstormpath_Auth_Source_StormpathResourceProvider &$resourceProvider)
    {
        $this->resourceProvider = $resourceProvider;
    }
}
