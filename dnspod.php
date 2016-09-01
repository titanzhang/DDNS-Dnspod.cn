<?php
class DnsRecord {
    var $domainID;
    var $subDomain;
    var $recordID;
    var $recordLine;
    var $recordType;
    var $ipAddress;
}

class dnspod {
    private $apiID;
    private $apiToken;

    public function __construct($is_global_serv, $apiID, $apiToken) {
        $this->is_global_serv = $is_global_serv;
        $this->apiToken = $apiToken;
        $this->apiID = $apiID;
    }

    public function apiCall($api, $data) {
        if ($api == '' || !is_array($data)) {
            $this->message('Error', 'API not specified');
        }

        if ($this->is_global_serv) {
            $api = 'https://api.dnspod.com/' . $api;
            $token_key = 'user_token';
            
        }
        else {
            $api = 'https://dnsapi.cn/' . $api;
            $token_key = 'login_token';
        }
        
        $data = array_merge($data, array($token_key => $this->apiID.','.$this->apiToken,
            'format' => 'json', 'lang' => 'en', 'error_on_empty' => 'no'));

        $result = $this->postData($api, $data);
        if (!$result) {
            $this->message('Error', 'Fail to call API '.$api);
            return;
        }

        $results = @json_decode($result, 1);
        if (!is_array($results)) {
            $this->message('Error', 'Invalid response format');
            var_dump($result);
            return;
        }
        
        if ($results['status']['code'] != 1 && $results['status']['code'] != 50) {
            $this->message('Error', 'Server response: '.$results['status']['message']);
            return;
        }
        
        return $results;
    }

    public function message($status, $message) {
        $text = $status.":\t".$message."\n";
        echo($text);
    }

    private function postData($url, $data) {
        if ($url == '' || !is_array($data)) {
            $this->message('Error', 'Invalid API or parameter');
        }

        $ch = @curl_init();
        if (!$ch) {
            $this->message('Error', 'CURL not supported');
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSLVERSION, 1);
        // curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_USERAGENT, 'Liang\'s DDNS Client/1.0.0 (titanzhang@gmail.com)');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    public function getData($url) {
        if ($url == '') {
            $this->message('Error', 'GET: URL not specified');
        }

        $ch = @curl_init();
        if (!$ch) {
            $this->message('Error', 'GET: CURL not supported', '');
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSLVERSION, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Liang\'s DDNS Client/1.0.0 (titanzhang@gmail.com)');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }
}
