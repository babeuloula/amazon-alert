<?php

namespace BaBeuloula;

use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;

class AmazonAlert
{
    private $sqlite = "amazon-alert.sqlite";

    /** @var array */
    private $settings;

    /** @var DB */
    private $db;

    /** @var Amazon */
    private $amazon;

    /**
     * Init application
     */
    public function init (): void
    {
        $root = dirname(dirname(__DIR__));

        $this->settings = parse_ini_file($root."/settings.ini", true, INI_SCANNER_TYPED);
        $this->settings['folders'] = [
            'root' => $root,
            'db'   => $root . "/db",
        ];

        if (!is_file($this->settings['folders']['db']."/".$this->sqlite)) {
            file_put_contents($this->settings['folders']['db']."/".$this->sqlite, "");
        }

        $this->db = new DB($this->settings['folders']['db']."/".$this->sqlite);

        $this->amazon = new Amazon();
    }

    /**
     * Add a product to Amazon alert
     *
     * @param string $code
     *
     * @throws \Exception
     */
    public function addProduct (string $code): void
    {
        $data = $this->amazon->find($code);

        if (!empty($this->db->find('products', $code))) {
            throw new \Exception("A product with the same code #{$code} already exist!");
        }

        $now = (new \DateTime())->getTimestamp();

        $this->db->add('products', [
            'code',
            'title',
            'image',
            'created_at',
        ], [
            $code,
            $data['title'],
            $data['image'],
            $now,
        ]);

        $this->db->add('prices', [
            'product_code',
            'price',
            'created_at',
        ], [
            $code,
            $data['price'],
            $now,
        ]);
    }

    /**
     * Remove a product from Amazon alert
     *
     * @param string $code
     *
     * @throws \Exception
     */
    public function removeProduct (string $code): void
    {
        $this->db->remove('products', 'code', $code);
        $this->db->remove('prices', 'product_code', $code);
    }

    /**
     * Check prices of all products on Amazon
     *
     * @throws \Exception
     */
    public function checkPrices (): void
    {
        $now = (new \DateTime())->getTimestamp();

        $failedProducts = [];
        foreach ($this->db->findAllProducts() as $product) {
            try {
                $data = $this->amazon->find($product->code);

                $this->db->add('prices', [
                    'product_code',
                    'price',
                    'created_at',
                ], [
                    $product->code,
                    $data['price'],
                    $now,
                ]);
            } catch (\Exception $e) {
                $failedProducts[] = $product->code;
            }
        }

        if (!empty($failedProducts)) {
            $body = "";
            $this->sendEmail($body, true);
        }
    }

    /**
     * Send an email with the prices of all products from the last 30 days
     *
     * @throws \Exception
     */
    public function sendPrices (): void
    {
        $to = new \DateTime();
        $from = clone $to;
        $from->modify("-30 days");

        $body = "";
        foreach ($this->db->findAll($from, $to) as $product) {
            // TODO create body email
        }

        $this->sendEmail($body);
    }

    /**
     * Send the emails
     *
     * @param string $body
     * @param bool   $error
     */
    private function sendEmail (string $body, bool $error = false): void
    {
        $transport = new Swift_SmtpTransport($this->settings['email']['smtp'], $this->settings['email']['smtp_port']);
        $transport->setUsername($this->settings['email']['smtp_user'])
                  ->setPassword($this->settings['email']['smtp_password']);

        $mailer = new Swift_Mailer($transport);

        $message = (new Swift_Message(($error) ? "Amazon Alert Error" : "Amazon Alert"))
            ->setFrom($this->settings['email']['from'])
            ->setTo($this->settings['email']['to'])
            ->setBody($body);

        $mailer->send($message);
    }
}