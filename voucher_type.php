<?php

// Parse information on vouchers

// Parse voucher string 
function parse_voucher_type($string, $debug = false)
{	
	$matched = false;
	
	$result = new stdclass;
	$result->text = $string;
	$result->comments = [];
	$result->parsed = false;
	
	$type_pattern = '(?<typestatus>(Allo|Co|Holo|Iso|Lecto|Neallo|Neo|Para|Paralecto|Syn|Topo)type)';
	
	if (preg_match('/type/i', $string))
	{		
		if (!$result->parsed)
		{
			if (preg_match('/^\s*' . $type_pattern . '([\.\?]\s*)?(\s+of)?\s+(?<name>.*)$/i', $string, $m))
			{		
				if ($debug)	
				{
					print_r($m);
				}
				$result->parsed = true;
				
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
			}		
		}
		
		if (!$result->parsed)
		{
			if (preg_match('/^\s*(Type:\s*)?' . $type_pattern . '\s*$/i', $string, $m))
			{
				if ($debug)	
				{
					print_r($m);
				}				
				$result->parsed = true;
				
				// http://rs.gbif.org/vocabulary/gbif/type_status
				$result->type_status = strtolower($m['typestatus']);
			}			
		}

		if (!$result->parsed)
		{
			if (preg_match('/^\s*(Museum vouchered:\s*)?' . $type_pattern . '\s*$/i', $string, $m))
			{
				if ($debug)	
				{
					print_r($m);
				}				
				$result->parsed = true;
				
				// http://rs.gbif.org/vocabulary/gbif/type_status
				$result->type_status = strtolower($m['typestatus']);					
			}			
		}

		if (!$result->parsed)
		{
			if (preg_match('/^\s*(?<typestatus>Type)(\s+of)?\s+(?<name>.*)$/i', $string, $m))
			{
				if ($debug)	
				{
					print_r($m);
				}				
				$result->parsed = true;
				
				// http://rs.gbif.org/vocabulary/gbif/type_status
				$result->type_status = strtolower($m['typestatus']);
			}			
		}	
		
		// Registered: Holotype specimen
		if (!$result->parsed)
		{
			if (preg_match('/(?<typestatus>type)\s+specimen/i', $string, $m))
			{
				if ($debug)	
				{
					print_r($m);
				}				
				$result->parsed = true;
				
				// http://rs.gbif.org/vocabulary/gbif/type_status
				$result->type_status = strtolower($m['typestatus']);
			}			
		}
		
		if (!$result->parsed)
		{
			if (preg_match('/(?<typestatus>type\s+series)/i', $string, $m))
			{
				if ($debug)	
				{
					print_r($m);
				}				
				$result->parsed = true;
				
				// http://rs.gbif.org/vocabulary/gbif/type_status
				$result->type_status = 'type';
			}			
		}	
		
		// Museum Vouchered:Type
		if (!$result->parsed)
		{
			if (preg_match('/Museum[\s+|:]Vouchered:\s*(?<typestatus>type)/i', $string, $m))
			{
				if ($debug)	
				{
					print_r($m);
				}				
				$result->parsed = true;
				
				// http://rs.gbif.org/vocabulary/gbif/type_status
				$result->type_status = strtolower($m['typestatus']);
			}			
		}
		
		if (!$result->parsed)
		{
			if (preg_match('/Type(Series)?:\s*' . $type_pattern . '/i', $string, $m))
			{
				if ($debug)	
				{
					print_r($m);
				}
				
				$result->parsed = true;
				
				// http://rs.gbif.org/vocabulary/gbif/type_status
				$result->type_status = strtolower($m['typestatus']);
			}			
		}	
			

		// ...(holotype)...
		if (!$result->parsed)
		{
			if (preg_match('/\(' . $type_pattern . '\)/i', $string, $m))
			{
				if ($debug)	
				{
					print_r($m);
				}				
				$result->parsed = true;
				
				// http://rs.gbif.org/vocabulary/gbif/type_status
				$result->type_status = strtolower($m['typestatus']);
			}			
		}	

		// ^Isotype$
		if (!$result->parsed)
		{
			if (preg_match('/^' . $type_pattern . '$/i', $string, $m))
			{
				if ($debug)	
				{
					print_r($m);
				}				
				$result->parsed = true;
				
				// http://rs.gbif.org/vocabulary/gbif/type_status
				$result->type_status = strtolower($m['typestatus']);
			}			
		}	
	}
	
	return $result;
}


//----------------------------------------------------------------------------------------

// test
if (0)
{
	$filename = 'voucher_type.json';
	$json = file_get_contents($filename);
	
	$values = json_decode($json);
	
	$not_parsed = array();
	$not_matched = array();
	
	$results = array();
	
	$debug = false;
	
	foreach ($values as $value)
	{
		echo $value->voucher_type . "\n";
		
		$result = parse_voucher_type($value->voucher_type);
		
		print_r($result);
		
		if ($result->parsed)
		{
		
		}
		else
		{
			$not_parsed[] = $value->voucher_type;
		}
	}
	
	print_r($not_parsed);
}

?>
