<?php

namespace App\Http\Controllers\Api;

use App\Models\Barangay;
use App\Rules\ValidPolygon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\BarangayResource;
use App\Http\Resources\BarangayCollection;
use App\Http\Requests\StoreBarangayRequest;
use App\Http\Requests\UpdateBarangayRequest;
use Illuminate\Support\Facades\Storage;

class BarangayController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        $this->authorize('view-any', Barangay::class);
        $barangay = Barangay::paginate(10);
        $meta = [
            'current_page' => $barangay->currentPage(),
            'total_pages' => $barangay->lastPage(),
        ];
        return response()->json([
            'data' => new BarangayCollection($barangay),
            'meta' => $meta,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBarangayRequest $request)
    {
        $this->authorize('create', Barangay::class);
        $validated = $request->validated();
        $imagePath = null; // Initialize with null

        // Decode the base64 image data
        if ($request->has('image')) {
            $imageData = $validated['image'];
            $imagePath = $this->saveBase64Image($imageData);
        }
        $barangay = new Barangay([
            'moderator_id' => $validated['moderator_id'],
            'name' => $validated['name'],
            'district' => $validated['district'],
            'contact' => $validated['contact'],
            'address' => $validated['address'],
            'image' => $imagePath,
        ]);

        $barangay->save();

        return response()->json([
            'message' => 'Success',
            'data' => new BarangayResource($barangay),
        ], 201);
    }


    /**
     * Display the specified resource.
     */
    public function show(Barangay $barangay)
    {
        $this->authorize('view', $barangay);
        if (!$barangay) {
            return response()->json([
                'message' => 'Failed',
            ], 404);
        }

        return response()->json([
            'data' => new BarangayResource($barangay),
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBarangayRequest $request, Barangay $barangay)
    {
        $this->authorize('update', $barangay);
        if (!$barangay) {
            return response()->json([
                'message' => 'Failed'
            ], 404);
        }

        $validated = $request->validated();
        $imagePath = null; // Initialize with null

        // Decode the base64 image data
        if ($request->has('image')) {
            $imageData = $request->input('image');
            $imagePath = $this->saveBase64Image($imageData);

            if ($imagePath) {
                // Delete the previous image, if it exists
                if ($barangay->image) {
                    Storage::delete(str_replace('/storage', 'public', $barangay->image));
                }
                $validated['image'] = $imagePath;
            }
        }

        // Update the Barangay instance with validated data, including the 'boundary' column
        $barangay->update([
            'moderator_id' => $validated['moderator_id'],
            'name' => $validated['name'],
            'district' => $validated['district'],
            'contact' => $validated['contact'],
            'address' => $validated['address'],
            'image' => $validated['image'],
        ]);

        return response()->json([
            'message' => 'Success',
            'data' => new BarangayResource($barangay),
        ], 200);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Barangay $barangay)
    {
        $this->authorize('delete', $barangay);
        if (!$barangay) {
            return response()->json([
                'message' => 'Failed'
            ], 404);
        }

        $barangay->delete();
        return response()->json([
            'message' => 'Success'
        ], 200);
    }

    private function saveBase64Image($base64Data)
    {
        // Generate a unique file name
        $fileName = 'barangay_' . uniqid() . '.';

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
        $storagePath = 'public/images/barangays/' . $fileName;

        // Store the image in the storage
        Storage::put($storagePath, $imageData);

        // Generate the URL for the stored image
        $imageURL = Storage::url($storagePath);

        return $imageURL;
    }

    // {
    //     "moderator_id": 1,
    //     "name": "Your Organization Name",
    //     "district": 2,
    //     "contact": "123-456-7890",
    //     "address": "123 Main St",
    // }
}