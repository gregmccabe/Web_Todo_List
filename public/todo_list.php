
<?php

//establish DB Connection
$dbc = new PDO('mysql:host=127.0.0.1;dbname=todo_db', 'greg', 'letmein');
$dbc->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function read_lines($filename) {

    $handle = fopen($filename, "r");
    if(filesize($filename) > 0) {
        $contents = trim(fread($handle, filesize($filename))); //     contents = string
        $contents_array = explode("\n", $contents);

    } else {
        $contents_array = array();
    }

    fclose($handle);
    return $contents_array;
}

function getOffset() {
    $page = isset($_GET['page']) ? $_GET['page'] : 1;
    return ($page - 1) * 10;
}

$limitRecord = 10;
$pageNumber = 0;
$offset = 0;

if (isset($_GET['page'])) {
    $pageNumber=$_GET['page'];
    $offset = $pageNumber * $limitRecord;
}
//limit and offset
$query = "SELECT * FROM todo_list LIMIT :limitRecord OFFSET :offset";
$stmt = $dbc->prepare($query);
$stmt->bindValue(':limitRecord', $limitRecord, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$todos_array = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!empty($_POST)) {

    if (isset($_POST['input_item'])) {
        // item is being add to the DB
        $stmt = $dbc->prepare('INSERT INTO todo_list (add_todo) VALUES (:add_todo)');
        $stmt->bindValue(':add_todo', $_POST['input_item'], PDO::PARAM_STR);
        $stmt->execute();
        header("Location: /todo_list.php");
        exit(0);
    }

    if (isset($_POST['remove'])) {
        //item being removed from todo DB
        $stmt = $dbc->prepare('DELETE FROM todo_list WHERE id = :id');
        $stmt->bindValue(':id', $_POST['remove'], PDO::PARAM_INT);
        $stmt->execute();
        header("Location: /todo_list.php");
        exit(0);
    }
}

if (count($_FILES) > 0 && $_FILES['file1']['error'] == 0) {

    if ($_FILES['file1']["type"] != "text/plain") {
        echo "ERROR: file must be in text/plain!";
    } else {

        $upload_dir = '/vagrant/sites/todo.dev/public/uploads/';
        $uploadFilename = basename($_FILES['file1']['name']);
        $saved_filename = $upload_dir . $uploadFilename;
        move_uploaded_file($_FILES['file1']['tmp_name'], $saved_filename);


        $todos_uploaded = read_lines($saved_filename);
        $stmt = $dbc->prepare('INSERT INTO todo_list (add_todo) VALUES (:add_todo)');

        foreach ($todos_uploaded as $value) {
           $stmt->bindValue(':add_todo', $value, PDO::PARAM_STR);
           $stmt->execute();
        }
           header("Location: /todo_list.php");
           exit(0);

    }
}


$count = $dbc->query("SELECT * FROM todo_list;")->rowCount();
$numPage = floor($count / $limitRecord);
$nextPage = $pageNumber + 1;
$prevPage = $pageNumber - 1;

?>


<!DOCTYPE html>
<html>
<head>
	<title>"TODO List"</title>
    <link rel="stylesheet" type="text/css" href="todo_style.css">
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap-theme.min.css">
    <script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
    <script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
</head>
	<body class="background">
        <div class="container">
            <h1 id="fancy-header">ToDo List:</h1>

            <ul>
    		<? foreach ($todos_array as $item) : ?>
        	   <li id="remove"><?= htmlspecialchars(strip_tags($item['add_todo'])); ?> <button class="btn btn-danger btn-xs btn-remove" data-todo="<?= $item['id']; ?>">Remove</button></li>
  			<? endforeach; ?>
    		</ul>

            <form id="removeForm" action="/todo_list.php" method="POST">
                <input class="remove" id="removeId" type="hidden" name="remove" value="">
            </form>

            <br>

            <? if ($pageNumber > 0) : ?>
            <a href="todo_list.php?page=<?= $prevPage; ?>">&larr; Previous</a>
            <? endif ?>

            <? if ($pageNumber < $numPage) : ?>
            <a href="todo_list.php?page=<?= $nextPage; ?>">Next &rarr;</a>
            <? endif ?>
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
    			<p>
                    <input type="Submit" value= Submit>
                </p>
    		</form>

    		<br>

    		<h1>Upload File</h1>

            <form method="POST" enctype="multipart/form-data" action="/todo_list.php" role="form">
                <div class="form-group">
                	<label for="file1">File to upload:</label>
                	<input type="file" id="file1" name="file1">
                    <input type="submit" value="Upload">
                </div>
            </form>
        </div>
    <script>

        $('.btn-remove').click(function () {
            var todoId = $(this).data('todo');
            if (confirm('Are you sure you want to remove item ' + todoId + '?')) {
                $('#removeId').val(todoId);
                $('#removeForm').submit();
            };
        });

    </script>
    </body>
</html>