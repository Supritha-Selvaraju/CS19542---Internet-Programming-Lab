<?php
include('db_connect.php');
header('Content-Type: text/html; charset=utf-8');

if (isset($_GET['query']) && !empty(trim($_GET['query']))) {
    $query = htmlspecialchars(trim($_GET['query'])); // Sanitize input
    
    // Prepare the SQL statement with the correct column names
    $stmt = $conn->prepare("SELECT * FROM skincare_recommendations WHERE brand LIKE ? OR 'name' LIKE ? OR product_type LIKE ? OR ingredients LIKE ? OR skin_type LIKE ?");
    $searchTerm = '%' . $query . '%';
    $stmt->bind_param("sssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
    
    // Execute the statement
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        
        echo "<h2>Search Results for '$query'</h2>";
        
        // Check if results are found
        if ($result->num_rows > 0) {
            // Add the container for product list here
echo '<div class="product-container">';
echo '<div class="product-list">';
while ($row = $result->fetch_assoc()) {
    echo '<div class="product-item">';
    echo '<img src="' . htmlspecialchars($row['images']) . '" alt="' . htmlspecialchars($row['name']) . '">';
    echo '<div class="product-details">';
    echo '<h3>Brand: ' . htmlspecialchars($row['brand']) . '</h3>';
    echo '<p class="product-name">Product Name: ' . htmlspecialchars($row['name']) . '</p>';
    echo '<p>Type: ' . htmlspecialchars($row['product_type']) . '</p>';
    echo '<p>Country: ' . htmlspecialchars($row['country']) . '</p>';
    echo '<p>Ingredients: ' . htmlspecialchars($row['ingredients']) . '</p>';
    echo '<p>After Use: ' . htmlspecialchars($row['afterUse']) . '</p>';
    echo '<button class="compare-button" data-product=\''. json_encode($row) . '\'>Compare</button>'; 
    echo '</div>';
    echo '</div>';
}
echo '</div>';
echo '</div>';
        } else {
            echo "<p>No results found for '$query'.</p>";
        }
    } else {
        echo "Error executing the query: " . $stmt->error;
    }
} else {
    echo "<h2>No search query provided.</h2>";
}
?>

<div id="comparisonModal" class="modal">
    <div class="modal-content">
        <span class="close-button" onclick="closeModal()">&times;</span>
        <h2>Product Comparison</h2>
        <div id="comparisonDetails"></div>
    </div>
</div>


<script>
let selectedProducts = [];

// Add the modal functions here
function openModal() {
    document.getElementById('comparisonModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('comparisonModal').style.display = 'none';
}

document.querySelectorAll('.compare-button').forEach(button => {
    button.addEventListener('click', () => {
        const product = JSON.parse(button.getAttribute('data-product'));
        
        // Add product to the selected list
        if (!selectedProducts.includes(product)) {
            selectedProducts.push(product);
        }

        if (selectedProducts.length === 2) {
            displayComparison();
            selectedProducts = []; // Reset after comparison
        } else {
            alert('Please select 2 products to compare.');
        }
    });
});

// Function to display comparison in modal
function displayComparison() {
    const comparisonDetails = document.getElementById('comparisonDetails');
    comparisonDetails.innerHTML = ''; // Clear previous comparisons

    // Create a structured comparison layout
    comparisonDetails.innerHTML = `
        <div class="comparison-table">
            <div class="comparison-header">
                <div>Attribute</div>
                <div>${selectedProducts[0].brand} - ${selectedProducts[0].name}</div>
                <div>${selectedProducts[1].brand} - ${selectedProducts[1].name}</div>
            </div>
            <div class="comparison-row">
                <div>Type:</div>
                <div>${selectedProducts[0].product_type}</div>
                <div>${selectedProducts[1].product_type}</div>
            </div>
            <div class="comparison-row">
                <div>Country:</div>
                <div>${selectedProducts[0].country}</div>
                <div>${selectedProducts[1].country}</div>
            </div>
            <div class="comparison-row">
                <div>Ingredients:</div>
                <div class="ingredient-cell">${selectedProducts[0].ingredients}</div>
                <div class="ingredient-cell">${selectedProducts[1].ingredients}</div>
            </div>
            <div class="comparison-row">
                <div>After Use:</div>
                <div>${selectedProducts[0].afterUse}</div>
                <div>${selectedProducts[1].afterUse}</div>
            </div>
        </div>
    `;

    openModal(); // Call to open the modal here
}
</script>



<style>
    body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f9;
}

