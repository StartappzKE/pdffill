<?php

use PHPPDFFill\PDFFill as PDFFill;

class PDFFillTest extends PHPUnit_Framework_TestCase
{
	public $file;
	public $fields;

	public function __construct()
	{
		parent::__construct();
		$this->file = dirname(__FILE__)."/assets/name_color.pdf";
		$this->fields = array(
			'name'=> 'John Smith',
			'color' => 'Blue'
		);
	}

	public function testConstructNormalWay()
	{
		$obj = new PDFFill($this->file, $this->fields);
		$this->assertEquals(get_class($obj), "PHPPDFFill\PDFFill");	
	}

	public function testConstructStatically()
	{
		$obj = PDFFill::make($this->file, $this->fields);
		$this->assertEquals(get_class($obj), "PHPPDFFill\PDFFill");	
	}

	public function testConstructEmpty()
	{
		$obj = PDFFill::make();
		$this->assertEquals(get_class($obj), "PHPPDFFill\PDFFill");	
	}

	public function testSetFieldsViaConstruct()
	{
		$obj = PDFFill::make($this->file, $this->fields);
		$this->assertEquals($obj->getFields(), $this->fields);	
	}

	public function testSetFieldsViaMethods()
	{
		$obj = PDFFill::make();
		foreach($this->fields as $k=>$v)
		{
			$obj->set($k,$v);
		}
		$this->assertEquals($obj->getFields(), $this->fields);	
	}

	public function testCreateXFDFmakesFile()
	{
		$obj = PDFFill::make($this->file, $this->fields);
		$xfdf_path = str_replace(".pdf",".xfdf",$this->file);
		$obj->save_xfdf($xfdf_path);

		$this->assertEquals(file_exists($xfdf_path),true);
		unlink($xfdf_path);
	}

	public function testXFDFisCorrect()
	{
		$obj = PDFFill::make($this->file, $this->fields);
		$xfdf_path = str_replace(".pdf",".xfdf",$this->file);
		$timestamp = time();
		$obj->save_xfdf($xfdf_path);
		
		$xfdf_contents = file_get_contents($xfdf_path);
		unlink($xfdf_path);

		$expected_result = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".
"<xfdf xmlns=\"http://ns.adobe.com/xfdf/\" xml:space=\"preserve\">\n".
"<fields>\n".
"<field name=\"name\">\n".
"<value>John Smith</value>\n".
"</field>\n".
"<field name=\"color\">\n".
"<value>Blue</value>\n".
"</field>\n".
"</fields>\n".
"<ids original=\"".md5($xfdf_path)."\" modified=\"".$timestamp."\" />\n".
"<f href=\"".$xfdf_path."\" />\n".
"</xfdf>\n";

		$this->assertEquals($xfdf_contents, $expected_result);
	}

	public function testCreatePDFmakesFile()
	{
		$obj = PDFFill::make($this->file, $this->fields);
		$pdf_path = dirname(__FILE__).'/assets/output.pdf';
		$obj->save_pdf($pdf_path);

		$this->assertEquals(file_exists($pdf_path),true);
		unlink($pdf_path);
	}

	public function testCreatePDFFailsGracefullyWithoutTemplate()
	{
		$obj = PDFFill::make("non_existant_file.pdf", $this->fields);
		$pdf_path = dirname(__FILE__).'/assets/output.pdf';
		$response_to_save = $obj->save_pdf($pdf_path);

		$this->assertEquals($response_to_save,false);
		$this->assertEquals(file_exists($pdf_path),false);
	}

	public function testCreatePDFFailsGracefullyWithoutFields()
	{
		$obj = PDFFill::make($this->file, array());
		$pdf_path = dirname(__FILE__).'/assets/output.pdf';
		$response_to_save = $obj->save_pdf($pdf_path);

		$this->assertEquals($response_to_save,false);
		$this->assertEquals(file_exists($pdf_path),false);
	}

	public function testGetPDFFieldsFailsGracefullyWithoutTemplate()
	{
		$obj = PDFFill::template("non_existant_file.pdf");
		$read_fields = $obj->get_pdf_field_names();

		$this->assertEquals($read_fields,false);
	}

	public function testGetPDFFieldsReturnsCorrectArray()
	{
		$obj = PDFFill::template($this->file);
		$read_fields = $obj->get_pdf_field_names();

		$this->assertEquals($read_fields,array_keys($this->fields));
	}
}