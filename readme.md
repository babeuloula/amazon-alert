# Amazon alert

Amazon alert is an application to check price from a list of products.

It's based on the repo [Amazon-Alert](https://github.com/anfederico/Amazon-Alert) by [https://github.com/anfederico](anfederico), thanks to him.

## How to use

### Add a product

```bash
$ php amazon.php product --add PRODUCT_ID
```

With an URL like this `https://www.amazon.fr/gp/product/B01MEH71K2`, *PRODUCT_ID* will be `B01MEH71K2`.

### Remove a product

```bash
$ php amazon.php product --remove PRODUCT_ID
```

### Check price of all products

```bash
$ php amazon.php check-prices
```

You can use the command with a cron.

## Next features

- Use Amazon API to get information
- Create a container docker to run the application
- Add unit tests