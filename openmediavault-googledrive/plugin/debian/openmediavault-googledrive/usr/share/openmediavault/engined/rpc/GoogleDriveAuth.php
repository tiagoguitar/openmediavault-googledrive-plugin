<?php

require_once 'vendor/autoload.php'; // This will be handled during packaging

use Google\Client;
use Google\Service\Drive;

class GoogleDriveAuth
{
    private $client;
    private $configDir;

    public function __construct($configDir)
    {
        $this->configDir = $configDir;
        $this->client = new Client();
        $this->client->setAuthConfig($this->configDir . '/client_secret.json');
        $this->client->addScope(Drive::DRIVE);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('select_account consent');
    }

    public function getAuthUrl()
    {
        return $this->client->createAuthUrl();
    }

    public function authenticate($authCode)
    {
        $accessToken = $this->client->fetchAccessTokenWithAuthCode($authCode);
        $this->client->setAccessToken($accessToken);
        file_put_contents($this->configDir . '/token.json', json_encode($accessToken));
        return true;
    }

    public function isAuthenticated()
    {
        if (file_exists($this->configDir . '/token.json')) {
            $accessToken = json_decode(file_get_contents($this->configDir . '/token.json'), true);
            $this->client->setAccessToken($accessToken);
            if ($this->client->isAccessTokenExpired()) {
                if ($this->client->getRefreshToken()) {
                    $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
                    file_put_contents($this->configDir . '/token.json', json_encode($this->client->getAccessToken()));
                } else {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    public function getDriveService()
    {
        if ($this->isAuthenticated()) {
            return new Drive($this->client);
        }
        return null;
    }
}

