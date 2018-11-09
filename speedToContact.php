<?php
/**
 * Created by PhpStorm.
 * User: cvaldovinos
 * Date: 6/11/18
 * Time: 10:23 AM
 */
require_once __DIR__.'/../../classes/Integration/Homeins/SpeedToContactHome.php';
require_once __DIR__. '/../../classes/Integration/Autoins/SpeedToContactAuto.php';
use GuzzleHttp\Client;
class PostRequest_SpeedToContact extends PostRequest
{
    protected function _getRequest()
    {
        $lead = $this->postObject->getLeadPostData();
        $payload = null;
        switch ($this->postObject->getLeadType()) {
            case 'homeins':
                $payload = (new SpeedToContactHome)->mapHomePayload($lead);
                break;
            case 'autoins':
            default:
                $payload = (new SpeedToContactAuto)->mapAutoPayload($lead);
        }
        return json_encode($payload);
    }
   
    protected function _executeRequest()
    {
        $client = new Client([
            'defaults' => [
                'headers' => [
                    'content-Type' => 'application/json'
                ],
                'verify'  => false
            ]
        ]);
        try {
            $response = $client->post($this->config['speed_to_contact_url'], ['body' => ($this->_getRequest())]);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        return (string)$response->getBody();
    }
   
    protected function _wasRequestSuccessful()
    {
        $jsonResponse = json_decode($this->response, true);
        if ($jsonResponse && $jsonResponse['status']) {
            return true;
        }
        return false;
    }
    protected function _executeTestRequest()
    {
    }
}