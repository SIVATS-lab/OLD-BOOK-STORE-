<?php

include 'config.php';
session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:login.php');
   exit();
}

if(isset($_GET['delete'])){
   $delete_id = $_GET['delete'];
   mysqli_query($conn, "DELETE FROM `message` WHERE id = '$delete_id'") or die('query failed');
   header('location:admin_contacts.php');
   exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Admin - Messages</title>

   <!-- Font Awesome -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <style>
      body {
         font-family: 'Poppins', sans-serif;
         background: #f7f4fb;
         margin: 0;
         color: #333;
      }

      /* ===== Header (Matches Other Admin Pages) ===== */
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

      /* ===== Page Title ===== */
      .title {
         text-align: center;
         font-size: 2rem;
         color: #5a189a;
         text-transform: uppercase;
         margin: 30px 0 20px;
         letter-spacing: 1px;
      }

      /* ===== Messages Section ===== */
      .messages {
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

      td span {
         color: #5a189a;
         font-weight: 500;
      }

      .delete-btn {
         display: inline-block;
         padding: 6px 12px;
         border-radius: 6px;
         font-size: 14px;
         text-decoration: none;
         background: #dc3545;
         color: #fff;
         font-weight: 600;
         transition: 0.3s;
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

<!-- ===== Admin Header ===== -->
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

<!-- ===== Messages Table ===== -->
<section class="messages">
   <h1 class="title">User Messages</h1>

   <?php
      $select_message = mysqli_query($conn, "SELECT * FROM `message` ORDER BY id DESC") or die('query failed');
      if(mysqli_num_rows($select_message) > 0){
   ?>
   <table>
      <thead>
         <tr>
            <th>ID</th>
            <th>User ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Number</th>
            <th>Message</th>
            <th>Action</th>
         </tr>
      </thead>
      <tbody>
         <?php while($fetch_message = mysqli_fetch_assoc($select_message)){ ?>
         <tr>
            <td><?php echo $fetch_message['id']; ?></td>
            <td><?php echo $fetch_message['user_id']; ?></td>
            <td><?php echo $fetch_message['name']; ?></td>
            <td><?php echo $fetch_message['email']; ?></td>
            <td><?php echo $fetch_message['number']; ?></td>
            <td><?php echo $fetch_message['message']; ?></td>
            <td>
               <a href="admin_contacts.php?delete=<?php echo $fetch_message['id']; ?>" onclick="return confirm('Delete this message?');" class="delete-btn">Delete</a>
            </td>
         </tr>
         <?php } ?>
      </tbody>
   </table>
   <?php
      } else {
         echo '<p class="empty">You have no messages!</p>';
      }
   ?>
</section>

</body>
</html>
