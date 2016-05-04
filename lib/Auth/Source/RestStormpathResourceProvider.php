<?php


class sspmod_authstormpath_Auth_Source_RestStormpathResourceProvider implements
    sspmod_authstormpath_Auth_Source_StormpathResourceProvider
{

    /**
     * @var \SimpleSAML_Configuration
     */
    private $config;

    /**
     * sspmod_authstormpath_Auth_Source_RestStormpathResourceProvider constructor.
     */
    public function __construct(\SimpleSAML_Configuration $config)
    {
        $this->config = $config;
    }

    public function getClient()
    {
        $builder = new Stormpath\ClientBuilder();
        $client = $builder->setApiKeyFileLocation($this->config->getString('apiKeyFileLocation'))->build();
        return $client;
    }
}
