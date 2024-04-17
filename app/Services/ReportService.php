<?php


namespace App\Services;

use App\Repositories\BanRepository;
use App\Repositories\ReportRepository;
use App\Repositories\WarnRepository;

class ReportService
{

    private ReportRepository $reportRepository;

    public function __construct(ReportRepository $reportRepository)
    {
        $this->reportRepository = $reportRepository;
    }

    public function getReports()
    {
        return $this->reportRepository->getReports();
    }


    public function add($data)
    {
        return $this->reportRepository->add($data);
    }

    public function update($data)
    {
        return $this->reportRepository->update($data);
    }

    public function claim($data)
    {
        return $this->reportRepository->claim($data);
    }

    public function delete($id)
    {
        return $this->reportRepository->delete($id);
    }
}