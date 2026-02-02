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

    <section class="food-search text-center">
    <div class="container">
        <?php 
            //Get the Search Keyword
            $search = isset($_POST['search']) ? $_POST['search'] : '';
        ?>

        <h2><a href="#" class="text-white">Search for:</a></h2>
        <form id="searchForm" action="<?php echo SITEURL; ?>food-search.php" method="POST">
            <input type="search" id="searchInput" name="search" placeholder="Search Foods" required value="<?php echo $search; ?>">
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
                fetch('<?php echo SITEURL; ?>index.php', {
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
                        
                        // Reset after 2 seconds
                        setTimeout(function() {
                            btn.textContent = originalText;
                            btn.style.backgroundColor = '';
                            btn.disabled = false;
                        }, 2000);
                    }
                })
                .catch(error => {
                    alert('Error adding to cart');
                    btn.textContent = originalText;
                    btn.disabled = false;
                });
            });
        });
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
</style>

    <?php 
        if(isset($_SESSION['order']))
        {
            echo $_SESSION['order'];
            unset($_SESSION['order']);
        }
    ?>
    <?php
        $ordercount = "SELECT food_id,count(food_id) as popular from tbl_order group by food_id ORDER BY popular DESC LIMIT 2";
        if(mysqli_num_rows(mysqli_query($conn, $ordercount))>0){
    ?>
    <section class="food-menu">
        <div class="container">
            <h2 class="text-center">Popular Food</h2>
            <?php
                $result = mysqli_query($conn, $ordercount);
                while( ($who = mysqli_fetch_assoc($result)) ) {
                $refood = $who['food_id'];
                $resql = "SELECT * from tbl_food where id= $refood";
                $popresult = mysqli_query($conn, $resql);                
                while( ($rowho = mysqli_fetch_assoc($popresult)) ) {
                    //Get the Values
                    $id = $rowho['id'];
                    $title = $rowho['title'];
                    $description = $rowho['description'];
                    $price = $rowho['price'];
                    $image_name = $rowho['image_name'];
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
                                <img src="<?php echo SITEURL; ?>images/food/<?php echo $image_name; ?>" alt="" class="img-responsive img-curve">
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
                } }
            ?>
        </div>
    </section> 
    <?php } ?>
    <!-- CAtegories Section Starts Here -->
    <section class="categories">
        <div class="container">
            <h2 class="text-center">Explore Various Food Categories</h2>

            <?php 
                //Create SQL Query to Display CAtegories from Database
                $sql = "SELECT * FROM tbl_category WHERE active='Yes' AND featured='Yes' ORDER BY id DESC LIMIT 3";
                //Execute the Query
                $res = mysqli_query($conn, $sql);
                //Count rows to check whether the category is available or not
                $count = mysqli_num_rows($res);

                if($count>0)
                {
                    //CAtegories Available
                    while($row=mysqli_fetch_assoc($res))
                    {
                        //Get the Values like id, title, image_name
                        $id = $row['id'];
                        $title = $row['title'];
                        $image_name = $row['image_name'];
                        ?>
                        
                        <a href="<?php echo SITEURL; ?>category-foods.php?category_id=<?php echo $id; ?>">
                            <div class="box-3 float-container">
                                <?php 
                                    //Check whether Image is available or not
                                    if($image_name=="")
                                    {
                                        //Display MEssage
                                        echo "<div class='error'>Image not Available</div>";
                                    }
                                    else
                                    {
                                        //Image Available
                                        ?>
                                        <img src="<?php echo SITEURL; ?>images/category/<?php echo $image_name; ?>" alt="Pizza" class="img-responsive img-curve">
                                        <?php
                                    }
                                ?>
                                

                                <h3 class="float-text text-white" ><mark style="background-color:white;"><?php echo $title; ?></mark></h3>
                            </div>
                        </a>

                        <?php
                    }
                }
                else
                {
                    //Categories not Available
                    echo "<div class='error'>Category not Added.</div>";
                }
            ?>


            <div class="clearfix"></div>
        </div>
    </section>
    <!-- Categories Section Ends Here -->



    <!-- fOOD MEnu Section Starts Here -->
    <section class="food-menu">
        <div class="container">
            <h2 class="text-center">Our Food Menu</h2>

            <?php 
            
            //Getting Foods from Database that are active and featured
            //SQL Query
            $sql2 = "SELECT * FROM tbl_food WHERE active='Yes' AND featured='Yes' LIMIT 6";

            //Execute the Query
            $res2 = mysqli_query($conn, $sql2);

            //Count Rows
            $count2 = mysqli_num_rows($res2);

            //CHeck whether food available or not
            if($count2>0)
            {
                //Food Available
                while($row=mysqli_fetch_assoc($res2))
                {
                    //Get all the values
                    $id = $row['id'];
                    $title = $row['title'];
                    $price = $row['price'];
                    $description = $row['description'];
                    $image_name = $row['image_name'];
                    ?>

                    <div class="food-menu-box">
                        <div class="food-menu-img">
                            <?php 
                                //Check whether image available or not
                                if($image_name=="")
                                {
                                    //Image not Available
                                    echo "<div class='error'>Image not available.</div>";
                                }
                                else
                                {
                                    //Image Available
                                    ?>
                                    <img src="<?php echo SITEURL; ?>images/food/<?php echo $image_name; ?>" alt="Chicke Hawain Pizza" class="img-responsive img-curve">
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
                //Food Not Available 
                echo "<div class='error'>Food not available.</div>";
            }
            
            ?>

            

 

            <div class="clearfix"></div>

            

        </div>

        <p class="text-center">
            <a href="<?php echo SITEURL; ?>foods.php">See All Food</a>
        </p>
    </section>
    <!-- fOOD Menu Section Ends Here -->

    
    <?php include('partials-front/footer.php'); ?>