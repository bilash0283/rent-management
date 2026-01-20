<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
<title>Admin Login</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<div class="min-h-screen flex items-center justify-center">
  <form method="POST" action="/login-action"
    class="bg-white p-6 rounded shadow w-96">

    <h2 class="text-2xl font-bold mb-4 text-center">Admin Login</h2>

    <?php if(isset($_SESSION['error'])){ ?>
      <div class="bg-red-100 text-red-700 p-2 mb-3 rounded">
        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
      </div>
    <?php } ?>

    <input name="email"
      class="border w-full p-2 mb-3"
      placeholder="Email">

    <input type="password" name="password"
      class="border w-full p-2 mb-3"
      placeholder="Password">

    <button
      class="bg-blue-600 hover:bg-blue-700 text-white w-full py-2 rounded">
      Login
    </button>
  </form>
</div>

</body>
</html>
