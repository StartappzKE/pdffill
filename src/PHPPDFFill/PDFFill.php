<?php
namespace PHPPDFFill;

/*
	credit for some functions: http://koivi.com/fill-pdf-form-fields
*/

class PDFFill {
	
	protected $pdf_template_path;

	protected $field_data;

	//===================================================================
	//	CONSTRUCTORS
	//		Initialize the library
	//		INPUT
	//			$pdf_template_path - should be the full path and filename
	//			$field_data - array of keys and values to be placed in pdf fields
	//===================================================================

	public function __construct($pdf_template_path='', $field_data='')
	{
		if(!empty($pdf_template_path))
		{
			$this->pdf_template_path = $pdf_template_path;
		}
		if(!empty($field_data))
		{
			$this->field_data = $field_data;
		}	
	}

	protected function make($pdf_template_path='', $field_data='')
	{
		$this->field_data = $field_data;
		$this->pdf_template_path = $pdf_template_path;
		return $this;
	}
	//*******************************************************************
	//	CHAINABLES
	//		These methods may be chained if desired
	//*******************************************************************
	protected function template($pdf_template_path='')
	{
		if(!empty($pdf_template_path))
		{
			$this->pdf_template_path = $pdf_template_path;
		}
		
		return $this;
	}

	protected function set($key='', $value='')
	{
		if(!empty($key) && !empty($value))
		{
			$this->field_data[$key] = $value;
		}

		return $this;
	}
	//*******************************************************************
	//	END POINTS
	//		These methods cannot be chained
	//*******************************************************************

	protected function getFields()
	{
		return $this->field_data;
	}

	protected function get_pdf_field_names()
	{
		if(!empty($this->pdf_template_path) && file_exists($this->pdf_template_path))
		{
			$command = 'pdftk '.$this->pdf_template_path.' dump_data_fields';
			exec( $command, $output, $ret );
			$output = implode("\n",$output);
			$regex = "/FieldName: ([A-Za-z0-9_]+)/";
			preg_match_all($regex, $output, $field_names);
			return $field_names[1];
		}
		else
		{
			return false;
		}
	}

	protected function get_pdf_field_data()
	{
		if(!empty($this->pdf_template_path) && file_exists($this->pdf_template_path))
		{
			$command = 'pdftk '.$this->pdf_template_path.' dump_data_fields';
			exec( $command, $output, $ret );
			$field_data = array();
			$count = 0;
			array_shift($output);
			foreach($output as $o)
			{
				if($o == '---')
				{
					$count++;
				}
				else
				{
					if(preg_match("/FieldName: ([A-Za-z0-9_\s]+)/",$o, $matches))
					{
						$field_data[$count]["name"] = $matches[1];
					}
					else if(preg_match("/FieldType: ([A-Za-z0-9_]+)/",$o, $matches))
					{
						if($matches[1] == "Text")
						{
							$field_data[$count]["type"] = "text";
						}
						elseif($matches[1] == "Choice")
						{
							$field_data[$count]["type"] = "select";
						}
						elseif($matches[1] == "Button")
						{
							$field_data[$count]["type"] = "button";
						}
						elseif($matches[1] == "Signature")
						{
							$field_data[$count]["type"] = "signature";
						}
					}
					else if(preg_match("/FieldStateOption: ([A-Za-z0-9_]+)/",$o, $matches))
					{
						$field_data[$count]["options"][] = $matches[1];
					}
					else if(preg_match("/FieldValue: (.*)/",$o, $matches))
					{
						$field_data[$count]["value"] = $matches[1];
					}
				}
			}
			foreach($field_data as $k=>$d)
			{
				if(!isset($d['type']))
				{
					unset($field_data[$k]);
				}
				else if($d['type'] == "button" && isset($d['options']))
				{
					if($d['options'][0] == 'Off' && $d['options'][1] == 'Yes')
					{
						$field_data[$k]['type'] = "checkbox";
						unset($field_data[$k]['options']);
					}
					else
					{
						$field_data[$k]['type'] = "radio";
					}
				}
				
			}
			$field_data = array_values($field_data);
			return $field_data;
		}
		else
		{
			return false;
		}
	}

