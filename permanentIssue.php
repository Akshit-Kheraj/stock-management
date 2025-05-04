<?php
session_start();
include 'db_connection.php'; // Assuming this file handles database connection

// Fetch Locations
$locationQuery = "SELECT location_id, location_name FROM location";
$locationResult = $con->query($locationQuery);

// Fetch Stock Components
$stockQuery = "SELECT stock_id, stock_name, available FROM stock WHERE available > 0";
$stockResult = $con->query($stockQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permanent Issue</title>
    <link rel="stylesheet" href="add_stock.css?v=<?php echo time(); ?>">

    <!-- Include jQuery and Select2 CSS/JS for the search dropdown functionality -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
</head>
<body>
    <div class="container">
        <h2>Issue Components from Stock</h2>
        <form action="" method="POST">
            <!-- Location Dropdown -->
            <label for="location">Select Location:</label>
            <select name="location_id" class="location-dropdown" required>
                <option value="" disabled selected>Select Location</option>
                <?php while($row = $locationResult->fetch_assoc()): ?>
                    <option value="<?= $row['location_id'] ?>"><?= $row['location_name'] ?></option>
                <?php endwhile; ?>
            </select>

            <h3>Allocate Components</h3>
            <table id="issueTable">
                <thead>
                    <tr>
                        <th>Component</th>
                        <th>Available</th>
                        <th>Quantity to Issue</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <select name="component_id[]" class="component-dropdown" required>
                                <option value="" disabled selected>Select Component</option>
                                <?php while($row = $stockResult->fetch_assoc()): ?>
                                    <option value="<?= $row['stock_id'] ?>" data-available="<?= $row['available'] ?>">
                                        <?= $row['stock_name'] ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </td>
                        <td><span class="available"></span></td>
                        <td><input type="number" name="quantity[]" min="1" placeholder="Quantity" required></td>
                        <td><button type="button" class="remove-row">Remove</button></td>
                    </tr>
                </tbody>
            </table>
            <button type="button" class="add-row">Add Row</button>
            <input type="submit" value="Submit">
        </form>
    </div>

    <script>
        // Initialize Select2 on component and location dropdowns
        function initializeSelect2() {
            $('.component-dropdown').select2({
                placeholder: 'Search and select a component',
                allowClear: true
            });
            $('.location-dropdown').select2({
                placeholder: 'Search and select a location',
                allowClear: true
            });
        }

        // Update available stock when a component is selected
        function updateAvailable() {
            // Apply change event to all current and future selects
            $(document).on('change', 'select[name="component_id[]"]', function () {
                const selectedOption = $(this).find('option:selected');  // Get the selected option
                const available = selectedOption.data('available');  // Retrieve the "data-available" attribute
                $(this).closest('tr').find('.available').text(available);  // Update the available stock display
            });
        }

        // Add new row functionality
        document.querySelector('.add-row').addEventListener('click', function () {
            const table = document.getElementById('issueTable').getElementsByTagName('tbody')[0];
            const newRow = document.createElement('tr'); // Create new table row

            newRow.innerHTML = `
                <td>
                    <select name="component_id[]" class="component-dropdown" required>
                        <option value="" disabled selected>Select Component</option>
                        <?php
                        // Re-run the stock query for the new rows
                        $stockResult = $con->query($stockQuery);
                        while($row = $stockResult->fetch_assoc()): ?>
                            <option value="<?= $row['stock_id'] ?>" data-available="<?= $row['available'] ?>">
                                <?= $row['stock_name'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </td>
                <td><span class="available"></span></td>
                <td><input type="number" name="quantity[]" min="1" placeholder="Quantity" required></td>
                <td><button type="button" class="remove-row">Remove</button></td>
            `;

            // Append the new row to the table
            table.appendChild(newRow);

            // Reinitialize Select2 for new rows
            initializeSelect2();

            // Ensure available stock update works for new rows
            updateAvailable();

            // Reattach remove row functionality for the new button
            updateRemoveButtons();
        });

        // Remove row functionality
        function updateRemoveButtons() {
            const removeButtons = document.querySelectorAll('.remove-row');
            removeButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    this.closest('tr').remove();
                });
            });
        }

        // Initial setup
        initializeSelect2(); // Initialize Select2 for the first row
        updateAvailable(); // Set up availability display for first row
        updateRemoveButtons(); // Set up remove button for first row
    </script>
</body>
</html>

<?php
// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $locationId = $_POST['location_id'];
    $componentIds = $_POST['component_id'];
    $quantities = $_POST['quantity'];

    // Validate data
    if (count($componentIds) == count($quantities)) {
        for ($i = 0; $i < count($componentIds); $i++) {
            $componentId = (int) htmlspecialchars($componentIds[$i]);
            $quantity = (int) htmlspecialchars($quantities[$i]);

            // Insert data into permanent_issue table
            $sql = "INSERT INTO permanent_issue (stock_id, location_id, quantity) VALUES (?, ?, ?)";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("iii", $componentId, $locationId, $quantity);
            $stmt->execute();

            // Optionally update stock table to reduce available stock
            $updateStock = "UPDATE stock SET available = available - ? WHERE stock_id = ?";
            $updateStmt = $con->prepare($updateStock);
            $updateStmt->bind_param("ii", $quantity, $componentId);
            $updateStmt->execute();
        }
        echo "Components successfully issued.";
    } else {
        echo "Error: Mismatch in component and quantity data.";
    }
}

$con->close();
?>
