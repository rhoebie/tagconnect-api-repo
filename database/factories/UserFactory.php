<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Path to the sample image file
        $sampleImagePath = storage_path('app/public/images/base/user.png');

        // Check if the sample image file exists
        if (file_exists($sampleImagePath)) {
            // Generate a unique filename for the user's image
            $imageFilename = 'user_' . uniqid() . '.png';

            // Copy the sample image to the public/images directory
            Storage::copy('public/images/base/user.png', 'public/images/profiles/' . $imageFilename);

            // Construct the image URL based on the storage path
            $imageURL = Storage::url('public/images/profiles/' . $imageFilename);
        } else {
            // Handle the case where the sample image file doesn't exist
            $imageURL = null; // or provide a default image URL
        }


        $role1 = Role::where('name', 'User')->firstOrFail();
        return [
            'role_id' => $role1->id,
            'firstname' => $this->faker->firstName,
            'middlename' => $this->faker->firstName,
            'lastname' => $this->faker->lastName,
            'age' => $this->faker->numberBetween(18, 60),
            'birthdate' => $this->faker->date,
            'contactnumber' => '09' . $this->faker->randomNumber(9, true),
            'address' => $this->faker->address,
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('rhoebie123'),
            'image' => $imageURL,
        ];
    }

    public function unverified()
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}