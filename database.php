
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Highland plant gene database </title>
    </head>
    <body>
        <h1>Highland plant gene database</h1>
        <p>Welcome to the Highland plant gene database. This is a simple web application that allows you to search for genes in the Highland plant genome.</p>
       
        <?php
        $servername = "db";
        $user = 'user';
        $password = "userpass";
        $dbname = "plantdb";
        $conn = new mysqli($servername, $user, $password, $dbname);
        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        echo "<h2>Database Connnected !</h2>";
        ?>
   
    </body>
</html>
