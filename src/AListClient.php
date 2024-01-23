<?php

namespace As247\AList;
use GuzzleHttp\Client;

/**
 * @method fsMkdir(array $body = [], array $headers = [])
 * @method fsRename(array $body = [], array $headers = [])
 * @method fsForm(array $body = [], array $headers = [])
 * @method fsList(array $body = [], array $headers = [])
 * @method fsGet(array $body = [], array $headers = [])
 * @method fsSearch(array $body = [], array $headers = [])
 * @method fsDirs(array $body = [], array $headers = [])
 * @method fsBatchRename(array $body = [], array $headers = [])
 * @method fsRegexRename(array $body = [], array $headers = [])
 * @method fsMove(array $body = [], array $headers = [])
 * @method fsRecursiveMove(array $body = [], array $headers = [])
 * @method fsCopy(array $body = [], array $headers = [])
 * @method fsRemove(array $body = [], array $headers = [])
 * @method fsRemoveEmptyDirectory(array $body = [], array $headers = [])
 * @method fsPut(array $body = [], array $headers = [])
 * @method fsAddAria2(array $body = [], array $headers = [])
 * @method fsAddQbit(array $body = [], array $headers = [])
 */
class AListClient
{
    protected $address;
    protected $token;
    protected $options;
    protected $client;
    protected $apiList = [
        "/api/fs/mkdir" => "POST",
        "/api/fs/rename" => "POST",
        "/api/fs/form" => "PUT",
        "/api/fs/list" => "POST",
        "/api/fs/get" => "POST",
        "/api/fs/search" => "POST",
        "/api/fs/dirs" => "POST",
        "/api/fs/batch_rename" => "POST",
        "/api/fs/regex_rename" => "POST",
        "/api/fs/move" => "POST",
        "/api/fs/recursive_move" => "POST",
        "/api/fs/copy" => "POST",
        "/api/fs/remove" => "POST",
        "/api/fs/remove_empty_directory" => "POST",
        "/api/fs/put" => "PUT",
        "/api/fs/add_aria2" => "POST",
        "/api/fs/add_qbit" => "POST",
    ];
    protected $apiMethods=[];

    public function __construct($address, $token, $options = [])
    {
        $this->address = $address;
        $this->token = $token;
        $default = [
            'base_uri' => $address,
        ];
        $this->options = $options + $default;
        if (!isset($this->options['headers'])) {
            $this->options['headers'] = [];
        }
        if (!isset($this->options['headers']['Authorization'])) {
            $this->options['headers']['Authorization'] = $this->token;
        }
        $this->client = new Client($this->options);
        foreach ($this->apiList as $path => $method) {
            $method=str_replace('/api/', '', $path);
            $method=str_replace('/', '_', $method);
            $method=strtolower($method);
            $methodCamelCase=lcfirst(str_replace('_', '', ucwords($method, '_')));
            $this->apiMethods[$methodCamelCase] = $path;
        }

    }

    public function sign($path,$expire)
    {
        $toSign = $path . ':' . $expire;
        $signature=hash_hmac('sha256', $toSign, $this->token, true);
        $urlSafeBase64 = str_replace(['+', '/'], ['-', '_'], base64_encode($signature));
        return $urlSafeBase64 . ':' . $expire;
    }

    /**
     * @param $path
     * @param $body
     * @param $headers
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    public function send($path, $body = [], $headers = [])
    {
        if(isset($this->apiList[$path])){
            $method=$this->apiList[$path];
            $response = $this->client->request($method, $path, ['json' => $body, 'headers' => $headers]);
            return $this->parseResponse($response);
        }else{
            throw new \Exception("Api path [$path] not found");
        }

    }

    /**
     * @param $path
     * @param $body
     * @param $headers
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function post($path, $body = [], $headers = [])
    {
        return $this->request('POST', $path, ['json' => $body, 'headers' => $headers]);
    }

    /**
     * @param $path
     * @param $body
     * @param $headers
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function put($path, $body = [], $headers = [])
    {
        return $this->request('PUT', $path, ['json' => $body, 'headers' => $headers]);
    }

    /**
     * @param $path
     * @param $query
     * @param $headers
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function get($path, $query = [], $headers = [])
    {
        return $this->request('GET', $path, ['query' => $query, 'headers' => $headers]);
    }

    /**
     * @param $method
     * @param $path
     * @param $options
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function request($method, $path, $options = [])
    {
        $response = $this->client->request($method, $path, $options);
        return $this->parseResponse($response);
    }

    public function parseResponse($response)
    {
        $body = $response->getBody();
        return json_decode($body, true);
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    public function __call($name, $arguments)
    {
        if(strpos($name,'_')!==false){
            $nameCamelCase=lcfirst(str_replace('_', '', ucwords($name, '_')));
        }else{
            $nameCamelCase=$name;
        }
        if(isset($this->apiMethods[$nameCamelCase])) {
            $path = $this->apiMethods[$nameCamelCase];
            return $this->send($path, ...$arguments);
        }
        throw new \Exception("Method $nameCamelCase not found");
    }
}