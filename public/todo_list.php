
<?php

$dbc = new PDO('mysql:host=127.0.0.1;dbname=todo_db', 'greg', 'letmein');

$dbc->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo $dbc->getAttribute(PDO::ATTR_CONNECTION_STATUS) . "\n";


require('filestore.php');
$open = new Filestore('data/title.txt');

$todos_array = $open->read();

class UnexpectedTypeException extends Exception { }

try {
    if (isset($_POST["input_item"])) {
        if ($_POST["input_item"] == "" || strlen($_POST["input_item"] > 240)) {
            throw new UnexpectedTypeException('input_item must be 240 characters or less');

        }
        array_push($todos_array, $_POST["input_item"]);

        // $open->write($todos_array);
        $stmt = $dbc->prepare('INSERT INTO todo_list (add_todo) VALUES (:add_todo)');
        $stmt->bindValue(':add_todo',  $_POST['input_item'],  PDO::PARAM_STR);
        $stmt->execute();
        echo "Inserted ID: " . $dbc->lastInsertId() . PHP_EOL;

        // header('Location: /todo_list.php');
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


if (count($_FILES) > 0 && $_FILES['file1']['error'] == 0) {

    if ($_FILES['file1']["type"] != "text/plain") {
        echo "ERROR: file must be in text/plain!";
    } else {

        $upload_dir = '/vagrant/sites/todo.dev/public/uploads/';
        $uploadFilename = basename($_FILES['file1']['name']);
        $saved_filename = $upload_dir . $uploadFilename;
        move_uploaded_file($_FILES['file1']['tmp_name'], $saved_filename);

        $import = new Filestore("uploads/$uploadFilename");
        $todos_uploaded = $import->read();
        $todos_array = array_merge($todos_array, $todos_uploaded);
        $open->write($todos_array);

    }
}


if (isset($saved_filename)) {
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



