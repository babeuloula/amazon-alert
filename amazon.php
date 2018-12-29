<?php

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

require_once 'vendor/autoload.php';

$app = new Silly\Application();
$amazon = new BaBeuloula\AmazonAlert();

$app->command('init', function () use ($amazon) {
    $amazon->init();
});

$app->command('product [--add] [--remove] id', function (
    string $id,
    bool $add,
    bool $remove,
    InputInterface $input,
    OutputInterface $output
) use ($amazon) {
    $this->runCommand('init');

    if (!$add && !$remove) {
        throw new \Exception("You need to specify an action (add or remove).");
    }

    if ($add) {
        $amazon->addProduct($id);
        $output->writeln("<info>Product #{$id} have been added.</info>");
    }

    if ($remove) {
        $helper = $this->getHelperSet()->get('question');
        $question = new ConfirmationQuestion("Are you sure? [yes]/no\r\n", true);

        if ($helper->ask($input, $output, $question)) {
            $amazon->removeProduct($id);
            $output->writeln("\r\n<info>Product #{$id} have been removed.</info>");
        }
    }
})->descriptions("Add or remove a product from Amazon alert");

$app->command('check-prices [--period=]', function (OutputInterface $output, int $period = 30) use ($amazon) {
    $this->runCommand('init');

    $amazon->checkPrices();
    $amazon->sendPrices($period);

    $output->writeln("<info>All products have been checked and prices updated.</info>");
})->setDescription("Check prices of all products and send an email with prices");

$app->run();
