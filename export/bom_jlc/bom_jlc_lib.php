<?php
/*
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


function getJLCdata($partNumber) {
	$url = "https://jlcpcb.com/shoppingCart/smtGood/getComponentDetail?componentCode=";
	try {
		$jsonData = file_get_contents($url . $partNumber);

	    if ($jsonData === false) {
    		echo "Error!\r\n";
	    	die;
	    }
	} catch (Exception $e) {
		echo "Exception!\r\n";
		die;
	}

	if (!extension_loaded("json")) {
		echo "JSON not found! Please install PHP-JSON extension!\r\n";
		die;
	}
	try {
		$partData = json_decode($jsonData);
	} catch (Exception $e) {
		echo "JSON Decode error!\r\n";
		die;
	}
	return $partData;
}

// code from https://www.php.net/manual/en/function.fgetcsv.php
function analyse_file($file, $capture_limit_in_kb = 10) {
    // capture starting memory usage
    $output['peak_mem']['start']    = memory_get_peak_usage(true);

    // log the limit how much of the file was sampled (in Kb)
    $output['read_kb']                 = $capture_limit_in_kb;
   
    // read in file
    $fh = fopen($file, 'r');
        $contents = fread($fh, ($capture_limit_in_kb * 1024)); // in KB
    fclose($fh);
   
    // specify allowed field delimiters
    $delimiters = array(
        'comma'     => ',',
        'semicolon' => ';',
        'tab'         => "\t",
        'pipe'         => '|',
        'colon'     => ':'
    );
   
    // specify allowed line endings
    $line_endings = array(
        'rn'         => "\r\n",
        'n'         => "\n",
        'r'         => "\r",
        'nr'         => "\n\r"
    );
   
    // loop and count each line ending instance
    foreach ($line_endings as $key => $value) {
        $line_result[$key] = substr_count($contents, $value);
    }
   
    // sort by largest array value
    asort($line_result);
   
    // log to output array
    $output['line_ending']['results']     = $line_result;
    $output['line_ending']['count']     = end($line_result);
    $output['line_ending']['key']         = key($line_result);
    $output['line_ending']['value']     = $line_endings[$output['line_ending']['key']];
    $lines = explode($output['line_ending']['value'], $contents);
   
    // remove last line of array, as this maybe incomplete?
    array_pop($lines);
   
    // create a string from the legal lines
    $complete_lines = implode(' ', $lines);
   
    // log statistics to output array
    $output['lines']['count']     = count($lines);
    $output['lines']['length']     = strlen($complete_lines);
   
    // loop and count each delimiter instance
    foreach ($delimiters as $delimiter_key => $delimiter) {
        $delimiter_result[$delimiter_key] = substr_count($complete_lines, $delimiter);
    }
   
    // sort by largest array value
    asort($delimiter_result);
   
    // log statistics to output array with largest counts as the value
    $output['delimiter']['results']     = $delimiter_result;
    $output['delimiter']['count']         = end($delimiter_result);
    $output['delimiter']['key']         = key($delimiter_result);
    $output['delimiter']['value']         = $delimiters[$output['delimiter']['key']];
   
    // capture ending memory usage
    $output['peak_mem']['end'] = memory_get_peak_usage(true);
    return $output;
}

function getStock($part) {
	if (!property_exists($part, "data") || !is_object($part->data))
		return 0;
	if (!property_exists($part->data, "stockCount"))
		return 0;
	return intval($part->data->stockCount);
}

function getName($part) {
	if (!property_exists($part, "data") || !is_object($part->data))
		return "??? (Not in stock)";
	$name = "";
	if (property_exists($part->data, "erpComponentName"))
		$name .= $part->data->erpComponentName;
	if (property_exists($part->data, "componentSpecificationEn"))
		$name .= " [". $part->data->componentSpecificationEn . "]";
	if (property_exists($part->data, "componentModelEn") && strcasecmp($part->data->componentModelEn, $part->data->erpComponentName) != 0)
		$name .= " (" . $part->data->componentModelEn . ")";
	return $name;
}

function getPrice($part, $num) {
	$zeroPrice = array("price"=>0, "minNum"=>0, "isBase"=>0);
	$minNum = 0;
	$price = 0;
	$cost = 1000;
	if (!property_exists($part, "data") || !is_object($part->data))
		return $zeroPrice;
	if (property_exists($part->data, "jlcPrices") && is_array($part->data->jlcPrices))
		$priceList = $part->data->jlcPrices;
	else if (property_exists($part->data, "prices") && is_array($part->data->prices))
		$priceList = $part->data->prices;
	else
		return $zeroPrice;
	$isBase = (property_exists($part->data, "componentLibraryType") && strcasecmp($part->data->componentLibraryType, "base") == 0);
	foreach ($priceList as $p) {
		$sn = $p->startNumber;
		$newcost = max($sn, $num) * $p->productPrice;
		if ($newcost < $cost) {
			$price = $p->productPrice;
			$minNum = $sn;
			$cost = $newcost;
		}
	}
	return array("price"=>$price, "minNum"=>$minNum, "isBase"=>$isBase);
}

function saveJson($partNumber, $part) {
	$jsonFolder = "json/";
	ob_start();
	var_dump($part);
	$p = ob_get_contents();
	ob_end_clean();
	if (!file_exists($jsonFolder))
	    mkdir($jsonFolder, 0777);
	file_put_contents($jsonFolder.$partNumber.".dump", $p); 
}

function isCheck() {
	global $command;
	return strcasecmp($command, "check") == 0;
}

function isStock() {
	global $command;
	return strcasecmp($command, "stock") == 0;
}

function findColumn($colName, $data) {
	$colIdx = array_search($colName, $data);
	if ($colIdx === FALSE) {
		echo "Error! Cannot find the correct CSV-file column '$colName'!\r\n";
		die;
	}
	return intval($colIdx);
}

?>