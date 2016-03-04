Form loop by Young Webdevelopment
Young Webdevelopment copyright 2016



Usage of this plugin:
1) Include the functions.php file, include(.../form-loop/functions.php);
2) Add something such as the following array on in your file, after the include:
Example array:
$args = array(
	"name" => "Personal Data",
	"singular_code_name" => "personal_data",
	"save" => array("database",array("info@youngdev.nl")),
	"custom_form_class" => "personal_data",
	"custom_form_id" => "custom_form_id",
	"items" => array(
		array(
			"type" => "text",
			"surrounding_tags" => "h2",
			"custom_class" => "title-header",
			"custom_id" => "custom_id",
			"text" => "This is a title"
		),
		array(
			"type" => "text",
			"surrounding_tags" => "p",
			"custom_class" => "title-header",
			"custom_id" => "custom_id",
			"text" => "Please fill-out the form below"
		),
		array(
			"name" => "Firstname",
			"type" => "input",
			"input-type" => "text",
			"custom_class" => "title-header",
			"custom_id" => "custom_id",
			"placeholder" => "Firstname",
			"required" => true,
			"singular_code_name" => "fname",
			"var_type" => array("varchar",400),
			"var_type_short" => "string"
		),
		array(
			"name" => "Age",
			"type" => "input",
			"input-type" => "number",
			"custom_class" => "title-header",
			"custom_id" => "custom_id",
			"placeholder" => "Age",
			"required" => true,
			"singular_code_name" => "age",
			"var_type" => array("int",100),
			"var_type_short" => "int"
		),
		array(
			"name" => "Nationality",
			"type" => "input",
			"input-type" => "select",
			"custom_class" => "title-header",
			"custom_id" => "custom_id",
			"placeholder" => "Nationality",
			"required" => true,
			"singular_code_name" => "nationality",
			"options" => array("The Netherlands","England","France","Belgium","German"),
			"var_type" => array("varchar",200),
			"var_type_short" => "string"
		),
		array(
			"type" => "submit",
			"custom_class" => "title-header",
			"custom_id" => "custom_id",
			"value" => "Send",
		),
	)
);

3) Now, run three functions:
connect_to_db('db_username','db_password','db_name','db_host');
create_form_loop($args);
close_connection();

Of course, replace the arguments in the first function with your database details.
In the second function, $args represents the array above.

4) After the functions ran at least once, you may delete the array and the functions in step 2 and 3. Now, type the following function to use your form. We will do the rest!

get_form('singular_code_name');
'singular_code_name' should be the singular_code_name of your form, specified in the array above. Depending on your preferences, we will save or mail the data.



Release notes

1.0
Release of the program. Provides an easy php framework to create forms, and easily display them on your website. 