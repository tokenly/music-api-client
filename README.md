# Tokenly Music API client.

Use this client for the Tokenly Music API.


# Installation


### Add the package via composer

```
composer require tokenly/music-client
```


### Create a new client

```php
// login with the public API client
$public_tokenly_api = new Tokenly\APIClient\TokenlyAPI('https://music.tokenly.com/api/v1', new Tokenly\HmacAuth\Generator(), 'MY_CLIENT_ID', 'MY_CLIENT_SECRET');
$public_music_api = new MusicAPI($public_tokenly_api);
$user_details = $public_music_api->login('myusername', 'mypassword');
$client_id = $user_details['apiToken'];
$secret_key = $user_details['apiSecretKey'];

// now use the protected API client
$tokenly_api = new Tokenly\APIClient\TokenlyAPI('https://music.tokenly.com/api/v1', new Tokenly\HmacAuth\Generator(), $client_id, $secret_key);
$music_api = new MusicAPI($public_tokenly_api);
$songs_array = $music_api->get('music/music/mysongs');
```
