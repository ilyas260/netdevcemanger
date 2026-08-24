<?php

namespace App\Http\Controllers;

use App\Models\ErrorLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ErrorLogController extends Controller
{
    /**
     * Display filtered listing of error logs.
     */
    public function index(Request $request): View
    {
        return view('error-logs.index');
    }

    /**
     * Mark an error as resolved.
     */
    public function resolve(Request $request, ErrorLog $errorLog): RedirectResponse
    {
        $request->validate([
            'resolution_note' => 'required|string|max:1000',
        ]);

        $errorLog->update([
            'is_resolved' => true,
            'resolved_at' => Carbon::now(),
            'resolved_by' => auth()->id(),
            'resolution_note' => $request->resolution_note,
        ]);

        return back()->with('success', "L'erreur #{$errorLog->id} a été marquée comme résolue.");
    }

    /**
     * Export logs to CSV.
     */
    public function export(): StreamedResponse
    {
        $fileName = 'error_logs_' . date('Y-m-d_H-i') . '.csv';
        $logs = ErrorLog::with('device')->latest('logged_at')->get();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['ID', 'Date', 'Appareil', 'IP', 'Sévérité', 'Message', 'Source', 'Statut'];

        $callback = function() use($logs, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->logged_at->format('Y-m-d H:i:s'),
                    $log->device->name,
                    $log->device->ip_address,
                    $log->severity,
                    $log->message,
                    $log->source,
                    $log->is_resolved ? 'Résolu' : 'En attente'
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
