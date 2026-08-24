<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenerateReportRequest;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\View\View;

class ReportController extends Controller
{
    protected ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Display the report generation form.
     */
    public function index(): View
    {
        return view('reports.index');
    }

    /**
     * Generate the report data and export to PDF.
     */
    public function generate(GenerateReportRequest $request)
    {
        $start = Carbon::parse($request->start_date)->startOfDay();
        $end = Carbon::parse($request->end_date)->endOfDay();

        $data = $this->reportService->generate($start, $end);

        return $this->reportService->exportPdf($data);
    }
}
