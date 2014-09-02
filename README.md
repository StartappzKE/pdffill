PDFFill
=======

The goal of this library is to streamline the filling ou of pdf forms via PHP.

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

PDFFill::make($template_path, $field_data)->save_pdf($output_path);
```