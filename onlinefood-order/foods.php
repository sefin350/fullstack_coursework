<?php 
// Include config (which already starts session)
include('config/constants.php');

// Initialize cart if not exists
if(!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Handle Add to Cart AJAX request
if(isset($_POST['action']) && $_POST['action'] == 'add_to_cart') {
    $food_id = $_POST['food_id'];
    $title = $_POST['title'];
    $price = $_POST['price'];
    $image = $_POST['image'];
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    
    // Check if item already exists in cart
    $item_exists = false;
    foreach($_SESSION['cart'] as $key => $item) {
        if($item['id'] == $food_id) {
            $_SESSION['cart'][$key]['quantity'] += $quantity;
            $item_exists = true;
            break;
        }
    }
    
    // If item doesn't exist, add new item
    if(!$item_exists) {
        $_SESSION['cart'][] = array(
            'id' => $food_id,
            'title' => $title,
            'price' => $price,
            'image' => $image,
            'quantity' => $quantity
        );
    }
    
    // Return cart count
    echo json_encode(array(
        'success' => true,
        'cart_count' => count($_SESSION['cart']),
        'message' => 'Added to cart successfully!'
    ));
    exit;
}

include('partials-front/menu.php'); 
?>

<!-- fOOD sEARCH Section Starts Here -->
<section class="food-search text-center">
    <div class="container">
        <?php 
            //Get the Search Keyword
            $search = isset($_POST['search']) ? $_POST['search'] : '';
        ?>

        <h2><a href="#" class="text-white">Search for Foods</a></h2>
        <form id="searchForm" action="<?php echo SITEURL; ?>food-search.php" method="POST">
            <input type="search" id="searchInput" name="search" placeholder="Search for Food.." required value="<?php echo $search; ?>">
            <input type="submit" name="submit" value="Search" class="btn btn-primary">
        </form>
        
        <!-- Loading indicator -->
        <div id="loadingIndicator" style="display: none; margin-top: 20px;">
            <p>Searching...</p>
        </div>
        
        <!-- Search results container -->
        <div id="searchResults" style="margin-top: 30px;">
            <!-- Results will be loaded here -->
        </div>
    </div>
</section>
<!-- fOOD sEARCH Section Ends Here -->

<!-- fOOD MEnu Section Starts Here -->
<section class="food-menu">
    <div class="container">
        <h2 class="text-center">Food Menu</h2>

        <?php 
            //Display Foods that are Active
            $sql = "SELECT * FROM tbl_food WHERE active='Yes'";

            //Execute the Query
            $res=mysqli_query($conn, $sql);

            //Count Rows
            $count = mysqli_num_rows($res);

            //CHeck whether the foods are availalable or not
            if($count>0)
            {
                //Foods Available
                while($row=mysqli_fetch_assoc($res))
                {
                    //Get the Values
                    $id = $row['id'];
                    $title = $row['title'];
                    $description = $row['description'];
                    $price = $row['price'];
                    $image_name = $row['image_name'];
                    ?>
                    
                    <div class="food-menu-box">
                        <div class="food-menu-img">
                            <?php 
                                //CHeck whether image available or not
                                if($image_name=="")
                                {
                                    //Image not Available
                                    echo "<div class='error'>Image not Available.</div>";
                                }
                                else
                                {
                                    //Image Available
                                    ?>
                                    <img src="<?php echo SITEURL; ?>images/food/<?php echo $image_name; ?>" alt="<?php echo $title; ?>" class="img-responsive img-curve">
                                    <?php
                                }
                            ?>
                            
                        </div>

                        <div class="food-menu-desc">
                            <h4><?php echo $title; ?></h4>
                            <p class="food-price">Rs <?php echo $price; ?></p>
                            <p class="food-detail">
                                <?php echo $description; ?>
                            </p>
                            <br>

                            <a href="#" 
                               class="btn btn-primary add-to-cart-btn" 
                               data-id="<?php echo $id; ?>"
                               data-title="<?php echo htmlspecialchars($title); ?>"
                               data-price="<?php echo $price; ?>"
                               data-image="<?php echo $image_name; ?>">
                                Add to Cart
                            </a>
                            <a href="<?php echo SITEURL; ?>order.php?food_id=<?php echo $id; ?>" class="btn btn-primary">Order Now</a>
                        </div>
                    </div>

                    <?php
                }
            }
            else
            {
                //Food not Available
                echo "<div class='error'>Food not found.</div>";
            }
        ?>

        <div class="clearfix"></div>

    </div>

</section>
<!-- fOOD Menu Section Ends Here -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.getElementById('searchForm');
    const searchInput = document.getElementById('searchInput');
    const loadingIndicator = document.getElementById('loadingIndicator');
    const searchResults = document.getElementById('searchResults');
    
    // Handle form submission
    searchForm.addEventListener('submit', function(e) {
        e.preventDefault(); // Prevent default form submission
        
        const searchValue = searchInput.value.trim();
        
        if (searchValue === '') {
            return;
        }
        
        // Show loading indicator
        loadingIndicator.style.display = 'block';
        searchResults.innerHTML = '';
        
        // Create FormData object
        const formData = new FormData();
        formData.append('search', searchValue);
        formData.append('submit', 'Search');
        formData.append('ajax', '1'); // Flag to identify AJAX requests
        
        // Send AJAX request
        fetch('<?php echo SITEURL; ?>food-search.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            // Hide loading indicator
            loadingIndicator.style.display = 'none';
            
            // Display results
            searchResults.innerHTML = data;
            
            // Re-attach cart listeners
            attachCartListeners();
        })
        .catch(error => {
            console.error('Error:', error);
            loadingIndicator.style.display = 'none';
            searchResults.innerHTML = '<p class="text-danger">An error occurred. Please try again.</p>';
        });
    });
    
    // Optional: Real-time search as user types (debounced)
    let searchTimeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        
        searchTimeout = setTimeout(function() {
            const searchValue = searchInput.value.trim();
            
            if (searchValue.length >= 2) { // Only search if 2+ characters
                searchForm.dispatchEvent(new Event('submit'));
            }
        }, 500); // Wait 500ms after user stops typing
    });
    
    // Function to attach cart listeners
    function attachCartListeners() {
        const addToCartBtns = document.querySelectorAll('.add-to-cart-btn');
        
        addToCartBtns.forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                
                const foodId = this.getAttribute('data-id');
                const title = this.getAttribute('data-title');
                const price = this.getAttribute('data-price');
                const image = this.getAttribute('data-image');
                
                // Create FormData
                const formData = new FormData();
                formData.append('action', 'add_to_cart');
                formData.append('food_id', foodId);
                formData.append('title', title);
                formData.append('price', price);
                formData.append('image', image);
                formData.append('quantity', 1);
                
                // Change button text
                const originalText = btn.textContent;
                btn.textContent = 'Adding...';
                btn.disabled = true;
                
                // Send AJAX request
                fetch('<?php echo SITEURL; ?>foods.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        // Show success
                        btn.textContent = '✓ Added!';
                        btn.style.backgroundColor = '#28a745';
                        
                        // Update cart count in navbar (if badge exists)
                        const cartBadge = document.querySelector('.menu a[href*="cart"] span');
                        if(cartBadge) {
                            cartBadge.textContent = data.cart_count;
                        }
                        
                        // Show notification
                        showNotification('Added to cart successfully!');
                        
                        // Reset after 2 seconds
                        setTimeout(function() {
                            btn.textContent = originalText;
                            btn.style.backgroundColor = '';
                            btn.disabled = false;
                        }, 2000);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error adding to cart');
                    btn.textContent = originalText;
                    btn.disabled = false;
                });
            });
        });
    }
    
    // Function to show notification
    function showNotification(message) {
        // Create notification element
        const notification = document.createElement('div');
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #28a745;
            color: white;
            padding: 15px 25px;
            border-radius: 5px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            z-index: 9999;
            animation: slideIn 0.3s ease-out;
        `;
        
        // Add to body
        document.body.appendChild(notification);
        
        // Remove after 3 seconds
        setTimeout(function() {
            notification.style.animation = 'slideOut 0.3s ease-out';
            setTimeout(function() {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }
    
    // Initial attachment of cart listeners
    attachCartListeners();
});
</script>

<style>
#searchResults {
    text-align: left;
}

#loadingIndicator {
    color: #fff;
    font-size: 18px;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOut {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(100%);
        opacity: 0;
    }
}
</style>

<?php include('partials-front/footer.php'); ?>