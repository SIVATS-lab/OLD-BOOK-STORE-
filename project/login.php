<?php

include 'config.php';
session_start();

if(isset($_POST['submit'])){

   $email = mysqli_real_escape_string($conn, $_POST['email']);
   $pass = mysqli_real_escape_string($conn, md5($_POST['password']));

   $select_users = mysqli_query($conn, "SELECT * FROM `users` WHERE email = '$email' AND password = '$pass'") or die('query failed');

   if(mysqli_num_rows($select_users) > 0){

      $row = mysqli_fetch_assoc($select_users);

      if($row['user_type'] == 'admin'){

         $_SESSION['admin_name'] = $row['name'];
         $_SESSION['admin_email'] = $row['email'];
         $_SESSION['admin_id'] = $row['id'];
         header('location:admin_page.php');

      }elseif($row['user_type'] == 'user'){

         $_SESSION['user_name'] = $row['name'];
         $_SESSION['user_email'] = $row['email'];
         $_SESSION['user_id'] = $row['id'];
         header('location:home.php');
      }

   }else{
      $message[] = 'Incorrect email or password!';
   }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Login</title>

   <!-- font awesome cdn -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <style>
      /* ===== General Page ===== */
      * {
         box-sizing: border-box;
         font-family: 'Poppins', sans-serif;
      }

      body {
         background: linear-gradient(135deg, #7b2cbf, #5a189a);
         display: flex;
         justify-content: center;
         align-items: center;
         height: 100vh;
         margin: 0;
      }

      /* ===== Login Box ===== */
      .form-container {
         background: #fff;
         border-radius: 15px;
         padding: 40px 45px;
         width: 100%;
         max-width: 420px;
         box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
         animation: fadeIn 0.6s ease;
      }

      .form-container h3 {
         color: #5a189a;
         text-align: center;
         margin-bottom: 25px;
         text-transform: uppercase;
         font-weight: 600;
         letter-spacing: 1px;
      }

      /* ===== Inputs ===== */
      .form-container .box {
         width: 100%;
         padding: 12px 14px;
         margin: 12px 0;
         border-radius: 10px;
         border: 1px solid #ccc;
         font-size: 16px;
         transition: 0.3s;
      }

      .form-container .box:focus {
         border-color: #7b2cbf;
         box-shadow: 0 0 8px rgba(123, 44, 191, 0.4);
      }

      /* ===== Show/Hide password icon ===== */
      .password-field {
         position: relative;
      }

      .toggle-password {
         position: absolute;
         top: 50%;
         right: 14px;
         transform: translateY(-50%);
         cursor: pointer;
         color: #888;
      }

      /* ===== Button ===== */
      .form-container .btn {
         background: linear-gradient(135deg, #7b2cbf, #5a189a);
         color: #fff;
         border: none;
         border-radius: 10px;
         padding: 12px 20px;
         font-size: 17px;
         font-weight: 600;
         cursor: pointer;
         width: 100%;
         margin-top: 10px;
         transition: 0.3s;
         box-shadow: 0 5px 15px rgba(123, 44, 191, 0.3);
      }

      .form-container .btn:hover {
         background: linear-gradient(135deg, #5a189a, #7b2cbf);
         transform: translateY(-2px);
      }

      /* ===== Bottom Text ===== */
      .form-container p {
         text-align: center;
         color: #333;
         margin-top: 20px;
         font-size: 15px;
      }

      .form-container a {
         color: #7b2cbf;
         text-decoration: none;
         font-weight: 600;
      }

      .form-container a:hover {
         text-decoration: underline;
      }

      /* ===== Message (Error/Success) ===== */
      .message {
         position: fixed;
         top: 20px;
         right: 20px;
         background: linear-gradient(135deg, #ff4d4d, #d62828);
         color: #fff;
         padding: 12px 20px;
         border-radius: 8px;
         font-size: 16px;
         box-shadow: 0 4px 10px rgba(0,0,0,0.2);
         animation: fadeInOut 3.5s ease-in-out forwards;
         z-index: 999;
      }

      @keyframes fadeIn {
         from {opacity: 0; transform: scale(0.9);}
         to {opacity: 1; transform: scale(1);}
      }

      @keyframes fadeInOut {
         0% {opacity: 0; transform: translateY(-10px);}
         10%, 90% {opacity: 1; transform: translateY(0);}
         100% {opacity: 0; transform: translateY(-10px);}
      }

      @media (max-width: 480px) {
         .form-container {
            margin: 20px;
            padding: 30px;
         }
      }
   </style>
</head>
<body>

<?php
if(isset($message)){
   foreach($message as $msg){
      echo '
      <div class="message">
         <span>'.$msg.'</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>';
   }
}
?>

<div class="form-container">
   <form action="" method="post">
      <h3>Login Now</h3>
      <input type="email" name="email" placeholder="Enter your email" required class="box">

      <div class="password-field">
         <input type="password" name="password" placeholder="Enter your password" required class="box" id="password">
         <i class="fas fa-eye toggle-password" id="togglePassword"></i>
      </div>

      <input type="submit" name="submit" value="Login Now" class="btn">
      <p>Don't have an account? <a href="register.php">Register now</a></p>
   </form>
</div>

<!-- ===== Small JavaScript for Eye Toggle & Auto Message Removal ===== -->
<script>
   // Show/Hide Password
   const togglePassword = document.getElementById('togglePassword');
   const password = document.getElementById('password');

   togglePassword.addEventListener('click', function () {
      const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
      password.setAttribute('type', type);
      this.classList.toggle('fa-eye-slash');
   });

   // Auto close messages
   setTimeout(() => {
      document.querySelectorAll('.message').forEach(msg => msg.remove());
   }, 4000);
</script>

</body>
</html>
