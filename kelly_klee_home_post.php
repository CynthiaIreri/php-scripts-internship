<?php
require_once __DIR__ . '/../classes/pingpostCommon.php';
require_once __DIR__ . '/../classes/HomeNormalizer.php';
use Ue\Airlock\Response;
use GuzzleHttp\Client;
//$url = '';
$url = '';
// exclusive, semi-exclusive
$channel = isset($_POST['channel']) ? $_POST['channel'] : (isset($_GET['channel']) ? $_GET['channel'] : null);
if (empty($channel)) {
    PingPostCommon::ping_response(Response::ERROR, Response::FAILURE_INTERNAL_FILTER, 'Missing channel');
    exit;
}
$pingPost = new PingPostCommon();
$lead = HomeNormalizer::normalize($pingPost->createLeadFromRequest());
$dateOfBirth = $lead['dob_month'] . '-' . $lead['dob_day'] . '-' . $lead['dob_year'];
$xml = '
<postRequest>
    <source_lead_id>' . $lead['id'] . '</source_lead_id>
    <leadExclusive>' . ($channel == 'exclusive' ? 1 : 0) . '</leadExclusive>
    <leadPrice>' . ($channel == 'exclusive' ? $lead['ping_meta']['bid_exclusive'] : $lead['ping_meta']['bid_shared']) . '</leadPrice>
    <marketingSource />
    <reportingSource1 />
    <reportingSource2 />
    <sellerLeadIdentifier>' . $lead['id'] . '</sellerLeadIdentifier>
    <leadIP>' . $lead['ipaddress'] . '</leadIP>
    <leadDatetime>' .date('Y-m-d\TH:i:s\Z').'</leadDatetime>
    <phoneConsent>' . (!empty($lead['optin']) ? 'true' : 'false' ) . '</phoneConsent>
    <leadiDToken>' . (!empty($lead['universal_leadid']) ? $lead['universal_leadid'] : ''). '</leadiDToken>
    <lead>
        <products>
            <add type="HomeownersInsurance" />
        </products>
        <contact>
            <firstName>' . $lead['name'] . '</firstName>
            <lastName>' . $lead['lastname'] . '</lastName>
            <primaryPhone>' . $lead['homephone'] . '</primaryPhone>
            <subsourceId>' . $lead['vendorid'] . '</subsourceId>
            <city>' . $lead['city'] . '</city>
            <state>' . $lead['st'] . '</state>
            <zipCode>' . $lead['zip'] . '</zipCode>
        </contact>
        <homeownersInsurance>
            <customerProfile>
                <gender>'.strtoupper(substr($lead['gender'], 0, 1)).'</gender>
                <maritalStatus />
                <residence>
                    <months>12</months>
                    <own>' . ($lead['property_ownership'] == 'own' ? 'true' : 'false') . '</own>
                </residence>
                <credit>
                    <rating>' . $lead['credit'] . '</rating>
                    <bankruptcy>false</bankruptcy>
                </credit>
                <dateOfBirth>' . $dateOfBirth . '</dateOfBirth>
            </customerProfile>
            <propertyAddress>
                <address>' . $lead['address'] . '</address>
                <city>' . $lead['city'] . '</city>
                <state>' . $lead['st'] . '</state>
                <zipCode>' . $lead['zip'] . '</zipCode>
            </propertyAddress>
            <propertyProfile>
                <businessOrFarmingConducted>false</businessOrFarmingConducted>
                <propertyType>' . $lead['propertytype'] . '</propertyType>
                <numberOfUnits>1</numberOfUnits>
                <dangerousDog>false</dangerousDog>
                <constructionDetails>
                    <yearBuilt>' . $lead['year_built'] . '</yearBuilt>
                    <livableSquareFeet>' . $lead['house_sqft'] . '</livableSquareFeet>
                    <bedrooms>' . $lead['bedrooms'] . '</bedrooms>
                    <bathrooms>' . $lead['bathrooms'] . '</bathrooms>
                    <garage>' . $lead['garage_type'] . '</garage>
                    <heating>' . (!empty($lead['primary_heating']) ? 'Other' : 'None') . '</heating>
                    <securitySystem>' . $lead['security_system'] . '</securitySystem>
                    <fireAlarm>' . (!empty($lead['fire_alarm']) ? 'Monitored' : 'None') . '</fireAlarm>
                    <stories>' . $lead['house_stories'] . '</stories>
                    <roofType>' . $lead['rooftype'] . '</roofType>
                    <exteriorWalls/>
                    <basement>' . $lead['foundation'] . '</basement>
                </constructionDetails>
            </propertyProfile>
            <propertyFeatures>
                <deadbolt>true</deadbolt>
                <centralAirConditioning>false</centralAirConditioning>
                <swimmingPool>false</swimmingPool>
                <smokeDetector>' . (!empty($lead['fire_alarm']) ? 'true' : 'false') . '</smokeDetector>
                <deck>false</deck>
                <fireExtinguisher>false</fireExtinguisher>
                <trampoline>false</trampoline>
                <fireplace>false</fireplace>
                <sauna>false</sauna>
                <hotTub>false</hotTub>
                <woodBurningStove>false</woodBurningStove>
                <sumpPump>false</sumpPump>
                <fireHydrantWithin1000Feet>true</fireHydrantWithin1000Feet>
                <fireStationWithin5Miles>true</fireStationWithin5Miles>
            </propertyFeatures>
            <requestedCoverage>
                <deductible>' . $lead['desired_deductible'] . '</deductible>
                <personalLiability>' . $lead['personal_liability_coverage'] . '</personalLiability>
                <replacementCost />
            </requestedCoverage>
        </homeownersInsurance>
    </lead>
</postRequest>';
$xml = PingPostCommon::cleanXML($xml);
$client = new Client([
    'defaults' => [
        'exceptions' => false,
        'headers' => [
            'Content-Type' => 'application/json',
            'x-api-key' => 'A3eaWDLDBxmZKMA7WjcR7MGjdYdAGhf1NffMRCri',
        ],
    ],
]);
try {
    $response = $client->post($url, ['body' => $xml])->json();
    $pingPost->setRequest($xml);
    $pingPost->setResponse(json_encode($response));
    if ($response['success'] == true) {
        $pingPost->printPostSuccess();
        exit;
    }
    $pingPost->printPostError(null, Response::FAILURE_GENERAL);
} catch (\Exception $e) {
    echo $e->getMessage();
    exit;
}