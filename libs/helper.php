<?php
/**
 * @author      Betapcode <betapcode@gmail.com>
 * @project		miner api tool
 * @created     17/01/2018
 * @version     1.0.0
 */
require_once 'Apiminer.php';

class Helper {
    private $app;
    function __construct() {
        $this->app = new Apiminer();
    }

    function getDataInfoMiner($ip){
        try {
            $dataInfoMiner  = $this->app->request($ip, 'stats');
        } catch (Exception $e) {
            $dataInfoMiner = null;
        }
        
        $arr = array(
            "ip" => $ip,
            "status"  => "Timeout !",
            "type" => "",
            "version" => "",
            "freq" => "",
            "elapsed" => "",
            "hashrate_5s" => "",
            "hashrate_avg" => "",
            "hw"        => "",
            "hwp"       => "",
            "temp"      => "",
            "fan"       => "",
            "pool"      => array()
        );

        if ($dataInfoMiner != null){

            $type           = $dataInfoMiner["BMMiner"]["Type"];
            $freq           = $dataInfoMiner["STATS0"]["frequency"];
            $hashRate_5s    = $dataInfoMiner["STATS0"]["GHS 5s"];
            $hashRate_avg   = $dataInfoMiner["STATS0"]["GHS av"];
            $hwp            = $dataInfoMiner["STATS0"]["Device Hardware%"];

            $status         = $this->getStatusName($dataInfoMiner["STATUS"]["STATUS"]);
            $version        = $this->formatTimeVersion($dataInfoMiner["BMMiner"]["CompileTime"]);

            $dataSummary    = $this->app->request($ip, 'summary');
            $elapsed        = $this->sumElapsed($dataSummary["SUMMARY"]["Elapsed"]);
            $hw             = $dataSummary["SUMMARY"]["Hardware Errors"];

            
            $temp           = $this->proccessItem($dataInfoMiner, "temp", 16);
            $fan            = $this->proccessItem($dataInfoMiner, "fan", 8);
            $pool           = $this->getPool($ip, "pools");

            $arr = array(
                "ip" => $ip,
                "status"  => $status,
                "type" => $type,
                "version" => $version,
                "freq" => $freq,
                "elapsed" => $elapsed,
                "hashrate_5s" => $hashRate_5s,
                "hashrate_avg" => $hashRate_avg,
                "hw"        => $hw,
                "hwp"       => $hwp,
                "temp"      => $temp,
                "fan"       => $fan,
                "pool"      => $pool
            );
        }
        return $arr;
    }

    function getPool($ip, $command){
        $dataInfo  = $this->app->request($ip, $command);
        $poolArr = array();
        if ($dataInfo != null) {
            for($i = 0; $i < 3; $i++){
                $tmpItem = "POOL". $i;
                $row = $dataInfo[$tmpItem];
                $_url = $row["URL"];
                $_user = $row["User"];
                $_itemArr = "pool".$i;
                $arrTmp = array(
                    $_itemArr => $_url,
                    "worker" => $_user
                );
                $poolArr[] = $arrTmp;
            }
        }
        return $poolArr;
    }

    function proccessItem($dataInfo, $itemName, $itemNum){
        $arr = array();
        for($i = 1; $i <= $itemNum; $i++){
            $_item = $itemName.$i;
            $tmpItem = $dataInfo["STATS0"][$_item];
            array_push($arr, $tmpItem);
        }
        $arrFilter = array_filter($arr, "tt_filter");
        $dataStr = implode("|", $arrFilter);
        return $dataStr;
    }

    function getStatusName($strStatus) {
        switch($strStatus) {
            case "W":
                $name = "Warning";
                break;
            case "I":
                $name = "Informational";
                break;
            case "S":
                $name = "Success";
                break;
            case "E":
                $name = "Error";
                break;
            case "F":
                $name = "Fatal (code bug)";
                break;
            default:
                $name = "Informational";
        }
        return $name;
    }

    function formatTimeVersion($time){
        $dt = new DateTime($time);
        $date = $dt->format('dmY');
        return $date;
    }

    function sumElapsed($value){
        $b = ''; //'&nbsp;';
        $s = $value % 60;
		$value -= $s;
		$value /= 60;
		if ($value == 0)
			$ret = $s.'s';
		else
		{
			$m = $value % 60;
			$value -= $m;
			$value /= 60;
			if ($value == 0)
				$ret = sprintf("%dm$b%02ds", $m, $s);
			else
			{
				$h = $value % 24;
				$value -= $h;
				$value /= 24;
				if ($value == 0)
					$ret = sprintf("%dh$b%02dm$b%02ds", $h, $m, $s);
				else
				{
					if ($value == 1)
						$days = '';
                    else
                        $days = '';
						// $days = 's';
	
					$ret = sprintf("%dd$days$b%02dh$b%02dm$b%02ds", $value, $h, $m, $s);
				}
			}
        }
        return $ret;
    }
    
}
?>
