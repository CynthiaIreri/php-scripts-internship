require_once __DIR__.'/../postRequest.php';
require_once __DIR__.'/../pingpostCommon.php';
require_once __DIR__.'/../HealthNormalizer.php';
use GuzzleHttp\Client;
class PostRequest_CallTools extends PostRequest
{
    const URL = '';
    protected function _getRequest()
    {
        $lead = HealthNormalizer::normalize($this->postObject->getLeadPostData());
        $request = [
            'phone_number'      => $lead['homephone'],
            'first_name'        => $lead['name'],
            'last_name'         => $lead['lastname'],
            'email_address'     => $lead['emailaddress'],
            'address'           => $lead['address'],
            'city'              => $lead['city'],
            'state'             => $lead['st'],
            'zip_code'          => $lead['zip'],
            'country'           => $lead['country'],
            'hot_lead'          =>'1',
            'score'             =>'0',
            'dial_duplicate'    =>'0',
            'custom4'           => $lead['vendorid'],
            'file'              => !empty($this->config['calltools_file_id']) ? $this->config['calltools_file_id'] : ''
        ];
        return json_encode($request);
    }
    protected function _executeRequest()
    {
        $client = new Client();
        $options = [
            'exceptions' => false,
            'body' => $this->getRequest(),
            'headers' => [
                'Authorization' => 'Token '. $this->config['calltools_api_token'],
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ]
        ];
        try {
            $response = $client->post(self::URL, $options);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        return (string)$response->getBody();
    }
    protected function _executeTestRequest()
    {
    }
    protected function _wasRequestSuccessful()
    {
        if (!$this->request) {
            throw new Exception('No request sent yet.');
        }
        $json = json_decode($this->getResponse());
        if (!empty($json->id)) {
            return true;
        }
        return false;
    }
}