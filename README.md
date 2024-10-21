# CS19542---Internet-Programming-Lab
Skincare Recommendation System 

1. Project Overview
The Skincare Recommendation System is a web application designed to recommend skincare products based on user input, such as skin type, product type, and other relevant attributes. It uses a user-friendly interface to display product details, allow product comparison, and manage user information. The project is built using HTML, CSS, JavaScript, PHP, and MySQL.

2. Project Objectives
The primary objective of this system is to:

Help users find skincare products tailored to their specific skin type.
Allow users to filter products by skin type, product type, and other parameters.
Provide an option to compare selected products side-by-side to help users make informed choices.

3. Features
3.1. User Input & Filtering
The system collects user information such as name, age, and skin type via a form.
Users can filter products based on their skin type and product preferences.
3.2. Product Recommendation
The system retrieves products from the database based on the user’s input and displays them as cards.
Each product card contains details such as the product image, name, brand, type, country of origin, ingredients, and after-use effects.
3.3. Product Comparison
Users can select up to 3 products for comparison.
The comparison is displayed in a modal, allowing users to easily evaluate differences between selected products.
3.4. Responsive Design
The application is designed to be responsive across various devices, with a clean and modern UI.

4. System Requirements
4.1. Software Requirements
XAMPP or any other PHP server
MySQL Database
Web Browser: Chrome, Firefox, or Edge
Editor: Visual Studio Code, Sublime Text, or Notepad++
4.2. Technical Stack
Frontend: HTML, CSS, JavaScript (with Bootstrap for UI elements)
Backend: PHP
Database: MySQL
                                 
5. Database Design
5.1. Tables
5.1.1. user_details
Columns:
id (int, auto-increment, primary key)
name (varchar(255), not null)
age (int, not null)
skin_type (varchar(50), not null)
created_at (timestamp, default to current timestamp)
5.1.2. skincare_recommendations
Columns:
id (int, auto-increment, primary key)
brand (text)
name (text)
product_type (text)
country (text)
ingredients (text)
afterUse (text)
skin_type (varchar(50))
images (text)
5.2. Database Connections
The database is connected using PHP's mysqli functions, with prepared statements for secure querying and data retrieval.

6. Project Workflow
6.1. User Form Submission
The user fills out the form with their name, age, skin type, and product type.
Upon form submission, the PHP script stores the user’s details in the user_details table.
A query is then executed to fetch matching products from the skincare_recommendations table.
6.2. Displaying Products
The products are displayed on the page as neatly designed vertical cards.
Each card shows essential details along with a button to compare the product.
6.3. Product Comparison
Upon selecting products for comparison, the products are added to a comparison list.
The comparison table is displayed in a modal, showing selected products side-by-side.

7. Key Functionalities
7.1. Form Data Processing
The form collects user data and sends it to the server for processing.
Validation ensures that all required fields are provided before submission.
7.2. Product Display
while ($row = $result->fetch_assoc()) {
    echo '<div class="product-item">';
    echo '<img src="' . htmlspecialchars($row['images']) . '" alt="' . htmlspecialchars($row['name']) . '">';
    echo '<div class="product-details">';
    echo '<h3>Brand: ' . htmlspecialchars($row['brand']) . '</h3>';
    echo '<p>Product Name: ' . htmlspecialchars($row['name']) . '</p>';
    echo '<button class="compare-btn" onclick="addToCompare(\'' . htmlspecialchars(json_encode($row)) . '\')">Compare</button>';
    echo '</div>';
    echo '</div>';
}

7.3. JavaScript for Comparison
let compareList = [];
function addToCompare(product) {
    const productData = JSON.parse(product);
    if (compareList.length >= 3) {
        alert("You can compare up to 3 products.");
    } else {
        compareList.push(productData);
        updateCompareSection();
    }
}

function updateCompareSection() {
    const compareContainer = document.getElementById("comparisonTable");
    compareContainer.innerHTML = "";
    compareList.forEach(product => {
        compareContainer.innerHTML += `<tr>
            <td><img src="${product.images}" alt="${product.name}" width="50"></td>
            <td>${product.brand}</td>
            <td>${product.name}</td>
            <td>${product.product_type}</td>
        </tr>`;
    });
}

8. User Interface Design
The UI follows a modern and minimalistic design principle. Bootstrap is used for layout and responsiveness, while custom CSS is added for the following components:

Product Cards: Styled to have consistent spacing and alignment.
Comparison Table: Displayed in a modal with a structured layout.

9. Conclusion
The Skincare Recommendation System provides an efficient solution for users looking to discover and compare skincare products suited to their skin type. The product comparison feature ensures users can make better decisions, while the clean UI and responsive design enhance user experience.
