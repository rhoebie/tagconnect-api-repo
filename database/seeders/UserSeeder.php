<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $moderators = array(
            1 => [
                'firstname' => 'Fort Bonifacio',
                'address' => 'Fort Bonifacio, Taguig City, Metro Manila, Philippines',
                'email' => 'fortbonifacio@moderator.com'
            ],
            2 => [
                'firstname' => 'Upper Bicutan',
                'address' => 'Upper Bicutan, Taguig City, Metro Manila, Philippines',
                'email' => 'upperbicutan@moderator.com'
            ],
            3 => [
                'firstname' => 'Western Bicutan',
                'address' => 'Western Bicutan, Taguig City, Metro Manila, Philippines',
                'email' => 'westernbicutan@moderator.com'
            ],
            4 => [
                'firstname' => 'Pinagsama',
                'address' => 'Pinagsama, Taguig City, Metro Manila, Philippines',
                'email' => 'pinagsama@moderator.com'
            ],
            5 => [
                'firstname' => 'Ususan',
                'address' => 'Ususan, Taguig City, Metro Manila, Philippines',
                'email' => 'ususan@moderator.com'
            ],
            6 => [
                'firstname' => 'Napindan',
                'address' => 'Napindan, Taguig City, Metro Manila, Philippines',
                'email' => 'napindan@moderator.com'
            ],
            7 => [
                'firstname' => 'Tuktukan',
                'address' => 'Tuktukan, Taguig City, Metro Manila, Philippines',
                'email' => 'tuktukan@moderator.com'
            ],
            8 => [
                'firstname' => 'Central Signal Village',
                'address' => 'Central Signal, Taguig City, Metro Manila, Philippines',
                'email' => 'centralsignalvillage@moderator.com'
            ],
            9 => [
                'firstname' => 'New Lower Bicutan',
                'address' => 'New Lower Bicutan, Taguig City, Metro Manila, Philippines',
                'email' => 'newlowerbicutan@moderator.com'
            ],
            10 => [
                'firstname' => 'Maharlika Village',
                'address' => 'Maharlika, Taguig City, Metro Manila, Philippines',
                'email' => 'maharlikavillage@moderator.com'
            ],
            11 => [
                'firstname' => 'Central Bicutan',
                'address' => 'Central Bicutan, Taguig City, Metro Manila, Philippines',
                'email' => 'centralbicutan@moderator.com'
            ],
            12 => [
                'firstname' => 'Lower Bicutan',
                'address' => 'Lower Bicutan, Taguig City, Metro Manila, Philippines',
                'email' => 'lowerbicutan@moderator.com'
            ],
            13 => [
                'firstname' => 'North Daang Hari',
                'address' => 'North Daang Hari, Taguig City, Metro Manila, Philippines',
                'email' => 'northdaanghari@moderator.com'
            ],
            14 => [
                'firstname' => 'Tanyag',
                'address' => 'Tanyag, Taguig City, Metro Manila, Philippines',
                'email' => 'tanyag@moderator.com'
            ],
            15 => [
                'firstname' => 'Bagumbayan',
                'address' => 'Bagumbayan, Taguig City, Metro Manila, Philippines',
                'email' => 'bagumbayan@moderator.com'
            ],
            16 => [
                'firstname' => 'South Daang Hari',
                'address' => 'South Daang Hari, Taguig City, Metro Manila, Philippines',
                'email' => 'southdaanghari@moderator.com'
            ],
            17 => [
                'firstname' => 'Palingon',
                'address' => 'Palingon, Taguig City, Metro Manila, Philippines',
                'email' => 'palingon@moderator.com'
            ],
            18 => [
                'firstname' => 'Ligid Tipas',
                'address' => 'Ligid Tipas, Taguig City, Metro Manila, Philippines',
                'email' => 'ligidtipas@moderator.com'
            ],
            19 => [
                'firstname' => 'Ibayo Tipas',
                'address' => 'Ibayo Tipas, Taguig City, Metro Manila, Philippines',
                'email' => 'ibayotipas@moderator.com'
            ],
            20 => [
                'firstname' => 'Calzada',
                'address' => 'Calzada, Taguig City, Metro Manila, Philippines',
                'email' => 'calzada@moderator.com'
            ],
            21 => [
                'firstname' => 'Bambang',
                'address' => 'Bambang, Taguig City, Metro Manila, Philippines',
                'email' => 'bambang@moderator.com'
            ],
            22 => [
                'firstname' => 'Sta Ana',
                'address' => 'Sta Ana, Taguig City, Metro Manila, Philippines',
                'email' => 'staana@moderator.com'
            ],
            23 => [
                'firstname' => 'Wawa',
                'address' => 'Wawa, Taguig City, Metro Manila, Philippines',
                'email' => 'wawa@moderator.com'
            ],
            24 => [
                'firstname' => 'Katuparan',
                'address' => 'Katuparan, Taguig City, Metro Manila, Philippines',
                'email' => 'katuparan@moderator.com'
            ],
            25 => [
                'firstname' => 'North Signal Village',
                'address' => 'North Signal, Taguig City, Metro Manila, Philippines',
                'email' => 'northsignalvillage@moderator.com'
            ],
            26 => [
                'firstname' => 'San Miguel',
                'address' => 'San Miguel, Taguig City, Metro Manila, Philippines',
                'email' => 'sanmiguel@moderator.com'
            ],
            27 => [
                'firstname' => 'South Signal Village',
                'address' => 'South Signal, Taguig City, Metro Manila, Philippines',
                'email' => 'southsignalvillage@moderator.com'
            ],
            28 => [
                'firstname' => 'Hagonoy',
                'address' => 'Hagonoy, Taguig City, Metro Manila, Philippines',
                'email' => 'hagonoy@moderator.com'
            ],
            29 => [
                'firstname' => 'Pembo',
                'address' => 'Pembo, Taguig City, Metro Manila, Philippines',
                'email' => 'pembo@moderator.com'
            ],
            30 => [
                'firstname' => 'Comembo',
                'address' => 'Comembo, Taguig City, Metro Manila, Philippines',
                'email' => 'comembo@moderator.com'
            ],
            31 => [
                'firstname' => 'Cembo',
                'address' => 'Cembo, Taguig City, Metro Manila, Philippines',
                'email' => 'cembo@moderator.com'
            ],
            32 => [
                'firstname' => 'South Cembo',
                'address' => 'South Cembo, Taguig City, Metro Manila, Philippines',
                'email' => 'southcembo@moderator.com'
            ],
            33 => [
                'firstname' => 'West Rembo',
                'address' => 'West Rembo, Taguig City, Metro Manila, Philippines',
                'email' => 'westrembo@moderator.com'
            ],
            34 => [
                'firstname' => 'East Rembo',
                'address' => 'East Rembo, Taguig City, Metro Manila, Philippines',
                'email' => 'eastrembo@moderator.com'
            ],
            35 => [
                'firstname' => 'Pitogo',
                'address' => 'Pitogo, Taguig City, Metro Manila, Philippines',
                'email' => 'pitogo@moderator.com'
            ],
            36 => [
                'firstname' => 'Rizal',
                'address' => 'Rizal, Taguig City, Metro Manila, Philippines',
                'email' => 'rizal@moderator.com'
            ],
            37 => [
                'firstname' => 'Post Proper North Side',
                'address' => 'Post Proper North Side, Taguig City, Metro Manila, Philippines',
                'email' => 'postpropernorthside@moderator.com'
            ],
            38 => [
                'firstname' => 'Post Proper South Side',
                'address' => 'Post Proper South Side, Taguig City, Metro Manila, Philippines',
                'email' => 'postpropersouthside@moderator.com'
            ]
        );



        User::create([
            'role_id' => 1,
            'firstname' => 'Rhoebie Jayriz',
            'middlename' => 'Cruz',
            'lastname' => 'Labrador',
            'age' => '22',
            'birthdate' => '2001-07-16',
            'contactnumber' => '09565478217',
            'address' => 'Block 121 Lot 11 Phase 8, Sitio Imelda, Upper Bicutan, Taguig City, Metro Manila',
            'email' => 'rhoebie.edu@gmail.com',
            'password' => Hash::make('Rhoebi3-JL'),
            'image' => null,
            'status' => 'Verified',
            'verification_code' => '00000000',
            'email_verified_at' => now(),
            'last_code_request' => now(),
        ]);

        User::create([
            'role_id' => 1,
            'firstname' => 'Taguig',
            'middlename' => 'Connect',
            'lastname' => ' Admin',
            'age' => 2,
            'birthdate' => null,
            'contactnumber' => null,
            'address' => 'General Santos Ave, Lower Bicutan, Taguig, 1632 Metro Manila, Philippines.',
            'email' => 'taguigconnect@admin.com',
            'password' => Hash::make('admin123'),
            'image' => null,
            'status' => 'Verified',
            'verification_code' => '00000000',
            'email_verified_at' => now(),
            'last_code_request' => now(),
        ]);

        foreach ($moderators as $id => $moderator) {
            User::create([
                'role_id' => 2,
                'firstname' => $moderator['firstname'],
                'middlename' => 'Taguig',
                'lastname' => ' Moderator',
                'age' => 2,
                'birthdate' => null,
                'contactnumber' => null,
                'address' => $moderator['address'],
                'email' => $moderator['email'],
                'password' => Hash::make('moderator123'),
                'image' => null,
                'status' => 'Verified',
                'verification_code' => '00000000',
                'email_verified_at' => now(),
                'last_code_request' => now(),
            ]);
        }

        User::factory()
            ->count(60)
            ->create();
    }
}