	//===================================================================
	//	SAVERS
	//		Save output files as a xfdf or pdf
	//		INPUT
	//			$output_path - should be the full path and filename
	//===================================================================

	protected function save_xfdf($output_path='')
	{
		if(!empty($output_path) && !empty($this->field_data))
		{
			$xfdf_content = $this->createXFDF( $output_path, $this->field_data );
			if( $fp = fopen( $output_path, 'w' ) )
			{
			    fwrite( $fp, $xfdf_content, strlen( $xfdf_content ) );
			}
			fclose($fp);
			return true;
		}
		else
		{
			return false;
		}
	}

	protected function save_pdf($output_path='')
	{
		if(!empty($output_path) && !empty($this->field_data) && !empty($this->pdf_template_path) && file_exists($this->pdf_template_path))
		{
			$folders = explode(DIRECTORY_SEPARATOR,$output_path);
			$filename = array_pop($folders);
			$dir_path = implode(DIRECTORY_SEPARATOR, $folders).DIRECTORY_SEPARATOR;
			if(substr_count($filename,'.pdf'))
			{
				$filename = str_replace('.pdf','',$filename);
			}

			if($this->save_xfdf($dir_path.$filename.'.xfdf'))
			{
				$command = 'pdftk '.$this->pdf_template_path.' fill_form '.$dir_path.$filename.'.xfdf output '.$dir_path.$filename.'.pdf flatten';
				exec( $command, $output, $ret );
				
				unlink($dir_path.$filename.'.xfdf');

				return true;
			}
			else
			{
				return false;
			}			
		}
		else
		{
			return false;
		}
	}
	//*******************************************************************
	//	PRIVATE METHODS
	//*******************************************************************
	//===================================================================
	//  CREATE XFDF
	//		Generates a string for the content of a xfdf file
	//		INPUT
	//			$file - filename of xfdf file
	//			$info - array with key/value pais for pdf fields
	//			$enc - character encoding type 
	//
	//		Based on Adobe XFDF Standard:
	//			http://partners.adobe.com/public/developer/en/xml/XFDF_Spec_3.0.pdf
	//===================================================================

	private function createXFDF( $file, $info, $enc='UTF-8' )
	{
	    $data = '<?xml version="1.0" encoding="'.$enc.'"?>' . "\n" .
	        '<xfdf xmlns="http://ns.adobe.com/xfdf/" xml:space="preserve">' . "\n" .
	        '<fields>' . "\n";
	    foreach( $info as $field => $val )
	    {
	        $data .= '<field name="' . $field . '">' . "\n";
	        if( is_array( $val ) )
	        {
	            foreach( $val as $opt )
	                $data .= '<value>' .
	                    htmlentities( $opt, ENT_COMPAT, $enc ) .
	                    '</value>' . "\n";
	        }
	        else
	        {
	            $data .= '<value>' .
	                htmlentities( $val, ENT_COMPAT, $enc ) .
	                '</value>' . "\n";
	        }
	        $data .= '</field>' . "\n";
	    }
	    $data .= '</fields>' . "\n" .
	        '<ids original="' . md5( $file ) . '" modified="' .
	            time() . '" />' . "\n" .
	        '<f href="' . $file . '" />' . "\n" .
	        '</xfdf>' . "\n";
	    return $data;
	}

	//*******************************************************************
	//  MAGIC METHODS
	//    Allow the function to be called and constructed statically 
	//    from any function
	//*******************************************************************

	public function __call($method='', $parameters='')
	{
		if(method_exists($this, $method)){
			return call_user_func_array(array($this, $method), $parameters);
		}
	}

	static public function __callStatic($method=null, $arguments=null )
	{
		$class = get_called_class();
		$obj = new $class;
		return call_user_func_array(array($obj, $method), $arguments);
	}
}
