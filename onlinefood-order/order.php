<?php include('partials-front/menu.php'); ?>

<?php
// ==========================
// FETCH FOOD DETAILS
// ==========================
if (isset($_GET['food_id']) && is_numeric($_GET['food_id'])) {

    $food_id = intval($_GET['food_id']);

    $sql = "SELECT * FROM tbl_food WHERE id = $food_id";
    $res = mysqli_query($conn, $sql);

    if (mysqli_num_rows($res) === 1) {
        $row = mysqli_fetch_assoc($res);
        $title = $row['title'];
        $price = $row['price'];
        $image_name = $row['image_name'];
    } else {
        header('location:' . SITEURL);
        exit;
    }
} else {
    header('location:' . SITEURL);
    exit;
}
?>

<section class="food-search2">
    <div class="container">

        <h2 class="text-center text-white">Fill this form to confirm your order.</h2>

        <form action="" method="POST" class="order">
            <fieldset>
                <legend>Selected Food</legend>

                <div class="food-menu-img">
                    <?php
                    if ($image_name == "") {
                        echo "<div class='error'>Image not Available.</div>";
                    } else {
                    ?>
                        <img src="<?php echo SITEURL; ?>images/food/<?php echo $image_name; ?>"
                             class="img-responsive img-curve">
                    <?php } ?>
                </div>

                <div class="food-menu-desc">
                    <h3><?php echo htmlspecialchars($title); ?></h3>

                    <p class="food-price">Rs <?php echo $price; ?></p>

                    <input type="hidden" name="food_id" value="<?php echo $food_id; ?>">

                    <div class="order-label">Quantity</div>
                    <input type="number" name="qty" value="1" min="1" max="10"
                           class="input-responsive" required>
                </div>
            </fieldset>

            <fieldset>
                <legend>Delivery Details</legend>

                <div class="order-label">Full Name</div>
                <input type="text" name="full_name" required class="input-responsive">

                <div class="order-label">Phone Number</div>
                <input type="text" name="contact" pattern="\d{10}" maxlength="10"
                       class="input-responsive" required>

                <div class="order-label">Email</div>
                <input type="email" name="email" class="input-responsive" required>

                <div class="order-label">Address</div>
                <textarea name="address" rows="6"
                          class="input-responsive" required></textarea>

                <input type="submit" name="submit" value="Confirm Order"
                       class="btn btn-primary">
            </fieldset>
        </form>

<?php
// ==========================
// PROCESS ORDER
// ==========================
if (isset($_POST['submit'])) {

    $foodid = intval($_POST['food_id']);
    $qty = intval($_POST['qty']);

    // Re-fetch price (ANTI-CHEAT)
    $price_sql = "SELECT title, price FROM tbl_food WHERE id = $foodid";
    $price_res = mysqli_query($conn, $price_sql);
    $food_data = mysqli_fetch_assoc($price_res);

    $food = mysqli_real_escape_string($conn, $food_data['title']);
    $price = $food_data['price'];

    $total = $price * $qty;
    $order_date = date("Y-m-d H:i:s");
    $status = "Ordered";

    $customer_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $customer_contact = mysqli_real_escape_string($conn, $_POST['contact']);
    $customer_email = mysqli_real_escape_string($conn, $_POST['email']);
    $customer_address = mysqli_real_escape_string($conn, $_POST['address']);

    $sql2 = "INSERT INTO tbl_order SET
        food = '$food',
        price = $price,
        qty = $qty,
        total = $total,
        order_date = '$order_date',
        status = '$status',
        customer_name = '$customer_name',
        customer_contact = '$customer_contact',
        customer_email = '$customer_email',
        customer_address = '$customer_address',
        food_id = $foodid
    ";

    $res2 = mysqli_query($conn, $sql2);

    if ($res2) {
        $_SESSION['order'] = "
        <div class='success text-center'>
            <p>Food Ordered Successfully 🎉</p>
            <table class='tbl-full'>
                <tr>
                    <th>Name</th>
                    <th>Food</th>
                    <th>Price</th>
                    <th>Qty</th>
                    <th>Total</th>
                </tr>
                <tr>
                    <td>$customer_name</td>
                    <td>$food</td>
                    <td>Rs $price</td>
                    <td>$qty</td>
                    <td>Rs $total</td>
                </tr>
            </table>
        </div>";

        header('location:' . SITEURL);
        exit;
    } else {
        $_SESSION['order'] = "<div class='error text-center'>Order Failed.</div>";
        header('location:' . SITEURL);
        exit;
    }
}
?>

    </div>
</section>

<?php
// ==========================
// SIMILAR FOODS
// ==========================
$cat_sql = "SELECT category_id FROM tbl_food WHERE id = $food_id";
$cat_res = mysqli_query($conn, $cat_sql);
$cat_row = mysqli_fetch_assoc($cat_res);
$category_id = $cat_row['category_id'];

$similar_sql = "SELECT * FROM tbl_food
                WHERE category_id = $category_id
                AND id != $food_id";

$similar_res = mysqli_query($conn, $similar_sql);

if (mysqli_num_rows($similar_res) > 0) {
?>
<section class="food-menu">
    <div class="container">
        <h2 class="text-center">Similar Foods</h2>

        <?php while ($row = mysqli_fetch_assoc($similar_res)) { ?>
            <div class="food-menu-box">
                <div class="food-menu-img">
                    <?php if ($row['image_name']) { ?>
                        <img src="<?php echo SITEURL; ?>images/food/<?php echo $row['image_name']; ?>"
                             class="img-responsive img-curve">
                    <?php } ?>
                </div>

                <div class="food-menu-desc">
                    <h4><?php echo htmlspecialchars($row['title']); ?></h4>
                    <p class="food-price">Rs <?php echo $row['price']; ?></p>
                    <p class="food-detail"><?php echo $row['description']; ?></p>
                    <br>
                    <a href="<?php echo SITEURL; ?>order.php?food_id=<?php echo $row['id']; ?>"
                       class="btn btn-primary">Order Now</a>
                </div>
            </div>
        <?php } ?>
    </div>
</section>
<?php } ?>

<?php include('partials-front/footer.php'); ?>
