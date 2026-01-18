<?php

// Parse information on vouchers


//----------------------------------------------------------------------------------------

$filename = 'voucher_type.json';
$json = file_get_contents($filename);

$values = json_decode($json);

$not_parsed = array();
$not_matched = array();

$types = array();

$results = array();

$debug = false;

foreach ($values as $value)
{
	//print_r($value);
	$string = $value->voucher_type;
	
	echo "Voucher string: $string\n";
	
	$matched = false;
	
	$result = new stdclass;
	$result->text = $string;
	$result->comments = [];
	
	$type_pattern = '(?<typestatus>(Allo|Holo|Lecto|Neallo|Neo|Para|Paralecto|Syn|Topo)type)';
	
	if (!$matched)
	{		
		if (preg_match('/type/i', $string))
		{
			$matched = true;
			
			$parsed = false;
			
			if (!$parsed)
			{
				if (preg_match('/^\s*' . $type_pattern . '([\.\?]\s*)?(\s+of)?\s+(?<name>.*)$/i', $string, $m))
				{		
					if ($debug)	
					{
						print_r($m);
					}
						
					$types[] = $m;
					
					$parsed = true;
					
					// http://rs.gbif.org/vocabulary/gbif/type_status
					$result->type_status = strtolower($m['typestatus']);
					
					// https://github.com/tdwg/dwc/issues/28 (proposed)
					// dwc:typifiedName
					$result->typified_name = $m['name'];
					
					if (preg_match('/[&]/', $result->typified_name))
					{
						$result->comments[] = 'contains bad characters';
					}

					if (preg_match('/^[A-Z]\./', $result->typified_name))
					{
						$result->comment[] = 'genus name not spelt out';
					}
					
					$results[] = $result;
				}
			
			}
			
			if (!$parsed)
			{
				if (preg_match('/^\s*(Type:\s*)?' . $type_pattern . '\s*$/i', $string, $m))
				{
					if ($debug)	
					{
						print_r($m);
					}
					
					$types[] = $m;
					
					$parsed = true;
					
					// http://rs.gbif.org/vocabulary/gbif/type_status
					$result->type_status = strtolower($m['typestatus']);
					
					$results[] = $result;
				}
			
			}

			if (!$parsed)
			{
				if (preg_match('/^\s*(Museum vouchered:\s*)?' . $type_pattern . '\s*$/i', $string, $m))
				{
					if ($debug)	
					{
						print_r($m);
					}
					
					$types[] = $m;
					
					$parsed = true;
					
					// http://rs.gbif.org/vocabulary/gbif/type_status
					$result->type_status = strtolower($m['typestatus']);
					
					$results[] = $result;
				}			
			}

			if (!$parsed)
			{
				if (preg_match('/^\s*(?<typestatus>Type)(\s+of)?\s+(?<name>.*)$/i', $string, $m))
				{
					if ($debug)	
					{
						print_r($m);
					}
					
					$types[] = $m;
					
					$parsed = true;
					
					// http://rs.gbif.org/vocabulary/gbif/type_status
					$result->type_status = strtolower($m['typestatus']);
					
					$results[] = $result;
				}			
			}			
			
			if (!$parsed)
			{
				$not_parsed[] = $string;
			}			
		}
	}
	
	if (!$matched)
	{		
		$not_matched[] = $string;
	}
	
}

if (0)
{
	// Didn't match any of our attempts to look for types
	echo "\nNot matched:\n";
	asort($not_matched);
	print_r($not_matched);
}

if (0)
{
	echo "\nTypes:\n";
	print_r($types);
}

if (1)
{
	// Looks like a type string but we couldn't figure it out
	echo "\nFailed to parse:\n";
	asort($not_parsed);
	print_r($not_parsed);
}

//print_r($results);


?>
