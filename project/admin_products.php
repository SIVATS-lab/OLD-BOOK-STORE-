<?php
include 'config.php';
session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:login.php');
   exit();
}

// Add Product
if(isset($_POST['add_product'])){

   $name = mysqli_real_escape_string($conn, $_POST['name']);
   $price = $_POST['price'];
   $image = $_FILES['image']['name'];
   $image_size = $_FILES['image']['size'];
   $image_tmp_name = $_FILES['image']['tmp_name'];
   $image_folder = 'uploaded_img/'.$image;

   $select_product_name = mysqli_query($conn, "SELECT name FROM `products` WHERE name = '$name'") or die('query failed');

   if(mysqli_num_rows($select_product_name) > 0){
      $message[] = 'Product name already exists!';
   }else{
      $add_product_query = mysqli_query($conn, "INSERT INTO `products`(name, price, image) VALUES('$name', '$price', '$image')") or die('query failed');

      if($add_product_query){
         if($image_size > 2000000){
            $message[] = 'Image size is too large!';
         }else{
            move_uploaded_file($image_tmp_name, $image_folder);
            $message[] = 'Product added successfully!';
         }
      }else{
         $message[] = 'Product could not be added!';
      }
   }
}

// Delete Product
if(isset($_GET['delete'])){
   $delete_id = $_GET['delete'];
   $delete_image_query = mysqli_query($conn, "SELECT image FROM `products` WHERE id = '$delete_id'") or die('query failed');
   $fetch_delete_image = mysqli_fetch_assoc($delete_image_query);
   unlink('uploaded_img/'.$fetch_delete_image['image']);
   mysqli_query($conn, "DELETE FROM `products` WHERE id = '$delete_id'") or die('query failed');
   header('location:admin_products.php');
   exit();
}

