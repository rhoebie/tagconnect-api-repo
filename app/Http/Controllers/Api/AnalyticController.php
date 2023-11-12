<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Barangay;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use SimplePie;

class AnalyticController extends Controller
{
    public function countUserReport()
    {
        // Check if the user is authenticated
        if (Auth::check()) {
            $user = Auth::user();

            // Retrieve the user's reports with the same user_id
            $reports = Report::where('user_id', $user->id)->get();

            // Create an array to store the formatted report data
            $formattedReports = [];

            foreach ($reports as $report) {
                // Use DB::select to extract latitude and longitude from the POINT
                $locationData = DB::select("SELECT X(location) AS latitude, Y(location) AS longitude FROM reports WHERE id = ?", [$report->id])[0];
                $status = $report->isDone == 1 ? "Done" : "Pending";
                $status1 = $report->casualties == 1 ? "True" : "False";

                $formattedReports[] = [
                    'id' => $report->id,
                    'user_id' => $report->users->name,
                    'barangay_id' => $report->barangays->name,
                    'emergency_type' => $report->emergency_type,
                    'for_whom' => $report->for_whom,
                    'description' => $report->description,
                    'casualties' => $status1,
                    'location' => [
                        'latitude' => (float) $locationData->latitude,
                        'longitude' => (float) $locationData->longitude,
                    ],
                    'image' => $report->image,
                    'isDone' => $status,
                    'created_at' => $report->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $report->updated_at->format('Y-m-d H:i:s'),
                ];
            }

            // Return the formatted reports as a JSON response
            return response()->json($formattedReports);
        } else {
            // Handle the case where the user is not authenticated
            return response()->json(['message' => 'Unauthorized.']);
        }
    }

    public function getReportsByEmergencyType()
    {
        $user = auth()->user(); // Get the authenticated user

        if ($user->isAdmin() || $user->isModerator()) {
            // User is an admin or a moderator

            $query = Report::query(); // Create a query builder instance

            if ($user->isAdmin()) {
                // If the user is an admin, retrieve all reports
            } elseif ($user->isModerator()) {
                // If the user is a moderator, retrieve reports based on their assigned barangay
                $barangay = Barangay::where('moderator_id', $user->id)->first();

                if ($barangay) {
                    $query->where('barangay_id', $barangay->id);
                } else {
                    return response()->json(['message' => 'You are not assigned to any barangay.']);
                }
            }

            // Retrieve reports based on the query
            $reports = $query->get();

            // Initialize an array to group reports by 'emergency_type'
            $groupedReports = [];

            foreach ($reports as $report) {
                $emergencyType = $report->emergency_type;
                // Create an array for each 'emergency_type' if it doesn't exist
                if (!isset($groupedReports[$emergencyType])) {
                    $groupedReports[$emergencyType] = ['Total' => 0, 'Reports' => []];
                }
                $groupedReports[$emergencyType]['Reports'][] = $report;
                $groupedReports[$emergencyType]['Total']++;
            }

            // Calculate the overall total count of reports
            $overallTotal = count($reports);

            return response()->json([
                'emergencies' => $groupedReports,
                'totalcount' => $overallTotal,
            ]);
        } else {
            // User is not an admin or moderator
            return response()->json([
                'message' => 'User does not have permission to access this data.',
            ], 403); // Return a 403 Forbidden status code
        }
    }


