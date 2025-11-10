<?php

include 'config.php';

if(isset($_POST['submit'])){

   $name = mysqli_real_escape_string($conn, $_POST['name']);
   $email = mysqli_real_escape_string($conn, $_POST['email']);
   $pass = mysqli_real_escape_string($conn, md5($_POST['password']));
   $cpass = mysqli_real_escape_string($conn, md5($_POST['cpassword']));
   $user_type = $_POST['user_type'];

   $select_users = mysqli_query($conn, "SELECT * FROM `users` WHERE email = '$email'") or die('query failed');

   if(mysqli_num_rows($select_users) > 0){
      $message[] = 'User already exists!';
   }else{
      if($pass != $cpass){
         $message[] = 'Confirm password does not match!';
      }else{
         mysqli_query($conn, "INSERT INTO `users`(name, email, password, user_type) VALUES('$name', '$email', '$cpass', '$user_type')") or die('query failed');
         $message[] = 'Registered successfully!';
         header('location:login.php');
         exit();
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
   <title>Register</title>

   <!-- Font Awesome -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <style>
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

      .form-container {
         background: #fff;
         border-radius: 15px;
         padding: 40px 45px;
         width: 100%;
         max-width: 440px;
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

      .form-container p {
         text-align: center;
         color: #333;
         margin-top: 15px;
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
      <h3>Register Now</h3>
      <input type="text" name="name" placeholder="Enter your name" required class="box">
      <input type="email" name="email" placeholder="Enter your email" required class="box">

      <div class="password-field">
         <input type="password" name="password" placeholder="Enter your password" required class="box" id="password">
         <i class="fas fa-eye toggle-password" id="togglePassword"></i>
      </div>

      <div class="password-field">
         <input type="password" name="cpassword" placeholder="Confirm your password" required class="box" id="confirmPassword">
         <i class="fas fa-eye toggle-password" id="toggleConfirmPassword"></i>
      </div>

      <select name="user_type" class="box">
         <option value="user">User</option>
      </select>

      <input type="submit" name="submit" value="Register Now" class="btn">
      <p>Already have an account? <a href="login.php">Login now</a></p>
   </form>
</div>

<!-- ===== Small JavaScript ===== -->
<script>
   // Toggle password visibility
   const togglePassword = document.getElementById('togglePassword');
   const password = document.getElementById('password');
   const toggleConfirm = document.getElementById('toggleConfirmPassword');
   const confirmPassword = document.getElementById('confirmPassword');

   togglePassword.addEventListener('click', function () {
      const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
      password.setAttribute('type', type);
      this.classList.toggle('fa-eye-slash');
   });

   toggleConfirm.addEventListener('click', function () {
      const type = confirmPassword.getAttribute('type') === 'password' ? 'text' : 'password';
      confirmPassword.setAttribute('type', type);
      this.classList.toggle('fa-eye-slash');
   });

   // Auto close message
   setTimeout(() => {
      document.querySelectorAll('.message').forEach(msg => msg.remove());
   }, 4000);
</script>

</body>
</html>
