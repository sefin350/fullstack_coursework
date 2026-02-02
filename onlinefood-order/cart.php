<?php 
include('config/constants.php');

if(!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

if(isset($_GET['remove'])) {
    $remove_id = $_GET['remove'];
    foreach($_SESSION['cart'] as $key => $item) {
        if($item['id'] == $remove_id) {
            unset($_SESSION['cart'][$key]);
            $_SESSION['cart'] = array_values($_SESSION['cart']);
            break;
        }
    }
    header('Location: cart.php');
    exit;
}

if(isset($_POST['update_cart'])) {
    foreach($_POST['quantity'] as $item_id => $quantity) {
        foreach($_SESSION['cart'] as $key => $item) {
            if($item['id'] == $item_id) {
                $_SESSION['cart'][$key]['quantity'] = (int)$quantity;
                break;
            }
        }
    }
    header('Location: cart.php');
    exit;
}

if(isset($_GET['clear'])) {
    $_SESSION['cart'] = array();
    header('Location: cart.php');
    exit;
}

$total = 0;
foreach($_SESSION['cart'] as $item) {
    $total += $item['price'] * $item['quantity'];
}

include('partials-front/menu.php'); 
?>

<section class="food-search text-center">
    <div class="container">
        <h2 class="text-white">Shopping Cart</h2>
    </div>
</section>

<section class="food-menu">
    <div class="container">
        
        <?php if(count($_SESSION['cart']) > 0): ?>
            
            <form method="POST" action="">
                <table style="width: 100%; border-collapse: collapse; margin: 20px 0; background: white;">
                    <thead>
                        <tr style="background-color: #ff6b81; color: white;">
                            <th style="padding: 15px;">Image</th>
                            <th style="padding: 15px;">Food Item</th>
                            <th style="padding: 15px;">Price</th>
                            <th style="padding: 15px;">Quantity</th>
                            <th style="padding: 15px;">Subtotal</th>
                            <th style="padding: 15px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($_SESSION['cart'] as $item): ?>
                        <tr>
                            <td style="padding: 15px;">
                                <?php if($item['image'] != ""): ?>
                                    <img src="<?php echo SITEURL; ?>images/food/<?php echo $item['image']; ?>" 
                                         style="width: 80px; height: 80px; border-radius: 5px;">
                                <?php endif; ?>
                            </td>
                            <td style="padding: 15px;"><strong><?php echo $item['title']; ?></strong></td>
                            <td style="padding: 15px;">Rs <?php echo $item['price']; ?></td>
                            <td style="padding: 15px;">
                                <input type="number" name="quantity[<?php echo $item['id']; ?>]" 
                                       value="<?php echo $item['quantity']; ?>" min="1" max="10"
                                       style="width: 60px; padding: 5px;">
                            </td>
                            <td style="padding: 15px;"><strong>Rs <?php echo $item['price'] * $item['quantity']; ?></strong></td>
                            <td style="padding: 15px;">
                                <a href="cart.php?remove=<?php echo $item['id']; ?>" 
                                   class="btn btn-primary" 
                                   onclick="return confirm('Remove?');">Remove</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td colspan="4" style="padding: 15px; text-align: right;"><strong>Total:</strong></td>
                            <td style="padding: 15px;"><strong style="color: #ff6b81;">Rs <?php echo $total; ?></strong></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
                
                <div style="text-align: center; margin: 30px;">
                    <button type="submit" name="update_cart" class="btn btn-primary">Update Cart</button>
                    <a href="checkout.php" class="btn btn-primary">Checkout</a>
                    <a href="cart.php?clear=1" class="btn btn-primary" onclick="return confirm('Clear cart?');">Clear Cart</a>
                    <a href="foods.php" class="btn btn-primary">Continue Shopping</a>
                </div>
            </form>
            
        <?php else: ?>
            
            <div style="text-align: center; padding: 50px; background: white; margin: 40px;">
                <h2>🛒 Your cart is empty!</h2>
                <br>
                <a href="foods.php" class="btn btn-primary">Browse Foods</a>
            </div>
            
        <?php endif; ?>
        
    </div>
</section>

<?php include('partials-front/footer.php'); ?>
