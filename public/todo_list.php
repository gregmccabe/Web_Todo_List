<!DOCTYPE html>

<?php

	function read_file($filename) {
	    $handle = fopen($filename, "r");
	    if(filesize($filename) > 0) {
	    	$contents = trim(fread($handle, filesize($filename))); // contents = string
	    	$contents_array = explode("\n", $contents);
	    	// var_dump($contents_array);
	    	// echo 'run';
	    } else {
	    	// echo 'else got run';
	    	$contents_array = array();
	    }

	    fclose($handle);
	    return $contents_array;
	}
	function write_file_save($filename, $items) {
        $handle = fopen($filename, 'w');
        fwrite($handle, implode("\n", $items));
        fclose($handle);
	}

	// $_POST["input_item"]; //POST is the array and input_item is the key.

	$filename = 'data/title.txt';//$filename is a var assigned to data "string"
	$todos_array = read_file($filename);


	if (!empty($_POST["input_item"])) {
		array_push($todos_array, $_POST["input_item"]);
		write_file_save($filename, $todos_array);
		header('Location: /todo_list.php');
	}
	if (isset($_GET['removeIndex'])) {
		unset($todos_array[$_GET['removeIndex']]);
		write_file_save($filename, $todos_array);
		header('Location: /todo_list.php');
	}
	$todos_array = read_file($filename);
	// var_dump($_GET);
	// var_dump($_POST);
?>
<hr>
<html>
<head>
	<title>"TODO List"</title>
</head>

	<body>
		<h1>ToDo List:</h1>
		<ul>

		<?php
			foreach ($todos_array as $key => $value) {
        		echo "<li>{$value} <a href=\"todo_list.php?removeIndex={$key}\">Remove Item</a><br></li>";
  			}
		?>

		</ul>
			<br>
			<h3>Add item to your List</h3>
		<form method="POST" action="todo_list.php">
			<p>
				<label for="input_item">Enter Item</label>
				<input id="input_item" name="input_item" type="text" placeholder="Enter">
			</p>
			<p><input type="Submit" value= Submit></p>
		</form>
	</body>
</html>



