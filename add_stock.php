<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Stock </title>
    <link rel="stylesheet" href="add_stock.css?v=<?php echo time(); ?>">
</head>
<body>

    <div class="container">
        <h2>Add new stock in existing stock data</h2>
        <form action="" method="POST">
            <table id="inputTable">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Stock Name</th>
                        <th>Quantity</th>
                        <th>Available</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><input type="text" name="category" placeholder="Stock Category" required>
                        <td><input type="text" name="Stock_name[]" placeholder="Stock Name" required></td>
                        <td><input type="number" name="quantity[]" placeholder="StockQuantity" required></td>
                        <td><input type="text" name="available[]" placeholder="Available Stock" required></td>
                        <td><button type="button" class="remove-row">Remove</button></td>
                    </tr>
                </tbody>
            </table>
            <button type="button" class="add-row">Add Row</button>
           
            <input type="submit" value="Submit">
        </form>
    </div>

    <script>
        // Add a new row when the "Add Row" button is clicked
        document.querySelector('.add-row').addEventListener('click', function () {
            const table = document.getElementById('inputTable').getElementsByTagName('tbody')[0];
            const newRow = table.insertRow();

            newRow.innerHTML = `
                 <td><input type="text" name="category" placeholder="Stock Category" required>
                <td><input type="text" name="stock_name[]" placeholder="Stock Name" required></td>
                <td><input type="text" name="qunatity[]" placeholder="Stock Quantity " required></td>
                <td><input type="text" name="available[]" placeholder="Available Stock" required></td>
                <td><button type="button" class="remove-row">Remove</button></td>
            `;

            // Add event listener to new remove buttons
            updateRemoveButtons();
        });

        // Remove a row when the "Remove" button is clicked
        function updateRemoveButtons() {
            const removeButtons = document.querySelectorAll('.remove-row');
            removeButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    this.closest('tr').remove();
                });
            });
        }

        updateRemoveButtons(); 
    </script>

</body>
</html>


<?php

session_start();
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    if (isset($_POST['Stock_name']) && isset($_POST['quantity']) && isset($_POST['available'])) {
        
        $category=$_POST['category'];
        $stockNames = $_POST['Stock_name'];
        $quantities = $_POST['quantity'];
        $availables = $_POST['available'];

        
        if (count($stockNames) == count($quantities) && count($quantities) == count($availables)) {
            
            for ($i = 0; $i < count($stockNames); $i++) {
                $category =htmlspecialchars($category[$i]);
                $stockName = htmlspecialchars($stockNames[$i]);
                $quantity = (int)htmlspecialchars($quantities[$i]);
                $available = htmlspecialchars($availables[$i]);

                // Insert the data into the database
                $sql = "INSERT INTO stock ( category,stock_name, quantity, available) VALUES (?,?, ?, ?)";
                $stmt = statement($con, $sql,$category, $stockName, $quantity, $available);
                $stmt->execute();
            }
            
            echo "Data successfully inserted.";
        } else {
            echo "Error: The number of fields do not match.";
        }
    } else {
        echo "Error: Missing form data.";
    }
} 
$con->close();
?>
