<?php


class sspmod_authstormpath_Auth_Source_RestStormpathResourceProvider implements
    sspmod_authstormpath_Auth_Source_StormpathResourceProvider
{

    public function getClient()
    {
        $builder = new Stormpath\ClientBuilder();
        //FIXME: set file location
        $client = $builder->setApiKeyFileLocation('/Users/patrick/.stormpath/apiKey.properties')->build();
        return $client;
    }
}
