<!DOCTYPE html>

<?php
var_dump($_FILES);

$filename = 'data/title.txt';
$todos_array = read_file($filename);

function read_file($filename) {
    $handle = fopen($filename, "r");
    if(filesize($filename) > 0) {
    	$contents = trim(fread($handle, filesize($filename))); // contents = string
    	$contents_array = explode("\n", $contents);

    } else {
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

if (!empty($_POST["input_item"])) {
	array_push($todos_array, $_POST["input_item"]);
	write_file_save($filename, $todos_array);
	header('Location: /todo_list.php');
	exit;
}
if (isset($_GET['removeIndex'])) {
	unset($todos_array[$_GET['removeIndex']]);
	write_file_save($filename, $todos_array);
	header('Location: /todo_list.php');
	exit;
}

// Verify there were uploaded files and no errors
if (count($_FILES) > 0 && $_FILES['file1']['error'] == 0) {

	if ($_FILES['file1']["type"] != "text/plain") {
		echo "ERROR: file must be in text/plain!";
	} else {
		// Set the destination directory for uploads
	    // Grab the filename from the uploaded file by using basename
	    $upload_dir = '/vagrant/sites/todo.dev/public/uploads/';
	    $uploadFilename = basename($_FILES['file1']['name']);
	    // Create the saved filename using the file's original name and our upload directory
	    $saved_filename = $upload_dir . $uploadFilename;
	    // Move the file from the temp location to our uploads directory
	    move_uploaded_file($_FILES['file1']['tmp_name'], $saved_filename);

	    // load the new todos
	    // merge with existing list
	    $todos_uploaded = read_file($saved_filename);
	    $todos_array = array_merge($todos_array, $todos_uploaded);
	    write_file_save($filename, $todos_array);
	}
}

// Check if we saved a file
if (isset($saved_filename)) {
    // If we did, show a link to the uploaded file
    echo "<p>You can download your file <a href='/uploads/{$filename}'>here</a>.</p>";
}

?>


<html>
<head>
	<title>"TODO List"</title>
</head>

	<body>
		<h1>ToDo List:</h1>
		<ul>

		  <? foreach ($todos_array as $key => $value) : ?>
        	<li><?= "{$value} <a href=\"todo_list.php?removeIndex={$key}\">Remove Item</a>"; ?></li>
  			<? endforeach; ?>

		</ul>
			<br>
		<h3>Add item to your List</h3>
		<form method="POST" action="/todo_list.php">
			<p>
				<label for="input_item">Enter Item</label>
				<input id="input_item" name="input_item" type="text" placeholder="Enter" autofocus>
			</p>
			<p><input type="Submit" value= Submit></p>
		</form>

			<br>
		<h1>Upload File</h1>

		<form method="POST" enctype="multipart/form-data" action="/todo_list.php">
	    	<p>
	        	<label for="file1">File to upload: </label>
	        	<input type="file" id="file1" name="file1">
	    	</p>

	        <p><input type="submit" value="Upload"></p>

		</form>

	</body>
</html>



