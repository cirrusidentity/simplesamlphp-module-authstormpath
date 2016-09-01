<?php

class sspmod_authstormpath_Auth_Source_Stormpath extends sspmod_core_Auth_UserPassBase
{
    /**
     * @var SimpleSAML_Configuration
     */
    private $config;

    private $resourceProvider;

    private $applicationHref;

    private $accountStore;

    private $customAttributesToFilter = ['href', 'createdAt', 'modifiedAt', 'httpStatus'];

    public function __construct($info, &$config)
    {
        parent::__construct($info, $config);

        $this->config = \SimpleSAML_Configuration::loadFromArray($config);

        $this->applicationHref = $this->config->getString('applicationHref');
        // defaults against authenticating against all account stores
        $this->accountStore = $this->config->getString('accountStore', null);

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
            $options = [];
            if (!empty($this->accountStore)) {
                $accountStoreResource = new Stormpath\Resource\AccountStore();
                $data = new \stdClass();
                $data->href = $this->accountStore;
                $accountStoreResource->setProperties($data);
                /* Retrieving the actual resource breaks the resulting auth POST since the body contains unhandled
                attributes. In addition, we can save an extra API call by just creating out own unmaterialized resource.
                */
                //$accountStoreResource = $client->getDataStore()
                //->getResource($this->accountStore, \Stormpath\Stormpath::ACCOUNT_STORE);
                $options['accountStore'] = $accountStoreResource;
            }

            $authenticationRequest = new Stormpath\Authc\UsernamePasswordRequest(
                $username,
                $password,
                $options
            );
            $result = $application->authenticateAccount($authenticationRequest);

            //$result = $application->authenticate($username, $password);
            $account = $result->getAccount();
            //SimpleSAML_Logger::info('account ' . var_dump($account, true));
            $attributes = array(
                'username' => array($account->username),
                'email' => array($account->email),
                'fullName' => array($account->fullName),
                'givenName' => array($account->givenName),
                'surname' => array($account->surname),
            );
            $customData = $account->getCustomData();

            // Stormpath always sets these, but they are a pain to set during mocking.
            if ($customData && $customData->getHref()) {
                //$customData doesn't seem to actually have been materialized without first doing a get on a property
                // but we want to list of all the properties so we get the whole resource.
                $customData = $client->getDataStore()->getResource(
                    $customData->getHref(),
                    \Stormpath\Stormpath::CUSTOM_DATA
                );
                foreach ($customData->getPropertyNames() as $property) {
                    // TODO: handle complex custom attributes
                    SimpleSAML_Logger::debug('mapping custom property ' . $property);
                    if (!in_array ($property, $this->customAttributesToFilter, true)) {
                        $value = $customData->getProperty($property);
                        if (is_object($value)) {
                            SimpleSAML_Logger::debug('Skipping object attribute ' . $property);
                            continue;
                        } elseif (is_array($value)) {
                            $attributes[$property] = $value;
                        } else {
                            // SSP likes attributes as an array
                            $attributes[$property] = [$value];
                        }
                    }
                }
            }
            return $attributes;
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
