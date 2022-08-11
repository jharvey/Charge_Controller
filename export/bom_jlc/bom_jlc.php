<?php
/*
*** BOM_JLC ***
A script to check your "Bill Of Materials" CSV-file for JLCPCB SMT Parts Library 
stock availability & prices.

Usage: 
1) bom_jlc.php check [file.csv] [jlc_part_number_column_name] [bom_part_number_column_name] [bom_package_column_name]
2) bom_jlc.php stock [file.csv] [jlc_part_number_column_name] [designator_column_name] [num_boards] [add_cost_of_manufacturing] [add_cost_of_each_exp_parttype]

Copyright (c) 2020 andreika <prometheus.pcb@gmail.com>

All online data sources used by this program belong to their respective owners.

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/


require "bom_jlc_lib.php";

if (count($argv) < 3) {
	echo "Wrong arguments! Please see Usage examples.\r\n";
	die;
}

$command = $argv[1];
$csvFile = $argv[2];
$csvPartColName = $argv[3];

echo "BOM_JLC (c) andreika, 2020.\r\n";
echo "Checking BOM file for JLCPCB stock & prices!\r\n";
echo "DISCLAIMER: All data output is estimated and advisory, use it on your own risk! Please see LICENSE...\r\n\r\n";

if (file_exists($csvFile) === FALSE) {
	echo "Error! Cannot open csv file " . $csvFile . "\r\n";
	die;
}
echo "* BOM file: " . $csvFile . "\r\n";

if (isCheck()) {
	$csvNameColName = $argv[4];
	$csvPackageColName = $argv[5];
} else if (isStock()) {
	if (count($argv) < 8) {
		echo "Wrong arguments! Please see Usage examples.\r\n";
		die;
	}
	$csvDesignatorColName = $argv[4];
	$numBoards = intval($argv[5]);
	$addCostMan = floatval($argv[6]);
	$addCostExp = floatval($argv[7]);

	$cost = $addCostMan;
	$noStock = FALSE;
	$noPrice = FALSE;

	echo "* Number of Boards: ".$numBoards . "\r\n";
	echo "* Manufacturing cost: $".$addCostMan.", Additional cost for each expanded part: $".$addCostExp."\r\n";
	echo "* Current time: ".date("Y-m-d H:i:s")."\r\n";
} else {
	echo "Wrong command '$command'!\r\n";
	die;
}

$csvStructure = analyse_file($csvFile, 10);
$delim = $csvStructure['delimiter']['value'];
$row = 0;

ini_set("auto_detect_line_endings", true);
if (($handle = fopen($csvFile, "rt")) === FALSE) {
	echo "Cannot open the file!\r\n";
	die;
}

for ($row = 0; (($data = fgetcsv($handle, 1000, $delim)) !== FALSE); $row++) {
	if ($row == 0) { // header
		$csvPartCol = findColumn($csvPartColName, $data);
		if (isCheck()) {
			$csvNameCol = findColumn($csvNameColName, $data);
			$csvPackageCol = findColumn($csvPackageColName, $data);
		} else if (isStock()) {
			$csvDesignatorCol = findColumn($csvDesignatorColName, $data);
		}
		continue;
	}
	// get data
    $partNumber = $data[$csvPartCol];
	$part = getJLCdata($partNumber);
	saveJson($partNumber, $part);

	// analyze
	if (isCheck()) {
		$partJlcName = getName($part);
		echo "[".$row. "] " . $partNumber . " {BOM} " . $data[$csvNameCol] . " [". $data[$csvPackageCol] . "] === {JLC} " . $partJlcName. "\r\n";
	}
	else if (isStock()) {
	    $designators = explode(",", $data[$csvDesignatorCol]);
	    $numPartsPerBoard = count($designators);
	    $numberOfParts = $numPartsPerBoard * $numBoards;
		$stock = getStock($part);
		$price = getPrice($part, $numberOfParts);
		$addType = $stock > 0 ? ($price["isBase"] ? "(base)" : "+$".$addCostExp."(exp)") : "";
		echo "[".$row. "] " . $partNumber . " Stock=" . $stock . " Price=$" . $price["price"] . " [Min:". $price["minNum"] . "] x ".$numberOfParts."pcs ".$addType. "\r\n";
		if (strlen($partNumber) < 2) {
			echo "  *** Skipping empty part number!\r\n";
		}
		else if ($stock <= 0) {
			echo "  *** WARNING! Not in stock!\r\n";
			$noStock = TRUE;
		}
		else if ($stock < $numberOfParts) {
			echo "  *** WARNING! Not enough parts in stock!\r\n";
			$noStock = TRUE;
		}
		else if ($price["price"] == 0) {
			echo "  *** WARNING! Price is not set/unknown!\r\n";
			$noPrice = TRUE;
		}
		else {
			$np = max($numberOfParts, $price["minNum"]);
			$cost += $price["price"] * $np;
			if (!$price["isBase"])
				$cost += $addCostExp;
		}
	}
}
fclose($handle);

if (isStock()) {
	$cost1 = round($cost / (float)$numBoards, 2);
	$cost = round($cost, 2);
	echo "\r\nTotal Manufacturing+Assembly Cost for 1 Board = $" . $cost1 . " (And $".$cost." Total for $numBoards boards.)\r\n";
	if ($noStock) {
		echo "*** WARNING! Some parts are not in stock!\r\n";
	} else {
		echo "*** All parts are in stock!\r\n";
	}
	if ($noPrice) {
		echo "*** WARNING! Some parts have invalid price! The total cost is not correct!\r\n";
	}
}
?>