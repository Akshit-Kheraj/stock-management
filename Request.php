<?php

include 'db_connection.php';

if($_SERVER["REQUEST_METHOD"] =="POST"){

  $category =$_POST['category'];
  $comp_name =$_POST['name'];
  $Quantity ="quantity";

 
}

?>



<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Request</title>

    <link rel="stylesheet" href="register.css" />
  </head>

  <body>
    <h1><b>Request</b></h1>
    <hr />
    <form action="submit" method="post">
      <label for="category">Category of Component</label>
      <br />
      <select name="category" id="category">
        <option>Electronic</option>
        <option>Stationary</option>
        <option>Stationary</option>
      </select>

      <br />
      <label for="name">Component Name</label>
      <br />
      <input
        type="text"
        id="name"
        name="name"
        placeholder="Component Name"
        required
      />
      <br />
      <label for="quantity">Quantity</label>
      <br />
      <input
        type="number"
        id="quantity"
        name="quantity"
        placeholder="Quantity"
        required
      />
      
      <br />
      <button type="submit">Submit</button>
    </form>
    <br />
  </body>
</html>
