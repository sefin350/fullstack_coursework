<?php include('config/constants.php'); ?>
<?php include('partials-front/menu.php'); ?>

<?php 
    // Check whether id is passed or not
    if(isset($_GET['category_id']))
    {
        // Category id is set and get the id
        $category_id = mysqli_real_escape_string($conn, $_GET['category_id']);
        
        // Get the Category Title Based on Category ID
        $sql = "SELECT title FROM tbl_category WHERE id='$category_id'";

        // Execute the Query
        $res = mysqli_query($conn, $sql);

        // Check if query was successful and row exists
        if($res && mysqli_num_rows($res) > 0)
        {
            // Get the value from Database
            $row = mysqli_fetch_assoc($res);
            // Get the Title
            $category_title = $row['title'];
        }
        else
        {
            // Category not found
            header('location:'.SITEURL);
            exit();
        }
    }
    else
    {
        // Category not passed
        // Redirect to Home page
        header('location:'.SITEURL);
        exit();
    }
?>

<!-- fOOD sEARCH Section Starts Here -->
<section class="food-search text-center">
    <div class="container">
        <h2><a href="#" class="text-white">Foods on "<?php echo htmlspecialchars($category_title); ?>"</a></h2>
    </div>
</section>
<!-- fOOD sEARCH Section Ends Here -->

<!-- fOOD MEnu Section Starts Here -->
<section class="food-menu">
    <div class="container">
        <h2 class="text-center">Food Menu</h2>

        <?php 
            // Create SQL Query to Get foods based on Selected Category
            $sql2 = "SELECT * FROM tbl_food WHERE category_id='$category_id'";

            // Execute the Query
            $res2 = mysqli_query($conn, $sql2);

            // Count the Rows
            $count2 = mysqli_num_rows($res2);

            // Check whether food is available or not
            if($count2 > 0)
            {
                // Food is Available
                while($row2 = mysqli_fetch_assoc($res2))
                {
                    $id = $row2['id'];
                    $title = $row2['title'];
                    $price = $row2['price'];
                    $description = $row2['description'];
                    $image_name = $row2['image_name'];
                    ?>
                    
                    <div class="food-menu-box">
                        <div class="food-menu-img">
                            <?php 
                                if($image_name == "")
                                {
                                    // Image not Available
                                    echo "<div class='error'>Image not Available.</div>";
                                }
                                else
                                {
                                    // Image Available
                                    ?>
                                    <img src="<?php echo SITEURL; ?>images/food/<?php echo $image_name; ?>" alt="<?php echo htmlspecialchars($title); ?>" class="img-responsive img-curve">
                                    <?php
                                }
                            ?>
                        </div>

                        <div class="food-menu-desc">
                            <h4><?php echo htmlspecialchars($title); ?></h4>
                            <p class="food-price">Rs <?php echo htmlspecialchars($price); ?></p>
                            <p class="food-detail">
                                <?php echo htmlspecialchars($description); ?>
                            </p>
                            <br>

                            <a href="<?php echo SITEURL; ?>order.php?food_id=<?php echo $id; ?>" class="btn btn-primary">Order Now</a>
                        </div>
                    </div>

                    <?php
                }
            }
            else
            {
                // Food not available
                echo "<div class='error'>Food not Available.</div>";
            }
        ?>

        <div class="clearfix"></div>
    </div>
</section>
<!-- fOOD Menu Section Ends Here -->

<?php include('partials-front/footer.php'); ?>