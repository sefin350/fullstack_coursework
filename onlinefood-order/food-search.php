<?php 
// Include config FIRST to get database connection
include('config/constants.php');

// Check if this is an AJAX request
$isAjax = isset($_POST['ajax']) && $_POST['ajax'] == '1';

// Only include header/menu if NOT an AJAX request
if(!$isAjax) {
    include('partials-front/menu.php'); 
}
?>

<?php if(!$isAjax): ?>
<!-- fOOD sEARCH Section Starts Here -->
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
            <p style="color: #fff;">Searching...</p>
        </div>
    </div>
</section>
<!-- fOOD sEARCH Section Ends Here -->

<!-- fOOD MEnu Section Starts Here -->
<section class="food-menu">
    <div class="container">
        <h2 class="text-center">Food Menu</h2>
        <div id="searchResults">
<?php endif; ?>

<?php 
// SEARCH LOGIC (runs for both AJAX and normal requests)
if(isset($_POST['search']) && $_POST['search'] != '') {
    $search = mysqli_real_escape_string($conn, $_POST['search']);

    //SQL Query to Get foods based on search keyword
    $sql = "SELECT * FROM tbl_food WHERE title LIKE '%$search%' OR description LIKE '%$search%'";

    //Execute the Query
    $res = mysqli_query($conn, $sql);

    //Count Rows
    $count = mysqli_num_rows($res);

    //Check whether food available or not
    if($count > 0) {
        //Food Available
        while($row = mysqli_fetch_assoc($res)) {
            //Get the details
            $id = $row['id'];
            $title = $row['title'];
            $price = $row['price'];
            $description = $row['description'];
            $image_name = $row['image_name'];
            ?>

            <div class="food-menu-box">
                <div class="food-menu-img">
                    <?php 
                        // Check whether image name is available or not
                        if($image_name == "") {
                            //Image not Available
                            echo "<div class='error'>Image not Available.</div>";
                        } else {
                            //Image Available
                            ?>
                            <img src="<?php echo SITEURL; ?>images/food/<?php echo $image_name; ?>" alt="<?php echo $title; ?>" class="img-responsive img-curve">
                            <?php 
                        }
                    ?>
                </div>

                <div class="food-menu-desc">
                    <h4><?php echo $title; ?></h4>
                    <p class="food-price">RS <?php echo $price; ?></p>
                    <p class="food-detail">
                        <?php echo $description; ?>
                    </p>
                    <br>

                    <a href="<?php echo SITEURL; ?>order.php?food_id=<?php echo $id; ?>" class="btn btn-primary">Order Now</a>
                </div>
            </div>

            <?php
        }
    } else {
        //Food Not Available
        echo "<div class='error'>Food not found.</div>";
    }
}

// If this is an AJAX request, exit here (don't send footer)
if($isAjax) {
    exit;
}
?>

<?php if(!$isAjax): ?>
        </div>
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
        e.preventDefault();
        
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
        formData.append('ajax', '1');
        
        const url = '<?php echo SITEURL; ?>food-search.php';
        
        // Send AJAX request
        fetch(url, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('HTTP error! status: ' + response.status);
            }
            return response.text();
        })
        .then(data => {
            // Hide loading indicator
            loadingIndicator.style.display = 'none';
            
            // Display results
            searchResults.innerHTML = data;
        })
        .catch(error => {
            loadingIndicator.style.display = 'none';
            searchResults.innerHTML = '<div class="error">An error occurred. Please try again.</div>';
        });
    });
    
    // Real-time search as user types
    let searchTimeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        
        searchTimeout = setTimeout(function() {
            const searchValue = searchInput.value.trim();
            
            if (searchValue.length >= 2) {
                searchForm.dispatchEvent(new Event('submit'));
            }
        }, 500);
    });
});
</script>

<style>
.error {
    padding: 20px;
    color: #ff0000;
    font-size: 18px;
    text-align: center;
}
</style>

<?php include('partials-front/footer.php'); ?>
<?php endif; ?>