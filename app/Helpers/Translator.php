<?php

// app/Helpers/LibreTranslateHelper.php

namespace App\Helpers;

use GuzzleHttp\Client;

class Translator
{
    protected $baseUrl = 'https://api.mymemory.translated.net/get';

    public function translate($text, $targetLanguage)
    {
        $client = new Client([
            'base_uri' => $this->baseUrl,
        ]);

        $response = $client->get('', [
            'query' => [
                'q' => $text,
                'langpair' => 'en|' . $targetLanguage,
                'de' => 'abduxalilovmuhammadali1@gmail.com'
            ],
        ]);

        $data = json_decode($response->getBody(), true);

        if (isset($data['responseData']['translatedText'])) {
            return $data['responseData']['translatedText'];
        } else {
            return '';
        }
    }
}
