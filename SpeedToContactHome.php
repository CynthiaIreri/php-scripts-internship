/**
 * Created by PhpStorm.
 * User: cvaldovinos
 * Date: 6/15/18
 * Time: 11:23 AM
 */
require_once __DIR__ . '/../../../classes/pingpostCommon.php';
class SpeedToContactHome
{
    public function mapHomePayload($lead)
    {
        $insured = false;
        if (in_array(strtolower($lead['currentinsured']), ['1', 'yes'])) {
            $insured = true;
        }
       
        $request = [
            'gender'                 => $lead['gender'],
            'property_type'          => $this->mapProperty($lead['propertytype']),
            'year_built'             => $lead['year_built'],
            'property_ownership'     => !empty($lead['property_ownership']) ? $lead['property_ownership'] : 'own',
            'owner_occupied'         => !empty($lead['leadtype']) ? 'yes' : 'no',
            'property_zip_code'      => $lead['zip'],
            'property_address'       => $lead['address'],
            'property_city'          => $lead['city'],
            'property_state'         => $lead['st'],
            'propertyCountry'        => 'USA',
            'construction_type'      => 'other',
            'foundation'             => $this->mapFoundation(strtolower($lead['foundation'])),
            'roof_type'              => $this->mapRoof(strtolower($lead['rooftype'])),
            'roof_age'               => $this->mapRoofAge($lead['roof_age']),
            'primary_heating_system' => 'electric',
            'bedrooms'               => $lead['bedrooms'],
            'bathrooms'              => $this->mapBathrooms($lead['bathrooms']),
            'stories'                => $this->mapStories($lead['house_stories']),
            'garage_type'            => $this->mapGarageType($lead['garage_type']),
            'square_feet'            => $lead['house_sqft'],
            'security_system'        => $this->mapSecurity($lead['security_system']),
            'fire_alarm'             => '',
            'dog_breed'              => strtolower($lead['dog_breeds']) == 'yes',
            'located_in_flood_plain' => '',
            'dead_bolts'             => '',
            'fire_extinguisher'      => '',
            'trampoline'             => '',
            'covered_deck_patio'     => '',
            'swimming_pool'          => '',
            'replacement_cost'       => '',
            'personal_liability'     => $this->mapPersonalLiability($lead['personal_liability_coverage']),
            'desired_deductible'     => $this->mapDeductibleValues($lead['desired_deductible']),
            'fiveYearClaims'         => (strtolower($lead['claims']) == 'yes') ? 'yes' : 'no',
            'currentlyInsured'       => $insured ? 'yes' : 'no',
            'vendor'                 => $lead['vendorid']
        ];
        return $request;
    }
    public function mapFoundation($foundation)
    {
        $foundations = [
            'basement'             => 'fully-finished',
            'fully-finished'       => 'fully-finished',
            'concreteslab'         => 'slab',
            'concrete slab'        => "slab",
            'slab'                 => "slab",
            'crawlspace'           => 'crawlspace'
        ];
        if (array_key_exists(strtolower($foundation), $foundations)) {
            return $foundations[$foundation];
        }
        return 'other';
    }
   
