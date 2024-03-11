<?php

namespace Tron;

use IEXBase\TronAPI\Exception\TronException;
use Tron\Exceptions\TransactionException;
use Tron\Exceptions\TronErrorException;
use Tron\Support\Formatter;
use Tron\Support\Utils;
use InvalidArgumentException;

class TRC20 extends TRX
{
    protected $contractAddress;
    protected $decimals;

    public function __construct(Api $_api, array $config)
    {
        parent::__construct($_api, $config);

        $this->contractAddress = new Address(
            $config['contract_address'],
            '',
            $this->tron->address2HexString($config['contract_address'])
        );
        $this->decimals = $config['decimals'];
    }

    public function balance(Address $address)
    {
        $format = Formatter::toAddressFormat($address->hexAddress);
        $body = $this->_api->post('/wallet/triggersmartcontract', [
            'contract_address' => $this->contractAddress->hexAddress,
            'function_selector' => 'balanceOf(address)',
            'parameter' => $format,
            'owner_address' => $address->hexAddress,
        ]);

        if (isset($body->result->code)) {
            throw new TronErrorException(hex2bin($body->result->message));
        }

        try {
            $balance = Utils::toDisplayAmount(hexdec($body->constant_result[0]), $this->decimals);
        } catch (InvalidArgumentException $e) {
            throw new TronErrorException($e->getMessage());
        }
        return $balance;
    }

    public function transfer(Address $from, Address $to, float $amount, $feeMultiplier = 1.1): Transaction
    {
        $functionSelector = 'transfer(address,uint256)';

        $this->tron->setAddress($from->address);
        $this->tron->setPrivateKey($from->privateKey);

        $toFormat = Formatter::toAddressFormat($to->hexAddress);
        try {
            $amount = Utils::toMinUnitByDecimals($amount, $this->decimals);
        } catch (InvalidArgumentException $e) {
            throw new TronErrorException($e->getMessage());
        }
        $numberFormat = Formatter::toIntegerFormat($amount);
        $parameter = "{$toFormat}{$numberFormat}";

        $energyUsed = $this->estimateEnergyToTriggerContract($from, $functionSelector, $parameter);
        $feeLimit = round($this->energyUnitPrice * $energyUsed * $feeMultiplier);

        $body = $this->_api->post('/wallet/triggersmartcontract', [
            'contract_address' => $this->contractAddress->hexAddress,
            'function_selector' => $functionSelector,
            'parameter' => $parameter,
            'fee_limit' => $feeLimit,
            'call_value' => 0,
            'owner_address' => $from->hexAddress,
        ], true);

        if (isset($body['result']['code'])) {
            throw new TransactionException(hex2bin($body['result']['message']));
        }

        try {
            $tradeobj = $this->tron->signTransaction($body['transaction']);
            $response = $this->tron->sendRawTransaction($tradeobj);
        } catch (TronException $e) {
            throw new TransactionException($e->getMessage(), $e->getCode());
        }

        if (isset($response['result']) && $response['result'] == true) {
            return new Transaction(
                $body['transaction']['txID'],
                $body['transaction']['raw_data'],
                'PACKING'
            );
        } else {
            throw new TransactionException(hex2bin($response['result']['message']));
        }
    }

    public function estimateEnergyToTriggerContract(Address $from, $functionSelector, $parameter)
    {
        $this->tron->setAddress($from->address);
        $this->tron->setPrivateKey($from->privateKey);

        $body = $this->_api->post('/wallet/triggerconstantcontract', [
            'contract_address' => $this->contractAddress->hexAddress,
            'function_selector' => $functionSelector,
            'parameter' => $parameter,
            'owner_address' => $from->hexAddress,
        ], true);

        if (isset($body['result']['code'])) {
            throw new TransactionException(hex2bin($body['result']['message']));
        }

        if (isset($body['result']) && $body['result'] == true && isset($body['energy_used'])) {
            return $body['energy_used'];
        } else {
            throw new TronErrorException(hex2bin($body['result']['message']));
        }
    }
}
