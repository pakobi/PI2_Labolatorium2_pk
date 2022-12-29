<?php

namespace Dropbox\Service;

use Laminas\Http\Client;
use Laminas\Json\Json;
use Laminas\Session\Container;


class Dropbox
{
    const API_URL = 'https://api.dropboxapi.com/2/';
    const CONTENT_URL = 'https://content.dropboxapi.com/2/';
    const REDIRECT_URI = 'http://localhost/pi2_Lab2_test/public/dropbox/finish';

    //const OLD_REDIRECT_URI = 'http://localhost/pi2/public/dropbox/finish'

    private Container $container;

    private array $config;

    public function __construct(array $config)
    {
        $this->container = new Container();
        $this->config = $config['dropbox'];
    }

    /**
     * Generates Dropbox authorization URL
     *
     * @return string
     */
    public function generateAuthorizationUrl(): string
    {
        return sprintf(
            "https://www.dropbox.com/oauth2/authorize?client_id=%s&redirect_uri=%s&response_type=code",
            $this->config['key'],
            self::REDIRECT_URI
        );
    }

    /**
     * Checks wheather user has Dropbox access token
     *
     * @return bool
     */
    public function isAuthorized(): bool
    {
        return isset($this->container->access_token);
    }

    /**
     * Gets Dropbox access token
     *
     * @param $authorizationCode
     * @return bool
     * @throws \Exception
     */
    public function getAccessToken($authorizationCode): bool
    {
        $client = new Client('https://api.dropboxapi.com/oauth2/token');
        $client->setMethod('post');
        $client->setParameterPost([
            'code' => $authorizationCode,
            'grant_type' => 'authorization_code',
            'client_id' => $this->config['key'],
            'client_secret' => $this->config['secret'],
            'redirect_uri' => self::REDIRECT_URI,
        ]);

        $response = $client->send();

        if ($response->isSuccess()) {
            $data = Json::decode($response->getBody());

            if (!empty($data->access_token)) {
                $this->container->access_token = $data->access_token;

                return true;
            }
        }

        throw new \Exception($response->getBody());
    }

    /**
     * Gets file list from a specified directory
     *
     * @param string $path
     * @return array
     * @throws \Exception
     */
    public function getFileList(string $path): array
    {
        $files = $this->sendRequest('/files/list_folder', ['path' => $path]);

        return $files['entries'];
    }
    
    /**
     * Delete file from a specified directory
     * @param string $fileName
     * @param string $path
     * @return array
     * @throws \Exception
     */
    public function fileToDelete(string $path): array
    {
        $deleted = $this->sendRequest('/files/delete_v2', ['path' => $path]);
        return $deleted['metadata'];
    }

    /**
     * Create new file with content in a specified directory
     * @param string $data
     * @param string $path
     * @return array
     * @throws \Exception
     */
    public function addFileDropbox($data, string $path): void
    {
        $fileName = $data->nazwaPliku;
        $fileContent = $data->trescPliku;
        $parameters = [ 'autorename' => false, 
                        'mode' => 'add', 
                        'mute' => false ,
                        'path' => $path . '/' . $fileName . '.txt',
                        'strict_conflict' => false
        ]; 

        $fileCreated = $this->sendRequestUploadFile('/files/upload', $fileContent, $parameters);
    }

    /**
     * Update file with content of the file in a specified directory
     * @param string $data
     * @param string $path
     * @return array
     * @throws \Exception
     */
    public function updateFileDropbox($data, string $path): void
    {
        $fileName = $data->nazwaPliku;
        $fileContent = $data->trescPliku;

        $parameters = [ 'autorename' => false, 
                        'mode' => 'overwrite', // Update content of the file
                        'mute' => false ,
                        'path' => '/' . $fileName,
                        'strict_conflict' => false
        ]; 

        $fileEdited = $this->sendRequestUploadFile('/files/upload', $fileContent, $parameters);
    }

    /**
     * Get http link to file content from specified directory
     * @param string $path
     * @return string
     * @throws \Exception
     */