    public function mapBathrooms($bathroom)
    {
        $bathrooms = [
            '2'  => '2',
            '3'  => '3',
            '4'  => '4+',
            '5'  => '4+',
            '6'  => '4+',
            '7'  => '4+',
            '8'  => '4+',
            '9'  => '4+',
            '10' => '4+',
            '18' => '4+',
            '4+' => '4+'
        ];
        if (array_key_exists($bathroom, $bathrooms)) {
            return $bathrooms[$bathroom];
        }
       
        return '1';
    }
    public function mapStories($story)
    {
        $stories = [
            '1'        => 'single',
            'single'   => 'single',
            'double'   => 'double',
            '2'        => 'double',
            'trilevel' => 'trilevel',
            '3'        => 'trilevel'
        ];
        if (array_key_exists(strtolower($story), $stories)) {
            return $stories[$story];
        }
        return 'other';
    }
    public function mapGarageType($garage)
    {
        $garageTypes = [
            'attached1cargarage'    => 'attached1',
            'attached2cargarage'    => 'attached2',
            'attached3cargarage'    => 'attached3',
            'attached1'             => 'attached1',
            'attached2'             => 'attached2',
            'attached3'             => 'attached3',
            'attachedcarport'       => 'attached-carport',
            'attached-carport'      => 'attached-carport',
            'detached1cargarage'    => 'detached1',
            'detached2cargarage'    => 'detached2',
            'detached3cargarage'    => 'detached3',
            'detached1'             => 'detached1',
            'detached2'             => 'detached2',
            'detached3'             => 'detached3',
            'detachedcarport'       => 'detached-carport',
            'none'                  => 'nogarage',
            'nogarage'              => 'nogarage',
            'attached 1-car garage' => "attached1",
            'attached 2-car garage' => "attached2",
            'attached 3-car garage' => "attached3",
            'attached carport'      => "attached-carport",
            'detached carport'      => "detached-carport",
            'detached 1-car garage' => "detached1",
            'detached 2-car garage' => "detached2",
            'detached 3-car garage' => "detached3"
        ];
        if (array_key_exists(strtolower($garage), $garageTypes)) {
            return $garageTypes[$garage];
        }
        return 'other';
    }
    public function mapRoof($roof)
    {
        $roofs = [
            'asphalt shingle' => 'asphalt',
            'asphalt'         => 'asphalt',
            'metal'           => 'other',
            'other'           => 'other',
            'tile shingle'    => 'tile',
            'tileshingle'     => 'tile',
            'tile'            => 'tile'
        ];
        if (array_key_exists(strtolower($roof), $roofs)) {
            return $roofs[$roof];
        }
       
        return 'wood';
    }
    public function mapRoofAge($roofAge)
    {
        $roofsAges = [
            '1-3 years' => '1-5',
            '1_3_years' => '1-5',
            '1-5'       => '1-5',
            '4-6 years' => '1-5',
            '4_6_years' => '1-5',
            '7-9 years' => '6-10',
            '7_9_years' => '6-10',
            '10+ years' => '11-25',
            '10_years'  => '11-25',
            '1'         => '1-5',
            '2'         => '1-5',
            '3'         => '1-5',
            '4'         => '1-5',
            '5'         => '1-5',
            '6'         => '6-10',
            '7'         => '6-10',
            '8'         => '6-10',
            '9'         => '6-10',
            '10'        => '6-10',
            ''          => '6-10'
        ];
        if (array_key_exists(strtolower($roofAge), $roofsAges)) {
            return $roofsAges[$roofAge];
        }
        return '';
    }
    public function mapSecurity($security)
    {
        $securitySystems = [
            'homeonly'          => 'unmonitored',
            'monitoringcompany' => 'monitored'
        ];
        if (array_key_exists(strtolower($security), $securitySystems)) {
            return $securitySystems[$security];
        }
        return 'none';
    }
    public function mapProperty($property)
    {
        $properties = [
            'townhouse' => 'condo',
            'condo'     => 'condo',
            'mobile'    => 'mobile',
            'multi'     => 'condo'
        ];
        if (array_key_exists(strtolower($property), $properties)) {
            return $properties[$property];
        }
        return 'single';
    }
    public function mapPersonalLiability($personalLiability)
    {
        $liability = PingPostCommon::getClosest($personalLiability, [100000, 300000, 500000, 1000000]);
        $liabilities = [
            '300000'  => '300k',
            '500000'  => '500k',
            '1000000' => '1m',
            '5000000' => '1m'
        ];
        if (array_key_exists($liability, $liabilities)) {
            return $liabilities[$liability];
        }
        return '100k';
    }
    public function mapDeductibleValues($deductibleValue)
    {
        return '$' . PingPostCommon::getClosest($deductibleValue, [250, 500, 1000, 2000]);
    }
}