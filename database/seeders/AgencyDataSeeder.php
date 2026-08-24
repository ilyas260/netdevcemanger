<?php

namespace Database\Seeders;

use App\Models\Agency;
use Illuminate\Database\Seeder;

class AgencyDataSeeder extends Seeder
{
    public function run(): void
    {
        $agencies = [
            [
                'name' => 'Agence de Services Bejjad',
                'nd_technique' => '0523525768',
                'debit_cible' => '8 Mbps',
                'router_ip' => '10.110.13.1',
                'hostname' => 'SR10-SITE13',
            ],
            [
                'name' => 'Agence de Services Boujniba',
                'nd_technique' => '0523570562',
                'debit_cible' => '8 Mbps',
                'router_ip' => '10.110.17.1',
                'hostname' => 'SR10-SITE17',
            ],
            [
                'name' => 'Agence de Services Oued Zem',
                'nd_technique' => '0523524737',
                'debit_cible' => '8 Mbps',
                'router_ip' => '10.110.19.1',
                'hostname' => 'SR10-SITE19',
            ],
            [
                'name' => 'Direction Provinciale Distribution Khouribga',
                'nd_technique' => '0523560214',
                'debit_cible' => '20 Mbps',
                'router_ip' => '10.110.31.1',
                'hostname' => 'SR10-SITE31',
            ],
            [
                'name' => 'Agence de Services Provinciale Khouribga',
                'nd_technique' => '0523567750',
                'debit_cible' => '20 Mbps',
                'router_ip' => '10.110.32.1',
                'hostname' => 'SR10-SITE32',
            ],
            [
                'name' => 'SIEGE DP KHOURIBGA',
                'nd_technique' => '0523496143',
                'debit_cible' => '4 Mbps',
                'router_ip' => '10.110.66.1',
                'hostname' => 'SR10-SITE66',
            ],
            [
                'name' => 'CENTRE BIR MEZOUI',
                'nd_technique' => '0523524936',
                'debit_cible' => '4 Mbps',
                'router_ip' => '10.110.67.1',
                'hostname' => 'SR10-SITE67',
            ],
            [
                'name' => 'CENTRE BOUJNIBA',
                'nd_technique' => '0523570864',
                'debit_cible' => '4 Mbps',
                'router_ip' => '10.110.68.1',
                'hostname' => 'SR10-SITE68',
            ],
            [
                'name' => 'CENTRE BOULANOUAR',
                'nd_technique' => '0523577954',
                'debit_cible' => '4 Mbps',
                'router_ip' => '10.110.69.1',
                'hostname' => 'SR10-SITE69',
            ],
            [
                'name' => 'CENTRE HATTANE',
                'nd_technique' => '523574588',
                'debit_cible' => '4 Mbps',
                'router_ip' => '10.110.70.1',
                'hostname' => 'SR10-SITE70',
            ],
            [
                'name' => 'US KHOURIBGA',
                'nd_technique' => '0523560145',
                'debit_cible' => '4 Mbps',
                'router_ip' => '10.110.71.1',
                'hostname' => 'SR10-SITE71',
            ],
            [
                'name' => 'US OUED ZEM',
                'nd_technique' => '0523524785',
                'debit_cible' => '4 Mbps',
                'router_ip' => '10.110.72.1',
                'hostname' => 'SR10-SITE72',
            ],
            [
                'name' => 'US BOUJAAD',
                'nd_technique' => '0523525681',
                'debit_cible' => '4 Mbps',
                'router_ip' => '10.110.74.1',
                'hostname' => 'SR10-SITE74',
            ],
        ];

        foreach ($agencies as $agencyData) {
            Agency::updateOrCreate(
                ['router_ip' => $agencyData['router_ip']],
                $agencyData
            );
        }
    }
}
