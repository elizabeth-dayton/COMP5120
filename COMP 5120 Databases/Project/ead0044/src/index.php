<?php

  function fetch_all($mysqli_result) {
    $rows = array();

    if(mysqli_num_rows($mysqli_result) > 0) {
      while($row = mysqli_fetch_assoc($mysqli_result)) {
        array_push($rows, $row);
      }
    }
    return $rows;
  }

  function get_rows($database_connection, $table_name) {
    /* 
    parameters: 
      $database_connection is a connection to a database created by the mysqli_connect function
      $table_name (string) is the name of the table you wish to get the attributes of
    returns:
      $rows is an array of arrays with each inner array representing a row of the table
    */

    $rows = array();
    $sql = "SELECT * FROM {$table_name}";
    $result = mysqli_query($database_connection, $sql);

    if(mysqli_num_rows($result) > 0) {
      while($row = mysqli_fetch_assoc($result)) {
        array_push($rows, $row);
      }
    }

    return $rows;
  }


  function get_tables($database_connection) {
    /* 
      parameters: 
        $database_connection is a connection to a database created by the mysqli_connect function
      returns:
        $tables is an array of all of the tables in the database
    */

    $tables = array();
    $result = mysqli_query($database_connection, "SHOW TABLES");

    $rows = array();
    while($row = mysqli_fetch_assoc($result)) {
      array_push($rows, $row);
    }

    $tables_in_database_name_array = array_keys($rows[0]); // This exists solely for the purpose of getting $tables_in_database_name
    $tables_in_database_name = $tables_in_database_name_array[0];

    for($i = 0; $i < count($rows); $i++) {
      array_push($tables, $rows[$i][$tables_in_database_name]);
    }

    return $tables;
  }


  function get_attributes($database_connection, $table_name) {
    /* 
      parameters: 
        $database_connection is a connection to a database created by the mysqli_connect function
        $table_name (string) is the name of the table you wish to get the attributes of
      returns:
        $attributes (string array) is an array of all of the attributes in a table
    */
    
    $sql = "SELECT * FROM {$table_name}";
    $result = mysqli_query($database_connection, $sql);
    $row = mysqli_fetch_assoc($result);
    $attributes = array_keys($row);
    return $attributes;
  }


  function print_table($database_connection, $table_name) {
    /* 
      parameters: 
        $database_connection is a connection to a database created by the mysqli_connect function
        $table_name (string) is the name of the table you wish to get the attributes of
      returns:
        nothing
    */

    $attributes = get_attributes($database_connection, $table_name);
    $tuples = get_rows($database_connection, $table_name);

    print("<table>\n");
        print("<tr>\n");
          for($i = 0; $i < count($attributes); $i++) {
            print("<th>{$attributes[$i]}</th>\n");
          }
        print("</tr>\n");
          for($j = 0; $j < count($tuples); $j++) {
            print("<tr>\n");
              foreach($attributes as $attribute) {
                print("<td>{$tuples[$j][$attribute]}</td>\n");
              }
            print("</tr>\n");
          }

    print("</table>\n");
  }

  
  function print_tables($database_connection) {
    /* 
      parameters: 
        $database_connection is a connection to a database created by the mysqli_connect function
      returns:
        nothing
    */

    $tables = get_tables($database_connection);

    foreach($tables as $table) {
      print("<h3>{$table}</h3>");
      print_table($database_connection, $table);
      print("<br><br>");
    }
  }

  function html_table($data) {
    /*
    parameters:
      $data is a php array that needs to be formatted into html
    returns:
      nothing
    */

    $row = mysqli_fetch_assoc($data);
    $rows = array();
    array_push($rows, $row);
    $attributes = array_keys($row);

    if(mysqli_num_rows($data) > 0) {
      while($row = mysqli_fetch_assoc($data)) {
        array_push($rows, $row);
      }
    }

    print("<table>\n");
        print("<tr>\n");
          for($i = 0; $i < count($attributes); $i++) {
            print("<th>{$attributes[$i]}</th>\n");
          }
        print("</tr>\n");
          for($j = 0; $j < count($rows); $j++) {
            print("<tr>\n");
              foreach($attributes as $attribute) {
                print("<td>{$rows[$j][$attribute]}</td>\n");
              }
            print("</tr>\n");
          }

    print("</table>\n");

}

  function print_query_result($database_connection, $mysql_query) {
    /*
      parameters:
        $database_connection is a connection to a database created by the mysqli_connect function
        $sql_query is a sql query inputed in the form by the user (any drop queries will not be accepted)
      returns:
        a table containing the relevant elements from the $sql_query
    */
    $word = "drop";

    if (strpos($mysql_query, $word) !== false) {
      print("Drop is not an accepted operation.");
    }

    else {
      $result = mysqli_query($database_connection, $mysql_query);

      if ($result) {
        html_table($result);
      }

      else {
        echo mysqli_error($database_connection);
      }
    }
  }

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scranton Books</title>
    <link rel="stylesheet" href="main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.14.0/css/all.min.css">
  </head>
  
  <body>

    <nav class="nav">
        <i class="fas fa-book-reader fa-2x" style="color:white;"></i>
        <a href="#tables">Tables</a>
        <a href="#query">SQL Query</a>
    </nav>

    <div id="tables" class="tables">
      <?php
        // Create connection to database
        $conn = mysqli_connect("mysql.auburn.edu", "clb0119", "Brendens0crease$$$", "clb0119db");
        if (!$conn) {
          die("Connection failed: " . mysqli_connect_error());
        }

        // Print the tables to the browser
        print_tables($conn);

      ?>
    </div>

    <div id="query" class="query">
      <form action="" method="post">
          <label for="sql-query">SQL Query:</label>
          <input type="text" name="query" id="sql-query" style="width:900px; height:50px; font-size:20px"></input>
          <input type="submit" name="submit" value="Submit" style="font-family: Arial, Helvetica, sans-serif; font-size:25px"></input>
      </form>

      <?php
        if(isset($_POST["submit"])){
          $sql_query = $_POST["query"];

          $query = stripslashes($sql_query);
          print_query_result($conn, $query);

        }
      ?>
    </div>

  </body>
</html>
