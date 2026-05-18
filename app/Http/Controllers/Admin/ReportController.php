<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Cleaner;
use App\Models\City;
use App\Models\Commission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin|super_admin']);
    }

    /**
     * Reports dashboard
     */
    public function index()
    {
        return view('admin.reports.index');
    }

    /**
     * Revenue report
     */
    public function revenue(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'city_id' => 'nullable|exists:cities,id',
            'group_by' => 'nullable|in:day,week,month,city',
        ]);

        $startDate = $request->start_date ?? now()->subDays(30);
        $endDate = $request->end_date ?? now();
        $groupBy = $request->group_by ?? 'day';

        $query = Booking::whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('status', ['completed', 'in_progress'])
            ->when($request->city_id, function ($q, $cityId) {
                return $q->where('city_id', $cityId);
            });

        if ($groupBy === 'city') {
            $revenueData = $query->selectRaw('
                city_id, 
                COUNT(*) as total_bookings,
                SUM(total_amount) as total_revenue,
                SUM(commission_amount) as total_commission,
                AVG(total_amount) as average_booking_value
            ')
            ->groupBy('city_id')
            ->with('city')
            ->get();
        } else {
            $dateFormat = match($groupBy) {
                'week' => '%Y-%u',
                'month' => '%Y-%m',
                default => '%Y-%m-%d',
            };

            $revenueData = $query->selectRaw("
                DATE_FORMAT(created_at, '{$dateFormat}') as period,
                COUNT(*) as total_bookings,
                SUM(total_amount) as total_revenue,
                SUM(commission_amount) as total_commission,
                SUM(instant_booking_fee) as total_instant_fees
            ")
            ->groupBy('period')
            ->orderBy('period')
            ->get();
        }

        $summary = [
            'total_revenue' => $revenueData->sum('total_revenue'),
            'total_commission' => $revenueData->sum('total_commission'),
            'total_bookings' => $revenueData->sum('total_bookings'),
            'average_booking' => $revenueData->sum('total_bookings') > 0 
                ? $revenueData->sum('total_revenue') / $revenueData->sum('total_bookings') 
                : 0,
        ];

        $cities = City::where('is_active', true)->get();

        return view('admin.reports.revenue', compact(
            'revenueData', 'summary', 'startDate', 'endDate', 'groupBy', 'cities'
        ));
    }

    /**
     * Cleaner performance report
     */
    public function cleanerPerformance(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        $startDate = $request->start_date ?? now()->subDays(30);
        $endDate = $request->end_date ?? now();

        $cleaners = Cleaner::with(['user', 'city'])
            ->withCount(['bookings as total_bookings' => function ($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate]);
            }])
            ->withCount(['bookings as completed_bookings' => function ($q) use ($startDate, $endDate) {
                $q->where('status', 'completed')
                  ->whereBetween('created_at', [$startDate, $endDate]);
            }])
            ->withSum(['bookings as total_earnings' => function ($q) use ($startDate, $endDate) {
                $q->whereIn('status', ['completed', 'in_progress'])
                  ->whereBetween('created_at', [$startDate, $endDate]);
            }], 'cleaner_payout_amount')
            ->orderByDesc('completed_bookings')
            ->get();

        return view('admin.reports.cleaners', compact('cleaners', 'startDate', 'endDate'));
    }

    /**
     * Export report as Excel/CSV
     */
    public function export(Request $request, string $type)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'format' => 'nullable|in:csv,excel',
        ]);

        $startDate = $request->start_date ?? now()->subDays(30);
        $endDate = $request->end_date ?? now();
        $format = $request->format ?? 'csv';

        $data = match($type) {
            'revenue' => $this->getRevenueExportData($startDate, $endDate),
            'commissions' => $this->getCommissionExportData($startDate, $endDate),
            'cleaners' => $this->getCleanerExportData($startDate, $endDate),
            default => [],
        };

        $filename = "{$type}_report_{$startDate}_{$endDate}." . ($format === 'excel' ? 'xlsx' : 'csv');

        return response()->streamDownload(function () use ($data) {
            $output = fopen('php://output', 'w');
            
            if (!empty($data)) {
                fputcsv($output, array_keys($data[0]));
                foreach ($data as $row) {
                    fputcsv($output, $row);
                }
            }
            
            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function getRevenueExportData(string $startDate, string $endDate): array
    {
        return Booking::whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('status', ['completed', 'in_progress'])
            ->with(['service', 'city'])
            ->get()
            ->map(function ($booking) {
                return [
                    'Date' => $booking->created_at->format('Y-m-d'),
                    'Booking ID' => $booking->booking_number,
                    'Service' => $booking->service->name,
                    'City' => $booking->city->name,
                    'Amount' => $booking->total_amount,
                    'Commission' => $booking->commission_amount,
                    'Status' => $booking->status,
                ];
            })
            ->toArray();
    }

    private function getCommissionExportData(string $startDate, string $endDate): array
    {
        return Commission::whereBetween('created_at', [$startDate, $endDate])
            ->with(['cleaner.user', 'booking.service'])
            ->get()
            ->map(function ($commission) {
                return [
                    'Cleaner' => $commission->cleaner->user->full_name ?? 'N/A',
                    'Service' => $commission->booking->service->name ?? 'N/A',
                    'Expected' => $commission->expected_total_amount,
                    'Submitted' => $commission->actual_submitted_amount,
                    'Remaining' => $commission->remaining_unpaid_amount,
                    'Status' => $commission->payment_status,
                ];
            })
            ->toArray();
    }

    private function getCleanerExportData(string $startDate, string $endDate): array
    {
        return Cleaner::with('user')
            ->get()
            ->map(function ($cleaner) use ($startDate, $endDate) {
                return [
                    'Cleaner' => $cleaner->user->full_name,
                    'Rating' => $cleaner->rating,
                    'Completed Jobs' => $cleaner->total_completed_jobs,
                    'Completion Rate' => $cleaner->completion_rate,
                    'Total Earnings' => $cleaner->total_earnings,
                ];
            })
            ->toArray();
    }
}