    public function averageResponse()
    {
        $averages = [];

        // Retrieve all barangays
        $barangays = Barangay::all();

        foreach ($barangays as $barangay) {
            // Retrieve all reports for the current barangay where "isDone" is true
            $doneReports = Report::where('isDone', true)
                ->where('barangay_id', $barangay->id)
                ->get();

            $totalTimeDifference = 0;
            $count = $doneReports->count();

            // Calculate the total time difference for all "done" reports in the current barangay
            foreach ($doneReports as $report) {
                $createdAt = $report->created_at;
                $updatedAt = $report->updated_at;
                $timeDifference = $createdAt->diffInSeconds($updatedAt);
                $totalTimeDifference += $timeDifference;
            }

            if ($count > 0) {
                // Calculate the average response time for the current barangay
                $averageResponseTime = $totalTimeDifference / $count;
            } else {
                $averageResponseTime = 0; // Prevent division by zero
            }

            // Format the average response time
            $formattedAverageResponseTime = $this->formatAverageResponseTime($averageResponseTime);

            // Add the results to the $averages array, including barangay data
            $location = DB::select("SELECT X(location) AS latitude, Y(location) AS longitude FROM barangays WHERE id = ?", [$barangay->id])[0];

            $averages[] = [
                'barangay' => [
                    'id' => $barangay->id,
                    'name' => $barangay->name,
                    'district' => $barangay->district,
                    'contact' => $barangay->contact,
                    'address' => $barangay->address,
                    'location' => [
                        'latitude' => (float) $location->latitude,
                        'longitude' => (float) $location->longitude,
                    ],
                    'image' => $barangay->image,
                ],
                'response_time' => $formattedAverageResponseTime,
                'resolved_reports' => $count,
            ];
        }

        return response()->json(
            $averages,
        );
    }

    private function formatAverageResponseTime($timeInSeconds)
    {
        if ($timeInSeconds < 60) {
            return $timeInSeconds . ' seconds';
        } elseif ($timeInSeconds < 3600) {
            return round($timeInSeconds / 60) . ' minutes';
        } else {
            return round($timeInSeconds / 3600) . ' hours';
        }
    }

    public function latestDoneReport(Request $request)
    {
        // Get the user's ID from the bearer token
        $userId = Auth::user()->id;

        // Query the latest report where isDone is 1 and the user_id is not the current user's ID
        $latestDoneReport = Report::where('isDone', 1)
            ->where('user_id', '!=', $userId)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($latestDoneReport) {

            $userLatitude = $request->input('latitude');
            $userLongitude = $request->input('longitude');


            $location = DB::select("SELECT X(location) AS latitude, Y(location) AS longitude FROM barangays WHERE id = ?", [$latestDoneReport->id])[0];

            $reportLatitude = $location->latitude;
            $reportLongitude = $location->longitude;

            // Calculate the distance using the Haversine formula
            $distance = DistanceCalculator::calculateDistance($userLatitude, $userLongitude, $reportLatitude, $reportLongitude);

            $createdAt = $latestDoneReport->created_at;
            $updatedAt = $latestDoneReport->updated_at;

            // Calculate the time difference in seconds
            $timeDifference = $createdAt->diffInSeconds($updatedAt);

            // Format the time difference
            $formattedTimeDifference = $this->formatTimeDifference($timeDifference);

            // Prepare the response data
            $responseData = [
                'id' => $latestDoneReport->id,
                'user_id' => $latestDoneReport->user_id,
                'barangay_id' => $latestDoneReport->barangay_id,
                'emergency_type' => $latestDoneReport->emergency_type,
                'for_whom' => $latestDoneReport->for_whom,
                'description' => $latestDoneReport->description,
                'casualties' => $latestDoneReport->casualties,
                'location' => $latestDoneReport->location,
                'image' => $latestDoneReport->image,
                'isDone' => $latestDoneReport->isDone,
                'created_at' => $latestDoneReport->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $latestDoneReport->updated_at->format('Y-m-d H:i:s'),
                'response_time' => $formattedTimeDifference,
                'distance' => $distance,
            ];

            return response()->json($responseData);
        } else {
            return response()->json(['message' => 'No done reports found for other users.']);
        }
    }

