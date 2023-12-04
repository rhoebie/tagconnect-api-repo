<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Report;
use App\Models\Barangay;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ReportResource;
use App\Http\Resources\BarangayResource;

class AnalyticController extends Controller
{

    // users analytics
    public function getUserReports()
    {
        // Check if the user is authenticated
        if (Auth::check()) {
            $user = Auth::user();
            $reports = Report::where('user_id', $user->id)->get();

            $reportDetails = ReportResource::collection($reports);

            return response()->json([
                'data' => $reportDetails,
            ], 200);
        } else {
            return response()->json(['message' => 'Unauthorized.']);
        }
    }

    public function getAllBarangay()
    {
        // Check if the user is valid using the Bearer token
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Retrieve all barangays
        $barangays = Barangay::all();

        // Format the data using BarangayResource
        $formattedBarangays = BarangayResource::collection($barangays);

        return response()->json([
            'data' => $formattedBarangays,
        ], 200);
    }


    function getfeedReports(Request $request)
    {
        // Check if the user is valid using the Bearer token
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $barangayName = $request->input('barangayName', 'all');

        if (strtolower($barangayName) === 'all') {
            $reports = Report::where('status', 'Resolved')
                ->where('visibility', 'Public')
                ->orderBy('created_at', 'desc')->paginate(10);
        } else {
            $barangay = Barangay::where('name', $barangayName)->first();

            if (!$barangay) {
                return response()->json(['error' => 'Barangay not found'], 404);
            }
            $reports = Report::where('barangay_id', $barangay->id)
                ->where('status', 'Resolved')
                ->where('visibility', 'Public')
                ->orderBy('created_at', 'desc')->paginate(10);
            ;
        }

        $reportDetails = ReportResource::collection($reports);

        $meta = [
            'current_page' => $reports->currentPage(),
            'total_pages' => $reports->lastPage(),
        ];

        return response()->json([
            'data' => $reportDetails,
            'meta' => $meta,
        ], 200);
    }

