<?php

namespace App\Http\Controllers\Api;

use App\Models\Report;
use App\Models\Barangay;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AnalyticController extends Controller
{
    public $baseUrl = 'http://localhost:8000';

    public function getUserReports()
    {
        // Check if the user is authenticated
        if (Auth::check()) {
            $user = Auth::user();
            $reports = Report::where('user_id', $user->id)->get();
            $formattedReports = [];
            foreach ($reports as $report) {
                $locationData = DB::select("SELECT X(location) AS latitude, Y(location) AS longitude FROM reports WHERE id = ?", [$report->id])[0];
                $status = $report->isDone == 1 ? "Done" : "Pending";
                $status1 = $report->casualties == 1 ? "True" : "False";
                if ($report->image != null) {
                    $imageUrl = $this->baseUrl . $report->image;
                } else {
                    $imageUrl = null;
                }

                $formattedReports[] = [
                    'id' => $report->id,
                    'user_id' => $report->users->lastname,
                    'barangay_id' => $report->barangays->name,
                    'emergency_type' => $report->emergency_type,
                    'for_whom' => $report->for_whom,
                    'description' => $report->description,
                    'casualties' => $status1,
                    'location' => [
                        'latitude' => (float) $locationData->latitude,
                        'longitude' => (float) $locationData->longitude,
                    ],
                    'image' => $imageUrl,
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

    function getBarangay(Request $request)
    {
        // Get the $baseUrl
        // Retrieve all barangays
        $barangays = Barangay::all();

        $result = [];

        // Pagination parameters
        $perPage = $request->input('per_page', 10);
        $currentPage = $request->input('page', 1);

        // Loop through each barangay
        foreach ($barangays as $barangay) {
            $analytics = [
                'General' => 0,
                'Medical' => 0,
                'Fire' => 0,
                'Crime' => 0,
                'ResponseTime' => 0,
                'TotalReports' => 0,
            ];

            // Retrieve reports for the current barangay
            $reports = Report::where('barangay_id', $barangay->id)->get();

            // Loop through each report
            foreach ($reports as $report) {
                // Count emergency types
                $analytics[$report->emergency_type]++;

                // Calculate ResponseTime
                $createdAt = Carbon::parse($report->created_at);
                $updatedAt = Carbon::parse($report->updated_at);
                $responseTime = $updatedAt->diffInSeconds($createdAt);
                $analytics['ResponseTime'] += $responseTime;

                // Count total reports
                $analytics['TotalReports']++;
            }

            // Calculate average ResponseTime
            if ($analytics['TotalReports'] > 0) {
                $responseTimeInSeconds = $analytics['ResponseTime'] / $analytics['TotalReports'];

                // Format ResponseTime
                if ($responseTimeInSeconds < 60) {
                    $responseTimeFormatted = $responseTimeInSeconds . ' seconds';
                } elseif ($responseTimeInSeconds < 3600) {
                    $responseTimeFormatted = round($responseTimeInSeconds / 60, 2) . ' minutes';
                } else {
                    $responseTimeFormatted = round($responseTimeInSeconds / 3600, 2) . ' hours';
                }
            } else {
                $responseTimeFormatted = 'N/A';
            }

            if ($barangay->image != null) {
                $imageUrl = $this->baseUrl . $barangay->image;
            } else {
                $imageUrl = null;
            }

            // Retrieve location data using raw SQL query
            $locationData = DB::select("SELECT X(location) AS latitude, Y(location) AS longitude FROM reports WHERE barangay_id = ?", [$barangay->id]);

            // Add barangay data and analytics to the result array
            $result[] = [
                'id' => $barangay->id,
                'moderator_id' => $barangay->moderator_id,
                'name' => $barangay->name,
                'district' => $barangay->district,
                'contact' => $barangay->contact,
                'address' => $barangay->address,
                'location' => [
                    'latitude' => (float) $locationData[0]->latitude,
                    'longitude' => (float) $locationData[0]->longitude,
                ],
                'image' => $imageUrl,
                'analytics' => [
                    'General' => $analytics['General'],
                    'Medical' => $analytics['Medical'],
                    'Fire' => $analytics['Fire'],
                    'Crime' => $analytics['Crime'],
                    'ResponseTime' => $responseTimeFormatted,
                    'TotalReports' => $analytics['TotalReports'],
                ],
            ];
        }

        return response()->json([
            'data' => $result,
        ]);
    }

    function getReports(Request $request)
    {
        // Get the barangayName parameter from the request
        $barangayName = $request->input('barangayName', 'all');

        // Set the number of items per page
        $perPage = $request->input('per_page', 10);

        // Check if the input barangay name is 'all'
        if (strtolower($barangayName) === 'all') {
            // Retrieve all reports without filtering by barangay
            $query = Report::where('isDone', 1)
                ->orderBy('created_at', 'desc');
        } else {
            // Find the barangay by name
            $barangay = Barangay::where('name', $barangayName)->first();

            if (!$barangay) {
                return response()->json(['error' => 'Barangay not found'], 404);
            }

            // Retrieve reports for the specified barangay where isDone is equal to 1
            $query = Report::where('barangay_id', $barangay->id)
                ->where('isDone', 1)
                ->orderBy('created_at', 'desc');
        }

        // Paginate the results
        $reports = $query->paginate($perPage);

        // Transform the paginated reports data as needed
        $formattedReports = [];

        foreach ($reports as $report) {
            // Retrieve location data using raw SQL query
            $locationData = DB::select("SELECT X(location) AS latitude, Y(location) AS longitude FROM reports WHERE id = ?", [$report->id]);

            // Concatenate $baseUrl with the value of the image column
            $imageUrl = $this->baseUrl . $report->image;

            // Calculate time difference between updated_at and created_at in seconds
            $resolveTimeInSeconds = $report->updated_at->diffInSeconds($report->created_at);

            // Format the resolveTime based on seconds
            if ($resolveTimeInSeconds < 60) {
                $resolveTimeFormatted = $resolveTimeInSeconds . ' seconds';
            } elseif ($resolveTimeInSeconds < 3600) {
                $resolveTimeFormatted = round($resolveTimeInSeconds / 60) . ' minutes';
            } else {
                $resolveTimeFormatted = round($resolveTimeInSeconds / 3600) . ' hours';
            }

            $formattedReports[] = [
                'id' => $report->id,
                'barangay_id' => $report->barangay_id,
                'emergency_type' => $report->emergency_type,
                'for_whom' => $report->for_whom,
                'description' => $report->description,
                'casualties' => $report->casualties,
                'location' => [
                    'latitude' => (float) $locationData[0]->latitude,
                    'longitude' => (float) $locationData[0]->longitude,
                ],
                'image' => $imageUrl,
                'isDone' => $report->isDone,
                'created_at' => $report->created_at,
                'updated_at' => $report->updated_at,
                'resolveTime' => $resolveTimeFormatted,
            ];
        }

        // Return the paginated results with metadata
        return response()->json([
            'meta' => [
                'total_page' => $reports->lastPage(),
                'current_page' => $reports->currentPage(),
            ],
            'data' => $formattedReports,
        ]);
    }
}