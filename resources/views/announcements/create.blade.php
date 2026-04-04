<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <form action="{{ route('announcements.store') }}"
      method="POST" 
      enctype="multipart/form-data">

    @csrf

    <label>Announcement Title</label>
    <input type="text" name="AnnouncementTitle" required>

    <label>Announcement Description</label>
    <textarea name="AnnouncementDescription" required></textarea>

    <label>Attach Images</label>
    <input type="file" name="images[]" multiple accept="image/*">
    <input type="file" name="video" accept="video/mp4,video/webm,video/ogg">


    <button type="submit">Publish Announcement</button>
</form>
</body>
</html>