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

    // moderator
    public function moderatorBrgyInfo()
    {
        // Step 1: Check if the user is a moderator
        $user = Auth::user();
        if (!$user->isModerator()) {
            // Return an error response since the user is not a moderator
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Step 2: Get the assigned barangay of the moderator
        $barangay = $user->barangays;

        // Step 3: Format the barangay information using BarangayResource
        $formattedBarangay = new BarangayResource($barangay);

        // Step 4: Return the formatted barangay information
        return response()->json(['data' => $formattedBarangay], 200);
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
        $users = User::whereIn('id', $userIds)->get(); // Remove pagination

        $userDetails = UserResource::collection($users);

        return response()->json([
            'data' => $userDetails,
        ], 200);
    }


    function moderatorAllReports()
    {
        // Step 1: Check if the user is a moderator
        $user = Auth::user();

        if ($user->isModerator()) {
            $barangayId = $user->barangays->id;
        } else {
            // Return an error response since the user is not a moderator
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Step 3: Get all reports assigned to the barangay
        $reports = Report::where('barangay_id', $barangayId)->get();

        $reportDetails = ReportResource::collection($reports);

        // Step 4: Return the reports
        return response()->json(['data' => $reportDetails], 200);
    }

    public function moderatorReportTypes()
    {
        $user = Auth::user();
        if (!$user->isModerator()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $barangay = $user->barangays->name;
        $reports = Report::whereHas('barangays', function ($query) use ($barangay) {
            $query->where('name', $barangay);
        })->get();

        $emergencyTypes = ['General', 'Medical', 'Fire', 'Crime']; // List of all emergency types

        $emergencyTypeCounts = collect($emergencyTypes)->mapWithKeys(function ($type) use ($reports) {
            return [$type => $reports->where('emergency_type', $type)->count()];
        });

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

        // Step 3: Get the inputted month (e.g., 'November')
        $inputMonth = $request->input('month');

        // Step 4: Get the current year
        $currentYear = Carbon::now()->year;

        // Step 5: Get the number of days in the specified month
        $daysInMonth = Carbon::parse("{$currentYear}-{$inputMonth}")->daysInMonth;

        // Step 6: If the month is the current month, limit the range to the current day
        if ($inputMonth == strtolower(Carbon::now()->format('F'))) {
            $currentDay = Carbon::now()->day;
            $daysInMonth = min($currentDay, $daysInMonth);
        }

        // Step 7: Initialize the result array with all dates set to 0
        $result = [
            'month' => $inputMonth,
            'year' => $currentYear,
            'dates' => array_fill(1, $daysInMonth, 0),
        ];

        // Step 8: Get all reports assigned to the barangay for the specified month and year
        $reports = Report::where('barangay_id', $barangayId)
            ->whereYear('created_at', $currentYear)
            ->whereMonth('created_at', Carbon::parse($inputMonth)->month)
            ->get();

        // Step 9: Populate the result array based on the reports
        foreach ($reports as $report) {
            $createdAt = Carbon::parse($report->created_at);
            $dayOfMonth = $createdAt->day;

            // Increment the count for the specific day
            $result['dates'][$dayOfMonth] += 1;
        }

        // Step 10: Return the result
        return response()->json([
            'data' => $result,
        ], 200);
    }

    public function moderatorWeeklyReport()
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
        $thisWeekStart = Carbon::now()->startOfWeek(Carbon::SUNDAY);
        $thisWeekEnd = Carbon::now()->endOfWeek(Carbon::SATURDAY);

        // To calculate last week's start and end, clone the current week's start and end
        $lastWeekStart = $thisWeekStart->copy()->subWeek();
        $lastWeekEnd = $thisWeekEnd->copy()->subWeek();

        // Step 6: Iterate through reports and count for this week and last week
        foreach ($reports as $report) {
            $createdAt = Carbon::parse($report->created_at);
            $dayName = $createdAt->dayName;

            if ($createdAt->between($thisWeekStart, $thisWeekEnd)) {
                $thisWeekCount[$dayName] = ($thisWeekCount[$dayName] ?? 0) + 1;
            } elseif ($createdAt->between($lastWeekStart, $lastWeekEnd)) {
                $lastWeekCount[$dayName] = ($lastWeekCount[$dayName] ?? 0) + 1;
            }
        }

        // Step 7: Fill in missing days with 0 count
        $daysOfWeek = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $thisWeekCount = array_merge(array_fill_keys($daysOfWeek, 0), $thisWeekCount);
        $lastWeekCount = array_merge(array_fill_keys($daysOfWeek, 0), $lastWeekCount);

        // Step 8: Return the result
        return response()->json([
            'data' => [
                'thisDate' => $thisWeekStart->format('M,d') . '-' . $thisWeekEnd->format('M,d'),
                'lastDate' => $lastWeekStart->format('M,d') . '-' . $lastWeekEnd->format('M,d'),
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