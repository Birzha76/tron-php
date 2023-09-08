English | [中文](./README-CN.md)

<h1 align="center">TRON-PHP</h1>

## Introduction

Support TRON's TRX and TRC20, which include functions such as address creation, balance query, transaction transfer, query the latest blockchain, query information based on the blockchain, and query information based on the transaction hash

## Advantage

1. One set of scripts is compatible with all TRX currencies and TRC20 certifications in the TRON network
1. Interface methods can be added or subtracted flexibly

## Support Method

- Generate address `generateAddress()`
- Verify address `validateAddress(Address $address)`
- Get the address according to the private key `privateKeyToAddress(string $privateKeyHex)`
- Check balances `balance(Address $address)`
- Transaction transfer (offline signature) `transfer(string $from, string $to, float $amount)`
- Query the latest block `blockNumber()`
- Query information according to the blockchain `blockByNumber(int $blockID)`
- *Query information based on transaction hash `transactionReceipt(string $txHash)`

## Quick Start

### Install

PHP8
``` php
composer require fenguoz/tron-php
```

or PHP7
``` php
composer require fenguoz/tron-php ~1.3
```

### Interface

``` php
use GuzzleHttp\Client;

$uri = 'https://api.trongrid.io';// mainnet
// $uri = 'https://api.shasta.trongrid.io';// shasta testnet
$api = new \Tron\Api(new Client(['base_uri' => $uri]));

$trxWallet = new \Tron\TRX($api);
$addressData = $trxWallet->generateAddress();
// $addressData->privateKey
// $addressData->address

$config = [
    'contract_address' => 'TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t',// USDT TRC20
    'decimals' => 6,
];
$trc20Wallet = new \Tron\TRC20($api, $config);
$addressData = $trc20Wallet->generateAddress();
```

## Plan

- Support TRC10
- Smart Contract

## Package

| Name | description | Scenes |
| :-----| :---- | :---- |
| [Fenguoz/tron-api](https://github.com/Fenguoz/tron-api) | TRON official document recommends PHP extension package | TRON basic API |

