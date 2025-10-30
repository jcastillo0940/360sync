<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use App\Models\SyncConfiguration;

class FtpService
{
    protected $connection;
    protected $server;
    protected $port;
    protected $username;
    protected $password;
    protected $enabled;

    public function __construct()
    {
        $config = SyncConfiguration::getFtpConfig();
        
        $this->enabled = $config['enabled'];
        $this->server = $config['server'];
        $this->port = $config['port'];
        $this->username = $config['username'];
        $this->password = $config['password'];
    }

    /**
     * Conectar al servidor FTP
     */
    public function connect()
    {
        if (!$this->enabled) {
            return [
                'success' => false,
                'error' => 'FTP is disabled in configuration'
            ];
        }

        try {
            $this->connection = @ftp_connect($this->server, $this->port, 10);

            if (!$this->connection) {
                throw new \Exception("Could not connect to FTP server: {$this->server}:{$this->port}");
            }

            $login = @ftp_login($this->connection, $this->username, $this->password);

            if (!$login) {
                throw new \Exception("FTP authentication failed for user: {$this->username}");
            }

            // Activar modo pasivo
            ftp_pasv($this->connection, true);

            return [
                'success' => true,
                'message' => 'FTP connection established'
            ];

        } catch (\Exception $e) {
            Log::error('FTP Connection Error', [
                'server' => $this->server,
                'message' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Subir archivo al servidor FTP
     */
    public function uploadFile($localFile, $remoteFile)
    {
        if (!$this->enabled) {
            return [
                'success' => false,
                'error' => 'FTP is disabled'
            ];
        }

        // Conectar si no está conectado
        if (!$this->connection) {
            $connectResult = $this->connect();
            if (!$connectResult['success']) {
                return $connectResult;
            }
        }

        try {
            if (!file_exists($localFile)) {
                throw new \Exception("Local file not found: {$localFile}");
            }

            $upload = @ftp_put($this->connection, $remoteFile, $localFile, FTP_BINARY);

            if (!$upload) {
                throw new \Exception("Failed to upload file to FTP: {$remoteFile}");
            }

            Log::info('FTP Upload Success', [
                'local_file' => $localFile,
                'remote_file' => $remoteFile,
                'size' => filesize($localFile)
            ]);

            return [
                'success' => true,
                'message' => 'File uploaded successfully',
                'remote_file' => $remoteFile,
                'size' => filesize($localFile)
            ];

        } catch (\Exception $e) {
            Log::error('FTP Upload Error', [
                'local_file' => $localFile,
                'remote_file' => $remoteFile,
                'message' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Listar archivos en directorio FTP
     */
    public function listFiles($directory = '.')
    {
        if (!$this->connection) {
            $connectResult = $this->connect();
            if (!$connectResult['success']) {
                return $connectResult;
            }
        }

        try {
            $files = @ftp_nlist($this->connection, $directory);

            if ($files === false) {
                throw new \Exception("Could not list files in directory: {$directory}");
            }

            return [
                'success' => true,
                'files' => $files,
                'count' => count($files)
            ];

        } catch (\Exception $e) {
            Log::error('FTP List Files Error', [
                'directory' => $directory,
                'message' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Descargar archivo desde FTP
     */
    public function downloadFile($remoteFile, $localFile)
    {
        if (!$this->connection) {
            $connectResult = $this->connect();
            if (!$connectResult['success']) {
                return $connectResult;
            }
        }

        try {
            $download = @ftp_get($this->connection, $localFile, $remoteFile, FTP_BINARY);

            if (!$download) {
                throw new \Exception("Failed to download file from FTP: {$remoteFile}");
            }

            return [
                'success' => true,
                'message' => 'File downloaded successfully',
                'local_file' => $localFile
            ];

        } catch (\Exception $e) {
            Log::error('FTP Download Error', [
                'remote_file' => $remoteFile,
                'message' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Eliminar archivo del servidor FTP
     */
    public function deleteFile($remoteFile)
    {
        if (!$this->connection) {
            $connectResult = $this->connect();
            if (!$connectResult['success']) {
                return $connectResult;
            }
        }

        try {
            $delete = @ftp_delete($this->connection, $remoteFile);

            if (!$delete) {
                throw new \Exception("Failed to delete file from FTP: {$remoteFile}");
            }

            return [
                'success' => true,
                'message' => 'File deleted successfully'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Cerrar conexión FTP
     */
    public function disconnect()
    {
        if ($this->connection) {
            @ftp_close($this->connection);
            $this->connection = null;
        }
    }

    /**
     * Test de conexión FTP
     */
    public function testConnection()
    {
        $result = $this->connect();

        if ($result['success']) {
            $currentDir = @ftp_pwd($this->connection);
            $filesList = $this->listFiles('.');

            $this->disconnect();

            return [
                'success' => true,
                'message' => 'FTP connection test successful',
                'current_directory' => $currentDir,
                'files_count' => $filesList['count'] ?? 0
            ];
        }

        return $result;
    }

    /**
     * Verificar si FTP está habilitado
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * Destructor para cerrar conexión automáticamente
     */
    public function __destruct()
    {
        $this->disconnect();
    }
}