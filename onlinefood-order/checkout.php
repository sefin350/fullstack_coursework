<?php 
include('config/constants.php');

// Check if cart is empty
if(!isset($_SESSION['cart']) || count($_SESSION['cart']) == 0) {
    header('Location: ' . SITEURL . 'foods.php');
    exit;
}

// Calculate total
$total = 0;
foreach($_SESSION['cart'] as $item) {
    $total += $item['price'] * $item['quantity'];
}

// Handle order submission
if(isset($_POST['submit'])) {
    // Get form data
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $order_date = date("Y-m-d H:i:s");
    $status = "Ordered";
    
    // Insert order for each cart item
    $order_success = true;
    
    foreach($_SESSION['cart'] as $item) {
        $food_id = $item['id'];
        $food_title = $item['title'];
        $price = $item['price'];
        $qty = $item['quantity'];
        $item_total = $price * $qty;
        
        // Insert into tbl_order
        $sql = "INSERT INTO tbl_order SET 
                food = '$food_title',
                price = $price,
                qty = $qty,
                total = $item_total,
                order_date = '$order_date',
                status = '$status',
                customer_name = '$full_name',
                customer_contact = '$phone',
                customer_email = '$email',
                customer_address = '$address',
                food_id = $food_id
        ";
        
        $res = mysqli_query($conn, $sql);
        
        if(!$res) {
            $order_success = false;
            break;
        }
    }
    
    if($order_success) {
        // Clear cart
        $_SESSION['cart'] = array();
        
        // Set success message
        $_SESSION['order'] = "<div class='success text-center'>Order placed successfully!</div>";
        header('Location: ' . SITEURL);
        exit;
    } else {
        $_SESSION['order'] = "<div class='error text-center'>Failed to place order. Please try again.</div>";
    }
}

include('partials-front/menu.php'); 
?>

<section class="food-search text-center">
    <div class="container">
        <h2 class="text-white">Checkout</h2>
    </div>
</section>

<section class="food-menu">
    <div class="container">
        
        <div style="max-width: 1000px; margin: 0 auto;">
            
            <!-- Order Summary -->
            <div style="background: white; padding: 30px; border-radius: 10px; margin-bottom: 30px;">
                <h3 style="margin-bottom: 20px; color: #ff6b81;">Order Summary</h3>
                
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                            <th style="padding: 15px; text-align: left;">Item</th>
                            <th style="padding: 15px; text-align: center;">Price</th>
                            <th style="padding: 15px; text-align: center;">Quantity</th>
                            <th style="padding: 15px; text-align: center;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($_SESSION['cart'] as $item): ?>
                        <tr style="border-bottom: 1px solid #dee2e6;">
                            <td style="padding: 15px;">
                                <strong><?php echo $item['title']; ?></strong>
                            </td>
                            <td style="padding: 15px; text-align: center;">
                                Rs <?php echo number_format($item['price'], 2); ?>
                            </td>
                            <td style="padding: 15px; text-align: center;">
                                <?php echo $item['quantity']; ?>
                            </td>
                            <td style="padding: 15px; text-align: center;">
                                <strong>Rs <?php echo number_format($item['price'] * $item['quantity'], 2); ?></strong>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <tr style="background-color: #f8f9fa; border-top: 2px solid #ff6b81;">
                            <td colspan="3" style="padding: 20px; text-align: right;">
                                <strong style="font-size: 20px;">Total:</strong>
                            </td>
                            <td style="padding: 20px; text-align: center;">
                                <strong style="font-size: 24px; color: #ff6b81;">Rs <?php echo number_format($total, 2); ?></strong>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Delivery Details Form -->
            <div style="background: white; padding: 30px; border-radius: 10px;">
                <h3 style="margin-bottom: 20px; color: #ff6b81;">Delivery Details</h3>
                
                <?php 
                if(isset($_SESSION['order'])) {
                    echo $_SESSION['order'];
                    unset($_SESSION['order']);
                }
                ?>
                
                <form action="" method="POST">
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: bold;">Full Name *</label>
                        <input type="text" 
                               name="full_name" 
                               placeholder="Enter your full name" 
                               required
                               style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; font-size: 16px;">
                    </div>
                    
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: bold;">Phone Number *</label>
                        <input type="tel" 
                               name="phone" 
                               placeholder="Enter your phone number" 
                               required
                               style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; font-size: 16px;">
                    </div>
                    
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: bold;">Email *</label>
                        <input type="email" 
                               name="email" 
                               placeholder="Enter your email" 
                               required
                               style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; font-size: 16px;">
                    </div>
                    
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: bold;">Delivery Address *</label>
                        <textarea name="address" 
                                  rows="4" 
                                  placeholder="Enter your complete delivery address" 
                                  required
                                  style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; font-size: 16px; resize: vertical;"></textarea>
                    </div>
                    
                    <div style="text-align: center; margin-top: 30px;">
                        <button type="submit" 
                                name="submit" 
                                class="btn btn-primary" 
                                style="padding: 15px 40px; font-size: 18px; background-color: #28a745; border: none;">
                            Place Order (Rs <?php echo number_format($total, 2); ?>)
                        </button>
                        <br><br>
                        <a href="cart.php" class="btn btn-primary" style="background-color: #6c757d;">
                            ← Back to Cart
                        </a>
                    </div>
                </form>
            </div>
            
        </div>
        
    </div>
</section>

<?php include('partials-front/footer.php'); ?>