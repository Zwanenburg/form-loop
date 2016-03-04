<?php
	/**
	 * @version 1.0
	 * @copyright Young Webdevelopment 2016
	*/

	function connect_to_db($db_user,$db_pass,$db_name,$db_host) {
		/**
		 * Connect to the database
		 * @since 1.0
		*/
		$conn = mysqli_connect($db_host,$db_user,$db_pass);
		if(!$conn) {
			die("Failed to connect to database.");
		}
		if(!mysqli_select_db($conn,$db_name)) {
			die("Failed to find database ".$db_name." in the databases.");
		}
		$GLOBALS['db'] = $conn;
		$GLOBALS['db_name'] = $db_name;
	}

	function close_connection() {
		mysqli_close($GLOBALS['db']);
		$GLOBALS['db'] = null;
	}

	function c($e) {
		/**
		 * Check if element has a value
		 * @since 1.0
		*/
		if($e == null || $e == '') {
			return false;
		} else {
			return true;
		}
	}

	function create_form_table($args) {
		/**
		 * Create table in db to save details when the form is filled in
		 * @since 1.0
		*/
		$table_name = $args["singular_code_name"];
		$result = $GLOBALS['db']->query("DROP TABLE ".$table_name);
		$result = $GLOBALS['db']->query("CREATE TABLE IF NOT EXISTS ".$table_name." (id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY)");

		$items = $args["items"];

		foreach($items as $item) {
			if($item["type"] == 'input') {
				switch($item["var_type"][0]) {
					case 'varchar':
					case 'text':
					case 'string':

						$result = $GLOBALS['db']->query("ALTER TABLE ".$table_name." ADD ".$item["singular_code_name"]." VARCHAR(".$item["var_type"][1].") NOT NULL");

					break;

					default:

						$result = $GLOBALS['db']->query("ALTER TABLE ".$table_name." ADD ".$item["singular_code_name"]." ".$item["var_type"][0]."(".$item["var_type"][1].") NOT NULL");

					break;
				}
			}
		}

		$result = $GLOBALS['db']->query("ALTER TABLE ".$table_name." ADD reg_date TIMESTAMP");
	}

	function create_form_loop($args) {
		/**
		 * Save the form (in a json file)
		 * @since 1.0
		*/
		$save_email = false;
		$save_db = false;

		foreach($args["save"] as $save) {
			if($save == "database") {
				create_form_table($args);
				$save_db = true;
			} else {
				$save_email = true;
			}
		}

		//save args in json file

		$json = file_get_contents('json/forms.json');
		$json_array = json_decode($json,true);
		
		if(!c($json_array)) { $json_array = array(); }

		foreach($json_array as $key => $json_item) {
			if($json_item["singular_code_name"] == $args["singular_code_name"]) {
				unset($json_array[$key]);
			}
		}

		array_push($json_array,$args);
		$json_array = json_encode($json_array);

		$fp = fopen('json/forms.json', 'w');
		fwrite($fp, $json_array);
		fclose($fp);

	}

	function get_form_html($args) {
		/**
		 * Get form html
		 * @since 1.0
		*/
		$items = $args["items"];
		echo '<form id="'.$args["custom_form_id"].'" class="'.$args["custom_form_id"].'" action="" method="post">';
		foreach($items as $item) {

			switch($item["type"]) {
				case "text":
					echo '<'.$item["surrounding_tags"].' id="'.$item["custom_id"].'" class="'.$item["custom_class"].'">'.$item["text"].'</'.$item["surrounding_tags"].'>';
				break;

				case 'input':

					switch($item["input-type"]) {

						case 'select':
							echo '<select name="'.$item["singular_code_name"].'" placeholder="'.$item["placeholder"].'" id="'.$item["custom_id"].'" class="'.$item["custom_class"].'">';
							foreach($item["options"] as $option) {
								echo '<option value="'.$option.'">'.$option.'</option>';
							}
							echo '</select>';
						break;

						default:
							echo '<input id="'.$item["custom_id"].'" class="'.$item["custom_class"].'" type="'.$item["input-type"].'" name="'.$item["singular_code_name"].'" placeholder="'.$item["placeholder"].'">';
						break;

					}

				break;

				case 'submit':

					echo '<button id="'.$item["custom_id"].'" class="'.$item["custom_class"].'" type="submit" name="submit_'.$args["singular_code_name"].'">'.$item["value"].'</button>';

				break;
			}

		}
		echo '</form>';
	}

	function get_form_php($args) {
		/**
		 * Get form php
		 * @since 1.0
		*/
		if(isset($_POST["submit_".$args["singular_code_name"]])) {	
			$items = $args["items"];
			$validate = true;
			$upload_args = array();
			foreach($items as $item) {
				if($item["type"] == 'input') {
					$val = $_POST[$item["singular_code_name"]];
					if(!c($val) && $item["required"]) {
						$validate = false;
					}
					array_push($upload_args,array($item["singular_code_name"],$val,$item["var_type_short"],$item["name"]));
				}
			}

			$save_db = false;
			$save_email = false;

			foreach($args["save"] as $save) {
				if($save == "database") {
					$save_db = true;
				} else {
					$save_email = true;
				}
			}

			if($validate && $save_db) {
				//construct sql query
				$sql = "INSERT INTO ".$args["singular_code_name"]." (";
				foreach($upload_args as $key => $upload_arg) {
					$sql .= $upload_arg[0];
					if($key != count($upload_args)-1) { $sql.=','; }
				}
				$sql.=') VALUES (';
				foreach($upload_args as $key => $upload_arg) {
					if($upload_arg[2] == 'string') {
						$sql .= '$upload_arg[1]';
					} else {
						$sql .= $upload_arg[1];
					}
					if($key != count($upload_args)-1) { $sql.=','; }
				}
				$sql.=')';
				$GLOBALS["db"]->query("INSERT INTO ".$args["singular_code_name"]." () VALUES ()");
				$id = $GLOBALS["db"]->insert_id;

				foreach($upload_args as $upload_arg) {
					if($upload_arg[2] == 'string') {
						$GLOBALS["db"]->query("UPDATE ".$args["singular_code_name"]." SET ".$upload_arg[0]." = '".$upload_arg[1]."' WHERE id = $id");
					} elseif($upload_arg[2] == 'int') {
						$GLOBALS["db"]->query("UPDATE ".$args["singular_code_name"]." SET ".$upload_arg[0]." = ".$upload_arg[1]." WHERE id = $id");
					}
				}
			}

			if($validate && $save_email) {
				$msg = "<html>Beste, <br>";
				$msg .= "Someone applied to the form '".$args["singular_code_name"]."' on your website. You will find the details below. You cannot reply to this message.<br><br>";
				foreach($upload_args as $upload_arg) {
					$msg .= '<b>'.$upload_arg[3].'</b>: '.$upload_arg[1].'<br>';
				}
				$msg .= '</html>';

				$headers = "From: ".$email."" . "\r\n";
				$headers .= "Reply-to: " . $email . "\r\n";
				$headers .= "MIME-Version: 1.0\r\n";
				$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

				foreach($args["save"] as $save) {
					if(is_array($save)) {
						foreach($save as $to) {
							mail($to,"Applience of form '".$args["singular_code_name"]."'",$msg,$headers);
						}
					}
				}
			}
		}
	}

	function get_form($singular_code_name) {
		/**
		 * Retrieve the form
		 * @since 1.0
		*/
		$json = file_get_contents('json/forms.json');
		$items = json_decode($json,true);

		foreach($items as $item) {
			if($item["singular_code_name"] == $singular_code_name) {
				//we found the right one
				$args = $item;
				break;
			}
		}

		get_form_php($args);
		get_form_html($args);
	}
?>