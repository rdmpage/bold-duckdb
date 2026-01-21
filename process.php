<?php

// Process file

require_once (dirname(__FILE__) . '/voucher_type.php');

ini_set('memory_limit', '-1');

$headings = array();

$row_count = 0;

$filename = "voucher_type.tsv";

$file_handle = fopen($filename, "r");
while (!feof($file_handle)) 
{
	$line = trim(fgets($file_handle));
		
	$row = explode("\t",$line);
	
	$go = is_array($row) && count($row) > 1;
	
	if ($go)
	{
		if ($row_count == 0)
		{
			$headings = $row;	
		}
		else
		{
			$data = new stdclass;
		
			foreach ($row as $k => $v)
			{
				if (trim($v) != '')
				{
					$data->{$headings[$k]} = $v;
				}
			}
		
			// debugging
			//print_r($data);	
			
			$result = parse_voucher_type($data->voucher_type);
			
			//print_r($result);
			
			if ($result->parsed)
			{
				echo '<https://portal.boldsystems.org/record/' . $data->processid . '> ';
				echo '<http://rs.tdwg.org/dwc/terms/typeStatus> ';
				echo '<http://rs.gbif.org/vocabulary/gbif/type_status/' . $result->type_status . '> .';
				echo "\n";
			}

			
			/*
			if (isset($data->coord))
			{
				echo $data->coord . "\n";
			}
			*/
		}
	}

	$row_count++;
	
	if ($row_count > 1000)
	{
		//exit();
	}
}	


?>
