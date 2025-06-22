<?php

use OMV\Rpc\OMVModuleRpcAbstract;

require_once __DIR__ . "/GoogleDriveAuth.php";

class OMVModuleRpcGoogleDrive extends OMVModuleRpcAbstract
{
    private $auth;

    public function __construct()
    {
        parent::__construct();
        // Define o diretório de configuração para o plugin
        $configDir = '/etc/openmediavault/googledrive';
        if (!is_dir($configDir)) {
            mkdir($configDir, 0755, true);
        }
        $this->auth = new GoogleDriveAuth($configDir);
    }

    /**
     * Get test message.
     *
     * @return array
     */
    public function getTestMessage()
    {
        return ["message" => "Hello from Google Drive Plugin!"];
    }

    /**
     * Get Google Drive authentication URL.
     *
     * @return array
     */
    public function getAuthUrl()
    {
        try {
            return ["url" => $this->auth->getAuthUrl()];
        } catch (Exception $e) {
            return ["error" => $e->getMessage()];
        }
    }

    /**
     * Authenticate with Google Drive using the provided authorization code.
     *
     * @param string $authCode The authorization code from Google.
     * @return array
     */
    public function setAuthCode($authCode)
    {
        try {
            $success = $this->auth->authenticate($authCode);
            return ["success" => $success];
        } catch (Exception $e) {
            return ["error" => $e->getMessage()];
        }
    }

    /**
     * Check if authenticated with Google Drive.
     *
     * @return array
     */
    public function isAuthenticated()
    {
        return ["authenticated" => $this->auth->isAuthenticated()];
    }

    /**
     * List files from Google Drive.
     *
     * @return array
     */
    public function listFiles()
    {
        $service = $this->auth->getDriveService();
        if (!$service) {
            return ["error" => "Not authenticated."];
        }

        try {
            $optParams = [
                'pageSize' => 10,
                'fields' => 'nextPageToken, files(id, name, mimeType, size, createdTime, modifiedTime)'
            ];
            $results = $service->files->listFiles($optParams);
            $files = [];
            if (count($results->getFiles()) == 0) {
                return ["files" => []];
            }
            foreach ($results->getFiles() as $file) {
                $files[] = [
                    'id' => $file->getId(),
                    'name' => $file->getName(),
                    'mimeType' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'createdTime' => $file->getCreatedTime(),
                    'modifiedTime' => $file->getModifiedTime()
                ];
            }
            return ["files" => $files];
        } catch (Exception $e) {
            return ["error" => $e->getMessage()];
        }
    }

    /**
     * Download a file from Google Drive.
     *
     * @param string $fileId The ID of the file to download.
     * @param string $outputPath The path to save the downloaded file.
     * @return array
     */
    public function downloadFile($fileId, $outputPath)
    {
        $service = $this->auth->getDriveService();
        if (!$service) {
            return ["error" => "Not authenticated."];
        }

        try {
            $content = $service->files->get($fileId, ['alt' => 'media']);
            file_put_contents($outputPath, $content->getBody()->getContents());
            return ["success" => true, "path" => $outputPath];
        } catch (Exception $e) {
            return ["error" => $e->getMessage()];
        }
    }

    /**
     * Upload a file to Google Drive.
     *
     * @param string $filePath The path to the file to upload.
     * @param string $fileName The name of the file in Google Drive.
     * @param string $parentId The ID of the parent folder (optional).
     * @return array
     */
    public function uploadFile($filePath, $fileName, $parentId = null)
    {
        $service = $this->auth->getDriveService();
        if (!$service) {
            return ["error" => "Not authenticated."];
        }

        try {
            $fileMetadata = new Google\Service\Drive\DriveFile([
                'name' => $fileName,
                'parents' => $parentId ? [$parentId] : []
            ]);
            $content = file_get_contents($filePath);
            $file = $service->files->create($fileMetadata, [
                'data' => $content,
                'mimeType' => mime_content_type($filePath),
                'uploadType' => 'multipart'
            ]);
            return ["success" => true, "fileId" => $file->getId()];
        } catch (Exception $e) {
            return ["error" => $e->getMessage()];
        }
    }

    /**
     * Delete a file from Google Drive.
     *
     * @param string $fileId The ID of the file to delete.
     * @return array
     */
    public function deleteFile($fileId)
    {
        $service = $this->auth->getDriveService();
        if (!$service) {
            return ["error" => "Not authenticated."];
        }

        try {
            $service->files->delete($fileId);
            return ["success" => true];
        } catch (Exception $e) {
            return ["error" => $e->getMessage()];
        }
    }
}

