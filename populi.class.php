<?php
require_once("configuration.php");

class Populi{
    //Set to the correct URL for your college
    protected $api_url = API_URL;
    //You can set this to a valid access token - if null, you'll need to call login() before calling doTask()
    private $api_token = API_TOKEN; 
    
    public function login( $user_name, $password ){
        $params = 'username=' . urlencode($user_name) . '&password=' . urlencode($password);
        
        // Place the results into an XML string. We can't use file_get_contents since it randomly fails... so we now use curl
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_URL, $this->api_url);
        $response = curl_exec($curl);
        curl_close($curl);
        
        if( $response !== false) {
            // Use SimpleXML to put results into Simple XML object (requires PHP5)
            $xml = new SimpleXMLElement($response);
            if( isset($xml->access_key) ){
                $this->api_token = (string)$xml->access_key;
            }
            else{
                throw new PopuliException("Oops! Please enter a valid username/password.", 'AUTHENTICATION_ERROR');
            }
        }
        else{
            throw new PopuliException("Oops! We're having trouble connecting to Populi right now... please try again later.", 'CONNECTION_ERROR');
        }
    }
    
    public function logout(){
        $this->api_token = null;
    }
    
    //By default, we'll attempt to parse the response into a SimpleXML object and return that.
    //For certain tasks, though, (like downloadFile), you'll want the raw response and will need to set return_raw to true.
    public function doTask( $task, $params = array(), $return_raw = false ){
        if( !$this->api_token ){
            throw new Exception("Whoops! Please call login before trying to perform a task!");
        }
        
        $post = 'task=' . urlencode($task) . '&access_key=' . $this->api_token;
        
        foreach($params as $param => $value){
            if( is_array($value) ){
                foreach($value as $array_value){
                    $post .= '&' . $param . '[]=' . urlencode($array_value);
                }
            }
            else{
                $post .= "&$param=" . urlencode($value);
            }
        }
        //echo $post."\n"."<!---------------EOP-------------------/!>";
        //echo "\n\n";
        // Place the results into an XML string. We can't use file_get_contents since it randomly fails... so we now use curl
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_URL, $this->api_url);
        $response = curl_exec($curl);
        curl_close($curl);
        
        if( $curl !== false ){
            if( $return_raw ){
                return $response;
            }
            else{
                // Use Simple XML to put results into Simple XML object (requires PHP5)
                try{
                    $xml = new SimpleXMLElement($response);
                }
                catch(Exception $e){
                    echo htmlentities($response) . '<br><br><br>';
                    throw new PopuliException('Problem parsing the XML response: ' . $e->getMessage());
                }
                
                if( $xml->getName() == 'response' ){
                    return $xml;
                }
                else if( $xml->getName() == 'error' ){
                    throw new PopuliException((string)$xml->message, (string)$xml->code);
                }
                else{
                    //Woah - response or error should always be the root element
                    throw new PopuliException('Problem parsing the XML response: invalid root element.');
                }
            }
        }
        else{
            throw new PopuliException('Could not connect to Populi.', 'CONNECTION_ERROR');
        }
    }
}

class PopuliException extends Exception{
    /**************************************************************************************************
     *  We have our own variable since we don't feel like using numeric error codes
     *  Should be one of:
     *      AUTHENTICATION_ERROR - Couldn't login to the API (bad username/password)
     *      BAD_PARAMETER - You called a task using parameters it didn't like
     *      CONNECTION_ERROR - Thrown if we can't connect to Populi 
     *      LOCKED_OUT - Your user account is blocked (too many failed login attempts?)
     *      OTHER_ERROR - Default generic error
     *      PERMISSIONS_ERROR - You aren't allowed to call that task with those parameters
     *      UKNOWN_TASK - You tried to call an API task that doesn't exist
    ****************************************************************************************************/
    public $populi_code = null;
    
    public function __construct($message, $populi_code = 'OTHER_ERROR'){
        parent::__construct($message);
        $this->populi_code = $populi_code;
    }
    
    public function getPopuliCode(){
        return $this->populi_code;
    }
}
?>