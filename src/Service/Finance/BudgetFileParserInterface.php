<?php

namespace App\Service\Finance;

use App\Entity\BankExtraction;

interface BudgetFileParserInterface
{
    /**
     * @return array<int>
     */
    public function parse(BankExtraction $bankExtraction): array;

}
