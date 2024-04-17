<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{

    public ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }


    public function view()
    {
        return $this->reportService->getReports();
    }

    public function update(Request $request)
    {
        return $this->reportService->update($request);
    }

    public function claim(Request $request)
    {
        return $this->reportService->claim($request);
    }
}