    public function getFileContentDropbox(string $path): string
    {
        $link = $this->getFileLinkDropbox($path); // dostaje link

        $client = new Client(
            $link,
            [
                'timeout'   => 30,
            ]
        );
        $client->setRawBody($path);
        $response = $client->send(); // dostaje tablice z content
        if ($response->isSuccess()) {
            return $fileContent = $response->getBody();
        } else {
            throw new \Exception($response->getContent());
        }
    }

    /**
     * Download file with content into specified directory
     * @param string $data
     * @param string $path
     * @return array
     * @throws \Exception
     */
    public function getFileLinkDropbox(string $path): string
    {
        $fileDownload = $this->sendRequest('/files/get_temporary_link', ['path' => $path]);

        return $fileDownload['link'];
    }

    /**
     * Get full file content from specified directory
     * @param string $filename
     * @return array
     * @throws \Exception
     */
    
    public function downloadFileDropbox(string $fileName)
    {
        return $this->sendRequest('files/download', ['path' => $fileName]);
    }

    /**
     * Create folder in specified directory
     * @param string $folderName
     * @return array
     * @throws \Exception
     */

    public function createFolderDropbox(string $folderName)
    {
        return $this->sendRequest('/files/create_folder_v2', ['path' => $folderName]);
    }

    /**
     * Sends a request to Dropbox API
     *
     * @param string $function
     * @param array  $parameters
     * @return array
     * @throws \Exception
     */
    private function sendRequest(string $function, array $parameters = []): mixed
    {
        switch($function)
        {
        // https://www.dropbox.com/developers/documentation/http/documentation
                // Starts returning the contents of a folder
            case '/files/list_folder':
                // Delete the file or folder at a given path. If the path is a folder, all its contents will be deleted too
            case '/files/delete_v2':
                // Get a temporary link to stream content of a file
            case '/files/get_temporary_link':
                // Create a folder at a given path
            case '/files/create_folder_v2':

                $client = new Client(self::API_URL . $function);
                $client->setMethod('post');
                $client->setHeaders([
                    'Authorization' => 'Bearer ' . $this->container->access_token,
                    'Content-Type' => 'application/json',
                ]);
                $client->setRawBody(Json::encode($parameters));
                $response = $client->send();

                if ($response->isSuccess()) {
                    return Json::decode($response->getBody(), Json::TYPE_ARRAY);
                } else {
                    throw new \Exception($response->getContent());
                }
                
                // Download a file from a user's Dropbox
            case 'files/download':

                $client = new Client(self::CONTENT_URL . $function);
                $client->setMethod('post');
                $client->setHeaders([
                   'Authorization' => 'Bearer ' . $this->container->access_token,
                   'Dropbox-API-Arg' => Json::encode($parameters),
                   'Content-Type'=>'text/plain'
                ]);
        
                $response = $client->send();
                
                if ($response->isSuccess()) {
                    return $response->getBody();
                } else {
                    throw new \Exception($response->getContent());
                }
        }
    }

    /**
     * Creates file and upload file with its content in Dropbox API
     *
     * @param string $function
     * @param string $fileContent
     * @param array $parameters
     * @return array
     * @throws \Exception
     */
    public function sendRequestUploadFile(string $function, string $fileContent, array $parameters = []): array
    {
        //dd("Tu jestem w newFile", $parameters, $function, $fileContent);
        //$parameters = $fileContent;

        $client = new Client(self::CONTENT_URL . $function);
        $client->setMethod('post');
        $client->setHeaders([
           'Authorization' => 'Bearer ' . $this->container->access_token,
           'Dropbox-API-Arg' => Json::encode($parameters),
           'Content-Type' => 'application/octet-stream',           
        ]);

        $client->setRawBody($fileContent);
        //dd($client);
        $response = $client->send();
        //dd($response);
        if ($response->isSuccess()) {
            return Json::decode($response->getBody(), Json::TYPE_ARRAY);
        } else {
            throw new \Exception($response->getContent());
        }
    }

}
