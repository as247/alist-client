# PHP Api Client for AList

## Installation

```bash
composer require as247/alist-client
```

## Usage

### Make a request

You can call get, post, put on the client to make a request.

```php
$client = new \As247\AList\AListClient($url, $token);

$client->post('/api/fs/list', ['path' => '/']);
$client->put('/api/fs/form', ['path' => '/test']);
//Auto determine method
$client->send($path, $data, $headers);
```

You also can use the helper method to make a request.

```php
$client->fsList(['path' => '/']);
//List all available helper method
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
```

### Sign a request

You can use `sign()` method to sign a request.

```php
$client = new \As247\AList\AListClient($url, $token);
$client->sign($path, $expire);
```