// Update Product
if(isset($_POST['update_product'])){

   $update_p_id = $_POST['update_p_id'];
   $update_name = $_POST['update_name'];
   $update_price = $_POST['update_price'];

   mysqli_query($conn, "UPDATE `products` SET name = '$update_name', price = '$update_price' WHERE id = '$update_p_id'") or die('query failed');

   $update_image = $_FILES['update_image']['name'];
   $update_image_tmp_name = $_FILES['update_image']['tmp_name'];
   $update_image_size = $_FILES['update_image']['size'];
   $update_folder = 'uploaded_img/'.$update_image;
   $update_old_image = $_POST['update_old_image'];

   if(!empty($update_image)){
      if($update_image_size > 2000000){
         $message[] = 'Image file size is too large!';
      }else{
         mysqli_query($conn, "UPDATE `products` SET image = '$update_image' WHERE id = '$update_p_id'") or die('query failed');
         move_uploaded_file($update_image_tmp_name, $update_folder);
         unlink('uploaded_img/'.$update_old_image);
      }
   }

   header('location:admin_products.php');
   exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Admin - Manage Products</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <style>
      body {
         font-family: 'Poppins', sans-serif;
         background: #f8f5ff;
         margin: 0;
         color: #333;
      }

      /* ===== ADMIN NAVBAR ===== */
      .admin-header {
         background: linear-gradient(135deg, #7b2cbf, #5a189a);
         color: white;
         padding: 20px 8%;
         display: flex;
         justify-content: space-between;
         align-items: center;
         box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
      }

      .admin-header h2 {
         font-size: 1.5rem;
         text-transform: uppercase;
         letter-spacing: 1px;
      }

      .admin-header nav a {
         color: #fff;
         text-decoration: none;
         margin: 0 15px;
         font-weight: 500;
         transition: 0.3s;
      }

      .admin-header nav a:hover {
         color: #ffd6ff;
      }

      .logout-btn {
         background: #fff;
         color: #5a189a;
         padding: 8px 16px;
         border-radius: 8px;
         font-weight: 600;
         text-decoration: none;
         transition: 0.3s;
      }

      .logout-btn:hover {
         background: #eaddff;
      }

      .title {
         text-align: center;
         font-size: 2rem;
         color: #5a189a;
         margin: 30px 0;
         font-weight: 700;
      }

      /* ===== Add Product Form ===== */
      .add-products {
         max-width: 600px;
         margin: 20px auto 50px;
         background: #fff;
         border-radius: 14px;
         box-shadow: 0 6px 20px rgba(123, 44, 191, 0.15);
         padding: 30px 40px;
         border: 1px solid #e2d4ff;
      }

      .add-products h3 {
         text-align: center;
         color: #5a189a;
         margin-bottom: 20px;
         font-size: 1.4rem;
      }

      .add-products .box {
         width: 100%;
         padding: 12px 14px;
         margin: 10px 0;
         border-radius: 8px;
         border: 1px solid #ccc;
         background: #faf9ff;
         transition: 0.3s;
         font-size: 16px;
      }

      .add-products .box:focus {
         border-color: #7b2cbf;
         box-shadow: 0 0 5px rgba(123, 44, 191, 0.3);
         background: #fff;
      }

      .btn {
         display: block;
         width: 100%;
         background: linear-gradient(135deg, #7b2cbf, #5a189a);
         color: #fff;
         border: none;
         padding: 12px 20px;
         font-size: 16px;
         font-weight: 600;
         border-radius: 8px;
         cursor: pointer;
         transition: 0.3s;
         margin-top: 10px;
         box-shadow: 0 4px 12px rgba(123, 44, 191, 0.25);
      }

      .btn:hover {
         background: linear-gradient(135deg, #5a189a, #7b2cbf);
         transform: translateY(-2px);
      }

      /* ===== Product Display ===== */
      .show-products {
         padding: 0 8%;
         margin-bottom: 60px;
      }

      .box-container {
         display: grid;
         grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
         gap: 25px;
      }

      .show-products .box {
         background: #fff;
         border-radius: 14px;
         box-shadow: 0 6px 15px rgba(90, 24, 154, 0.15);
         text-align: center;
         padding: 20px;
         transition: 0.3s;
         border: 1px solid #ede1ff;
      }

      .show-products .box:hover {
         transform: translateY(-5px);
         box-shadow: 0 8px 20px rgba(123, 44, 191, 0.25);
      }

      .show-products img {
         height: 180px;
         width: auto;
         margin-bottom: 10px;
         border-radius: 10px;
         object-fit: contain;
      }

      .show-products .name {
         font-size: 1.2rem;
         color: #333;
         font-weight: 600;
         margin: 5px 0;
      }

      .show-products .price {
         color: #5a189a;
         font-weight: 600;
         margin-bottom: 10px;
      }

      .option-btn, .delete-btn {
         display: inline-block;
         padding: 8px 14px;
         border-radius: 8px;
         font-size: 14px;
         text-decoration: none;
         margin: 5px;
         font-weight: 600;
         transition: 0.3s;
      }

      .option-btn {
         background: #7b2cbf;
         color: #fff;
      }

      .option-btn:hover {
         background: #5a189a;
      }

      .delete-btn {
         background: #dc3545;
         color: #fff;
      }

      .delete-btn:hover {
         background: #b02a37;
      }

      .empty {
         text-align: center;
         font-size: 1.2rem;
         color: #777;
         padding: 20px;
      }

      /* ===== Edit Form ===== */
      .edit-product-form {
         display: flex;
         justify-content: center;
         align-items: center;
         min-height: 100vh;
         background: rgba(0,0,0,0.4);
         position: fixed;
         top: 0;
         left: 0;
         width: 100%;
         z-index: 100;
      }

      .edit-product-form form {
         background: #fff;
         padding: 30px;
         border-radius: 14px;
         box-shadow: 0 6px 20px rgba(123,44,191,0.25);
         text-align: center;
         width: 90%;
         max-width: 400px;
      }

      .edit-product-form img {
         height: 150px;
         margin-bottom: 10px;
         border-radius: 10px;
      }

      #close-update {
         background: #ccc;
         color: #333;
         margin-top: 10px;
      }

      @media (max-width: 768px) {
         .admin-header {
            flex-direction: column;
            align-items: flex-start;
         }

         .admin-header nav {
            margin-top: 10px;
         }
      }
   </style>
</head>

<body>

<header class="admin-header">
   <h2>Admin Panel</h2>
   <nav>
      <a href="admin_page.php">Dashboard</a>
      <a href="admin_products.php">Products</a>
      <a href="admin_orders.php">Orders</a>
      <a href="admin_users.php">Users</a>
      <a href="admin_contacts.php">Messages</a>
   </nav>
   <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
</header>

<h1 class="title">Manage Products</h1>

<section class="add-products">
   <form action="" method="post" enctype="multipart/form-data">
      <h3>Add New Product</h3>
      <input type="text" name="name" class="box" placeholder="Enter product name" required>
      <input type="number" min="0" name="price" class="box" placeholder="Enter product price" required>
      <input type="file" name="image" accept="image/jpg, image/jpeg, image/png" class="box" required>
      <input type="submit" value="Add Product" name="add_product" class="btn">
   </form>
</section>

<section class="show-products">
   <div class="box-container">
      <?php
         $select_products = mysqli_query($conn, "SELECT * FROM `products`") or die('query failed');
         if(mysqli_num_rows($select_products) > 0){
            while($fetch_products = mysqli_fetch_assoc($select_products)){
      ?>
      <div class="box">
         <img src="uploaded_img/<?php echo $fetch_products['image']; ?>" alt="">
         <div class="name"><?php echo $fetch_products['name']; ?></div>
         <div class="price">$<?php echo $fetch_products['price']; ?>/-</div>
         <a href="admin_products.php?update=<?php echo $fetch_products['id']; ?>" class="option-btn">Update</a>
         <a href="admin_products.php?delete=<?php echo $fetch_products['id']; ?>" class="delete-btn" onclick="return confirm('Delete this product?');">Delete</a>
      </div>
      <?php
         }
      }else{
         echo '<p class="empty">No products added yet!</p>';
      }
      ?>
   </div>
</section>

<section class="edit-product-form">
   <?php
      if(isset($_GET['update'])){
         $update_id = $_GET['update'];
         $update_query = mysqli_query($conn, "SELECT * FROM `products` WHERE id = '$update_id'") or die('query failed');
         if(mysqli_num_rows($update_query) > 0){
            while($fetch_update = mysqli_fetch_assoc($update_query)){
   ?>
   <form action="" method="post" enctype="multipart/form-data">
      <input type="hidden" name="update_p_id" value="<?php echo $fetch_update['id']; ?>">
      <input type="hidden" name="update_old_image" value="<?php echo $fetch_update['image']; ?>">
      <img src="uploaded_img/<?php echo $fetch_update['image']; ?>" alt="">
      <input type="text" name="update_name" value="<?php echo $fetch_update['name']; ?>" class="box" required placeholder="Enter product name">
      <input type="number" name="update_price" value="<?php echo $fetch_update['price']; ?>" min="0" class="box" required placeholder="Enter product price">
      <input type="file" class="box" name="update_image" accept="image/jpg, image/jpeg, image/png">
      <input type="submit" value="Update" name="update_product" class="btn">
      <input type="reset" value="Cancel" id="close-update" class="btn" onclick="window.location='admin_products.php'">
   </form>
   <?php
         }
      }
      }else{
         echo '<script>document.querySelector(".edit-product-form").style.display = "none";</script>';
      }
   ?>
</section>

</body>
</html>
