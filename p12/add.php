<?php
// add.php
include 'db.php';

$errors = [];

if (isset($_POST['submit'])) {
    $nis = trim($_POST['nis']);
    $name = trim($_POST['name']);
    $major = trim($_POST['major']);
    $year = (int)$_POST['year'];

    // Validate minimal
    if ($nis === '' || $name === '' || $major === '' || $year <= 0) {
        $errors[] = "Semua field wajib diisi dengan benar.";
    }

    // Handle upload
    $photoName = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/jpeg','image/png','image/gif'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $_FILES['photo']['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mime, $allowed)) {
                $errors[] = "Tipe file tidak diperbolehkan. Hanya JPG/PNG/GIF.";
            } else {
                $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
                $photoName = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $target = __DIR__ . '/uploads/' . $photoName;
                if (!move_uploaded_file($_FILES['photo']['tmp_name'], $target)) {
                    $errors[] = "Gagal meng-upload file.";
                }
            }
        } else {
            $errors[] = "Upload error code: " . $_FILES['photo']['error'];
        }
    }

    if (empty($errors)) {
        $sql = "INSERT INTO students (nis, name, major, year, photo) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssis", $nis, $name, $major, $year, $photoName);
        if (mysqli_stmt_execute($stmt)) {
            header('Location: index.php');
            exit;
        } else {
            $errors[] = "DB Error: " . mysqli_error($conn);
            // if upload succeeded but DB failed, consider unlink file
            if ($photoName && file_exists('uploads/'.$photoName)) unlink('uploads/'.$photoName);
        }
    }
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Add Student</title></head>
<body>
  <h2>Add Student</h2>
  <?php if (!empty($errors)): ?>
    <div style="color:red;">
      <?php foreach ($errors as $e) echo '<p>'.esc($e).'</p>'; ?>
    </div>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data">
    NIS: <input type="text" name="nis" required><br><br>
    Name: <input type="text" name="name" required><br><br>
    Major: <input type="text" name="major" required><br><br>
    Year: <input type="number" name="year" required><br><br>
    Photo: <input type="file" name="photo" accept="image/*"><br><br>
    <button type="submit" name="submit">Save</button>
  </form>

  <p><a href="index.php">Back to list</a></p>
</body>
</html>