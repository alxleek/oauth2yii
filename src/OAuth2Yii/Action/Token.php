<?php

namespace OAuth2Yii\Action;

use \OAuth2Yii\Component\ServerComponent;

use \OAuth2\GrantType;
use \OAuth2\Request;

use \Yii as Yii;
use \CAction as CAction;
use \CException as CException;
use \CWebLogRoute as CWebLogRoute;
use \CProfileLogRoute as CProfileLogRoute;

class Token extends CAction
{
    /**
     * @var string name of the OAuth2Yii application component. Default is 'oauth2'
     */
    public $oauth2Component = 'oauth2';

    public function run()
    {
        if(!Yii::app()->hasComponent($this->oauth2Component)) {
            throw new CException("Could not find OAuth2Yii/Server component '{$this->oauth2Component}'");
        }

        $oauth2     = Yii::app()->getComponent($this->oauth2Component);
        $server     = $oauth2->getServer();

        if(!$oauth2->getCanGrant()) {
            throw new CException("No grant types enabled");
        }

        if($oauth2->enableAuthorization) {
            $authorizationStorage = $oauth2->getStorage(ServerComponent::STORAGE_AUTHORIZATION_CODE);
            $server->addGrantType(new GrantType\AuthorizationCode($authorizationStorage));
        }

        if($oauth2->enableClientCredentials) {
            $clientStorage = $oauth2->getStorage(ServerComponent::STORAGE_CLIENT_CREDENTIALS);
            $server->addGrantType(new GrantType\ClientCredentials($clientStorage));
        }

        // Disable any potential output from Yii logroutes
        foreach(Yii::app()->log->routes as $r) {
            if($r instanceof \CWebLogRoute || $r instanceof CProfileLogRoute) {
                $r->enabled=false;
            }
        }
        $request = Request::createFromGlobals();
        $server->handleTokenRequest($request)->send();
    }
}