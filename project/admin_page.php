<?php
include 'config.php';
session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:login.php');
   exit();
}

/*
 * AJAX endpoint: returns JSON for charts when ?ajax=charts is requested.
 * Keeps same detection logic for users date column as used on page.
 */
if(isset($_GET['ajax']) && $_GET['ajax'] === 'charts'){

    // 1) Payment status data
    $status_labels = [];
    $status_counts = [];
    $status_q = mysqli_query($conn, "SELECT payment_status, COUNT(*) AS cnt FROM `orders` GROUP BY payment_status") or die('query failed');
    while($r = mysqli_fetch_assoc($status_q)){
       $status_labels[] = $r['payment_status'];
       $status_counts[] = (int)$r['cnt'];
    }

    // 2) Detect appropriate users date column for user-activity
    $users_date_column = null;
    $candidate_names = ['created_at','created','createdOn','created_on','registered_at','registered_on','reg_date','joined_on','joined_at','date','register_date','registered','joined','signup_date','signup_at','added_on'];
    $cols_q = mysqli_query($conn, "SHOW COLUMNS FROM `users`") or die('query failed');
    if($cols_q){
       while($col = mysqli_fetch_assoc($cols_q)){
          $colname = $col['Field'];
          $coltype = strtolower($col['Type']);
          foreach($candidate_names as $cand){
             if(strcasecmp($colname, $cand) === 0){
                $users_date_column = $colname;
                break 2;
             }
          }
          if($users_date_column === null && (strpos($coltype,'date')!==false || strpos($coltype,'datetime')!==false || strpos($coltype,'timestamp')!==false)){
             $users_date_column = $colname;
          }
       }
    }

    $months = [];
    $user_counts = [];

    if($users_date_column !== null){
       for ($i = 5; $i >= 0; $i--) {
           $monthKey = date('Y-m', strtotime("-{$i} months"));
           $monthLabel = date('M Y', strtotime("-{$i} months"));
           $months[] = $monthLabel;
           $col = $users_date_column;
           $safe_q = "SELECT COUNT(*) AS total FROM `users` WHERE DATE_FORMAT(`$col`, '%Y-%m') = '$monthKey'";
           $q = mysqli_query($conn, $safe_q);
           if($q){
              $row = mysqli_fetch_assoc($q);
              $user_counts[] = (int)$row['total'];
           } else {
              $user_counts[] = 0;
           }
       }
    } else {
       for ($i = 5; $i >= 0; $i--) {
          $months[] = date('M Y', strtotime("-{$i} months"));
          $user_counts[] = 0;
       }
    }

    header('Content-Type: application/json');
    echo json_encode([
        'status_labels' => $status_labels,
        'status_counts' => $status_counts,
        'months' => $months,
        'user_counts' => $user_counts,
        'users_date_column' => $users_date_column // helpful for debugging if needed
    ]);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Admin Dashboard</title>

   <!-- Font Awesome -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <style>
      :root {
         --purple-light: #a78bfa;
         --purple-dark: #5b21b6;
         --purple-mid: #7c3aed;
         --white: #fff;
         --bg-light: #f8f6ff;
      }

      body {
         font-family: 'Poppins', sans-serif;
         background: var(--bg-light);
         margin: 0;
         color: #333;
      }

      /* ====== Matching Header (Same as admin_products.php) ====== */
      .admin-header {
         background: var(--white);
         box-shadow: 0 4px 14px rgba(91,33,182,0.15);
         padding: 15px 8%;
         position: sticky;
         top: 0;
         z-index: 1000;
         display: flex;
         justify-content: space-between;
         align-items: center;
      }

      .admin-logo {
         font-size: 1.8rem;
         font-weight: 800;
         color: var(--purple-dark);
         letter-spacing: 2px;
         text-decoration: none;
      }

      .admin-header nav a {
         color: var(--purple-dark);
         margin: 0 12px;
         text-decoration: none;
         font-weight: 600;
         text-transform: uppercase;
         position: relative;
         font-size: 0.95rem;
         letter-spacing: 0.5px;
      }

      .admin-header nav a::after {
         content: '';
         position: absolute;
         left: 0;
         bottom: -3px;
         width: 0;
         height: 2px;
         background: var(--purple-dark);
         transition: width 0.3s ease;
      }

      .admin-header nav a:hover::after {
         width: 100%;
      }

      .logout-btn {
         background: linear-gradient(135deg, var(--purple-mid), var(--purple-dark));
         color: #fff;
         padding: 8px 14px;
         border-radius: 6px;
         font-weight: 600;
         text-decoration: none;
         transition: 0.3s;
      }

      .logout-btn:hover {
         background: var(--purple-mid);
      }

      /* ===== Dashboard Layout ===== */
      .dashboard {
         padding: 50px 8%;
         min-height: 100vh;
      }

      .dashboard .title {
         text-align: center;
         font-size: 2rem;
         color: var(--purple-dark);
         text-transform: uppercase;
         margin-bottom: 40px;
         letter-spacing: 1px;
         font-weight: 700;
      }

      .box-container {
         display: grid;
         grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
         gap: 25px;
      }

      .box {
         background: #fff;
         border-radius: 14px;
         padding: 25px 20px;
         box-shadow: 0 4px 15px rgba(123, 44, 191, 0.15);
         border: 1px solid #ece0ff;
         text-align: center;
         transition: 0.3s ease;
         position: relative;
         overflow: hidden;
      }

      .box::before {
         content: "";
         position: absolute;
         top: 0;
         left: 0;
         width: 100%;
         height: 6px;
         background: linear-gradient(135deg, var(--purple-mid), var(--purple-dark));
         border-radius: 14px 14px 0 0;
      }

      .box h3 {
         font-size: 1.8rem;
         color: var(--purple-dark);
         margin: 15px 0;
         font-weight: 700;
      }

      .box p {
         font-size: 1rem;
         color: #555;
      }

      .box:hover {
         transform: translateY(-6px);
         box-shadow: 0 8px 25px rgba(123, 44, 191, 0.25);
      }

      @media (max-width: 768px) {
         .admin-header {
            flex-direction: column;
            align-items: flex-start;
         }

         .admin-header nav {
            margin-top: 10px;
         }

         .dashboard {
            padding: 30px 5%;
         }

         .dashboard .title {
            font-size: 1.6rem;
         }
      }

      /* Chart Section */
      .charts-section {
         margin-top: 60px;
         display: grid;
         grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
         gap: 40px;
      }

      .chart-box {
         background: #fff;
         border-radius: 14px;
         padding: 25px;
         box-shadow: 0 4px 15px rgba(123, 44, 191, 0.15);
         border: 1px solid #ece0ff;
      }

      .chart-box h3 {
         text-align: center;
         color: var(--purple-dark);
         margin-bottom: 20px;
         font-size: 1.3rem;
         font-weight: 700;
      }
   </style>
</head>

<body>

<!-- ===== Matching Navbar ===== -->
<header class="admin-header">
   <a href="admin_page.php" class="admin-logo">𝙏𝙀𝙓𝙏𝙐𝙍𝘼</a>
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

<!-- ===== Dashboard Content ===== -->
<section class="dashboard">
   <h1 class="title">Dashboard Overview</h1>

   <div class="box-container">

      <div class="box">
         <?php
            $total_pendings = 0;
            $select_pending = mysqli_query($conn, "SELECT total_price FROM `orders` WHERE payment_status = 'pending'") or die('query failed');
            if(mysqli_num_rows($select_pending) > 0){
               while($fetch_pendings = mysqli_fetch_assoc($select_pending)){
                  $total_pendings += $fetch_pendings['total_price'];
               };
            };
         ?>
         <h3>$<?php echo $total_pendings; ?>/-</h3>
         <p>Total Pending Payments</p>
      </div>

      <div class="box">
         <?php
            $total_completed = 0;
            $select_completed = mysqli_query($conn, "SELECT total_price FROM `orders` WHERE payment_status = 'completed'") or die('query failed');
            if(mysqli_num_rows($select_completed) > 0){
               while($fetch_completed = mysqli_fetch_assoc($select_completed)){
                  $total_completed += $fetch_completed['total_price'];
               };
            };
         ?>
         <h3>$<?php echo $total_completed; ?>/-</h3>
         <p>Completed Payments</p>
      </div>

      <div class="box">
         <?php 
            $select_orders = mysqli_query($conn, "SELECT * FROM `orders`") or die('query failed');
            $number_of_orders = mysqli_num_rows($select_orders);
         ?>
         <h3><?php echo $number_of_orders; ?></h3>
         <p>Orders Placed</p>
      </div>

      <div class="box">
         <?php 
            $select_products = mysqli_query($conn, "SELECT * FROM `products`") or die('query failed');
            $number_of_products = mysqli_num_rows($select_products);
         ?>
         <h3><?php echo $number_of_products; ?></h3>
         <p>Products Added</p>
      </div>

      <div class="box">
         <?php 
            $select_users = mysqli_query($conn, "SELECT * FROM `users` WHERE user_type = 'user'") or die('query failed');
            $number_of_users = mysqli_num_rows($select_users);
         ?>
         <h3><?php echo $number_of_users; ?></h3>
         <p>Registered Users</p>
      </div>

      <div class="box">
         <?php 
            $select_messages = mysqli_query($conn, "SELECT * FROM `message`") or die('query failed');
            $number_of_messages = mysqli_num_rows($select_messages);
         ?>
         <h3><?php echo $number_of_messages; ?></h3>
         <p>New Messages</p>
      </div>

      <div class="box">
         <?php 
            $select_sell = mysqli_query($conn, "SELECT * FROM `sell_books`") or die('query failed');
            $number_of_sell = mysqli_num_rows($select_sell);
         ?>
         <h3><?php echo $number_of_sell; ?></h3>
         <p>Sell Requests</p>
      </div>

   </div>

   <!-- ===== CHARTS SECTION (initial PHP-rendered data) ===== -->
   <?php
      // 1) Payment status data for pie chart (safe, unchanged)
      $status_labels = [];
      $status_counts = [];
      $status_q = mysqli_query($conn, "SELECT payment_status, COUNT(*) AS cnt FROM `orders` GROUP BY payment_status") or die('query failed');
      while($r = mysqli_fetch_assoc($status_q)){
         $status_labels[] = $r['payment_status'];
         $status_counts[] = $r['cnt'];
      }

      // 2) Detect appropriate users date column for user-activity
      $users_date_column = null;
      $candidate_names = ['created_at','created','createdOn','created_on','registered_at','registered_on','reg_date','joined_on','joined_at','date','register_date','registered','joined','signup_date','signup_at','added_on'];
      $cols_q = mysqli_query($conn, "SHOW COLUMNS FROM `users`") or die('query failed');
      if($cols_q){
         while($col = mysqli_fetch_assoc($cols_q)){
            $colname = $col['Field'];
            $coltype = strtolower($col['Type']);
            foreach($candidate_names as $cand){
               if(strcasecmp($colname, $cand) === 0){
                  $users_date_column = $colname;
                  break 2;
               }
            }
            if($users_date_column === null && (strpos($coltype,'date')!==false || strpos($coltype,'datetime')!==false || strpos($coltype,'timestamp')!==false)){
               $users_date_column = $colname;
            }
         }
      }

      $months = [];
      $user_counts = [];

      if($users_date_column !== null){
         for ($i = 5; $i >= 0; $i--) {
            $monthKey = date('Y-m', strtotime("-{$i} months"));
            $monthLabel = date('M Y', strtotime("-{$i} months"));
            $months[] = $monthLabel;
            $col = $users_date_column;
            $safe_q = "SELECT COUNT(*) AS total FROM `users` WHERE DATE_FORMAT(`$col`, '%Y-%m') = '$monthKey'";
            $q = mysqli_query($conn, $safe_q);
            if($q){
               $row = mysqli_fetch_assoc($q);
               $user_counts[] = (int)$row['total'];
            } else {
               $user_counts[] = 0;
            }
         }
      } else {
         for ($i = 5; $i >= 0; $i--) {
            $monthLabel = date('M Y', strtotime("-{$i} months"));
            $months[] = $monthLabel;
            $user_counts[] = 0;
         }
      }
   ?>

   <div class="charts-section">
      <div class="chart-box">
         <h3>Payment Status Overview</h3>
         <canvas id="paymentChart" height="200"></canvas>
      </div>

      <div class="chart-box">
         <h3>User Activity (Last 6 Months)</h3>
         <canvas id="userChart" height="200"></canvas>

         <?php
            if($users_date_column === null){
               echo '<p style="font-size:0.9rem;color:#666;margin-top:12px;text-align:center;">Note: Could not detect a date column in <code>users</code> table — user activity shows 0. Add a date/datetime column (e.g. <code>created_at</code>) for accurate data.</p>';
            } else {
               echo '<!-- users date column used: ' . htmlspecialchars($users_date_column) . ' -->';
            }
         ?>
      </div>
   </div>
</section>

</body>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
   // initial data rendered by PHP for immediate first paint
   let statusLabels = <?php echo json_encode($status_labels); ?>;
   let statusCounts = <?php echo json_encode($status_counts); ?>;
   let months = <?php echo json_encode($months); ?>;
   let userCounts = <?php echo json_encode($user_counts); ?>;

   // create charts
   const ctxPayment = document.getElementById('paymentChart').getContext('2d');
   const paymentChart = new Chart(ctxPayment, {
      type: 'pie',
      data: {
         labels: statusLabels,
         datasets: [{
            data: statusCounts,
            backgroundColor: [
               'rgba(124,58,237,0.9)',
               'rgba(167,139,250,0.9)',
               'rgba(99,102,241,0.8)',
               'rgba(59,130,246,0.8)'
            ]
         }]
      },
      options: {
         responsive: true,
         plugins: {
            legend: { position: 'bottom' }
         }
      }
   });

   const ctxUser = document.getElementById('userChart').getContext('2d');
   const userChart = new Chart(ctxUser, {
      type: 'line',
      data: {
         labels: months,
         datasets: [{
            label: 'New Users',
            data: userCounts,
            fill: true,
            tension: 0.4,
            borderColor: 'rgba(91,33,182,0.9)',
            backgroundColor: 'rgba(124,58,237,0.15)',
            pointBorderColor: 'rgba(91,33,182,1)',
            pointBackgroundColor: '#fff',
            borderWidth: 2
         }]
      },
      options: {
         responsive: true,
         plugins: {
            legend: { display: false }
         },
         scales: {
            y: { beginAtZero: true, precision: 0 }
         }
      }
   });

   // AJAX fetch function to update charts
   async function fetchAndUpdateCharts(){
      try{
         const resp = await fetch(window.location.pathname + '?ajax=charts', { cache: 'no-store' });
         if(!resp.ok) throw new Error('Network response was not OK');
         const data = await resp.json();

         // update payment chart
         if(Array.isArray(data.status_labels) && Array.isArray(data.status_counts)){
            paymentChart.data.labels = data.status_labels;
            paymentChart.data.datasets[0].data = data.status_counts;
            paymentChart.update();
         }

         // update user chart
         if(Array.isArray(data.months) && Array.isArray(data.user_counts)){
            userChart.data.labels = data.months;
            userChart.data.datasets[0].data = data.user_counts;
            userChart.update();
         }
      } catch (err){
         // silently fail (do not break UI); optionally log to console
         console.error('Failed to fetch chart data:', err);
      }
   }

   // Auto-refresh every 15 seconds
   const REFRESH_INTERVAL_MS = 15000;
   setInterval(fetchAndUpdateCharts, REFRESH_INTERVAL_MS);

   // Also fetch once after a short delay (so charts refresh almost immediately after load)
   setTimeout(fetchAndUpdateCharts, 2000);
</script>
</html>
