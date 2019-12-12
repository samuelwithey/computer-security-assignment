<!DOCTYPE html>
<html>
<body>

<form action="upload.php" method="post" enctype="multipart/form-data">
    Select file to upload:
    <input type="file" name="fileToUpload" id="fileToUpload">
    <input type="submit" value="Upload CV" name="submit">
</form>

</body>
</html>

<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Include config file
require_once "config.php";

if(isset($_FILES["fileToUpload"])) {
    // Specifies the directory where the file is going to be placed
    $target_dir = "uploads/";
    // Specifies the path of the file to be uploaded
    $target_file = $target_dir .  $_SESSION["id"] . basename($_FILES["fileToUpload"]["name"]);
    /* Acts as a boolean for True. If any errors occur, the value will be changed to 0 
     and the file will not be uploaded. */
    $uploadOk = 1;
    // Holds the file extension of the file (in lower case)
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
    // Check if file already exists
    if (file_exists($target_file) ) {
        echo "Sorry, file already exists. <br>";
        $uploadOk = 0;
    }
    // Check if user has uploaded a file
    if ($_SESSION["file_uploaded"] === 1) {
        echo "Sorry, user has already uploaded a file. <br>";
        $uploadOk = 0;
    }
    // Check file size
    if ($_FILES["fileToUpload"]["size"] > 500000) {
        echo "Sorry, your file is too large. <br>";
        $uploadOk = 0;
    }
    // Allow .pdf, .doc and .docx file formats
    if($imageFileType !== "pdf" && $imageFileType !== "doc" && $imageFileType !== "docx") {
        echo "Sorry, only pdf, doc & docx file formats are allowed. <br>";
        $uploadOk = 0;
    }
    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded. <br>";
    // if everything is ok, try to upload file
    } else {
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
            echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";
            // Attempt to connect to MySQL database
            $link = mysqli_connect($DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_NAME);
            if($link === false){
                die("ERROR: Could not connect. " . mysqli_connect_error());
            }
            // Prepare a select statement
            $sql = "UPDATE users SET file_uploaded = 1 WHERE id = ? ";
            if($stmt = mysqli_prepare($link, $sql)){
                 //Strip everything but numbers from SESSION ID
                $idStripped = preg_replace('/[^0-9]/', '', $_SESSION["id"]);;
                // Bind variables to the prepared statement as parameters
                mysqli_stmt_bind_param($stmt, "i", intval($idStripped));
                // Attempt to execute the prepared statement
                if(mysqli_stmt_execute($stmt)){
                    // Close statement
                    mysqli_stmt_close($stmt);
                    $sql = "SELECT file_uploaded FROM users WHERE id = ?";
                    if($stmt = mysqli_prepare($link, $sql)){
                        // Bind variables to the prepared statement as parameters
                        mysqli_stmt_bind_param($stmt, "i", intval($idStripped));
                        // Attempt to execute the prepared statement
                        if(mysqli_stmt_execute($stmt)){
                            // Store result
                            mysqli_stmt_store_result($stmt);
                            // Check if id exists
                            if(mysqli_stmt_num_rows($stmt) == 1){ 
                                // Bind result variables
                                mysqli_stmt_bind_result($stmt, $file_uploaded);
                                if(mysqli_stmt_fetch($stmt)) {
                                    /* Update session variable file_uploaded with
                                     value from database */
                                    $_SESSION["file_uploaded"] = $file_uploaded; 
                                }
                            }
                            // Close statement
                            mysqli_stmt_close($stmt);
                        }
                    }
                }
            }
            // Close connection
            mysqli_close($link);
        } else {
            echo "Sorry, there was an error uploading your file. <br>";
        }
    }
}
?>