<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\UserCollection;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('view-any', User::class);
        $user = User::all();
        return response()->json([
            'message' => 'Success',
            'data' => new UserCollection($user),
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();
        $validated['password'] = Hash::make($validated['password']);
        $imagePath = null; // Initialize with null

        // Handle base64 image upload and store the image link in the database
        if ($request->has('image')) {
            $imageData = $request->input('image');
            $imagePath = $this->saveBase64Image($imageData);

            if ($imagePath) {
                $validated['image'] = $imagePath; // Save the image URL in the database
            }
        }

        $user = User::create($validated);

        return response()->json([
            'message' => 'User created successfully',
            'data' => new UserResource($user),
        ], 201);
    }

    // Helper function to save a base64 image and return the path

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $this->authorize('view', $user);

        if (!$user) {
            return response()->json([
                'message' => 'Failed',
            ], 404);
        }

        return response()->json([
            'message' => 'Success',
            'data' => new UserResource($user),
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $this->authorize('update', $user);
        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        $validated = $request->validated();
        $imagePath = null; // Initialize with null

        // Handle updating the user's image if a new base64 image is provided
        if ($request->has('image')) {
            $imageData = $request->input('image');
            $imagePath = $this->saveBase64Image($imageData);

            if ($imagePath) {
                // Delete the previous image, if it exists
                if ($user->image) {
                    Storage::delete(str_replace('/storage', 'public', $user->image));
                }

                $validated['image'] = $imagePath; // Update the user's image URL in the $validated array
            }
        }

        // Update other user attributes
        $user->update($validated);

        return response()->json([
            'message' => 'User updated successfully',
            'data' => new UserResource($user),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);
        if (!$user) {
            return response()->json([
                'message' => 'Failed'
            ], 404);
        }

        $user->delete();
        return response()->json([
            'message' => 'Success',
        ], 200);
    }

    private function saveBase64Image($base64Data)
    {
        // Generate a unique file name
        $fileName = 'user_' . uniqid() . '.';

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
        $storagePath = 'public/images/profiles/' . $fileName;

        // Store the image in the storage
        Storage::put($storagePath, $imageData);

        // Generate the URL for the stored image
        $imageURL = Storage::url($storagePath);

        return $imageURL;
    }

    // {
    //     "name": "John",
    //     "age": 30,
    //     "birthdate": "1993-05-15",
    //     "contactnumber": "123-456-7890",
    //     "address": "123 Main St",
    //     "email": "johndoe@example.com",
    //     "password": "your_password_here",
    //     "password_confirmation": "your_password_here",
    //     "image": null
    // }
}