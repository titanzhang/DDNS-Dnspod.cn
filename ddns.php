<?php
// error_reporting(0);
require __DIR__.'/dnspod.php';
require __DIR__.'/config.php'; // pseudo config
if (file_exists(__DIR__.'/../config.php')) {
    require __DIR__.'/../config.php';
}

function getRecord($vendor, $dnsRecord)
{
    return $vendor->apiCall('Record.List',
        array('domain_id' => $dnsRecord->domainID,
        'sub_domain' => $dnsRecord->subDomain
        )
    );
}

function updateRecord($vendor, $dnsRecord)
{
    return $vendor->apiCall('Record.Modify',
        array('domain_id' => $dnsRecord->domainID,
            'record_id' => $dnsRecord->recordID,
            'sub_domain' => $dnsRecord->subDomain,
            'record_type' => $dnsRecord->recordType,
            'record_line' => $dnsRecord->recordLine,
            'value' => $dnsRecord->ipAddress
        )
    );
}

$dnsRecord = new DnsRecord();
$dnsRecord->domainID = $CONFIG_API->domainID;
$dnsRecord->subDomain = $CONFIG_API->subDomain;

$dnspod = new dnspod($CONFIG_API->globalServer,
                     $CONFIG_API->apiID,
                     $CONFIG_API->apiToken);

// Get DNS record
$response = getRecord($dnspod, $dnsRecord);
$records = $response["records"];
if (count($records) < 1) {
    exit("Error: no dns record return\n");
}

// Get my IP address
$myIP = $dnspod->getData("http://www.dnspod.com/About/IP");
if (!$myIP) {
    echo("Error: Can not get my IP\n");
}

// Get my IP address (IPv6)
$myIPv6 = $dnspod->getData("http://v6.ipv6-test.com/api/myip.php");
if (!$myIPv6) {
    echo("Error: Can not get my IP(v6)\n");
}

if (!$myIP && !$myIPv6)
{
    exit("No IP info can be obtained. Exit.\n");
}

foreach($records as $record)
{
    $dnsRecord->ipAddress = $record["value"];
    $dnsRecord->recordID = $record["id"];
    $dnsRecord->recordLine = strtolower($record["line"]);
    $dnsRecord->recordType = $record["type"];

    if ($myIP == $dnsRecord->ipAddress || $myIPv6 == $dnsRecord->ipAddress) {
        echo("Notice: IP does not change\n");
        continue;
    }

    // Update DNS record
    if ($dnsRecord->recordType == "A") {
        $dnsRecord->ipAddress = $myIP;
    }
    else {
        $dnsRecord->ipAddress = $myIPv6;
    }
    updateRecord($dnspod, $dnsRecord);

    echo("Notice: IP change to ".$dnsRecord->ipAddress."\n");
}
