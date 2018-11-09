<?php
/**
 * Created by PhpStorm.
 * User: cvaldovinos
 * Date: 6/14/18
 * Time: 5:22 PM
 */
require_once __DIR__ . '/../../../classes/pingpostCommon.php';
class SpeedToContactAuto
{
    protected $coverages = [
        'STANDARD'     => [
            'numeric' => '100/300/50',
            'text'    => 'Standard (Bodily Injury = $100K/person | $300K/accident; Property Damage = $50K)',
        ],
        'STATEMINIMUM' => [
            'numeric' => '15/30/5',
            'text'    => 'State Minimum',
        ],
        'BASIC'        => [
            'numeric' => '50/100/25',
            'text'    => 'Basic (Bodily Injury = $50K/person | $100K/accident; Property Damage = $25K)',
        ],
        'SUPERIOR'     => [
            'numeric' => '250/500/100',
            'text'    => 'Superior (Bodily Injury = $250K/person - $500K/accident; Property Damage = $100K)',
        ],
    ];
    public function mapAutoPayload($lead)
    {
        $desiredCoverageType = $this->coverages['STANDARD']['text'];
        if (isset($this->coverages[$lead['desiredcoveragetype']])) {
            $desiredCoverageType = $this->coverages[$lead['desiredcoveragetype']]['text'];
        }
        $request = [
            'CURRENTINSURANCECOMPANY'        => $lead['CURRENTINSURANCECOMPANY'],
            'desiredcollisiondeductible'     => $lead['desiredcollisiondeductible'],
            'desiredcomprehensivedeductible' => $lead['desiredcomprehensivedeductible'],
            'currentlyinsured'               => $lead['currentlyinsured'] == 1,
            'desiredcoveragetype'            => $desiredCoverageType,
            'coveragetypekey'                => $lead['desiredcoveragetype'],
            'yearsatresidence'               => !empty($lead['yearsatresidence']) ? $lead['yearsatresidence'] : 0,
            'driver1firstname'               => $lead['name'],
            'driver1lastname'                => $lead['lastname'],
            'currentpolicyexpiration'        => !empty($lead['currentpolicyexpiration']) ? $lead['currentpolicyexpiration'] : '',
            'vendor'                         => $lead['vendorid']
        ];
        $drivers = PingPostCommon::getDrivers($lead);
        $driverCount = 1;
        foreach ($drivers as $driver) {
            $hasDui = false;
            if (!empty($lead["driver{$driverCount}dui"]) && $lead["driver{$driverCount}dui"] == 1) {
                $hasDui = true;
            }
            $request += [
                "driver{$driverCount}gender"                  => $driver['gender'],
                "driver{$driverCount}maritalstatus"           => strtolower($driver['maritalstatus']),
                "driver{$driverCount}edulevel"                => $driver['edulevel'],
                "driver{$driverCount}license_age"             => $driver['licenseage'],
                "driver{$driverCount}age"                     => !empty($driver['age']) ? $driver['age'] : PingPostCommon::getAge($driver['dob_year'], $driver['dob_month'],$driver['dob_day']),
                "driver{$driverCount}dob_month"               => $driver['dob_month'],
                "driver{$driverCount}dob_day"                 => $driver['dob_day'],
                "driver{$driverCount}dob_year"                => $driver['dob_year'],
                "driver{$driverCount}sr22"                    => $driver['sr22'] == 1,
                "driver{$driverCount}dui"                     => $hasDui ? 'yes' : 'no',
                "driver{$driverCount}occupation"              => $driver['occupation'],
                "driver{$driverCount}licenseage"              => $driver['licenseage'],
                "driver{$driverCount}relationshiptoapplicant" => strtolower($driver['relationshipToApplicant']),
                "driver{$driverCount}yearsatresidence"        => isset($driver['yearsatresidence']) ? $driver['yearsatresidence'] : 0,
            ];
            $driverCount++;
        }
        $vehicles = PingPostCommon::getVehicles($lead);
       
        $vehicleCount = 1;
        foreach ($vehicles as $vehicle) {
            $vin = PingPostCommon::getVINStub($vehicle['year'], $vehicle['make'], $vehicle['model'], $vehicle['trim']);
            $request += [
                "vehicle{$vehicleCount}year"              => $vehicle['year'],
                "vehicle{$vehicleCount}make"              => $vehicle['make'],
                "vehicle{$vehicleCount}model"             => $vehicle['model'],
                "vehicle{$vehicleCount}trim"              => $vehicle['trim'],
                "vehicle{$vehicleCount}leased"            => $vehicle['leased'] == 'yes',
                "vehicle{$vehicleCount}primaryUse"        => $vehicle['primaryUse'],
                "vehicle{$vehicleCount}commutedays"       => $vehicle['commutedays'],
                "vehicle{$vehicleCount}distance"          => $vehicle['commuteAvgMileage'],
                "vehicle{$vehicleCount}commuteAvgMileage" => $vehicle['commuteAvgMileage'],
                "vehicle{$vehicleCount}annualMileage"     => $vehicle['annualMileage'],
                "vehicle{$vehicleCount}alarm"             => $vehicle['alarm'] == 1,
                "vehicle{$vehicleCount}garagetype"        => $this->mapGarageType($vehicle['garageType']),
                "vehicle{$vehicleCount}vin"               => $vin
            ];
           
            $vehicleCount++;
        }
        return $request;
    }
    public function mapGarageType($garageType)
    {
        $garageTypeMapping = [
            'no cover'         => 'nocover',
            'nocover'          => 'nocover',
            'car Port'         => 'carport',
            'carport'          => 'carport',
            'attached-carport' => 'carport',
            'on Street'        => 'nocover'
        ];
        if (array_key_exists(strtolower($garageType), $garageTypeMapping)) {
            return $garageTypeMapping[$garageType];
        }
       
        return 'garage';
    }
}