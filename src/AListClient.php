<?php

namespace As247\AList;
use GuzzleHttp\Client;

/**
 * @method fsMkdir(array $body = [], array $headers = [])
 * @method fsRename(array $body = [], array $headers = [])
 * @method fsForm(array $body = [], array $headers = [])
 * @method fsList(array $body = [], array $headers = [])
 * @method fsGet(array $body = [], array $headers = [])
 * @method fsLink(array $body = [], array $headers = [])
 * @method fsSearch(array $body = [], array $headers = [])
 * @method fsDirs(array $body = [], array $headers = [])
 * @method fsBatchRename(array $body = [], array $headers = [])
 * @method fsRegexRename(array $body = [], array $headers = [])
 * @method fsMove(array $body = [], array $headers = [])
 * @method fsRecursiveMove(array $body = [], array $headers = [])
 * @method fsCopy(array $body = [], array $headers = [])
 * @method fsRemove(array $body = [], array $headers = [])
 * @method fsRemoveEmptyDirectory(array $body = [], array $headers = [])
 * @method fsPut($body, array $headers = [])
 * @method fsAddAria2(array $body = [], array $headers = [])
 * @method fsAddQbit(array $body = [], array $headers = [])
 */
class AListClient
{
    protected $address;
    protected $proxyUrl;
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
        "/api/fs/link" => "POST",
    ];
    protected $apiMethods=[];

    public function __construct($address, $token, $options = [])
    {
        $this->address = rtrim($address,'/');
        $this->token = $token;
        if (!isset($this->options['headers'])) {
            $this->options['headers'] = [];
        }
        if (!isset($this->options['headers']['Authorization'])) {
            $this->options['headers']['Authorization'] = $this->token;
        }
        if(isset($this->options['proxy_url'])){
            $this->setProxyUrl($this->options['proxy_url']);
        }
        $clientOptions=$this->options['client']??[];
        $clientOptions['base_uri']=$this->address;
        $clientOptions['headers']=$this->options['headers'];

        $this->client = new Client($clientOptions);
        foreach ($this->apiList as $path => $method) {
            $method=str_replace('/api/', '', $path);
            $method=str_replace('/', '_', $method);
            $method=strtolower($method);
            $methodCamelCase=lcfirst(str_replace('_', '', ucwords($method, '_')));
            $this->apiMethods[$methodCamelCase] = $path;
        }

    }
    public function setProxyUrl($url)
    {
        $this->proxyUrl=rtrim($url,'/');
        return $this;
    }
    public function getBaseUrl()
    {
        return $this->address;
    }
    public function getGuzzleClient()
    {
        return $this->client;
    }
    public function getDownloadUrl($path,$expire=0)
    {
        $path='/'.ltrim($path,'/');
        $sign=$this->sign($path,$expire);
        $base=$this->proxyUrl?:$this->address.'/d';
        return $base.$path.'?sign='.$sign;
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
            if($method==='PUT'){
                if($path==='/api/fs/put'){
                    //Put file with body content
                    $response = $this->client->request('PUT', $path, ['body' => $body, 'headers' => $headers]);
                }
                if($path==='/api/fs/form'){
                    //Put file with form data
                    $response = $this->client->request('PUT', $path, ['multipart'=>[
                        [
                            'name'=>'file',
                            'content'=>$body
                        ]
                    ], 'headers' => $headers]);
                }
            }
            if(!isset($response)) {
                $response = $this->client->request($method, $path, ['json' => $body, 'headers' => $headers]);
            }
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