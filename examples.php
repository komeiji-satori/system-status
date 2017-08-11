<?php
error_reporting(0);
include 'system_status.php';

$SystemStatus = new SystemStatus();
print_r($SystemStatus->GetNetworkBandwidth());
print_r($SystemStatus->GetSystemUptime());
print_r($SystemStatus->GetCpuInfo());
print_r($SystemStatus->GetMemoryInfo());
print_r($SystemStatus->GetNetworkBandwidth());