h2 {
    text-align: center;
    font-size: 1.8em;
    color: #333;
    margin-top: 30px;
}
.product-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
    max-width: 1200px;
    margin: 20px auto;
    padding: 20px;
}

.product-item {
    display: flex;
    align-items: center;
    background-color: #ffffff;
    border-radius: 15px;
    border: 1px solid #e0e0e0;
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.05);
    padding: 20px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.product-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
}

.product-item img {
    width: 130px;
    height: 130px;
    border-radius: 12px;
    object-fit: cover;
    margin-right: 25px;
    flex-shrink: 0;
}

.product-details {
    display: flex;
    flex-direction: column;
    justify-content: center;
    flex-grow: 1;
}

.product-details h3 {
    font-size: 1.3em;
    color: #333;
    margin: 0 0 10px;
    line-height: 1.2;
}

.product-details p {
    font-size: 1em;
    color: #666;
    margin: 5px 0;
}

.product-details .product-name {
    font-weight: bold;
    color: #333;
}

.product-details .product-name:before {
    content: "";
    display: block;
    width: 40px;
    height: 2px;
    background-color: #007bff;
    margin: 5px 0;
}

@media (max-width: 600px) {
    .product-item {
        flex-direction: column;
        align-items: center;
        padding: 15px;
    }

    .product-item img {
        margin-right: 0;
        margin-bottom: 15px;
    }

    .product-details {
        text-align: center;
    }
}

/* Modal styles */
.modal {
    display: none; 
    position: fixed; 
    z-index: 1000; 
    left: 0;
    top: 0;
    width: 100%; 
    height: 100%; 
    overflow: auto; 
    background-color: rgba(0, 0, 0, 0.8); /* Darker overlay */
    backdrop-filter: blur(4px); /* Subtle blur effect */
    transition: opacity 0.4s ease; /* Smooth fade-in/out */
}

.modal-content {
    background-color: #fefefe; 
    margin: 5% auto; 
    padding: 40px; 
    border: none; 
    border-radius: 15px; 
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.5); 
    width: 90%; /* Increased width */
    max-width: 900px; /* Increased max width */
    max-height: 90%; /* Ensure modal doesn't overflow vertically */
    overflow-y: auto; 
    position: relative; 
    transition: transform 0.3s ease, opacity 0.3s ease;
}

/* Close button */
.close-button {
    color: #aaa; 
    position: absolute;
    top: 20px;
    right: 20px;
    font-size: 24px; 
    font-weight: bold;
    cursor: pointer;
    transition: color 0.3s ease;
}

.close-button:hover,
.close-button:focus {
    color: #ff6b6b; 
}

/* Comparison table styles */

.comparison-table {
    width: 100%;
    border-collapse: collapse;
    font-family: 'Roboto', sans-serif; 
    margin-top: 20px;
}

.comparison-header {
    font-weight: bold;
    background-color: #4a90e2;
    color: #fff; 
    display: flex;
    justify-content: space-between;
    align-items: center; /* Align items vertically */
    padding: 15px;
    border-radius: 10px 10px 0 0;
    font-size: 1.2em;
}

.comparison-header img {
    width: 50px;
    height: auto;
    margin-right: 10px;
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.comparison-row {
    display: flex;
    justify-content: space-between;
    align-items: center; 
    padding: 15px;
    background-color: #fafafa;
    border-bottom: 1px solid #eee;
    transition: background-color 0.3s ease, transform 0.2s ease;
}

.comparison-row:hover {
    background-color: #eaf4fc; 
    transform: translateY(-2px); 
}

.comparison-row div {
    padding: 15px;
    flex: 1;
    text-align: center; 
    transition: background-color 0.3s ease, transform 0.2s ease;
}

.comparison-row div:first-child {
    font-weight: bold;
    color: #4a90e2; 
}

/* Ingredient cell handling */
.ingredient-cell {
    max-width: 300px;
    word-wrap: break-word;
    white-space: pre-wrap;
    background-color: #f9f9f9; 
    padding: 10px;
    border-radius: 5px;
}

/* Scrollbar */
.modal-content::-webkit-scrollbar {
    width: 8px;
}

.modal-content::-webkit-scrollbar-thumb {
    background-color: #4a90e2; 
    border-radius: 4px;
}

.modal-content::-webkit-scrollbar-track {
    background-color: #f1f1f1;
}

/* Product image styling */
.product-image {
    width: 50px; 
    height: auto; 
    margin-right: 10px; 
    border-radius: 4px; 
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2); 
}

.product-info {
    display: flex;
    align-items: center; 
    gap: 10px; 
}


</style>