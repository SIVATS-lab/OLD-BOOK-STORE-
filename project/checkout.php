<?php

include 'config.php';
session_start();

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login.php');
}

if(isset($_POST['order_btn'])){

   $name = mysqli_real_escape_string($conn, $_POST['name']);
   $number = $_POST['number'];
   $email = mysqli_real_escape_string($conn, $_POST['email']);
   $method = mysqli_real_escape_string($conn, $_POST['method']);
   $address = mysqli_real_escape_string($conn, $_POST['street'].', '. $_POST['city']);
   $placed_on = date('d-M-Y');

   $cart_total = 0;
   $cart_products = [];

   $cart_query = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id = '$user_id'") or die('query failed');
   if(mysqli_num_rows($cart_query) > 0){
      while($cart_item = mysqli_fetch_assoc($cart_query)){
         $cart_products[] = $cart_item['name'].' ('.$cart_item['quantity'].') ';
         $sub_total = ($cart_item['price'] * $cart_item['quantity']);
         $cart_total += $sub_total;
      }
   }

   $total_products = implode(', ', $cart_products);

   $order_query = mysqli_query($conn, "SELECT * FROM `orders` WHERE name = '$name' AND number = '$number' AND email = '$email' AND method = '$method' AND address = '$address' AND total_products = '$total_products' AND total_price = '$cart_total'") or die('query failed');

   if($cart_total == 0){
      $message[] = 'your cart is empty';
   } else {
      if(mysqli_num_rows($order_query) > 0){
         $message[] = 'order already placed!'; 
      } else {
         mysqli_query($conn, "INSERT INTO `orders`(user_id, name, number, email, method, address, total_products, total_price, placed_on) VALUES('$user_id', '$name', '$number', '$email', '$method', '$address', '$total_products', '$cart_total', '$placed_on')") or die('query failed');
         $message[] = 'order placed successfully!';
         mysqli_query($conn, "DELETE FROM `cart` WHERE user_id = '$user_id'") or die('query failed');
      }
   }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Checkout</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">

   <style>
   /* ===== Order Summary Enhancement ===== */
   .display-order {
   max-width: 950px;
   margin: 60px auto 40px;
   background: #ffffff;
   border-radius: 18px;
   box-shadow: 0 10px 30px rgba(91, 33, 182, 0.15);
   padding: 50px 60px;
   border-top: 6px solid #7b2cbf;
   text-align: center; /* centered content */
}

.display-order h3 {
   text-align: center;
   color: #5a189a;
   font-size: 2.2rem;
   font-weight: 800;
   margin-bottom: 30px;
   text-transform: uppercase;
   letter-spacing: 1px;
}

/* Each item styled like a mini card row */
.display-order p {
   display: flex;
   justify-content: space-between;
   align-items: center;
   background: #f9f6ff;
   color: #333;
   padding: 15px 18px;
   font-size: 1.15rem;
   border-radius: 8px;
   margin-bottom: 12px;
   border: 1px solid #e4d7ff;
   transition: 0.3s ease;
}

.display-order p:hover {
   background: #f3ebff;
   transform: scale(1.01);
}

.display-order p span {
   color: #5a189a;
   font-weight: 700;
   font-size: 1.1rem;
}

/* GRAND TOTAL */
.display-order .grand-total {
   margin-top: 40px;
   text-align: right;
   font-size: 1.8rem;
   font-weight: 800;
   color: #5a189a;
   border-top: 3px dashed #cbb2fe;
   padding-top: 20px;
   letter-spacing: 0.5px;
   text-transform: capitalize;
}

   /* ===== Checkout Form ===== */
   .checkout {
      max-width: 850px;
      margin: 40px auto 60px;
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 8px 25px rgba(91, 33, 182, 0.15);
      padding: 40px 50px;
      border-top: 6px solid #7b2cbf;
   }

   .checkout h3 {
      text-align: center;
      font-size: 1.8rem;
      color: #5a189a;
      font-weight: 700;
      margin-bottom: 25px;
      text-transform: uppercase;
   }

   .checkout .flex {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 25px;
   }

   .checkout .inputBox {
      display: flex;
      flex-direction: column;
   }

   .checkout span {
      font-size: 1rem;
      color: #444;
      margin-bottom: 8px;
      font-weight: 600;
   }

   .checkout input,
   .checkout select {
      padding: 14px 16px;
      border-radius: 8px;
      border: 1px solid #cdb4ff;
      background: #faf9ff;
      font-size: 1rem;
      transition: 0.3s ease;
   }

   .checkout input:focus,
   .checkout select:focus {
      outline: none;
      border-color: #7b2cbf;
      box-shadow: 0 0 6px rgba(123, 44, 191, 0.25);
      background: #fff;
   }

   .checkout .btn {
      display: block;
      width: 100%;
      margin-top: 35px;
      background: linear-gradient(135deg, #7b2cbf, #5a189a);
      color: #fff;
      padding: 15px;
      border: none;
      border-radius: 8px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: 0.3s;
      box-shadow: 0 5px 15px rgba(91, 33, 182, 0.25);
   }

   .checkout .btn:hover {
      transform: translateY(-3px);
      background: linear-gradient(135deg, #5a189a, #7b2cbf);
   }

   @media (max-width: 600px) {
      .display-order, .checkout {
         padding: 25px;
      }

      .display-order h3 {
         font-size: 1.5rem;
      }

      .display-order p {
         font-size: 1rem;
      }

      .display-order .grand-total {
         font-size: 1.3rem;
      }
   }
   </style>
</head>
<body>
   
<?php include 'header.php'; ?>

<div class="heading">
   <h3>Checkout</h3>
   <p><a href="home.php">Home</a> / Checkout</p>
</div>

<section class="display-order">
   <h3>Your Order Summary</h3>
   <?php  
      $grand_total = 0;
      $select_cart = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id = '$user_id'") or die('query failed');
      if(mysqli_num_rows($select_cart) > 0){
         while($fetch_cart = mysqli_fetch_assoc($select_cart)){
            $total_price = ($fetch_cart['price'] * $fetch_cart['quantity']);
            $grand_total += $total_price;
   ?>
   <p>
      <?php echo $fetch_cart['name']; ?> 
      <span><?php echo '$'.$fetch_cart['price'].' × '.$fetch_cart['quantity']; ?></span>
   </p>
   <?php
      }
   }else{
      echo '<p class="empty">Your cart is empty</p>';
   }
   ?>
   <div class="grand-total">Grand Total: <span>$<?php echo $grand_total; ?>/-</span></div>
</section>

<section class="checkout">
   <form action="" method="post">
      <h3>Place Your Order</h3>
      <div class="flex">
         <div class="inputBox">
            <span>Your Name:</span>
            <input type="text" name="name" required placeholder="Enter your name">
         </div>
         <div class="inputBox">
            <span>Your Number:</span>
            <input type="number" name="number" required placeholder="Enter your number">
         </div>
         <div class="inputBox">
            <span>Your Email:</span>
            <input type="email" name="email" required placeholder="Enter your email">
         </div>
         <div class="inputBox">
            <span>Payment Method:</span>
            <select name="method">
               <option value="cash on delivery">Cash on Delivery</option>
            </select>
         </div>
         <div class="inputBox">
            <span>Street Address:</span>
            <input type="text" name="street" required placeholder="e.g. street name">
         </div>
         <div class="inputBox">
            <span>City:</span>
            <input type="text" name="city" required placeholder="e.g. Konni">
         </div>
         <div class="inputBox">
            <span>State:</span>
            <input type="text" name="state" required placeholder="e.g. Kerala">
         </div>
      </div>
      <input type="submit" value="Order Now" class="btn" name="order_btn">
   </form>
</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>
