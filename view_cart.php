<?php
session_start();
if (!isset($_SESSION['cart']) || count($_SESSION['cart']) == 0) {
    echo "<h2 style='text-align:center;'>🛒 Your cart is empty!</h2>";
    echo "<p style='text-align:center;'><a href='meal.php'>🍴 Back to Menu</a></p>";
    exit;
}

// Update quantities
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    foreach ($_POST['qty'] as $meal_id => $qty) {
        if ($qty <= 0) {
            unset($_SESSION['cart'][$meal_id]); // remove item
        } else {
            $_SESSION['cart'][$meal_id]['qty'] = $qty;
        }
    }
    header("Location: view_cart.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Cart</title>
  <style>
    body { font-family: Arial, sans-serif; background: #fdf2e9; }
    .cart-container { width: 80%; margin: 30px auto; background: #fff; padding: 20px; border-radius: 10px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 12px; border-bottom: 1px solid #ddd; text-align: center; }
    th { background: #d35400; color: #fff; }
    .btn { background: #d35400; color: #fff; padding: 8px 12px; border-radius: 6px; text-decoration: none; }
    .btn:hover { background: #a84300; }
    .checkout-btn { margin-top: 20px; display: inline-block; padding: 10px 18px; background: #27ae60; color: #fff; border-radius: 8px; text-decoration: none; }
    .checkout-btn:hover { background: #1e8449; }
    .back-btn { margin-top: 20px; display: inline-block; padding: 10px 18px; background: #d35400; color: #fff; border-radius: 8px; text-decoration: none; }
    .back-btn:hover { background: #a84300; }
  </style>
</head>
<body>
  <div class="cart-container">
    <h1>🛒 My Cart</h1>
    <form method="post" action="view_cart.php">
      <table>
        <tr>
          <th>Meal</th>
          <th>Price</th>
          <th>Quantity</th>
          <th>Total</th>
        </tr>
        <?php 
        $grand_total = 0;
        foreach ($_SESSION['cart'] as $meal_id => $item): 
          $total = $item['price'] * $item['qty'];
          $grand_total += $total;
        ?>
        <tr>
          <td><?= htmlspecialchars($item['name']) ?></td>
          <td>৳ <?= number_format($item['price'],2) ?></td>
          <td>
            <input type="number" name="qty[<?= $meal_id ?>]" value="<?= $item['qty'] ?>" min="0">
          </td>
          <td>৳ <?= number_format($total,2) ?></td>
        </tr>
        <?php endforeach; ?>
      </table>
      <h3 style="text-align:right; margin-top:15px;">Grand Total: ৳ <?= number_format($grand_total,2) ?></h3>
      <button type="submit" class="btn">Update Cart</button>
    </form>
    <br>
    <a href="checkout.php" class="checkout-btn">Proceed to Checkout</a>
    <a href="meal.php" class="back-btn">🍴 Back to Menu</a>
  </div>
</body>
</html>