    private function haversineDistance($lat1, $lon1, $lat2, $lon2)
    {
        // Radius of the Earth in kilometers
        $earthRadius = 6371;

        // Convert latitude and longitude from degrees to radians
        $lat1Rad = deg2rad($lat1);
        $lon1Rad = deg2rad($lon1);
        $lat2Rad = deg2rad($lat2);
        $lon2Rad = deg2rad($lon2);

        // Haversine formula
        $latDiff = $lat2Rad - $lat1Rad;
        $lonDiff = $lon2Rad - $lon1Rad;
        $a = sin($latDiff / 2) * sin($latDiff / 2) +
            cos($lat1Rad) * cos($lat2Rad) *
            sin($lonDiff / 2) * sin($lonDiff / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;

        return $distance * 1000;
    }

    // Helper function to format the time difference
    private function formatTimeDifference($seconds)
    {
        if ($seconds < 60) {
            return $seconds . ' seconds';
        } elseif ($seconds < 3600) {
            return round($seconds / 60) . ' minutes';
        } elseif ($seconds < 86400) {
            return round($seconds / 3600) . ' hours';
        } else {
            return round($seconds / 86400) . ' days';
        }
    }

    public function getEmergencyTypeCountsByBarangay(Request $request)
    {
        $barangayName = $request->input('barangayName');
        // Retrieve the barangay by name
        $barangay = Barangay::where('name', $barangayName)->first();

        if (!$barangay) {
            return response()->json(['message' => 'Barangay not found.'], 404);
        }

        // Query the reports for the given barangay
        $reports = Report::where('barangay_id', $barangay->id)->get();

        // Initialize an array to store the counts for each emergency type
        $emergencyTypeCounts = [];

        // Loop through the reports and count the occurrences of each emergency type
        foreach ($reports as $report) {
            $emergencyType = $report->emergency_type;

            if (!isset($emergencyTypeCounts[$emergencyType])) {
                $emergencyTypeCounts[$emergencyType] = 1;
            } else {
                $emergencyTypeCounts[$emergencyType]++;
            }
        }

        // Return the counts as JSON
        return response()->json(['Barangay' => $barangayName, 'Type' => $emergencyTypeCounts]);
    }

    public function getReportsByAssignedBarangay()
    {
        // Get the authenticated user using the bearer token
        $user = Auth::user();

        // Check if the user is a moderator
        if ($user->isModerator()) {
            // Retrieve the assigned barangay's ID
            $assignedBarangay = Barangay::where('moderator_id', $user->id)->first();

            if ($assignedBarangay) {
                // Retrieve reports where barangay_id matches the assigned barangay's ID
                $reports = Report::where('barangay_id', $assignedBarangay->id)->get();

                return response()->json(['reports' => $reports]);
            } else {
                return response()->json(['message' => 'User is not assigned to any barangay.']);
            }
        } else {
            return response()->json(['message' => 'User is not a moderator.']);
        }
    }

    public function getNews()
    {
        $feedUrl = 'https://www.manilatimes.net/news/feed/';
        $maxItems = 5; // You can adjust this to the number of news items you want to retrieve

        $feed = new SimplePie();
        $feed->set_feed_url($feedUrl);
        $feed->enable_cache(false); // Disable caching for simplicity
        $feed->init();

        $news = [];

        foreach ($feed->get_items(0, $maxItems) as $item) {
            $news[] = [
                'title' => $item->get_title(),
                'author' => $item->get_author()->get_name(),
                // Get the author's name
                'link' => $item->get_permalink(),
                'description' => $item->get_description(),
                'date' => $item->get_date('Y-m-d H:i:s'),
                'image' => $item->get_enclosure()->get_link(),
                // Get the URL of the image or thumbnail
            ];
        }

        return response()->json($news);
    }
}

class DistanceCalculator
{
    const EARTH_RADIUS = 6371; // Earth's radius in kilometers

    public static function calculateDistance($latitude1, $longitude1, $latitude2, $longitude2)
    {
        // Convert latitude and longitude from degrees to radians
        $radLatitude1 = deg2rad($latitude1);
        $radLongitude1 = deg2rad($longitude1);
        $radLatitude2 = deg2rad($latitude2);
        $radLongitude2 = deg2rad($longitude2);

        // Haversine formula
        $deltaLatitude = $radLatitude2 - $radLatitude1;
        $deltaLongitude = $radLongitude2 - $radLongitude1;

        $a = sin($deltaLatitude / 2) * sin($deltaLatitude / 2) +
            cos($radLatitude1) * cos($radLatitude2) * sin($deltaLongitude / 2) * sin($deltaLongitude / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = self::EARTH_RADIUS * $c;

        $formattedDistance = number_format($distance, 4);

        if ($distance < 1) {
            $distanceInMeters = $distance * 1000;
            $formattedDistance = number_format($distanceInMeters, 4) . ' m';
        } else {
            $formattedDistance .= ' km';
        }

        return $formattedDistance;
    }
}