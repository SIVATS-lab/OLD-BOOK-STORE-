<?php
include 'config.php';
session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:login.php');
   exit();
}

// Approve book request
if(isset($_GET['approve'])){
   $approve_id = intval($_GET['approve']);

   $select_book = mysqli_query($conn, "SELECT * FROM `sell_books` WHERE id = '$approve_id'") or die('query failed');
   if(mysqli_num_rows($select_book) > 0){
      $book = mysqli_fetch_assoc($select_book);

      // Move approved book to products
      $title = mysqli_real_escape_string($conn, $book['book_title']);
      $price = mysqli_real_escape_string($conn, $book['price']);
      $image = mysqli_real_escape_string($conn, $book['image']);

      mysqli_query($conn, "INSERT INTO `products` (name, price, image) VALUES ('$title', '$price', '$image')") or die('query failed');
      mysqli_query($conn, "UPDATE `sell_books` SET status='approved' WHERE id='$approve_id'") or die('query failed');
   }

   header('location:admin_sell_books.php');
   exit();
}

// Reject book request
if(isset($_GET['reject'])){
   $reject_id = intval($_GET['reject']);
   mysqli_query($conn, "UPDATE `sell_books` SET status='rejected' WHERE id='$reject_id'") or die('query failed');
   header('location:admin_sell_books.php');
   exit();
}

// Delete rejected book
if(isset($_GET['delete'])){
   $delete_id = intval($_GET['delete']);
   mysqli_query($conn, "DELETE FROM `sell_books` WHERE id='$delete_id'") or die('query failed');
   header('location:admin_sell_books.php');
   exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin - Sell Book Requests</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
:root{
   --purple-dark:#5a189a;
   --purple-mid:#7b2cbf;
   --purple-light:#e0c3fc;
   --bg:#f8f6ff;
}

body{
   font-family:'Poppins',sans-serif;
   margin:0;
   background:var(--bg);
   color:#333;
}

/* ===== Header Same as Admin Panel ===== */
.admin-header{
   background:linear-gradient(135deg,var(--purple-mid),var(--purple-dark));
   color:white;
   padding:18px 8%;
   display:flex;
   justify-content:space-between;
   align-items:center;
   box-shadow:0 4px 12px rgba(0,0,0,0.15);
}

.admin-header h2{
   font-size:1.6rem;
   font-weight:700;
   text-transform:uppercase;
}

.admin-header nav a{
   color:#fff;
   text-decoration:none;
   margin:0 12px;
   font-weight:500;
   transition:.3s;
}

.admin-header nav a:hover{
   color:#d9b3ff;
}

.logout-btn{
   background:#fff;
   color:var(--purple-dark);
   padding:8px 14px;
   border-radius:8px;
   font-weight:600;
   text-decoration:none;
   transition:.3s;
}

.logout-btn:hover{
   background:#eaddff;
}

/* ===== Table ===== */
section{
   padding:40px 6%;
}

h1.title{
   text-align:center;
   color:var(--purple-dark);
   margin-bottom:25px;
   font-size:2rem;
   text-transform:uppercase;
   letter-spacing:1px;
}

table{
   width:100%;
   border-collapse:collapse;
   background:#fff;
   border-radius:14px;
   overflow:hidden;
   box-shadow:0 6px 20px rgba(123,44,191,0.15);
}

th,td{
   padding:14px;
   text-align:center;
   border-bottom:1px solid #eee;
}

th{
   background:linear-gradient(135deg,var(--purple-mid),var(--purple-dark));
   color:#fff;
   text-transform:uppercase;
   font-size:.9rem;
   letter-spacing:.5px;
}

td img{
   width:80px;
   height:100px;
   object-fit:cover;
   border-radius:6px;
}

.status{
   font-weight:600;
   text-transform:capitalize;
}

.status.pending{ color:orange; }
.status.approved{ color:green; }
.status.rejected{ color:red; }

.action-btn{
   padding:6px 10px;
   border-radius:6px;
   color:#fff;
   text-decoration:none;
   font-size:.9rem;
   font-weight:600;
   margin:2px;
   display:inline-block;
}

.approve{ background:#28a745; }
.reject{ background:#dc3545; }
.delete{ background:#6c757d; }

.action-btn:hover{ opacity:.8; }

.empty{
   background:#fff;
   padding:20px;
   text-align:center;
   border-radius:10px;
   box-shadow:0 4px 12px rgba(90,24,154,0.1);
   font-weight:500;
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
      <a href="admin_sell_books.php">Sell Requests</a>
   </nav>
   <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
</header>

<section>
   <h1 class="title">Sell Book Requests</h1>

   <?php
   $select_books = mysqli_query($conn, "SELECT * FROM `sell_books` ORDER BY id DESC") or die('query failed');

   if(mysqli_num_rows($select_books) > 0){
      echo '<table>
               <thead>
                  <tr>
                     <th>ID</th>
                     <th>User ID</th>
                     <th>Book Title</th>
                     <th>Author</th>
                     <th>Price</th>
                     <th>Description</th>
                     <th>Image</th>
                     <th>Status</th>
                     <th>Actions</th>
                  </tr>
               </thead>
               <tbody>';

      while($book = mysqli_fetch_assoc($select_books)){
         echo '<tr>
            <td>'.$book['id'].'</td>
            <td>'.$book['user_id'].'</td>
            <td>'.$book['book_title'].'</td>
            <td>'.$book['author'].'</td>
            <td>$'.$book['price'].'</td>
            <td style="max-width:250px;">'.htmlspecialchars($book['description']).'</td>
            <td><img src="uploaded_sell_books/'.$book['image'].'" alt=""></td>
            <td class="status '.$book['status'].'">'.$book['status'].'</td>
            <td>';
         
         if($book['status'] == 'pending'){
            echo '<a href="admin_sell_books.php?approve='.$book['id'].'" class="action-btn approve">Approve</a>
                  <a href="admin_sell_books.php?reject='.$book['id'].'" class="action-btn reject">Reject</a>';
         } elseif($book['status'] == 'rejected'){
            echo '<a href="admin_sell_books.php?delete='.$book['id'].'" class="action-btn delete" onclick="return confirm(\'Delete this request?\');">Delete</a>';
         } else {
            echo '<span style="color:green;font-weight:600;">Approved</span>';
         }

         echo '</td></tr>';
      }

      echo '</tbody></table>';
   } else {
      echo '<p class="empty">No book requests found.</p>';
   }
   ?>
</section>

</body>
</html>
