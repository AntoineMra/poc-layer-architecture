<?php

namespace App\Service\Ingenierie;


use App\Repository\BudgetRepository;
use App\Repository\InvestRepository;

interface DataStatisticsInterface
{
    /**
     * @return array<int>
     */
    public function getBudgetStatistics(BudgetRepository $repository): array;

    /**
     * @return array<int>
     */
    public function getInvestStatistics(InvestRepository $repository): array;

    /**
     * @return array<int>
     */
    public function getMainStatistics(): array;
}
