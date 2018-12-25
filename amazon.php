<?php

    use Symfony\Component\Console\Helper\QuestionHelper;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;
    use Symfony\Component\Console\Question\ConfirmationQuestion;

    require_once 'vendor/autoload.php';

$app = new Silly\Application();
$amazon = new BaBeuloula\AmazonAlert();

$app->command('product id [--add] [--remove]', function (
    string $id,
    bool $add,
    bool $remove,
    InputInterface $input,
    OutputInterface $output
) use ($amazon) {
    if (!$add && !$remove) {
        throw new \Exception("You need to specify an action (add or remove).");
    }

    if ($add) {
        $amazon->addProduct($id);
        $output->writeln("<info>Product #{$id} have been added.</info>");
    }

    if ($remove) {
        $helper = new QuestionHelper();
        $question = new ConfirmationQuestion("Continue with this action? [yes]/no\r\n", true);

        if ($helper->ask($input, $output, $question)) {
            $amazon->removeProduct($id);
            $output->writeln("\r\n<info>Product #{$id} have been removed.</info>");
        }
    }
})->descriptions("Add or remove a product from Amazon alert");

$app->command('check-prices', function (OutputInterface $output) use ($amazon) {
    $amazon->checkPrices();
    $amazon->sendPrices();

    $output->writeln("<info>All products have been checked and prices updated.</info>");
})->setDescription("Check prices of all products and send an email with prices");

$amazon->init();
$app->run();