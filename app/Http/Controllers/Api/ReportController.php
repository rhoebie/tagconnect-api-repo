<?php

namespace App\Http\Controllers\Api;

use App\Models\Report;
use App\Models\Barangay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ReportResource;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreReportRequest;
use App\Http\Requests\UpdateReportRequest;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('view-any', Report::class);
        $report = Report::all();
        return response()->json([
            'message' => 'Success',
            'data' => ReportResource::collection($report),
        ], 200);
    }

    /**
     * Store a newly created resource in storage.   
     */
    public function store(StoreReportRequest $request)
    {
        $this->authorize('create', Report::class);
        $imagePath = null; // Initialize with null

        // Extract latitude and longitude from the 'location' array
        $latitude = $request->input('location.latitude');
        $longitude = $request->input('location.longitude');

        // Decode the base64 image data if it's not null
        $imageData = $request->input('image');
        if ($imageData !== null) {
            $imagePath = $this->saveBase64Image($imageData);

            if (!$imagePath) {
                return response()->json([
                    'message' => 'Failed to save the image',
                ], 500);
            }
        }

        // Get the user ID from the authenticated user
        $user_id = Auth::id();

        // Retrieve the barangay ID based on the provided name
        $barangayName = $request->input('barangay_id');
        $barangay = Barangay::where('name', $barangayName)->first();

        if (!$barangay) {
            return response()->json([
                'message' => 'Barangay not found',
            ], 404);
        }

        // Create a new Report instance and populate it with the validated data
        $report = new Report([
            'user_id' => $user_id,
            'barangay_id' => $barangay->id,
            'emergency_type' => $request->input('emergency_type'),
            'for_whom' => $request->input('for_whom'),
            'description' => $request->input('description'),
            'casualties' => $request->input('casualties'),
            'location' => DB::raw("POINT($latitude, $longitude)"),
            'image' => $imagePath,
            'isDone' => false,
        ]);

        $report->save();

        return response()->json([
            'message' => 'Success',
            'data' => new ReportResource($report),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Report $report)
    {
        $this->authorize('view', $report);
        if (!$report) {
            return response()->json([
                'message' => 'Failed',
            ], 404);
        }

        return response()->json([
            'message' => 'Success',
            'data' => [
                'report' => new ReportResource($report),
            ],
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateReportRequest $request, Report $report)
    {
        $this->authorize('update', $report);
        if (!$report) {
            return response()->json([
                'message' => 'Report not found',
            ], 404);
        }

        $validated = $request->validated();
        $imagePath = null; // Initialize with null

        // Extract latitude and longitude from the 'location' object
        $latitude = $validated['location']['latitude'];
        $longitude = $validated['location']['longitude'];

        // Decode the base64 image data
        if ($request->has('image')) {
            $imageData = $request->input('image');
            $imagePath = $this->saveBase64Image($imageData);

            if ($imagePath) {
                // Delete the previous image, if it exists
                if ($report->image) {
                    Storage::delete(str_replace('/storage', 'public', $report->image));
                }

                // Update the 'image' field in the $validated array with the new image URL
                $validated['image'] = $imagePath;
            }
        }

        // Construct the POINT data type with latitude first
        $pointData = DB::raw("POINT($latitude, $longitude)");

        // Update the location attribute and other attributes
        $report->update([
            'location' => $pointData,
            'user_id' => $validated['user_id'],
            'barangay_id' => $validated['barangay_id'],
            'emergency_type' => $validated['emergency_type'],
            'for_whom' => $validated['for_whom'],
            'description' => $validated['description'],
            'casualties' => $validated['casualties'],
            'image' => $validated['image'],
            'isDone' => $validated['isDone'],
        ]);

        return response()->json([
            'message' => 'Success',
            'data' => new ReportResource($report),
        ], 200);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Report $report)
    {
        $this->authorize('delete', $report);
        if (!$report) {
            return response()->json([
                'message' => 'Failed'
            ], 404);
        }

        $report->delete();
        return response()->json([
            'message' => 'Success'
        ], 200);
    }

    private function saveBase64Image($base64Data)
    {
        // Generate a unique file name
        $fileName = 'report_image_' . uniqid() . '.';

        // Decode the base64 image data
        $imageData = base64_decode($base64Data);

        // Detect the image format
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $imageFormat = $finfo->buffer($imageData);

        // Determine the file extension based on the image format
        switch ($imageFormat) {
            case 'image/jpeg':
                $fileExtension = 'jpg';
                break;
            case 'image/png':
                $fileExtension = 'png';
                break;
            case 'image/gif':
                $fileExtension = 'gif';
                break;
            default:
                // Handle unsupported image formats here or throw an error
                return null; // Return null for unsupported formats
        }

        // Append the file extension to the generated file name
        $fileName .= $fileExtension;

        // Define the storage path
        $storagePath = 'public/images/reports/' . $fileName;

        // Store the image in the storage
        Storage::put($storagePath, $imageData);

        // Generate the URL for the stored image
        $imageURL = Storage::url($storagePath);

        return $imageURL;
    }

    // {
    //     "user_id": 1,
    //     "barangay_id": 2,
    //     "type_id": 3,
    //     "details": "Incident details",
    //     "contact": "123-456-7890",
    //     "location": {
    //         "latitude": 123.456789,
    //         "longitude": -45.678901
    //     },
    //     "image": null,
    //     "isDone": true
    // }

}