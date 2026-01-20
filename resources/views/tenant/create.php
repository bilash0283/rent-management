<form method="POST" enctype="multipart/form-data" action="/tenant/store">
<input name="name" class="border p-2 w-full mb-2" placeholder="Name">
<input name="email" class="border p-2 w-full mb-2" placeholder="Email">
<input type="file" name="nid_image" class="mb-2">

<select name="unit_id" class="border p-2 w-full">
<!-- loop units -->
</select>

<button class="bg-green-600 text-white px-4 py-2">Save</button>
</form>
