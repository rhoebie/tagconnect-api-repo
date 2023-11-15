<?php

namespace App\Http\Controllers\Api;

use App\Models\Report;
use App\Models\Barangay;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Pagination\LengthAwarePaginator;

class AnalyticController extends Controller
{
    public $baseUrl = 'http://localhost:8000';
    function getBarangayAnalytics(Request $request)
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

        // Paginate the result array
        $paginatedResult = collect($result)->slice(($currentPage - 1) * $perPage, $perPage)->all();

        // Create a paginator instance
        $paginator = new LengthAwarePaginator(
            $paginatedResult,
            count($result),
            $perPage,
            $currentPage,
            ['path' => $request->url()]
        );

        return response()->json([
            'meta' => [
                'total_page' => $paginator->lastPage(),
                'current_page' => $paginator->currentPage(),
            ],
            'data' => $paginator->items(),
        ]);
    }

    function getReportsByBarangayName(Request $request)
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