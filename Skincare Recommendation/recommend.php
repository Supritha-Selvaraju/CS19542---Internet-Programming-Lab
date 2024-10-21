<?php
include('db_connect.php');
header('Content-Type: text/html; charset=utf-8');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $name = isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '';
    $age = isset($_POST['age']) ? (int)$_POST['age'] : 0;
    $skin_type = isset($_POST['skin_type']) ? htmlspecialchars($_POST['skin_type']) : ''; 
    $product_type = isset($_POST['product_type']) ? htmlspecialchars($_POST['product_type']) : null;


    // Ensure at least one of the filters is provided
    if ($name && $age && ($skin_type || $product_type)) {
        // Insert user details into the user_details table
        $insert_stmt = $conn->prepare("INSERT INTO user_details (name, age, skin_type) VALUES (?, ?, ?)");
        $insert_stmt->bind_param("sis", $name, $age, $skin_type);

        // Prepare the SQL statement based on user input
        $query = "SELECT * FROM skincare_recommendations WHERE 1=1"; // Start with a base query

        // Add conditions based on provided filters
        if (!empty($skin_type)) {
            $query .= " AND skin_type = ?";
        }
        if (!empty($product_type)) {
            $query .= " AND product_type = ?";
        }

        // Prepare the statement
        $select_stmt = $conn->prepare($query);

        // Bind parameters dynamically based on input
        if (!empty($skin_type) && !empty($product_type)) {
            // If both are provided, bind both parameters
            $select_stmt->bind_param("ss", $skin_type, $product_type);
        } elseif (!empty($skin_type)) {
            // If only skin_type is provided
            $select_stmt->bind_param("s", $skin_type);
        } elseif (!empty($product_type)) {
            // If only product_type is provided
            $select_stmt->bind_param("s", $product_type);
        }

// Execute the statement and get the result
$select_stmt->execute();
$result = $select_stmt->get_result();

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
        echo "<h2>All fields are required. Please provide your name and at least one filter.</h2>";
    }
} else {
    echo "<h2>No form data submitted.</h2>";
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
/* Container for product list */
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
    flex-shrink: 0; /* Prevents image from shrinking */
}

.product-details {
    display: flex;
    flex-direction: column;
    justify-content: center;
    flex-grow: 1; /* Allows the content to grow */
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

/* Visual separator */
.product-details .product-name:before {
    content: "";
    display: block;
    width: 40px;
    height: 2px;
    background-color: #007bff;
    margin: 5px 0;
}

/* Media query for responsive design */
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