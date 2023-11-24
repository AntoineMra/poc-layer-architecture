<?php

namespace App\Service\Ingenierie;

use App\Repository\BudgetRepository;
use App\Repository\InvestRepository;

class DataStatistics implements DataStatisticsInterface
{
    public function getBudgetStatistics(BudgetRepository $repository): array
    {
        $statistics = [];
        $extraction = $repository->findAll();
        /** @var Budget $extract */
        foreach ($extraction as $extract) {
            $statistics[] = $extract->getTransactionsTotalExpense();
        }

        return $statistics;
    }

    public function getInvestStatistics(InvestRepository $repository): array
    {
        $statistics = [];


        return $statistics;
    }

    public function getMainStatistics(): array
    {
        $statistics = [];


        return $statistics;
    }
}
