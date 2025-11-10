<?php
if(isset($message)){
   foreach($message as $message){
      echo '
      <div class="message">
         <span>'.$message.'</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
}
?>

<header class="header">
   
   <div class="header-1">
      <div class="flex">
         <div class="share">
            <a href="https://www.facebook.com/share/1VumJeXpEE/" target="_blank" rel="noopener noreferrer">
         <i class="fab fa-facebook"></i></a>
            <a href="#" class="fab fa-twitter"></a>
           <a href="https://www.instagram.com/_hey_siv?igsh=NGtxZDM4N3lnc2Np" target="_blank" rel="noopener noreferrer">
         <i class="fab fa-instagram"></i></a>
            <a href="#" class="fab fa-linkedin"></a>
         </div>
         <p> new <a href="login.php">login</a> | <a href="register.php">register</a> </p>
      </div>
   </div>

   <div class="header-2">
      <div class="flex">
         <a href="home.php" class="logo">𝙏𝙀𝙓𝙏𝙐𝙍𝘼</a>

         <nav class="navbar">
            <a href="home.php">𝙃𝙊𝙈𝙀</a>
            <a href="about.php">𝘼𝘽𝙊𝙐𝙏</a>
            <a href="shop.php">𝙎𝙃𝙊𝙋</a>
            <a href="contact.php">𝘾𝙊𝙉𝙏𝘼𝘾𝙏</a>
            <a href="orders.php">𝙊𝙍𝘿𝙀𝙍𝙎</a>
            <a href="cart.php"> 𝘾𝘼𝙍𝙏</a>
            <a href="sell_book.php"> 𝙎𝙀𝙇𝙇 𝘽𝙊𝙊𝙆𝙎</a>
         </nav>

         <div class="icons">
            <div id="menu-btn" class="fas fa-bars"></div>
            <a href="search_page.php" class="fas fa-search"></a>
            <div id="user-btn" class="fas fa-user"></div>
            <?php
               $select_cart_number = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id = '$user_id'") or die('query failed');
               $cart_rows_number = mysqli_num_rows($select_cart_number); 
            ?>
            <a href="cart.php"> <i class="fas fa-shopping-cart"></i> <span>(<?php echo $cart_rows_number; ?>)</span> </a>
         </div>

         <div class="user-box">
            <p>username : <span><?php echo $_SESSION['user_name']; ?></span></p>
            <p>email : <span><?php echo $_SESSION['user_email']; ?></span></p>
            <a href="logout.php" class="delete-btn">logout</a>
         </div>
      </div>
   </div>

</header>