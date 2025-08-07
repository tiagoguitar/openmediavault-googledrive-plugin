<?php

// OpenMediaVault Google Drive Authentication Handler
// Uses proper dependency management for OMV environment

namespace OMV\Engined\Rpc;

// Try to load Google API client from different possible locations
$googleClientLoaded = false;

// Check if installed via composer in the rpc directory
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
    $googleClientLoaded = true;
} elseif (file_exists('/usr/share/php/Google/autoload.php')) {
    // Check for system-wide installation
    require_once '/usr/share/php/Google/autoload.php';
    $googleClientLoaded = true;
} elseif (file_exists('/usr/local/lib/php/vendor/autoload.php')) {
    // Check for local installation
    require_once '/usr/local/lib/php/vendor/autoload.php';
    $googleClientLoaded = true;
}

if (!$googleClientLoaded) {
    throw new \Exception('Google API PHP Client not found. Please install it using: composer require google/apiclient');
}

use Google\Client;
use Google\Service\Drive;

class GoogleDriveAuth
{
    private $client;
    private $configDir;
    private $tokenFile;
    private $clientSecretFile;

    public function __construct($configDir)
    {
        if (empty($configDir) || !is_string($configDir)) {
            throw new \InvalidArgumentException('Config directory must be a valid string');
        }
        
        $this->configDir = rtrim($configDir, '/');
        $this->tokenFile = $this->configDir . '/token.json';
        $this->clientSecretFile = $this->configDir . '/client_secret.json';
        
        // Ensure config directory exists with proper permissions
        if (!is_dir($this->configDir)) {
            if (!mkdir($this->configDir, 0750, true)) {
                throw new \RuntimeException("Failed to create config directory: {$this->configDir}");
            }
        }
        
        // Verify client secret file exists
        if (!file_exists($this->clientSecretFile)) {
            throw new \RuntimeException("Google client secret file not found: {$this->clientSecretFile}");
        }
        
        $this->initializeClient();
    }
    
    private function initializeClient()
    {
        try {
            $this->client = new Client();
            $this->client->setAuthConfig($this->clientSecretFile);
            $this->client->addScope(Drive::DRIVE);
            $this->client->setAccessType('offline');
            $this->client->setPrompt('select_account consent');
        } catch (\Exception $e) {
            throw new \RuntimeException("Failed to initialize Google client: " . $e->getMessage(), 0, $e);
        }
    }

    public function getAuthUrl()
    {
        try {
            return $this->client->createAuthUrl();
        } catch (\Exception $e) {
            throw new \RuntimeException("Failed to create authentication URL: " . $e->getMessage(), 0, $e);
        }
    }

    public function authenticate($authCode)
    {
        if (empty($authCode) || !is_string($authCode)) {
            throw new \InvalidArgumentException('Authorization code must be a valid string');
        }
        
        try {
            $accessToken = $this->client->fetchAccessTokenWithAuthCode($authCode);
            
            if (isset($accessToken['error'])) {
                throw new \RuntimeException("Authentication failed: " . $accessToken['error']);
            }
            
            $this->client->setAccessToken($accessToken);
            
            // Save token with secure permissions
            $tokenJson = json_encode($accessToken, JSON_PRETTY_PRINT);
            if (file_put_contents($this->tokenFile, $tokenJson, LOCK_EX) === false) {
                throw new \RuntimeException("Failed to save authentication token");
            }
            
            // Set secure permissions on token file
            chmod($this->tokenFile, 0600);
            
            return true;
        } catch (\Exception $e) {
            throw new \RuntimeException("Authentication failed: " . $e->getMessage(), 0, $e);
        }
    }

    public function isAuthenticated()
    {
        try {
            if (!file_exists($this->tokenFile)) {
                return false;
            }
            
            $tokenContent = file_get_contents($this->tokenFile);
            if ($tokenContent === false) {
                return false;
            }
            
            $accessToken = json_decode($tokenContent, true);
            if (!$accessToken || !isset($accessToken['access_token'])) {
                return false;
            }
            
            $this->client->setAccessToken($accessToken);
            
            if ($this->client->isAccessTokenExpired()) {
                if ($this->client->getRefreshToken()) {
                    try {
                        $newToken = $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
                        
                        if (isset($newToken['error'])) {
                            return false;
                        }
                        
                        // Save the refreshed token
                        $tokenJson = json_encode($newToken, JSON_PRETTY_PRINT);
                        if (file_put_contents($this->tokenFile, $tokenJson, LOCK_EX) !== false) {
                            chmod($this->tokenFile, 0600);
                        }
                        
                        return true;
                    } catch (\Exception $e) {
                        return false;
                    }
                } else {
                    return false;
                }
            }
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getDriveService()
    {
        if ($this->isAuthenticated()) {
            try {
                return new Drive($this->client);
            } catch (\Exception $e) {
                throw new \RuntimeException("Failed to create Drive service: " . $e->getMessage(), 0, $e);
            }
        }
        return null;
    }
}

