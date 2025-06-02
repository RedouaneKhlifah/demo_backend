<?php 

namespace App\Services;

use App\Repositories\FactureRepository;
use App\Repositories\OrderRepository;
use Carbon\Carbon;

class DashboardService
{
    protected $factureRepo;

    protected $orderRepo;

    public function __construct(FactureRepository $factureRepo, OrderRepository $orderRepo)
    {
        $this->factureRepo = $factureRepo;
        $this->orderRepo = $orderRepo;
    }

    public function getDashboardData($startDate, $endDate): array
    {
        $startDate = Carbon::parse($startDate)->startOfDay(); // Ensure time starts at 00:00:00
        $endDate = Carbon::parse($endDate)->endOfDay(); 

        return [
            'factures_partial' => $this->factureRepo->getPartialStatusData($startDate, $endDate),
            'paid_amount_partial_complete' => $this->factureRepo->getPaidPartialCompleteSum($startDate, $endDate),
            'profit' => $this->factureRepo->getProfit($startDate, $endDate),
            'revenue' => $this->factureRepo->getRevenue($startDate, $endDate),
            'top_products_by_quantity' => $this->orderRepo->getTopProductsBySoldQuantity($startDate, $endDate),
            'facturesForChart' => $this->factureRepo->getFacturesForChart($startDate, $endDate)
        ];
    }
}