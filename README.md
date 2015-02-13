PDFFill
=======

The goal of this library is to streamline the filling out of pdf forms via PHP.

Requirements
------------

You will need the [PDFToolkit](https://www.pdflabs.com/tools/pdftk-the-pdf-toolkit/) `pdftk` for saving out, or reading from pdfs, if you only want generate a xfdf file from an array, this is not required.

Usage
-----
The simplest usage is to just output a .pdf file after filling in the fields in a template from an array.

```php
	$template_path = dirname(__FILE__).'/template.pdf';
	$output_path = dirname(__FILE__).'/example.pdf';
	$field_data = array(
		"name" => "John Smith",
		"color" => "Blue",
	);

	PHPPDFFill\PDFFill::make($template_path, $field_data)->save_pdf($output_path);
```

Alternatively you can fill out the fields using this syntax

```php
	$template_path = dirname(__FILE__).'/template.pdf';
	PHPPDFFill\PDFFill::template($template_path)
		->set("name","John Smith")
		->set("color","Blue")
		->save_pdf($output_path);
```

If you only want a xfdf file use the `save_xfdf()` function

```php
	$template_path = dirname(__FILE__).'/template.pdf';
	$output_path = dirname(__FILE__).'/example.xfdf';
	$field_data = array(
		"name" => "John Smith",
		"color" => "Blue",
	);

	PHPPDFFill\PDFFill::make($template_path, $field_data)->save_xfdf($output_path);
```

To get the field names out of a pdf, for validation or use in a form, use the `get_pdf_field_names()` method (Requires pdftk):

```php
	$template_path = dirname(__FILE__).'/template.pdf';
	$field_names = PHPPDFFill\PDFFill::template($template_path)->get_pdf_field_names();

	// Response:
	// Array( "name", "color" )
```

To get more data about the fields use the `get_pdf_field_data()` method:

```php
	$template_path = dirname(__FILE__).'/template.pdf';
	$field_data = PHPPDFFill\PDFFill::template($template_path)->get_pdf_field_data();

	// Response:
	// (
	//     [0] => Array
	//         (
	//             [type] => text
	//             [name] => name
	//         )
	//     [1] => Array
	//         (
	//             [type] => select
	//             [name] => favorite_color
	//             [options] => Array
	//                 (
	//                     [0] => Red
	//                     [1] => Green
	//                     [2] => Blue
	//                 )
	//         )
	// )
```

Credits
-------

http://koivi.com/fill-pdf-form-fields
