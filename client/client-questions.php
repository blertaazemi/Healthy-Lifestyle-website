<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ask a Question</title>
    <link rel="stylesheet" href="styles.css">

    <style>
        body {
    font-family: Arial, sans-serif;
    background-color: #f2f2f2;
}

.container {
    max-width: 500px;
    margin: 0 auto;
    padding: 20px;
    background-color: #ffffff;
    border-radius: 5px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    margin-top:100px;
}

h1 {
    text-align: center;
    margin-bottom: 20px;
}

form {
    text-align: center;
}

label {
    display: block;
    font-weight: bold;
    margin-bottom: 10px;
}

.Question{
    width: 100%;
    padding: 10px;
    font-size: 16px;
    border: 1px solid #ccc;
    border-radius: 5px;
    margin-bottom: 20px;
}

.Submit {
    padding: 10px 20px;
    font-size: 16px;
    background-color:#27ae60;
    color: #ffffff;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.Submit:hover {
    background-color: #45a049;
}

    </style>
</head>
<body>
<?php @include 'client-navbar.php' ?>
    <div class="container">
        <h1>Ask a Question</h1>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
            <label for="question" class="Teksti">Write your question:</label>
            <input type="text" class="Question" id="question" name="question" placeholder="Enter your question here" required>
            <button class="Submit"type="submit">Submit</button>
        </form>
    </div>

</body>
</html>


<?php
// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the question from the form submission
    $question = $_POST['question'];

    // Database connection parameters
    $servername = "localhost:3307";
    $username = "root";
    $password = "Replace.3";
    $dbname = "ueb2";

    try {
        // Create a new PDO instance
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        
        // Set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Prepare the SQL statement to insert the question into tbl_faq
        $sql = "INSERT INTO tbl_faq (question) VALUES (:question)";
        $statement = $conn->prepare($sql);
        
        // Bind the question parameter and execute the statement
        $statement->bindParam(':question', $question);
        $statement->execute();

        // Redirect the user to a success page or perform any additional actions
        header("Location: success.php");
        exit();
    } catch (PDOException $e) {
        // Handle any database errors
        echo "Connection failed: " . $e->getMessage();
    }
}
?>