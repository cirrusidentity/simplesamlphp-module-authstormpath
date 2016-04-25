<?php

class sspmod_authstormpath_Auth_Source_Stormpath extends sspmod_core_Auth_UserPassBase
{
    /**
     * Relaxed visibility for testability
     * @param string $username the username to check
     * @param string $password the user's password
     * @return array the users attributes
     * @throws SimpleSAML_Error_Error if user/password is incorrect, or some other error
     */
    public function login($username, $password)
    {
        /*
         * FIXME: place holder code while project is setup
         */
        if ($username !== 'theusername' || $password !== 'thepassword') {
            throw new SimpleSAML_Error_Error('WRONGUSERPASS');
        }
        return array(
            'uid' => array('theusername'),
            'displayName' => array('Some Random User'),
            'eduPersonAffiliation' => array('member', 'employee'),
        );
    }
}
