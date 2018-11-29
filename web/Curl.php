<?php
/**
* @description          : All Curl method
* @author               : John
**/

class Curl {

    //call RESTFul API Server using GET method
    public function curl_get($data_url, $data_type= false,$data_userpwd = false, $authorization = false){
        $ch = curl_init();
        switch ($data_type) {
            case 'json':
                $header = array('Accept: application/json');
                break;
            case 'auth':
                $header = array('Authorization: Bearer '.$authorization);
                break;
            default:
                 $header = array('Accept: plain/text');
                break;
        }
       
        curl_setopt($ch,CURLOPT_URL,$data_url);
        curl_setopt($ch,CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);

        if($data_userpwd){
            curl_setopt($ch, CURLOPT_USERPWD, $data_userpwd);
        }
        
        $fp = curl_exec($ch);
        //error handle
        if (!$fp or curl_errno($ch)){ 
            curl_close($ch);
            return false;
        }
        else{
            curl_close($ch);
            return $fp;
        }      
    }

    //Use curl to post json data to Restful/JSON RPC API Server
    public function curl_post($data_url, $json_output,$data_userpwd = false,$authorization = false){
        $ch = curl_init();

        if($authorization){
            $header = array('Authorization: Bearer '.$authorization);
        } else {
            $header = array('Content-type: application/json', 'Accept: application/json', 'Content-Length: ' . strlen($json_output));
        }

        curl_setopt($ch,CURLOPT_URL,$data_url);         
        curl_setopt($ch,CURLOPT_CUSTOMREQUEST,'POST');
        curl_setopt($ch,CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $json_output);

        if($data_userpwd){
            curl_setopt($ch, CURLOPT_USERPWD, $data_userpwd);
        }

        $result = curl_exec($ch);
        //error handle
        if (!$result or curl_errno($ch)){ 
            curl_close($ch);
            return false;
        }
        else{
            curl_close($ch);
            return $result;
        }      
    }

    //get JSON from API Server using system CURL command tool
    public function curl_system_get($data_url){
        $command = "curl -X GET --header 'Accept: application/json' '" . $data_url . "'";
        $rs = exec($command);
        return $rs;
    }

    //get JSON from API Server using system CURL command tool
    public function curl_system_post($data_url, $json_output){
        $command = "curl -v -H 'Content-Type: application/json' -X POST -d '". $json_output."' '" . $data_url . "'";
        $rs = exec($command);
        return $rs;
    }

    //Use curl to post form data to server (like google)
    public function curl_post_form($data_url, $data_output){
        $ch = curl_init();

        curl_setopt($ch,CURLOPT_URL,$data_url);         
        curl_setopt($ch,CURLOPT_CUSTOMREQUEST,'POST');
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $data_output);

        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }


}
?>