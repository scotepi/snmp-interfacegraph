<?php
session_start();

/**
    Script by Scott Peters, @scotepi
    Version 1.3
    
    1.3:
        - The forward and back buttons will now work for changing graphs & pages
    
    1.2:
        - Fixed overflow: none from hiding the host & interface list on small frames
        - Disabled the last console.log
        - Added another example host
        
    1.1:
        - Added support for location.search on index.htm by using
            * ?hostname=host&name=My Host
            * ?hostname=host&interface=4&name=My Interface
            
    1.0:
        - Inital Release
**/


// SNMPv2 Community String
$community = 'public';
$snmpVersion = SNMP::VERSION_2C; // SNMP::VERSION_2C or SNMP::VERSION_1, only tested with 2C

// 'hostname' => 'Friendly Name',
$hosts = array(
    'gateway' => 'Main Gateway',
    'switch' => 'Main Switch',
);





// Use ?clear=true to clear the session data for testing
if (isset($_GET['clear'])) session_unset();


if (isset($_REQUEST['hostname'])) {
    $hostname = $_REQUEST['hostname'];
    
    $snmp = new snmp($snmpVersion, $hostname, $community);
    $snmp->oid_output_format = SNMP_OID_OUTPUT_FULL;
    $snmp->valueretrieval = SNMP_VALUE_PLAIN;

    if (isset($_REQUEST['interface'])) {
        $interface = $_REQUEST['interface'];
        
        // Old Values
        $prev = $_SESSION[$hostname][$interface]['data'];
        
        // Current Values
        $time = time();
        $ifInOctets = $snmp->get('.1.3.6.1.2.1.2.2.1.10.' . $interface);
        $ifOutOctets = $snmp->get('.1.3.6.1.2.1.2.2.1.16.' . $interface);
        
        if (!isset($_SESSION[$hostname][$interface]['ifSpeed'])) {
            $_SESSION[$hostname][$interface]['ifSpeed'] = $snmp->get('.1.3.6.1.2.1.2.2.1.5.' . $interface);
        }
        
        // Over Time
        $timeDiff = $time - $prev['time'];
        $inDiff = $ifInOctets - $prev['ifInOctets'];
        $outDiff = $ifOutOctets - $prev['ifOutOctets'];
        
        $data = array(
            'ifInOctets' => $ifInOctets,
            'ifOutOctets' => $ifOutOctets,
            'time' => $time,
            'timeDiff' => $timeDiff,
            'inDiff' => $inDiff,
            'outDiff' => $outDiff,
        );
            
        if ($inDiff != 0 and $outDiff != 0) {
            $_SESSION[$hostname][$interface]['data'] = $data;
        }
        
        @header("Content-Type: application/json; charset=UTF-8");
        echo json_encode($_SESSION[$hostname][$interface]);
        die();

    } else {
        $interfaceNames = $snmp->walk('.iso.3.6.1.2.1.31.1.1.1', true);
        
        if (!isset($_SESSION[$hostname])) $_SESSION[$hostname] = array();
        $interfaces = array();
        
        foreach ($interfaceNames as $mibs=>$value) {
            list($mib, $if) = explode('.', $mibs);
            
            switch ($mib) {
                case 1: $_SESSION[$hostname][$if]['interface'] = $value; break;
                case 15: $_SESSION[$hostname][$if]['ifSpeed'] = $value * 1000000; break;
                case 18: $_SESSION[$hostname][$if]['name'] = $value; break;
            }
            
            $_SESSION[$hostname][$if]['host'] = $hosts[$hostname];
            $_SESSION[$hostname][$if]['data'] = array(
                                'ifInOctets' => 0,
                                'ifOutOctets' => 0,
                                'time' => time(),
                            );
            
            if (isset($_SESSION[$hostname][$if]['name']) and !empty($_SESSION[$hostname][$if]['name'])) {
                $interfaces[$if] = $_SESSION[$hostname][$if]['interface'] . ' ' . $_SESSION[$hostname][$if]['name'];
            }
        }
        
        @header("Content-Type: application/json; charset=UTF-8");
        echo json_encode($interfaces);
        die();
    }
    
    $snmp->close();
} else {
    @header("Content-Type: application/json; charset=UTF-8");
    echo json_encode($hosts);
    die();
}
