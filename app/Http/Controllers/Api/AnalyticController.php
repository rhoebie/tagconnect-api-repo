<?php

namespace App\Http\Controllers\Api;

use App\Models\Report;
use App\Models\Barangay;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class AnalyticController extends Controller
{
    function getBarangayAnalytics()
    {
        // Retrieve all barangays
        $barangays = Barangay::all();

        $result = [];

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
                'image' => $barangay->image,
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

        return $result;
    }
}