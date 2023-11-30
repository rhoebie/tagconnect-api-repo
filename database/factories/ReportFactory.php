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
        $images = [
            'public/images/base/incidentPic1.jpg',
            'public/images/base/incidentPic2.jpg',
            'public/images/base/incidentPic3.jpg',
        ];

        $selectedImage = collect($images)->random();

        if (Storage::exists($selectedImage)) {
            $imageFilename = 'report_image_' . uniqid() . '.jpg';

            Storage::copy($selectedImage, 'public/images/reports/' . $imageFilename);

            $imageURL = Storage::url('public/images/reports/' . $imageFilename);
        } else {
            $imageURL = null;
        }

        $barangay = DB::table('barangays')->inRandomOrder()->select('id', 'name')->first();

        $createdAt = $this->faker->dateTimeThisMonth;
        $intervalMinutes = $this->faker->numberBetween(15, 20);

        // Use clone to create a copy of the DateTime object
        $updatedAt = clone $createdAt;
        $updatedAt->add(new \DateInterval('PT' . $intervalMinutes . 'M'));
        return [
            'user_id' => DB::table('users')->inRandomOrder()->value('id'),
            'barangay_id' => $barangay->id,
            'emergency_type' => $this->faker->randomElement(['General', 'Medical', 'Fire', 'Crime']),
            'for_whom' => $this->faker->randomElement(['Myself', 'Another_Person']),
            'description' => $this->faker->sentence(40),
            'casualties' => $this->faker->boolean,
            'location' => $this->randomPoint(),
            'visibility' => $this->faker->randomElement(['Private', 'Public']),
            'image' => $imageURL,
            'status' => $this->faker->randomElement(['Submitted', 'Processing', 'Resolved']),
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ];
    }

    public function randomPoint()
    {
        // Generate random coordinates for a point within valid ranges
        $x = $this->faker->randomFloat(6, 14.49, 14.53); // Longitude (-180 to 180) with 6 decimal places
        $y = $this->faker->randomFloat(6, 121, 121.09); // Latitude (-90 to 90) with 6 decimal places

        // Define the point as a string
        $point = DB::raw("ST_GeomFromText('POINT($x $y)')");

        return $point;
    }
}