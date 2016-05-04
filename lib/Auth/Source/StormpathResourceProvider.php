<?php

/**
 * Provides an adapter for Stormpath SDK so that the main module doesn't need to invoke static calls or new Object().
 * The module can use different adapters to get access to the Stormpath SDK, which provides an interception point
 * for stubbing in unit tests.
 */
interface sspmod_authstormpath_Auth_Source_StormpathResourceProvider
{

    public function getClient();
}
