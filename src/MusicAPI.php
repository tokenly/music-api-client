<?php

namespace Tokenly\MusicClient;

use Exception;

class MusicAPI
{

    function __construct($tokenly_api) {
        $this->tokenly_api = $tokenly_api;
    }
    

    function getAlbums() {
        return $this->tokenly_api->getPublic('music/catalog/albums');
    }

    function getSongs($album_id) {
        return $this->tokenly_api->getPublic('music/catalog/songs/'.$album_id);
    }

    function getMySongs() {
        return $this->tokenly_api->get('music/mysongs');
    }

    function getDownloadInfoForSong($song_id) {
        return $this->tokenly_api->get('music/song/download/'.$song_id);
    }

    function registerAccount($username, $password, $email) {
        return $this->tokenly_api->postPublic('account/register', [
            'username' => $username,
            'password' => $password,
            'email'    => $email,
        ]);
    }

    function login($username, $password) {
        return $this->tokenly_api->postPublic('account/login', [
            'username' => $username,
            'password' => $password,
        ]);
    }

    // ------------------------------------------------------------------------
    

}
