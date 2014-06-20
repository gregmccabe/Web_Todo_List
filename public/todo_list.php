
<?php
// var_dump($_FILES);
require('filestore.php');
$open = new Filestore('data/title.txt');
// var_dump($_FILES);
// $filename = 'data/title.txt';
$todos_array = $open->read();

class UnexpectedTypeException extends Exception { }

try {
    if (isset($_POST["input_item"])) {
        if ($_POST["input_item"] == "" || strlen($_POST["input_item"] > 240)) {
            throw new UnexpectedTypeException('input_item must be 240 characters or less');
        }
        array_push($todos_array, $_POST["input_item"]);
        $open->write($todos_array);
        header('Location: /todo_list.php');
        exit;
    }

} catch (UnexpectedTypeException $e) {
    $msg = $e->getMessage() . PHP_EOL;
}

if (isset($_GET['removeIndex'])) {
    unset($todos_array[$_GET['removeIndex']]);
    $open->write($todos_array);
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
        $import = new Filestore("uploads/$uploadFilename");
        $todos_uploaded = $import->read();
        $todos_array = array_merge($todos_array, $todos_uploaded);
        $open->write($todos_array);
        // var_dump($todos_array);
    }
}

// Check if we saved a file
if (isset($saved_filename)) {
    // If we did, show a link to the uploaded file
    echo "<p>You can download your file <a href='/{$import->filename}'>here</a>.</p>";
}

?>


<!DOCTYPE html>
<html>
<head>
	<title>"TODO List"</title>
    <link rel="stylesheet" type="text/css" href="todo_style.css">
</head>
    <div>
	<body class="background">
		<h1 id="fancy-header">ToDo List:</h1>
		<div>
        <ul>
		  <? foreach ($todos_array as $key => $value) : ?>
        	<li><?= htmlspecialchars(strip_tags($value)); ?> | <a href="todo_list.php?removeIndex=<?= $key?>">Remove Item</a></li>
  			<? endforeach; ?>

		</ul>
    </div>
			<br>
		<h1 class="addItem">Add item to your List</h1>
            <? if (isset($msg)) : ?>
                <?="Sorry, your input should be greater than 1 character and less than 240 characters"; ?>
            <? endif; ?>
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
    </div>
	</body>
</html>



