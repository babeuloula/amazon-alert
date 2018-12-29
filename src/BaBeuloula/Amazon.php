<?php

namespace BaBeuloula;

use ApaiIO\ApaiIO;
use ApaiIO\Configuration\GenericConfiguration;
use ApaiIO\Operations\Lookup;
use GuzzleHttp\Exception\GuzzleException;

/** @internal */
class Amazon
{
    /** @var \GuzzleHttp\Client */
    private $client;

    /** @var GenericConfiguration */
    private $conf;

    /** @var ApaiIO */
    private $apaiIO;

    public function __construct (string $country, string $accessKey, string $secretKey, string $associateTag)
    {
        $this->client = new \GuzzleHttp\Client();
        $this->conf = new GenericConfiguration();
        $request = new \ApaiIO\Request\GuzzleRequest($this->client);

        $this->conf
            ->setCountry($country)
            ->setAccessKey($accessKey)
            ->setSecretKey($secretKey)
            ->setAssociateTag($associateTag)
            ->setRequest($request);

        $this->apaiIO = new ApaiIO($this->conf);
    }

    public function find (string $code): array
    {
        $lookup = new Lookup();
        $lookup->setItemId($code);

        try {
            $response = $this->apaiIO->runOperation($lookup);
        } catch (GuzzleException $e) {
            throw $e;
        }

        var_dump($response);die;

        return [
            'title' => $title,
            'price' => $price,
            'image' => $image,
        ];
    }
}