    public function moderatorUsers()
    {
        // Step 1: Get the authenticated user's role and assigned barangay
        $user = Auth::user();

        if ($user->isModerator()) {
            $barangayId = $user->barangays->id;
        } else {
            // Return an error response since the user is not a moderator
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Step 2: Get all reports assigned to the barangay
        $reports = Report::where('barangay_id', $barangayId)->get();

        // Step 3: Get user information based on the user_id in each report
        $userIds = $reports->pluck('user_id')->unique();
        $users = User::whereIn('id', $userIds)->paginate(10);

        $userDetails = UserResource::collection($users);

        $meta = [
            'current_page' => $users->currentPage(),
            'total_pages' => $users->lastPage(),
        ];

        return response()->json([
            'data' => $userDetails,
            'meta' => $meta,
        ], 200);
    }

    public function moderatorReportTypes(Request $request)
    {
        $user = Auth::user();
        if (!$user->isModerator()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $barangayName = $request->input('barangay_name');
        $reports = Report::whereHas('barangays', function ($query) use ($barangayName) {
            $query->where('name', $barangayName);
        })->get();

        $emergencyTypeCounts = $reports->groupBy('emergency_type')->map->count();

        return response()->json(['data' => $emergencyTypeCounts], 200);
    }

    // moderator
    public function moderatorYearlyReport(Request $request)
    {
        // Step 1: Check if the user is a moderator
        $user = Auth::user();
        if (!$user->isModerator()) {
            // Return an error response since the user is not a moderator
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Step 2: Get the assigned barangay ID
        $barangayId = $user->barangays->id;

        // Step 3: Get the inputted year
        $inputYear = $request->input('year');

        // Step 4: Initialize the result array
        $result = [
            'year' => $inputYear,
            'month' => [
                'January' => 0,
                'February' => 0,
                'March' => 0,
                'April' => 0,
                'May' => 0,
                'June' => 0,
                'July' => 0,
                'August' => 0,
                'September' => 0,
                'October' => 0,
                'November' => 0,
                'December' => 0,
            ],
        ];

        // Step 5: Get all reports assigned to the barangay for the specified year
        $reports = Report::where('barangay_id', $barangayId)
            ->whereYear('created_at', $inputYear)
            ->get();

        // Step 6: Populate the result array based on the reports
        foreach ($reports as $report) {
            $createdAt = Carbon::parse($report->created_at);
            $monthName = $createdAt->format('F');

            // Increment the count for the specific month
            $result['month'][$monthName] += 1;
        }

        // Step 7: Return the result
        return response()->json([
            'data' => $result,
        ], 200);
    }
    public function moderatorMonthlyReport(Request $request)
    {
        // Step 1: Check if the user is a moderator
        $user = Auth::user();
        if (!$user->isModerator()) {
            // Return an error response since the user is not a moderator
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Step 2: Get the assigned barangay ID
        $barangayId = $user->barangays->id;

        // Step 3: Get the inputted month (e.g., 'January')
        $inputMonth = $request->input('month');

        // Step 4: Get the current year
        $currentYear = Carbon::now()->year;

        // Step 5: Get all days of the month
        $daysInMonth = Carbon::parse("{$currentYear}-{$inputMonth}")->daysInMonth;

        // Step 6: Initialize the result array with all days set to 0
        $result = [
            'month' => $inputMonth,
            'year' => $currentYear,
            'dates' => array_fill(1, $daysInMonth, 0),
        ];

        // Step 7: Get all reports assigned to the barangay for the specified month and year
        $reports = Report::where('barangay_id', $barangayId)
            ->whereYear('created_at', $currentYear)
            ->whereMonth('created_at', Carbon::parse($inputMonth)->month)
            ->get();

        // Step 8: Populate the result array based on the reports
        foreach ($reports as $report) {
            $createdAt = Carbon::parse($report->created_at);
            $dayOfMonth = $createdAt->day;

            // Increment the count for the specific day
            $result['dates'][$dayOfMonth] += 1;
        }

        // Step 9: Return the result
        return response()->json([
            'data' => $result,
        ], 200);
    }
    public function moderatorweeklyReport()
    {
        // Step 1: Check if the user is a moderator
        $user = Auth::user();
        if (!$user->isModerator()) {
            // Return an error response since the user is not a moderator
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Step 2: Get the assigned barangay ID
        $barangayId = $user->barangays->id;

        // Step 3: Get all reports assigned to the barangay
        $reports = Report::where('barangay_id', $barangayId)->get();

        // Step 4: Initialize counters for this week and last week
        $thisWeekCount = $lastWeekCount = [];

        // Step 5: Calculate the start and end dates for this week and last week
        $thisWeekStart = Carbon::now()->startOfWeek();
        $thisWeekEnd = Carbon::now()->endOfWeek();

        $lastWeekStart = $thisWeekStart->subWeek()->startOfWeek();
        $lastWeekEnd = $thisWeekEnd->subWeek()->endOfWeek();

        // Step 6: Iterate through reports and count for this week and last week
        foreach ($reports as $report) {
            $createdAt = Carbon::parse($report->created_at);

            if ($createdAt->between($thisWeekStart, $thisWeekEnd)) {
                $thisWeekCount[$createdAt->dayName] = ($thisWeekCount[$createdAt->dayName] ?? 0) + 1;
            } elseif ($createdAt->between($lastWeekStart, $lastWeekEnd)) {
                $lastWeekCount[$createdAt->dayName] = ($lastWeekCount[$createdAt->dayName] ?? 0) + 1;
            }
        }

        // Step 7: Fill in missing days with 0 count
        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        foreach ($daysOfWeek as $day) {
            $thisWeekCount[$day] = $thisWeekCount[$day] ?? 0;
            $lastWeekCount[$day] = $lastWeekCount[$day] ?? 0;
        }

        // Step 8: Return the result
        return response()->json([
            'data' => [
                'thisweek' => $thisWeekCount,
                'lastweek' => $lastWeekCount,
            ],
        ], 200);
    }

    // admin
    public function adminWeeklyReport(Request $request)
    {
        // Step 1: Check if the user is a moderator
        $user = Auth::user();
        if (!$user->isAdmin()) {
            // Return an error response since the user is not a moderator
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Step 2: Determine the start and end dates
        $inputDate = $request->input('date');
        $startDate = $this->getMondayDate($inputDate);

        // Calculate the end date as the next Sunday
        $endDate = $startDate->copy()->endOfWeek();

        // Adjust the end date if it's in the past
        $endDate = $endDate->isPast() ? $endDate : Carbon::today();

        // Step 3: Get the total count of reports for each day
        $reportCounts = [];
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $dayOfWeek = $currentDate->dayName;
            $reportCount = Report::whereDate('created_at', '>=', $currentDate->startOfDay())
                ->whereDate('created_at', '<=', $currentDate->endOfDay())
                ->count();
            $reportCounts[$dayOfWeek] = $reportCount;

            // Move to the next day
            $currentDate->addDay();
        }

        $data = [
            'start' => $startDate->toDateString(),
            'end' => $endDate->toDateString(),
            'reportcount' => $reportCounts,
        ];

        // Step 4: Return the result
        return response()->json([
            'data' => $data
        ], 200);
    }

}