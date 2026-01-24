<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Rent Management Dashboard</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex">

<!-- Sidebar -->
<aside class="w-64 bg-white h-screen shadow-md flex flex-col">
    <div class="p-6 text-2xl font-bold text-blue-600">
        Rent Management
    </div>
    <nav class="flex-1 px-4 space-y-2">
        <a href="/OFFICE/rent-manage/public/dashboard" class="block py-2 px-4 rounded hover:bg-blue-100 <?= ($_SERVER['REQUEST_URI']==='/OFFICE/rent-manage/public/dashboard')?'bg-blue-100':'' ?>">Dashboard</a>
        <a href="/OFFICE/rent-manage/public/unit" class="block py-2 px-4 rounded hover:bg-blue-100 <?= ($_SERVER['REQUEST_URI']==='/OFFICE/rent-manage/public/unit')?'bg-blue-100':'' ?>">Units</a>
        <a href="/OFFICE/rent-manage/public/tenant" class="block py-2 px-4 rounded hover:bg-blue-100 <?= ($_SERVER['REQUEST_URI']==='/OFFICE/rent-manage/public/tenant')?'bg-blue-100':'' ?>">Tenants</a>
        <a href="/OFFICE/rent-manage/public/building" class="block py-2 px-4 rounded hover:bg-blue-100 <?= ($_SERVER['REQUEST_URI']==='/OFFICE/rent-manage/public/building')?'bg-blue-100':'' ?>">Buildings</a>
        <a href="/OFFICE/rent-manage/public/bill" class="block py-2 px-4 rounded hover:bg-blue-100 <?= ($_SERVER['REQUEST_URI']==='/OFFICE/rent-manage/public/bill')?'bg-blue-100':'' ?>">Bills</a>
        <a href="/OFFICE/rent-manage/public/invoice" class="block py-2 px-4 rounded hover:bg-blue-100 <?= ($_SERVER['REQUEST_URI']==='/OFFICE/rent-manage/public/invoice')?'bg-blue-100':'' ?>">Invoices</a>
        <a href="/OFFICE/rent-manage/public/reports" class="block py-2 px-4 rounded hover:bg-blue-100 <?= ($_SERVER['REQUEST_URI']==='/OFFICE/rent-manage/public/reports')?'bg-blue-100':'' ?>">Reports</a>
    </nav>
    <div class="p-4 border-t">
        <a href="/OFFICE/rent-manage/public/" class="mt-2 block bg-red-600 text-white text-center py-2 rounded hover:bg-red-700">Logout</a>
    </div>
</aside>

<!-- Main Content -->
<main class="flex-1 p-6">
    <div class="flex justify-between align-center px-4">
        <div>
            <h1 class="text-3xl font-bold mb-6">Dashboard</h1>
        </div>
        <div>
            <span class="bg-red-500 text-white p-2 rounded-full">Ad</span>
        </div>
    </div>

    <!-- Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white p-6 rounded shadow flex justify-between items-center">
            <div>
                <p class="text-gray-500">Total Units</p>
                <p class="text-2xl font-bold">12</p>
            </div>
            <div class="text-blue-600 text-3xl">🏢</div>
        </div>

        <div class="bg-white p-6 rounded shadow flex justify-between items-center">
            <div>
                <p class="text-gray-500">Total Tenants</p>
                <p class="text-2xl font-bold">34</p>
            </div>
            <div class="text-green-600 text-3xl">👤</div>
        </div>

        <div class="bg-white p-6 rounded shadow flex justify-between items-center">
            <div>
                <p class="text-gray-500">Monthly Income</p>
                <p class="text-2xl font-bold">৳45,000</p>
            </div>
            <div class="text-yellow-600 text-3xl">💰</div>
        </div>
    </div>

    <!-- Latest Tenants Table -->
    <div class="bg-white rounded shadow p-4">
        <h2 class="text-xl font-bold mb-4">Latest Tenants</h2>
        <div class="overflow-x-auto">
            <table class="w-full table-auto border-collapse">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border p-2 text-left">Name</th>
                        <th class="border p-2 text-left">Unit</th>
                        <th class="border p-2 text-left">Email</th>
                        <th class="border p-2 text-left">Phone</th>
                        <th class="border p-2 text-left">Rent</th>
                        <th class="border p-2 text-left">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="border p-2">Rahim</td>
                        <td class="border p-2">A-1</td>
                        <td class="border p-2">rahim@gmail.com</td>
                        <td class="border p-2">017XXXXXXX</td>
                        <td class="border p-2">৳12,000</td>
                        <td class="border p-2 text-green-600 font-semibold">Active</td>
                    </tr>
                    <tr>
                        <td class="border p-2">Karim</td>
                        <td class="border p-2">B-3</td>
                        <td class="border p-2">karim@gmail.com</td>
                        <td class="border p-2">018XXXXXXX</td>
                        <td class="border p-2">৳10,000</td>
                        <td class="border p-2 text-red-600 font-semibold">Due</td>
                    </tr>
                    <!-- Dynamic rows from database -->
                </tbody>
            </table>
        </div>
    </div>

</main>

</body>
</html>
