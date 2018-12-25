<?php

namespace BaBeuloula;

/** @internal */
class Amazon
{
    /** @var string */
    private $url = "https://www.amazon.fr/gp/product/%s";

    /** @var \GuzzleHttp\Client */
    private $client;

    public function __construct ()
    {
        $this->client = new \GuzzleHttp\Client();
    }

    public function find (string $code): array
    {
        $res = $this->client->request('GET', sprintf($this->url, $code));

        if ($res->getStatusCode() !== 200) {
            throw new \Exception("Unable to find product #{$code}");
        }

        $body = $res->getBody();

        $dom = new \DOMDocument();
        @$dom->loadHTML($body); // Mute all warnings

        $xpath = new \DOMXPath($dom);

        $title = trim($xpath->query('//span[@id="productTitle"]')[0]->nodeValue);
        $price = floatval(str_replace(["EUR", ","], ["", "."], trim($xpath->query('//span[@id="priceblock_dealprice"]')[0]->nodeValue)));
        $image = $xpath->query('//img[@id="landingImage"]')[0]->getAttribute('src');

        return [
            'title' => $title,
            'price' => $price,
            'image' => $image,
        ];
    }
}