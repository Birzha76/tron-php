<?php

namespace Tron;

class Block
{
    public string $blockID;
    public array $block_header;
    public array $transactions;

    public function __construct(string $blockID, array $block_header, array $transactions = [])
    {
        if (!strlen($blockID)) {
            throw new \Exception('blockID empty');
        }

        $this->blockID = $blockID;
        $this->block_header = $block_header;
        $this->transactions = $transactions;
    }
}
