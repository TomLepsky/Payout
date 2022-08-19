<?php

namespace App\Model;

interface Solvent
{
    public function setAmount(float $amount) : self;

    public function getAmount() : ?float;

    public function getCurrency() : ?string;
}
