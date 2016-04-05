# Tokenly Music API client.

Use this client to call the Tokenly Music API.

[![Build Status](https://travis-ci.org/tokenly/music-api-client.svg?branch=master)](https://travis-ci.org/tokenly/music-api-client.svg)


# Installation

### Add the package via composer

```bash
composer require tokenly/music-client
```


### Create and use the API client

```php
// login with the public API client
$public_tokenly_api = new Tokenly\APIClient\TokenlyAPI('https://music-stage.tokenly.com/api/v1');
$public_music_api = new Tokenly\MusicClient\MusicAPI($public_tokenly_api);
$user_details = $public_music_api->login('myusername', 'mypassword');
$client_id = $user_details['apiToken'];
$secret_key = $user_details['apiSecretKey'];

// Once you have the client id and key, you can use the protected API client to call protected methods
$protected_tokenly_api = new Tokenly\APIClient\TokenlyAPI('https://music.tokenly.com/api/v1', new Tokenly\HmacAuth\Generator(), $client_id, $secret_key);
$protected_music_api = new Tokenly\MusicClient\MusicAPI($protected_tokenly_api);
$songs_array = $protected_music_api->getMySongs();
```
