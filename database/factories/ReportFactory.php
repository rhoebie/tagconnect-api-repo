<?php

namespace Database\Factories;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Providers\PointProvider;
use Illuminate\Support\Facades\Storage;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Report>
 */
class ReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Path to the sample image file
        $sampleImagePath = storage_path('app/public/images/base/incidentPic.jpg');

        // Check if the sample image file exists
        if (file_exists($sampleImagePath)) {
            // Generate a unique filename for the user's image
            $imageFilename = 'report_image_' . uniqid() . '.jpg';

            // Copy the sample image to the public/images directory
            Storage::copy('public/images/base/incidentPic.jpg', 'public/images/reports/' . $imageFilename);

            // Construct the image URL based on the storage path
            $imageURL = Storage::url('public/images/reports/' . $imageFilename);
        } else {
            // Handle the case where the sample image file doesn't exist
            $imageURL = null; // or provide a default image URL
        }

        $barangay = DB::table('barangays')->inRandomOrder()->select('id', 'name')->first();

        $createdAt = $this->faker->dateTimeBetween('-30 minutes', '-15 minutes');
        $updatedAt = $this->faker->dateTimeBetween($createdAt, 'now');
        return [
            'user_id' => DB::table('users')->inRandomOrder()->value('id'),
            'barangay_id' => $barangay->id,
            'emergency_type' => $this->faker->randomElement(['General', 'Medical', 'Fire', 'Crime']),
            'for_whom' => $this->faker->randomElement(['Myself', 'Another_Person']),
            'description' => $this->faker->sentence(40),
            'casualties' => $this->faker->boolean,
            'location' => $this->randomPoint(),
            'image' => $imageURL,
            'isDOne' => $this->faker->randomElement(['0', '1']),
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ];
    }

    public function randomPoint()
    {
        // Generate random coordinates for a point within valid ranges
        $x = $this->faker->randomFloat(6, 14.4, 14.5); // Longitude (-180 to 180) with 6 decimal places
        $y = $this->faker->randomFloat(6, 121, 121.1); // Latitude (-90 to 90) with 6 decimal places

        // Define the point as a string
        $point = DB::raw("ST_GeomFromText('POINT($x $y)')");

        return $point;
    }
}