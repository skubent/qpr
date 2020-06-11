<?php

namespace QuizProMaker;

use Exception;

class ApiClient {

    private const SERVER = 'https://api.quizy.pro';

    private const AUTH_URL = '/api/v1/login/auth';
    private const GAME_URL = '/api/v1/cabinet/interaction';

    private const LOGIN    = 'skubent@gmail.com';
    private const PASSWORD = 'Sk554571';

    /** @var string */
    private $authToken;

    /** @var bool */
    public static $testMode = false;

    public function __construct() {
        $this->authMe(static::LOGIN, static::PASSWORD);
    }

    public function addGame(GameDescription $game): int {
        $answer = $this->doPost(self::GAME_URL . '/', $game->getAsJson(), true);
        $obj    = json_decode($answer);
        return $obj->data->id;
    }

    private function authMe(string $login, string $password): void {
        $jsonAnswer = $this->doPost(self::AUTH_URL, json_encode(['email' => $login, 'password' => $password]), false);
        $obj = json_decode($jsonAnswer);
        $this->authToken = $obj->data->token ?? null;
        if (!$this->authToken) {
            throw new Exception('cant get auth token');
        }
    }

    public function loadGame(int $gameId): string {
        return $this->doGet(self::GAME_URL . '/' . $gameId, true);
    }

    private function doCurl(string $url, array $options): string {
        $curl = curl_init(self::SERVER . $url);
        curl_setopt_array($curl, $options);
        if (self::$testMode) {
            $rv = var_export($options, true);
        } else {
            $resultZipped = curl_exec($curl);
            if (!$resultZipped) {
                throw new Exception('bad answer found...');
            }
            $rv = gzdecode($resultZipped);
        }
        return $rv;
    }

    private function doGet(string $url, bool $addAuthToken): string {
        $headers = $this->getCommonHeaders();
        if ($addAuthToken) {
            $headers[] = 'Authorization: Bearer ' . $this->authToken;
        }
        $curlOptions = [
            CURLOPT_CUSTOMREQUEST  => 'GET',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_VERBOSE        => false,
            CURLOPT_HTTPHEADER     => $headers,
        ];
        return $this->doCurl($url, $curlOptions);
    }

    private function getCommonHeaders(): array {
        return [
            'Host: api.quizy.pro',
            'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:74.0) Gecko/20100101 Firefox/74.0',
            'Accept: application/json, text/plain, */*',
            'Accept-Language: en-US,en;q=0.5',
            'Accept-Encoding: gzip, deflate, br',
            'Content-Type: application/json;charset=utf-8',
            'Origin: https://quizy.pro',
            'DNT: 1',
            'Connection: keep-alive',
            'Referer: https://quizy.pro/login',
        ];
    }

    private function fileUpload(string $url, string $fileName, string $name): string {
        $boundary = "---------------------" . md5(mt_rand() . microtime());

        $payload = "--" . $boundary . "\r\n";
        $payload .= "Content-Disposition: form-data; name=\"{$name}\"; filename=\"".basename($fileName)."\"\r\n";
        $payload .= "Content-Type: image/png\r\n\r\n";
        $payload .= file_get_contents($fileName) . "\r\n";
        $payload .= "--" . $boundary . "--\r\n";

        $headers = [
            'Host: api.quizy.pro',
            'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:74.0) Gecko/20100101 Firefox/74.0',
            'Accept: application/json, text/plain, */*',
            'Accept-Language: en-US,en;q=0.5',
            'Accept-Encoding: gzip, deflate, br',
            'Content-Type: multipart/form-data; boundary='.$boundary,
            'Origin: https://quizy.pro',
            'DNT: 1',
            'Connection: keep-alive',
            'Referer: https://quizy.pro/login',
            'Content-Length: ' . strlen($payload),
            'Authorization: Bearer ' . $this->authToken,
        ];

        $curlOptions = [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_VERBOSE        => false,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => $headers,
        ];

        return $this->doCurl($url, $curlOptions);
    }

    public function uploadAnswerFile($gameId, $questionId, $pictureFileName): string {
        $url = '/api/v1/cabinet/interaction/' . $gameId . '/round/' . $questionId . '/file/answer_media';
        return $this->fileUpload($url, $pictureFileName, 'answer_media');
    }

    public function uploadQuestionFile($gameId, $questionId, $pictureFileName): string {
        $url = '/api/v1/cabinet/interaction/' . $gameId . '/round/' . $questionId . '/file/question_media';
        return $this->fileUpload($url, $pictureFileName, 'question_media');
    }

    private function doPost(string $url, string $payload, bool $addAuthToken): string {
        $headers = $this->getCommonHeaders();
        if ($payload) {
            $headers[] = 'Content-Length: ' . strlen($payload);
        }
        if ($addAuthToken) {
            $headers[] = 'Authorization: Bearer ' . $this->authToken;
        }
        $curlOptions = [
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_VERBOSE        => false,
            CURLOPT_HTTPHEADER     => $headers,
        ];
        if ($payload) {
            $curlOptions[CURLOPT_POSTFIELDS] = $payload;
        }
        return $this->doCurl($url, $curlOptions);
    }
}
