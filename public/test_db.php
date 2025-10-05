<?php
   // project-root/public/test_db.php
   
   require_once __DIR__ . '/../private/assets/database.php';

   $conn = db_connect();
   $sql = "SELECT COUNT(*) AS total FROM subjects";
   $result = $conn->query($sql);

   if ($result) {
       $row = $result->fetch_assoc();
       echo "✅ Database connected successfully. Subjects count: " . $row['total'];
   } else {
       echo "❌ Query failed: " . $conn->error;
   }

   db_disconnect($conn);
?>