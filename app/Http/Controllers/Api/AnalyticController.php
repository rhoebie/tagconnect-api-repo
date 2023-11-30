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
            $reports = Report::where('user_id', $user->id)->paginate(10);

            $reportDetails = ReportResource::collection($reports);

            $meta = [
                'current_page' => $reports->currentPage(),
                'total_pages' => $reports->lastPage(),
            ];

            return response()->json([
                'data' => $reportDetails,
                'meta' => $meta,
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

        // Paginate the barangays
        $barangays = Barangay::paginate(10);

        // Format the data using BarangayResource
        $formattedBarangays = BarangayResource::collection($barangays);

        // Build the meta information
        $meta = [
            'current_page' => $barangays->currentPage(),
            'total_pages' => $barangays->lastPage(),
        ];

        return response()->json([
            'data' => $formattedBarangays,
            'meta' => $meta,
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

    public function getBarangayUsers()
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

    public function countEmergencyTypes(Request $request)
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

        return response()->json($emergencyTypeCounts, 200);
    }

    public function weeklyReport(Request $request)
    {
        // Step 1: Check if the user is a moderator
        $user = Auth::user();
        if (!$user->isModerator()) {
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


        // Step 4: Return the result
        return response()->json([
            'Start' => $startDate->toDateString(),
            'End' => $endDate->toDateString(),
            'ReportCounts' => $reportCounts,
        ], 200);
    }

    private function getMondayDate($inputDate)
    {
        $date = $inputDate ? Carbon::parse($inputDate) : Carbon::today();
        return $date->startOfWeek(); // This will get the start of the week (Monday)
    }

    public function countReportsForDate(Request $request)
    {
        // Step 1: Check if the user is a moderator
        $user = Auth::user();
        if (!$user->isModerator()) {
            // Return an error response since the user is not a moderator
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Step 2: Get the input date from the request
        $inputDate = $request->input('date');

        // Step 3: Validate the input date (you may want to add further validation)
        if (!Carbon::hasFormat($inputDate, 'Y-m-d')) {
            return response()->json(['error' => 'Invalid date format'], 400);
        }

        // Step 4: Count the total reports for the input date
        $reportCount = Report::whereDate('created_at', Carbon::parse($inputDate)->toDateString())->count();

        // Step 5: Return the result
        return response()->json([
            'Date' => $inputDate,
            'ReportCount' => $reportCount,
        ], 200);
    }

}