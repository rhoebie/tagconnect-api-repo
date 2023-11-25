<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Barangay;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BarangaySeeder extends Seeder
{
    public function run()
    {
        $barangays = array(
            'Fort Bonifacio' => [
                'district' => 2,
                'contact' => '2994 0960',
                'address' => 'Motortown, Market Market, BGC, Taguig City',
                'latitude' => 14.525812728625057,
                'longitude' => 121.02681185156052,
                'image' => 'FORT_BONIFACIO.jpg'
            ],
            'Upper Bicutan' => [
                'district' => 2,
                'contact' => '2838 9265',
                'address' => 'Bonifacio Ave. corner Lapu-Lapu St., Upper Bicutan, Taguig City',
                'latitude' => 14.497156217923633,
                'longitude' => 121.05034073880151,
                'image' => 'UPPER_BICUTAN.jpg'
            ],
            'Western Bicutan' => [
                'district' => 2,
                'contact' => '8260 1320',
                'address' => 'Sampaguita Street, Western Bicutan, Taguig City',
                'latitude' => 14.509610785855699,
                'longitude' => 121.03812425955343,
                'image' => 'WESTERN_BICUTAN.jpg'
            ],
            'Pinagsama' => [
                'district' => 2,
                'contact' => '2541 6902',
                'address' => 'Phase 1, Pinagsama, Taguig City',
                'latitude' => 14.523131772369743,
                'longitude' => 121.05552239887288,
                'image' => 'PINAGSAMA.jpg'
            ],
            'Ususan' => [
                'district' => 1,
                'contact' => '2640 9066',
                'address' => '1 Tomas Avenue, Ususan, Taguig City',
                'latitude' => 14.535695681892296,
                'longitude' => 121.06873676763743,
                'image' => 'USUSAN.jpg'
            ],
            'Napindan' => [
                'district' => 1,
                'contact' => '2640 5291',
                'address' => 'Labao Street, Purok 4, Napindan, Taguig City',
                'latitude' => 14.540320604297419,
                'longitude' => 121.09614933880209,
                'image' => 'NAPINDAN.jpg'
            ],
            'Tuktukan' => [
                'district' => 1,
                'contact' => '2642 0970',
                'address' => 'Gen. A. Luna Street, Tuktukan, Taguig City',
                'latitude' => 14.528363758268435,
                'longitude' => 121.07172845229591,
                'image' => 'TUKTUKAN.jpg'
            ],
            'Central Signal Village' => [
                'district' => 2,
                'contact' => '8837 0495',
                'address' => 'No. 2 Col. Miranda St. Zone 2 Central Signal Village, Taguig, Philippines, 1630',
                'latitude' => 14.51147836917546,
                'longitude' => 121.05659164109595,
                'image' => 'CENTRAL_SIGNAL_VILLAGE.jpg'
            ],
            'New Lower Bicutan' => [
                'district' => 1,
                'contact' => '2838 5364',
                'address' => '#71 ML. Quezon St., Purok 1, New Lower Bicutan, Taguig City',
                'latitude' => 14.50630644736256,
                'longitude' => 121.06504167163715,
                'image' => 'NEW_LOWER_BICUTAN.jpg'
            ],
            'Maharlika Village' => [
                'district' => 2,
                'contact' => '2837 7002',
                'address' => 'Marawi Avenue, Maharlika Village, Taguig City',
                'latitude' => 14.498880738909504,
                'longitude' => 121.05311932967726,
                'image' => 'MAHARLIKA_VILLAGE.jpg'
            ],
            'Central Bicutan' => [
                'district' => 2,
                'contact' => '2612 0618',
                'address' => '123 Main Street, Fort Bonifacio',
                'latitude' => 14.492204647856969,
                'longitude' => 121.05400072346009,
                'image' => 'CENTRAL_BICUTAN.jpg'
            ],
            'Lower Bicutan' => [
                'district' => 1,
                'contact' => '2839 1857',
                'address' => '#1 C6 road, Purok 5, Lower Bicutan, Taguig, Philippines, 1632',
                'latitude' => 14.488991470581304,
                'longitude' => 121.06232548068967,
                'image' => 'LOWER_BICUTAN.jpg'
            ],
            'North Daang Hari' => [
                'district' => 2,
                'contact' => '2837 2658',
                'address' => 'Road 1 Extension, North Daang Hari, Taguig City',
                'latitude' => 14.485876816801992,
                'longitude' => 121.04827314008854,
                'image' => 'NORTH_DAANG_HARI.jpg'
            ],
            'Tanyag' => [
                'district' => 2,
                'contact' => '2837 2469',
                'address' => 'Purok 3, Tanyag, Taguig City',
                'latitude' => 14.478321095860435,
                'longitude' => 121.04944299647194,
                'image' => 'TANYAG.jpg'
            ],
            'Bagumbayan' => [
                'district' => 1,
                'contact' => '2837 6415',
                'address' => 'M.L. Quezon Street, Bagumbayan, Taguig City',
                'latitude' => 14.473803670867454,
                'longitude' => 121.05923359359221,
                'image' => 'BAGUMBAYAN.jpg'
            ],
            'South Daang Hari' => [
                'district' => 2,
                'contact' => '2970 8090',
                'address' => 'Purok 11, South Daang Hari, Taguig City',
                'latitude' => 14.471631289310178,
                'longitude' => 121.0487237811304,
                'image' => 'SOUTH_DAANG_HARI.jpg'
            ],
            'Palingon' => [
                'district' => 1,
                'contact' => '2640 7773',
                'address' => 'F. Manalo Street, Palingon, Taguig City',
                'latitude' => 14.538297715484092,
                'longitude' => 121.08032501181428,
                'image' => 'PALINGON.jpg'
            ],
            'Ligid Tipas' => [
                'district' => 1,
                'contact' => '2642 4745',
                'address' => '79 Labo Street, Ligid Tipas, Taguig City',
                'latitude' => 14.54225421805447,
                'longitude' => 121.08026656763752,
                'image' => 'LIGID_TIPAS.jpg'
            ],
            'Ibayo Tipas' => [
                'district' => 1,
                'contact' => '2642 2462',
                'address' => '17 Dr. Natividad St., Ibayo Tipas, Taguig City',
                'latitude' => 14.542109158696606,
                'longitude' => 121.0847747388021,
                'image' => 'IBAYO_TIPAS.jpg'
            ],
            'Calzada' => [
                'district' => 1,
                'contact' => '2643 9066',
                'address' => '3 Ruhale Street, Calzada, Taguig City',
                'latitude' => 14.533944336797951,
                'longitude' => 121.08001912530823,
                'image' => 'CALZADA.jpg'
            ],
            'Bambang' => [
                'district' => 1,
                'contact' => '2839 1940',
                'address' => 'M.L. Quezon St., Bambang, Taguig City',
                'latitude' => 14.52590650245167,
                'longitude' => 121.07287933695437,
                'image' => 'BAMBANG.jpg'
            ],
            'Sta Ana' => [
                'district' => 1,
                'contact' => '2642 2228',
                'address' => 'Liwayway Street, Sta. Ana, Taguig City',
                'latitude' => 14.528093401114347,
                'longitude' => 121.07683809647288,
                'image' => 'STA_ANA.jpg'
            ],
            'Wawa' => [
                'district' => 1,
                'contact' => ' 2838 5383',
                'address' => 'Dama De Noche Street, Wawa, Taguig City',
                'latitude' => 14.521968877005342,
                'longitude' => 121.07495783695418,
                'image' => 'WAWA.jpg'
            ],
            'Katuparan' => [
                'district' => 2,
                'contact' => '2541 2639',
                'address' => 'Pag-Asa Avenue, Katuparan, Taguig City',
                'latitude' => 14.521733924132256,
                'longitude' => 121.05838043880193,
                'image' => 'KATUPARAN.jpg'
            ],
            'North Signal Village' => [
                'district' => 2,
                'contact' => '2838 8488',
                'address' => 'Ipil Ipil Street, Signal Village, Taguig City',
                'latitude' => 14.514326501470087,
                'longitude' => 121.05722072867144,
                'image' => 'NORTH_SIGNAL_VILLAGE.jpg'
            ],
            'San Miguel' => [
                'district' => 1,
                'contact' => '2556 7330',
                'address' => '15-D M.L. Quezon St., San Miguel, Taguig City',
                'latitude' => 14.51798519355381,
                'longitude' => 121.0748633694848,
                'image' => 'SAN_MIGUEL.jpg'
            ],
            'South Signal Village' => [
                'district' => 2,
                'contact' => '2838 7538',
                'address' => 'Balleser corner Gen. Espino Sts., Zone 6',
                'latitude' => 14.505412535771779,
                'longitude' => 121.05365194020278,
                'image' => 'SOUTH_SIGNAL_VILLAGE.jpg'
            ],
            'Hagonoy' => [
                'district' => 1,
                'contact' => '2838 0990',
                'address' => 'M.L. Quezon Street, Hagonoy, Taguig City',
                'latitude' => 14.511922670537164,
                'longitude' => 121.06941012693439,
                'image' => 'HAGONOY.jpg'
            ],
            'Pembo' => [
                'district' => null,
                'contact' => '8856-5672',
                'address' => '29 Sampaguita Street, Pembo, Taguig City',
                'latitude' => 14.54400642681643,
                'longitude' => 121.05795258297898,
                'image' => 'PEMBO.jpg'
            ],
            'Comembo' => [
                'district' => null,
                'contact' => '8625-5732',
                'address' => 'J.P. Rizal Extension, Comembo, Taguig City',
                'latitude' => 14.56321805752191,
                'longitude' => 121.05178606948552,
                'image' => 'COMEMBO.jpg'
            ],
            'Cembo' => [
                'district' => null,
                'contact' => '8881-1091',
                'address' => 'Kalayaan Avenue, Cembo, Taguig City',
                'latitude' => 14.559592471970486,
                'longitude' => 121.04943067530859,
                'image' => 'CEMBO.jpg'
            ],
            'South Cembo' => [
                'district' => null,
                'contact' => '8728-1830',
                'address' => 'Del Pilar Street, South Cembo, Makati City',
                'latitude' => 14.559592471970486,
                'longitude' => 121.04943067530859,
                'image' => 'SOUTH_CEMBO.jpg'
            ],
            'West Rembo' => [
                'district' => null,
                'contact' => '8836-9731',
                'address' => '21st Street & J.P. Rizal Extension, West Rembo, Makati City',
                'latitude' => 14.5601348028968,
                'longitude' => 121.06323390811947,
                'image' => 'WEST_REMBO.jpg'
            ],
            'East Rembo' => [
                'district' => null,
                'contact' => '8728-1585',
                'address' => '7th Avenue & J.P. Rizal Extension, East Rembo, Makati City',
                'latitude' => 14.556861653025932,
                'longitude' => 121.06446603937509,
                'image' => 'EAST_REMBO.jpg'
            ],
            'Pitogo' => [
                'district' => null,
                'contact' => '8728-3615',
                'address' => 'Catanduanes Street, Pitogo, Makati City',
                'latitude' => 14.55708677958693,
                'longitude' => 121.0468408296982,
                'image' => 'PITOGO.jpg'
            ],
            'Rizal' => [
                'district' => null,
                'contact' => '7729-1995',
                'address' => 'Amarillo Street, Rizal, Makati City',
                'latitude' => 14.538870872990923,
                'longitude' => 121.06352153274705,
                'image' => 'RIZAL.jpg'
            ],
            'Post Proper North Side' => [
                'district' => null,
                'contact' => '8881-3898',
                'address' => 'Lawton Avenue, Northside, Makati City',
                'latitude' => 14.563899437014163,
                'longitude' => 121.05427127961698,
                'image' => 'POST_PROPER_NORTH_SIDE.jpg'
            ],
            'Post Proper South Side' => [
                'district' => null,
                'contact' => '8817-6381',
                'address' => 'Gate II, Lawton Avenue, Southside, Makati City',
                'latitude' => 14.54191098862258,
                'longitude' => 121.04643861181434,
                'image' => 'POST_PROPER_SOUTH_SIDE.jpg'
            ]
        );

        foreach ($barangays as $name => $data) {
            $district = $data['district'];
            $contact = $data['contact'];
            $address = $data['address'];
            $latitude = $data['latitude'];
            $longitude = $data['longitude'];
            $imageFileName = $data['image']; // Remove spaces from the name
            $sanitizedName = str_replace(' ', '', $name);
            $moderatorEmail = strtolower($sanitizedName) . '@moderator.com';
            $moderator = User::where('email', $moderatorEmail)->first();

            // Check if the image file exists
            $imagePath = storage_path('app/public/images/base/barangays/' . $imageFileName);

            if (file_exists($imagePath)) {
                // Generate a unique filename for the image
                $imageNewFileName = 'barangay_' . uniqid() . '.' . pathinfo($imageFileName, PATHINFO_EXTENSION);

                // Copy the image to the public/images/barangays/ directory
                Storage::copy('public/images/base/barangays/' . $imageFileName, 'public/images/barangays/' . $imageNewFileName);

                // Construct the image URL based on the storage path
                $imageURL = Storage::url('public/images/barangays/' . $imageNewFileName);
            } else {
                $imageURL = null;
            }

            DB::table('barangays')->insert([
                'moderator_id' => $moderator->id,
                'name' => $name,
                'district' => $district,
                'contact' => $contact,
                'address' => $address,
                'location' => DB::raw("ST_GeomFromText('POINT($latitude $longitude)')"),
                'image' => $imageURL,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}