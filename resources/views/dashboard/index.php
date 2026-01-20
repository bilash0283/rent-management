<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
<title>Dashboard</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<div class="p-6">
  <h1 class="text-2xl font-bold">
    Welcome, <?= $_SESSION['admin_name']; ?>
  </h1>

  <a href="/logout"
     class="mt-4 inline-block bg-red-600 text-white px-4 py-2 rounded">
     Logout
  </a>
</div>

</body>
</html>
