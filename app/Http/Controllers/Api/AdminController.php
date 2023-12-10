<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Report;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
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