PDFFill
=======

The goal of this library is to streamline the filling out of pdf forms via PHP.

Requirements
------------

You will need the [PDFToolkit](https://www.pdflabs.com/tools/pdftk-the-pdf-toolkit/) `pdftk` for saving out pdfs, if you only want a xfdf file, this is not required.

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

TODO FOR 1.0
-------------

* Output the raw pdf data for use as showing a file that's not on the server.

Credits
-------

http://koivi.com/fill-pdf-form-fields
