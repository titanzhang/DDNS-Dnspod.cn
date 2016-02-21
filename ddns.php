<?php
// error_reporting(0);
require __DIR__.'/dnspod.php';
require __DIR__.'/config.php'; // pseudo config
if (file_exists(__DIR__.'/../config.php')) {
    require __DIR__.'/../config.php';
}

$dnsRecord = new DnsRecord();
$dnsRecord->domainID = $CONFIG_API->domainID;
$dnsRecord->subDomain = $CONFIG_API->subDomain;

// Get DNS record
$dnspod = new dnspod($CONFIG_API->apiID, $CONFIG_API->apiToken);
$response = $dnspod->apiCall('Record.List',
    array('domain_id' => $dnsRecord->domainID,
        'sub_domain' => $dnsRecord->subDomain
    )
);
$records = $response["records"];
if (count($records) < 1) {
    exit("Error: no dns record return\n");
}
$dnsRecord->ipAddress = $records[0]["value"];
$dnsRecord->recordID = $records[0]["id"];
$dnsRecord->recordLine = $records[0]["line"];
$dnsRecord->recordType = $records[0]["type"];

// Get my IP address
$myIP = $dnspod->getData("http://www.dnspod.com/About/IP");
if (!$myIP) {
    exit("Error: Can not get my IP\n");
}

if ($myIP == $dnsRecord->ipAddress) {
    exit("Notice: IP does not change\n");
}

// Update DNS record
$dnsRecord->ipAddress = $myIP;
$response = $dnspod->apiCall('Record.Modify',
    array('domain_id' => $dnsRecord->domainID,
        'record_id' => $dnsRecord->recodeID,
        'sub_domain' => $dnsRecord->subDomain,
        'record_type' => $dnsRecord->recordType,
        'record_line' => $dnsRecord->recordLine,
        'value' => $dnsRecord->ipAddress
    )
);

exit("Notice: IP change to $myIP\n");

