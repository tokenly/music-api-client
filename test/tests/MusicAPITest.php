<?php

use Tokenly\HmacAuth\Generator;
use Tokenly\MusicClient\MusicAPI;
use \PHPUnit_Framework_Assert as PHPUnit;

class MusicAPITest extends PHPUnit_Framework_TestCase
{

    public function testPublicAPICalls() {
        $tokenly_api = Phake::partialMock('Tokenly\APIClient\TokenlyAPI', 'https://127.0.0.1/api/v1');
        Phake::when($tokenly_api)
            ->fetchFromAPI('GET', 'https://127.0.0.1/api/v1/music/catalog/albums', [], ['public' => true])
            ->thenReturn($this->sampleAlbumsData([1,2]));
        Phake::when($tokenly_api)
            ->fetchFromAPI('GET', 'https://127.0.0.1/api/v1/music/catalog/songs/xxxx-album-0001', [], ['public' => true])
            ->thenReturn($this->sampleSongsData([1,2,3]));
        Phake::when($tokenly_api)
            ->fetchFromAPI('POST', 'https://127.0.0.1/api/v1/account/register', ['username' => 'myusername', 'password' => 'mypassword', 'email' => 'myemail@myemail.com'], ['public' => true])
            ->thenReturn($this->sampleUserData());
        Phake::when($tokenly_api)
            ->fetchFromAPI('POST', 'https://127.0.0.1/api/v1/account/login', ['username' => 'myusername', 'password' => 'mypassword'], ['public' => true])
            ->thenReturn($this->sampleUserData(2));

        $music_api = new MusicAPI($tokenly_api);

        $result = $music_api->getAlbums();
        PHPUnit::assertEquals($this->sampleAlbumsData([1,2]), $result);
        $result = $music_api->getSongs('xxxx-album-0001');
        PHPUnit::assertEquals($this->sampleSongsData([1,2,3]), $result);

        $result = $music_api->registerAccount('myusername', 'mypassword', 'myemail@myemail.com');
        PHPUnit::assertEquals($this->sampleUserData(), $result);
        $result = $music_api->login('myusername', 'mypassword');
        PHPUnit::assertEquals($this->sampleUserData(2), $result);
    } 

    public function testProtectedAPICalls() {
        $called_vars = $this->callProtectedMusicAPI(function($music_api) {
            return $music_api->getMySongs();
        }, 'music/mysongs');

        // GET /music/song/download/{song}
        $called_vars = $this->callProtectedMusicAPI(function($music_api) {
            return $music_api->getDownloadInfoForSong('xxxx-song-0001');
        }, 'music/song/download/xxxx-song-0001');
    }

    // ------------------------------------------------------------------------
    
    protected function sampleAlbumsData($numbers) {
        $out = [];
        if (!is_array($numbers)) { $numbers = [$numbers]; }
        foreach($numbers as $number) {
            $out[] = ['id' => 'xxxx-album-'.sprintf('%04d', $number), 'name' => 'Album '.$number,];
        }
        return $out;
    }

    protected function sampleSongsData($numbers) {
        $out = [];
        if (!is_array($numbers)) { $numbers = [$numbers]; }
        foreach($numbers as $number) {
            $out[] = ['id' => 'xxxx-song-'.sprintf('%04d', $number), 'name' => 'Song '.$number,];
        }
        return $out;
    }

    protected function sampleUserData($number=1) {
        return [
            'id'           => 'xxx-user-'.sprintf('%04d', $number),
            'username'     => 'username'.sprintf('%04d', $number),
            'email'        => 'user'.sprintf('%04d', $number).'@test.com',
            'apiToken'     => 'Txxxx'.sprintf('%04d', $number),
            'apiSecretKey' => 'Kxxxxxxxxxxxx'.sprintf('%04d', $number),
        ];
    }


    protected function callProtectedMusicAPI($call_fn, $expected_url_extension, $expected_params=[], $sample_response=null) {
        if ($sample_response === null) { $sample_response = ['foo' => 'bar']; }

        $generator = new Generator();

        // set up the mock to check headers generated
        $tokenly_api = Phake::partialMock('Tokenly\APIClient\TokenlyAPI', 'https://127.0.0.1/api/v1', $generator, 'MY_CLIENT_ID', 'MY_CLIENT_SECRET');
        $called_vars = [];
        Phake::when($tokenly_api)
            ->callRequest(Phake::anyParameters())
            ->thenReturnCallback(function($url, $headers, $request_params, $method, $request_options) use ($sample_response, &$called_vars) {
                $called_vars = [];

                $response = new Requests_Response();
                $response->body = json_encode($sample_response);
                $called_vars['headers'] = $headers;
                $called_vars['url']     = $url;
                $called_vars['params']  = $request_params;
                return $response;
            });

        $music_api = new MusicAPI($tokenly_api);

        // check API call
        $result = $call_fn($music_api);
        PHPUnit::assertEquals($sample_response, $result);

        // check called URL
        PHPUnit::assertEquals('https://127.0.0.1/api/v1/'.$expected_url_extension, $called_vars['url']);

        // check headers
        $headers_generated = $called_vars['headers'];
        PHPUnit::assertNotEmpty($headers_generated);
        $nonce = $headers_generated['X-TOKENLY-AUTH-NONCE'];
        PHPUnit::assertGreaterThanOrEqual(time(), $nonce);
        PHPUnit::assertEquals('MY_CLIENT_ID', $headers_generated['X-TOKENLY-AUTH-API-TOKEN']);
        $expected_signature = $this->expectedSignature($nonce, 'https://127.0.0.1/api/v1/'.$expected_url_extension, $expected_params);
        PHPUnit::assertEquals($expected_signature, $headers_generated['X-TOKENLY-AUTH-SIGNATURE']);

        // return the called vars
        return $called_vars;
    }

    protected function expectedSignature($nonce, $url='https://127.0.0.1/api/v1/method/one', $params=['foo' => 'bar']) {
        $str = "GET\n{$url}\n".json_encode((array)$params, JSON_UNESCAPED_SLASHES | JSON_FORCE_OBJECT)."\nMY_CLIENT_ID\n".$nonce;
        return base64_encode(hash_hmac('sha256', $str, 'MY_CLIENT_SECRET', true));
    }

}
