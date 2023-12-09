<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Rules\ValidImage;
use Illuminate\Http\Request;
use App\Mail\VerificationEmail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function registerUser(Request $request)
    {
        try {
            // Validate user input
            $this->validate($request, [
                'firstname' => ['required', 'max:255', 'string'],
                'middlename' => ['required', 'max:255', 'string'],
                'lastname' => ['required', 'max:255', 'string'],
                'age' => ['required', 'integer'],
                'birthdate' => ['required', 'date'],
                'contactnumber' => ['required', 'max:255', 'string'],
                'address' => ['required', 'max:255', 'string'],
                'email' => ['required', 'unique:users,email', 'email'],
                'password' => ['required', 'min:6', 'confirmed'],
                'image' => ['required', new ValidImage],
                'fCMToken' => ['nullable', 'string']
            ]);

            $imagePath = null; // Initialize with null

            // Handle base64 image upload and store the image link in the database
            if ($request->has('image')) {
                $imageData = $request->input('image');
                $imagePath = $this->saveBase64Image($imageData);
            }

            // Create a new user
            $user = User::create([
                'role_id' => '3',
                'firstname' => $request->firstname,
                'middlename' => $request->middlename,
                'lastname' => $request->lastname,
                'age' => $request->age,
                'birthdate' => $request->birthdate,
                'contactnumber' => $request->contactnumber,
                'address' => $request->address,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'image' => $imagePath,
                // Save the image URL
                'status' => 'Pending',
                'fCMToken' => $request->fCMToken
            ]);

            // Generate a random 8-digit number
            $verificationCode = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);

            // Store the verification code and mark email as unverified
            $user->verification_code = $verificationCode;
            $user->email_verified_at = null;
            $user->last_code_request = now();
            $user->save();

            // Send an email with the verification code
            Mail::to($user->email)->send(new VerificationEmail($verificationCode));

            return response()->json(['message' => 'Registration successful. Please check your email for verification.'], 201);
        } catch (ValidationException $e) {
            // Validation failed
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            // Other exceptions (e.g., database errors, email sending failed)
            return response()->json(['error' => 'Registration failed. Please try again later.'], 500);
        }
    }

    public function registerModerator(Request $request)
    {
        try {
            // Validate user input
            $this->validate($request, [
                'email' => 'required|email|unique:users',
                'password' => 'required|min:6',
            ]);
            if (auth()->user()->isAdmin()) {
                // Create a new user with the 'Moderator' role
                $user = User::create([
                    'role_id' => '2',
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                ]);

                $user->save();

                return response()->json(['message' => 'Moderator account created successfully.'], 201);
            } else {
                return response()->json(['error' => 'You are not authorized to create a Moderator account.'], 403);
            }
        } catch (\Exception $e) {
            // Handle exceptions (e.g., database errors)
            return response()->json(['error' => 'Registration failed. Please try again later.'], 500);
        }
    }

    public function verifyOtp(Request $request)
    {
        try {
            // Validate the user's input
            $request->validate([
                'email' => 'required|email|exists:users',
                'verification_code' => 'required|digits:6',
            ]);

            // Retrieve user by email
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json(['error' => 'User not found.'], 404);
            }

            // Check if the email is already verified
            if ($user->email_verified_at === null) {
                // Generate a password reset token
                if ($user->verification_code === $request->verification_code) {
                    $passwordResetToken = Str::random(60);
                    $user->password_reset_token = $passwordResetToken;
                } else {
                    // Verification code doesn't match
                    return response()->json(['error' => 'Invalid verification code.'], 400);
                }

                $user->status = 'Verified';
                $user->email_verified_at = now();
                $user->verification_code = null;
                $user->save();

                return response()->json(['message' => 'Email verified successfully.', 'token' => $passwordResetToken], 200);
            } else {
                // Account is already verified, generate a password reset token
                $passwordResetToken = Str::random(60);
                $user->password_reset_token = $passwordResetToken;
                $user->save();

                return response()->json(['message' => 'Password reset token generated successfully.', 'token' => $passwordResetToken], 200);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Email verification failed. Please try again later.' . $e], 500);
        }
    }

    public function requestOtp(Request $request)
    {
        try {
            // Validate the user's email
            $request->validate([
                'email' => 'required|email|exists:users',
            ]);

            // Retrieve the user by email
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json(['error' => 'User not found.'], 404);
            }

            // Check if a code was requested within the last 5 minutes
            if ($user->last_code_request && now()->diffInMinutes($user->last_code_request) < 1) {
                return response()->json(['error' => 'Please wait at least 1 minute before requesting a new code.'], 429);
            }

            // Generate an 8-digit verification code
            $verificationCode = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);


            // Update the user's verification code and last request time in the database
            $user->verification_code = $verificationCode;
            $user->last_code_request = now();
            $user->save();

            // Send the new verification email
            Mail::to($user->email)->send(new VerificationEmail($verificationCode));

            return response()->json(['message' => 'Verification code generated successfully. Check your email.'], 200);
        } catch (\Exception $e) {
            // Handle exceptions (e.g., database errors)
            return response()->json(['error' => 'Verification code generation failed. Please try again later.'], 500);
        }
    }

    public function resetPassword(Request $request)
    {
        try {
            // Validate the user's email, password reset token, and new password
            $request->validate([
                'email' => 'required|email|exists:users',
                'password_reset_token' => 'required',
                'password' => 'required|min:6',
            ]);

            // Find the user by email and password reset token
            $user = User::where('email', $request->email)
                ->where('password_reset_token', $request->password_reset_token)
                ->first();

            if (!$user) {
                return response()->json(['error' => 'Invalid password reset token or email.'], 400);
            }

            // Reset the user's password and clear the password reset token
            $user->password = Hash::make($request->password);
            $user->password_reset_token = null;
            $user->save();

            return response()->json(['message' => 'Password reset successful.'], 200);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Password reset failed. Please try again later.'], 500);
        }
    }

    public function changePassword(Request $request)
    {
        try {
            // Ensure the user is authenticated with a valid token
            if (!Auth::check()) {
                return response()->json(['error' => 'Unauthenticated.'], 401);
            }
            // Validate the user's input
            $this->validate($request, [
                'id' => ['required', 'exists:users,id'],
                'old_password' => ['required'],
                'new_password' => ['required', 'min:6', 'confirmed'],
            ]);

            // Retrieve the user by ID
            $user = User::find($request->id);

            if (!$user) {
                return response()->json(['error' => 'User not found.'], 404);
            }

            // Verify the old password
            if (!Hash::check($request->old_password, $user->password)) {
                return response()->json(['error' => 'Old password is incorrect.'], 400);
            }

            // Update the user's password
            $user->password = Hash::make($request->new_password);
            $user->save();

            return response()->json(['message' => 'Password changed successfully.'], 200);
        } catch (ValidationException $e) {
            // Validation failed
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            // Other exceptions (e.g., database errors)
            return response()->json(['error' => 'Password change failed. Please try again later.'], 500);
        }
    }


    public function login(Request $request)
    {
        try {
            $validateUser = Validator::make(
                $request->all(),
                [
                    'email' => 'required|email',
                    'password' => 'required',
                    'fcmToken' => 'required|string' // Add validation for fcmToken
                ]
            );

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            if (!Auth::attempt($request->only(['email', 'password']))) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email & Password do not match with our records.',
                ], 401);
            }

            $user = User::where('email', $request->email)->first();

            // Update fCMToken column in the user table
            $user->update(['fCMToken' => $request->fcmToken]);

            return response()->json([
                'status' => true,
                'message' => 'User Logged In Successfully',
                'user_id' => $user->id,
                'role' => $user->roles->name,
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $validateUser = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required'
            ]);

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            if (!Auth::attempt($request->only(['email', 'password']))) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email and password do not match with our records.',
                ], 401);
            }

            $user = User::where('email', $request->email)->first();
            $user->tokens()->delete();

            // Update the expires_at field in the personal_access_tokens table
            $user->tokens()->update([
                'expires_at' => now()
            ]);

            return response()->json([
                'status' => true,
                'message' => 'User logged out successfully'
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
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
}