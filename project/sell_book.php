<?php
include 'config.php';
session_start();

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login.php');
   exit();
}

if(isset($_POST['sell_book'])){

   $book_title = mysqli_real_escape_string($conn, $_POST['book_title']);
   $author = mysqli_real_escape_string($conn, $_POST['author']);
   $price = $_POST['price'];
   $description = mysqli_real_escape_string($conn, $_POST['description']);

   $image = $_FILES['image']['name'];
   $image_tmp_name = $_FILES['image']['tmp_name'];
   $image_folder = 'uploaded_img/'.$image;

   if(!empty($book_title) && !empty($author) && !empty($price) && !empty($image)){
mysqli_query($conn, "INSERT INTO `sell_books`
   (user_id, book_title, author, price, description, image, status)
   VALUES('$user_id', '$book_title', '$author', '$price', '$description', '$image', 'pending')")
   or die('query failed');
      move_uploaded_file($image_tmp_name, $image_folder);
      $message[] = 'Book listed for sale successfully!';
   } else {
      $message[] = 'Please fill in all required fields!';
   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Sell Your Book</title>

   <!-- Font Awesome CDN -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- Your main site CSS -->
   <link rel="stylesheet" href="css/style.css">
</head>
<body>
   
<?php include 'header.php'; ?>

<div class="heading">
   <h3>Sell Your Book</h3>
   <p><a href="home.php">Home</a> / Sell</p>
</div>

<section class="contact"> <!-- reused form styling from contact page -->
   <form action="" method="post" enctype="multipart/form-data">
      <h3>List a Book for Sale</h3>
      <input type="text" name="book_title" required placeholder="Enter book title" class="box">
      <input type="text" name="author" required placeholder="Enter author name" class="box">
      <input type="number" name="price" required placeholder="Enter price ($)" class="box" min="0">
      <textarea name="description" class="box" placeholder="Enter description (optional)" cols="30" rows="6"></textarea>
      <input type="file" name="image" accept="image/jpg, image/jpeg, image/png" class="box" required>
      <input type="submit" value="Sell Now" name="sell_book" class="btn">
   </form>
</section>

<?php include 'footer.php'; ?>

<!-- main JS -->
<script src="js/script.js"></script>

</body>
</html>
