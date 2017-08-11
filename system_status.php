<?php
class SystemStatus {
	public function GetCoreInformation() {
		$data = file('/proc/stat');
		$cores = array();
		foreach ($data as $line) {
			if (preg_match('/^cpu[0-9]/', $line)) {
				$info = explode(' ', $line);
				$cores[] = array('user' => $info[1], 'nice' => $info[2], 'sys' => $info[3], 'idle' => $info[4], 'iowait' => $info[5], 'irq' => $info[6], 'softirq' => $info[7]);
			}
		}
		return $cores;
	}
	public function GetCpuPercentages($stat1, $stat2) {
		if (count($stat1) !== count($stat2)) {
			return;
		}
		$cpus = array();
		for ($i = 0, $l = count($stat1); $i < $l; $i++) {
			$dif = array();
			$dif['use'] = $stat2[$i]['user'] - $stat1[$i]['user'];
			$dif['nice'] = $stat2[$i]['nice'] - $stat1[$i]['nice'];
			$dif['sys'] = $stat2[$i]['sys'] - $stat1[$i]['sys'];
			$dif['idle'] = $stat2[$i]['idle'] - $stat1[$i]['idle'];
			$dif['iowait'] = $stat2[$i]['iowait'] - $stat1[$i]['iowait'];
			$dif['irq'] = $stat2[$i]['irq'] - $stat1[$i]['irq'];
			$dif['softirq'] = $stat2[$i]['softirq'] - $stat1[$i]['softirq'];
			$total = array_sum($dif);
			$cpu = array();
			foreach ($dif as $x => $y) {
				$cpu[$x] = round($y / $total * 100, 2);
			}
			$cpus[$i] = $cpu;
		}
		return $cpus;
	}
	public function GetCpuInfo() {
		$stat1 = $this->GetCoreInformation();
		sleep(1);
		$stat2 = $this->GetCoreInformation();
		$data = $this->GetCpuPercentages($stat1, $stat2);
		return $data;
	}
	public function GetMemoryInfo() {
		$mem = file_get_contents('/proc/meminfo');
		$mem = str_replace([' ', 'kB'], ['', ''], $mem);
		$mem = explode(PHP_EOL, $mem);
		for ($i = 0; $i < count($mem); $i++) {
			if ($mem[$i] == '') {
				unset($mem[$i]);
			}
		}
		$meminfo = [];
		foreach ($mem as $key => $value) {
			$tmp_memdata = explode(':', $value);
			$meminfo[$tmp_memdata[0]] = intval($tmp_memdata[1]);
		}
		return $meminfo;
	}
	public function formatsize($size) {
		$danwei = array(' B ', ' K ', ' M ', ' G ', ' T ');
		$allsize = array();
		$i = 0;
		for ($i = 0; $i < 5; $i++) {
			if (floor($size / pow(1024, $i)) == 0) {break;}
		}
		for ($l = $i - 1; $l >= 0; $l--) {
			$allsize1[$l] = floor($size / pow(1024, $l));
			$allsize[$l] = $allsize1[$l] - $allsize1[$l + 1] * 1024;
		}
		$len = count($allsize);
		for ($j = $len - 1; $j >= 0; $j--) {
			$fsize = $fsize . $allsize[$j] . $danwei[$j];
		}
		return $fsize;
	}
	public function GetNetworkFlow() {
		$strs = @file("/proc/net/dev");
		for ($i = 2; $i < count($strs); $i++) {
			preg_match_all("/([^\s]+):[\s]{0,}(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)/", $strs[$i], $info);
			$NetOutSpeed[$i] = $info[10][0];
			$NetInputSpeed[$i] = $info[2][0];
			$NetInput[$i] = $this->formatsize($info[2][0]);
			$NetOut[$i] = $this->formatsize($info[10][0]);
		}
		return [
			'NetOut2' => $NetOut[2],
			'NetOut3' => $NetOut[3],
			'NetOut4' => $NetOut[4],
			'NetOut5' => $NetOut[5],
			'NetOut6' => $NetOut[6],
			'NetOut7' => $NetOut[7],
			'NetOut8' => $NetOut[8],
			'NetOut9' => $NetOut[9],
			'NetOut10' => $NetOut[10],
			'NetInput2' => $NetInput[2],
			'NetInput3' => $NetInput[3],
			'NetInput4' => $NetInput[4],
			'NetInput5' => $NetInput[5],
			'NetInput6' => $NetInput[6],
			'NetInput7' => $NetInput[7],
			'NetInput8' => $NetInput[8],
			'NetInput9' => $NetInput[9],
			'NetInput10' => $NetInput[10],
			'NetOutSpeed2' => $NetOutSpeed[2],
			'NetOutSpeed3' => $NetOutSpeed[3],
			'NetOutSpeed4' => $NetOutSpeed[4],
			'NetOutSpeed5' => $NetOutSpeed[5],
			'NetInputSpeed2' => $NetInputSpeed[2],
			'NetInputSpeed3' => $NetInputSpeed[3],
			'NetInputSpeed4' => $NetInputSpeed[4],
			'NetInputSpeed5' => $NetInputSpeed[5]];
	}
	public function GetNetworkBandwidth() {
		$NetOut = $this->GetNetworkFlow();
		$NetInput = $this->GetNetworkFlow();
		if (false !== ($strs = @file("/proc/net/dev")));
		$net_card = [];
		for ($i = 2; $i < count($strs); $i++) {
			preg_match_all("/([^\s]+):[\s]{0,}(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)/", $strs[$i], $info);
			$net_card[$info[1][0]] = [
				'NetOutput' => $NetOut['NetOut' . $i],
				'NetInput' => $NetOut['NetInput' . $i],
			];
		}
		return $net_card;
	}
	public function GetSystemUptime() {
		if (false === ($str = @file("/proc/uptime"))) {
			return false;
		}
		$str = explode(" ", implode("", $str));
		$str = trim($str[0]);
		$min = $str / 60;
		$hours = $min / 60;
		$days = floor($hours / 24);
		$hours = floor($hours - ($days * 24));
		$min = floor($min - ($days * 60 * 24) - ($hours * 60));
		if ($days !== 0) {
			$res['uptime'] = $days . "天";
		}
		if ($hours !== 0) {
			$res['uptime'] .= $hours . "小时";
		}
		$res['uptime'] .= $min . "分钟";
		return $res['uptime'];
	}
}