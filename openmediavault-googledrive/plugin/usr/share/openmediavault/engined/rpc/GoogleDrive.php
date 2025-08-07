<?php

/**
 * OpenMediaVault Google Drive Plugin - Main RPC Service
 * 
 * This file is part of OpenMediaVault.
 * 
 * @license   http://www.gnu.org/licenses/gpl.html GPL Version 3
 * @author    OpenMediaVault Plugin Developer
 * @copyright Copyright (c) 2025
 */

namespace OMV\Engined\Rpc;

use OMV\Config\Database;
use OMV\Rpc\ServiceAbstract;
use OMV\Rpc\Exception as RpcException;

require_once __DIR__ . "/GoogleDriveAuth.php";

use OMV\Engined\Rpc\GoogleDriveAuth;

class OMVRpcServiceGoogleDrive extends ServiceAbstract
{
    private $auth;
    private $database;

    public function __construct()
    {
        $this->setName('GoogleDrive');
        
        // Initialize the OMV configuration database
        $this->database = Database::getInstance();
        
        // Define the configuration directory for the plugin
        $configDir = '/etc/openmediavault/googledrive';
        if (!is_dir($configDir)) {
            if (!mkdir($configDir, 0755, true)) {
                throw new RpcException(
                    RpcException::E_INTERNAL, 
                    "Failed to create configuration directory: {$configDir}"
                );
            }
        }
        
        try {
            $this->auth = new GoogleDriveAuth($configDir);
        } catch (\Exception $e) {
            // Don't fail construction if Google client is not set up yet
            $this->auth = null;
        }
    }
    
    private function ensureAuthInitialized()
    {
        if ($this->auth === null) {
            $configDir = '/etc/openmediavault/googledrive';
            try {
                $this->auth = new GoogleDriveAuth($configDir);
            } catch (\Exception $e) {
                throw new RpcException(
                    RpcException::E_CONFIG,
                    "Google Drive not properly configured: " . $e->getMessage()
                );
            }
        }
    }

    /**
     * Get test message.
     *
     * @param array $params An array containing the following fields:
     *   None required.
     * @param array $context The context array containing the current user.
     * @return array An array containing a test message.
     */
    public function getTestMessage($params, $context)
    {
        // Validate method context
        $this->validateMethodContext($context, ['role' => OMV_ROLE_ADMINISTRATOR]);
        
        return ["message" => "Hello from Google Drive Plugin!"];
    }

    /**
     * Get Google Drive authentication URL.
     *
     * @param array $params An array containing the following fields:
     *   None required.
     * @param array $context The context array containing the current user.
     * @return array An array containing the authentication URL.
     */
    public function getAuthUrl($params, $context)
    {
        // Validate method context
        $this->validateMethodContext($context, ['role' => OMV_ROLE_ADMINISTRATOR]);
        
        try {
            $this->ensureAuthInitialized();
            return ["url" => $this->auth->getAuthUrl()];
        } catch (\Exception $e) {
            throw new RpcException(
                RpcException::E_EXEC,
                "Failed to get authentication URL: " . $e->getMessage()
            );
        }
    }

    /**
     * Authenticate with Google Drive using the provided authorization code.
     *
     * @param array $params An array containing the following fields:
     *   \em authCode string The authorization code from Google.
     * @param array $context The context array containing the current user.
     * @return array An array containing the operation result.
     */
    public function setAuthCode($params, $context)
    {
        // Validate method context
        $this->validateMethodContext($context, ['role' => OMV_ROLE_ADMINISTRATOR]);
        
        // Validate method parameters
        $this->validateMethodParams($params, 'rpc.googledrive.setauthcode');
        
        try {
            $this->ensureAuthInitialized();
            $success = $this->auth->authenticate($params['authCode']);
            return ["success" => $success];
        } catch (\Exception $e) {
            throw new RpcException(
                RpcException::E_EXEC,
                "Authentication failed: " . $e->getMessage()
            );
        }
    }

    /**
     * Check if authenticated with Google Drive.
     *
     * @param array $params An array containing the following fields:
     *   None required.
     * @param array $context The context array containing the current user.
     * @return array An array containing the authentication status.
     */
    public function isAuthenticated($params, $context)
    {
        // Validate method context
        $this->validateMethodContext($context, ['role' => OMV_ROLE_ADMINISTRATOR]);
        
        try {
            $this->ensureAuthInitialized();
            return ["authenticated" => $this->auth->isAuthenticated()];
        } catch (\Exception $e) {
            return ["authenticated" => false];
        }
    }

    /**
     * List files from Google Drive.
     *
     * @param array $params An array containing the following fields:
     *   \em start integer The index of the first object to return (optional).
     *   \em limit integer The number of objects to process (optional).
     * @param array $context The context array containing the current user.
     * @return array An array containing the requested data.
     */
    public function listFiles($params, $context)
    {
        // Validate method context
        $this->validateMethodContext($context, ['role' => OMV_ROLE_ADMINISTRATOR]);
        
        try {
            $this->ensureAuthInitialized();
            $service = $this->auth->getDriveService();
            
            if (!$service) {
                throw new RpcException(RpcException::E_CONFIG, "Not authenticated with Google Drive");
            }

            $optParams = [
                'pageSize' => isset($params['limit']) ? min((int)$params['limit'], 100) : 10,
                'fields' => 'nextPageToken, files(id, name, mimeType, size, createdTime, modifiedTime)'
            ];
            
            $results = $service->files->listFiles($optParams);
            $files = [];
            
            foreach ($results->getFiles() as $file) {
                $files[] = [
                    'id' => $file->getId(),
                    'name' => $file->getName(),
                    'mimeType' => $file->getMimeType(),
                    'size' => $file->getSize() ?: 0,
                    'createdTime' => $file->getCreatedTime(),
                    'modifiedTime' => $file->getModifiedTime()
                ];
            }
            
            return [
                "total" => count($files),
                "data" => $files
            ];
        } catch (\Google\Service\Exception $e) {
            throw new RpcException(
                RpcException::E_EXEC,
                "Google Drive API error: " . $e->getMessage()
            );
        } catch (\Exception $e) {
            throw new RpcException(
                RpcException::E_EXEC,
                "Failed to list files: " . $e->getMessage()
            );
        }
    }

