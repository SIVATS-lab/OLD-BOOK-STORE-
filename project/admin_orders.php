<?php

include 'config.php';
session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:login.php');
   exit();
}

if(isset($_POST['update_order'])){
   $order_update_id = $_POST['order_id'];
   $update_payment = $_POST['update_payment'];
   mysqli_query($conn, "UPDATE `orders` SET payment_status = '$update_payment' WHERE id = '$order_update_id'") or die('query failed');
   $message[] = 'Payment status has been updated!';
}

if(isset($_GET['delete'])){
   $delete_id = $_GET['delete'];
   mysqli_query($conn, "DELETE FROM `orders` WHERE id = '$delete_id'") or die('query failed');
   header('location:admin_orders.php');
   exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Manage Orders</title>

   <!-- Font Awesome -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <style>
      body {
         font-family: 'Poppins', sans-serif;
         background: #f7f4fb;
         margin: 0;
         color: #333;
      }

      /* ===== Header (Matches Product & Dashboard) ===== */
      header {
         background: linear-gradient(135deg, #7b2cbf, #5a189a);
         padding: 18px 8%;
         display: flex;
         justify-content: space-between;
         align-items: center;
         color: #fff;
         box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
      }

      header h2 {
         font-size: 1.6rem;
         text-transform: uppercase;
         letter-spacing: 1px;
         font-weight: 600;
      }

      nav a {
         color: #fff;
         margin: 0 15px;
         text-decoration: none;
         font-weight: 500;
         transition: 0.3s;
      }

      nav a:hover {
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
         background: #e5d0ff;
      }

      /* ===== Title ===== */
      .title {
         text-align: center;
         font-size: 2rem;
         color: #5a189a;
         text-transform: uppercase;
         margin: 30px 0 20px;
         letter-spacing: 1px;
      }

      /* ===== Orders Table ===== */
      .orders {
         padding: 0 5% 60px;
      }

      table {
         width: 100%;
         border-collapse: collapse;
         background: #fff;
         box-shadow: 0 6px 20px rgba(123, 44, 191, 0.15);
         border-radius: 14px;
         overflow: hidden;
      }

      th, td {
         padding: 14px 16px;
         text-align: center;
         border-bottom: 1px solid #eee;
         font-size: 15px;
      }

      th {
         background: linear-gradient(135deg, #7b2cbf, #5a189a);
         color: #fff;
         text-transform: uppercase;
         letter-spacing: 0.5px;
         font-weight: 600;
      }

      tr:hover {
         background-color: #f2e9ff;
         transition: 0.3s;
      }

      select {
         padding: 6px 8px;
         border-radius: 6px;
         border: 1px solid #ccc;
         background: #faf9ff;
         font-size: 14px;
      }

      select:focus {
         border-color: #7b2cbf;
         outline: none;
         box-shadow: 0 0 4px rgba(123,44,191,0.3);
      }

      .option-btn, .delete-btn {
         display: inline-block;
         padding: 6px 12px;
         border-radius: 6px;
         font-size: 14px;
         text-decoration: none;
         margin: 2px;
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
         padding: 30px;
         background: #fff;
         border-radius: 12px;
         box-shadow: 0 4px 15px rgba(90, 24, 154, 0.1);
      }

      @media (max-width: 992px) {
         table {
            display: block;
            overflow-x: auto;
            white-space: nowrap;
         }
      }

      @media (max-width: 768px) {
         header {
            flex-direction: column;
            align-items: flex-start;
         }
         header h2 {
            margin-bottom: 10px;
         }
         nav {
            margin-top: 10px;
         }
      }
   </style>
</head>
<body>

<!-- ===== Header (Now Matches All Admin Pages) ===== -->
<header>
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

<!-- ===== Orders Section ===== -->
<section class="orders">
   <h1 class="title">Placed Orders</h1>

   <?php
   $select_orders = mysqli_query($conn, "SELECT * FROM `orders` ORDER BY id DESC") or die('query failed');
   if(mysqli_num_rows($select_orders) > 0){
   ?>
   <table>
      <thead>
         <tr>
            <th>ID</th>
            <th>User ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Number</th>
            <th>Address</th>
            <th>Products</th>
            <th>Total ($)</th>
            <th>Method</th>
            <th>Payment Status</th>
            <th>Placed On</th>
            <th>Actions</th>
         </tr>
      </thead>
      <tbody>
         <?php while($fetch_orders = mysqli_fetch_assoc($select_orders)){ ?>
         <tr>
            <td><?php echo $fetch_orders['id']; ?></td>
            <td><?php echo $fetch_orders['user_id']; ?></td>
            <td><?php echo $fetch_orders['name']; ?></td>
            <td><?php echo $fetch_orders['email']; ?></td>
            <td><?php echo $fetch_orders['number']; ?></td>
            <td><?php echo $fetch_orders['address']; ?></td>
            <td><?php echo $fetch_orders['total_products']; ?></td>
            <td>$<?php echo $fetch_orders['total_price']; ?>/-</td>
            <td><?php echo $fetch_orders['method']; ?></td>
            <td>
               <form action="" method="post">
                  <input type="hidden" name="order_id" value="<?php echo $fetch_orders['id']; ?>">
                  <select name="update_payment">
                     <option selected disabled><?php echo $fetch_orders['payment_status']; ?></option>
                     <option value="pending">Pending</option>
                     <option value="completed">Completed</option>
                  </select>
                  <input type="submit" name="update_order" value="Update" class="option-btn" style="margin-top:5px;">
               </form>
            </td>
            <td><?php echo $fetch_orders['placed_on']; ?></td>
            <td>
               <a href="admin_orders.php?delete=<?php echo $fetch_orders['id']; ?>" onclick="return confirm('Delete this order?');" class="delete-btn">Delete</a>
            </td>
         </tr>
         <?php } ?>
      </tbody>
   </table>
   <?php
   } else {
      echo '<p class="empty">No orders placed yet!</p>';
   }
   ?>
</section>

</body>
</html>
