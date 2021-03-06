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

    public function __construct($apiID, $apiToken) {
        $this->apiToken = $apiToken;
        $this->apiID = $apiID;
    }

    public function apiCall($api, $data) {
        if ($api == '' || !is_array($data)) {
            $this->message('Error', 'API not specified');
        }

        $api = 'https://dnsapi.cn/' . $api;
        $data = array_merge($data, array('login_token' => $this->apiID.",".$this->apiToken,
            'format' => 'json', 'lang' => 'en', 'error_on_empty' => 'no'));

        $result = $this->postData($api, $data);
        if (!$result) {
            $this->message('Error', 'Fail to call API '.$api);
        }

        $results = @json_decode($result, 1);
        if (!is_array($results)) {
            $this->message('Error', 'Invalid response format');
            var_dump($result);
        }
        
        if ($results['status']['code'] != 1 && $results['status']['code'] != 50) {
            $this->message('Error', 'Server response: '.$results['status']['message']);
        }
        
        return $results;
    }

    public function message($status, $message) {
        $text = $status.":\t".$message."\n";
        exit($text);
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