    /**
     * Download a file from Google Drive.
     *
     * @param array $params An array containing the following fields:
     *   \em fileId string The ID of the file to download.
     *   \em outputPath string The path to save the downloaded file.
     * @param array $context The context array containing the current user.
     * @return array An array containing the operation result.
     */
    public function downloadFile($params, $context)
    {
        // Validate method context
        $this->validateMethodContext($context, ['role' => OMV_ROLE_ADMINISTRATOR]);
        
        // Validate method parameters
        $this->validateMethodParams($params, 'rpc.googledrive.downloadfile');
        
        try {
            $this->ensureAuthInitialized();
            $service = $this->auth->getDriveService();
            
            if (!$service) {
                throw new RpcException(RpcException::E_CONFIG, "Not authenticated with Google Drive");
            }

            // Validate output path security
            $outputPath = realpath(dirname($params['outputPath'])) . '/' . basename($params['outputPath']);
            $allowedPaths = ['/tmp', '/srv', '/mnt', '/media'];
            $isAllowed = false;
            
            foreach ($allowedPaths as $allowedPath) {
                if (strpos($outputPath, $allowedPath) === 0) {
                    $isAllowed = true;
                    break;
                }
            }
            
            if (!$isAllowed) {
                throw new RpcException(RpcException::E_MISC_FAILURE, "Output path not allowed for security reasons");
            }

            $content = $service->files->get($params['fileId'], ['alt' => 'media']);
            
            if (file_put_contents($outputPath, $content->getBody()->getContents()) === false) {
                throw new RpcException(RpcException::E_EXEC, "Failed to write file to disk");
            }
            
            return ["success" => true, "path" => $outputPath];
        } catch (\Google\Service\Exception $e) {
            throw new RpcException(
                RpcException::E_EXEC,
                "Google Drive API error: " . $e->getMessage()
            );
        } catch (\Exception $e) {
            throw new RpcException(
                RpcException::E_EXEC,
                "Failed to download file: " . $e->getMessage()
            );
        }
    }

    /**
     * Upload a file to Google Drive.
     *
     * @param array $params An array containing the following fields:
     *   \em filePath string The path to the file to upload.
     *   \em fileName string The name of the file in Google Drive.
     *   \em parentId string The ID of the parent folder (optional).
     * @param array $context The context array containing the current user.
     * @return array An array containing the operation result.
     */
    public function uploadFile($params, $context)
    {
        // Validate method context
        $this->validateMethodContext($context, ['role' => OMV_ROLE_ADMINISTRATOR]);
        
        // Validate method parameters
        $this->validateMethodParams($params, 'rpc.googledrive.uploadfile');
        
        try {
            $this->ensureAuthInitialized();
            $service = $this->auth->getDriveService();
            
            if (!$service) {
                throw new RpcException(RpcException::E_CONFIG, "Not authenticated with Google Drive");
            }

            if (!file_exists($params['filePath'])) {
                throw new RpcException(RpcException::E_MISC_FAILURE, "File not found: " . $params['filePath']);
            }

            $fileMetadata = new \Google\Service\Drive\DriveFile([
                'name' => $params['fileName'],
                'parents' => isset($params['parentId']) && !empty($params['parentId']) ? [$params['parentId']] : []
            ]);
            
            $content = file_get_contents($params['filePath']);
            if ($content === false) {
                throw new RpcException(RpcException::E_EXEC, "Failed to read file: " . $params['filePath']);
            }
            
            $file = $service->files->create($fileMetadata, [
                'data' => $content,
                'mimeType' => mime_content_type($params['filePath']) ?: 'application/octet-stream',
                'uploadType' => 'multipart'
            ]);
            
            return ["success" => true, "fileId" => $file->getId()];
        } catch (\Google\Service\Exception $e) {
            throw new RpcException(
                RpcException::E_EXEC,
                "Google Drive API error: " . $e->getMessage()
            );
        } catch (\Exception $e) {
            throw new RpcException(
                RpcException::E_EXEC,
                "Failed to upload file: " . $e->getMessage()
            );
        }
    }

    /**
     * Delete a file from Google Drive.
     *
     * @param array $params An array containing the following fields:
     *   \em fileId string The ID of the file to delete.
     * @param array $context The context array containing the current user.
     * @return array An array containing the operation result.
     */
    public function deleteFile($params, $context)
    {
        // Validate method context
        $this->validateMethodContext($context, ['role' => OMV_ROLE_ADMINISTRATOR]);
        
        // Validate method parameters
        $this->validateMethodParams($params, 'rpc.googledrive.deletefile');
        
        try {
            $this->ensureAuthInitialized();
            $service = $this->auth->getDriveService();
            
            if (!$service) {
                throw new RpcException(RpcException::E_CONFIG, "Not authenticated with Google Drive");
            }

            $service->files->delete($params['fileId']);
            return ["success" => true];
        } catch (\Google\Service\Exception $e) {
            throw new RpcException(
                RpcException::E_EXEC,
                "Google Drive API error: " . $e->getMessage()
            );
        } catch (\Exception $e) {
            throw new RpcException(
                RpcException::E_EXEC,
                "Failed to delete file: " . $e->getMessage()
            );
        }
    }
}

