<?php
// edit.php
include 'db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: index.php'); exit;
}

// ambil data
$stmt = mysqli_prepare($conn, "SELECT * FROM students WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($res);
if (!$data) { header('Location: index.php'); exit; }

$errors = [];
if (isset($_POST['submit'])) {
    $nis = trim($_POST['nis']);
    $name = trim($_POST['name']);
    $major = trim($_POST['major']);
    $year = (int)$_POST['year'];

    // handle new upload (optional)
    $newPhoto = $data['photo'];
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
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $target)) {
                    // hapus file lama bila ada
                    if (!empty($data['photo']) && file_exists('uploads/'.$data['photo'])) {
                        @unlink('uploads/'.$data['photo']);
                    }
                    $newPhoto = $photoName;
                } else {
                    $errors[] = "Gagal meng-upload file baru.";
                }
            }
        } else {
            $errors[] = "Upload error code: " . $_FILES['photo']['error'];
        }
    }

    if (empty($errors)) {
        $sql = "UPDATE students SET nis=?, name=?, major=?, year=?, photo=? WHERE id=?";
        $stmt2 = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt2, "sssisi", $nis, $name, $major, $year, $newPhoto, $id);
        if (mysqli_stmt_execute($stmt2)) {
            header('Location: index.php'); exit;
        } else {
            $errors[] = "DB Error: " . mysqli_error($conn);
        }
    }
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Edit Student</title></head>
<body>
  <h2>Edit Student</h2>
  <?php if (!empty($errors)): ?>
    <div style="color:red;">
      <?php foreach ($errors as $e) echo '<p>'.esc($e).'</p>'; ?>
    </div>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data">
    NIS: <input type="text" name="nis" value="<?php echo esc($data['nis']); ?>" required><br><br>
    Name: <input type="text" name="name" value="<?php echo esc($data['name']); ?>" required><br><br>
    Major: <input type="text" name="major" value="<?php echo esc($data['major']); ?>" required><br><br>
    Year: <input type="number" name="year" value="<?php echo esc($data['year']); ?>" required><br><br>

    Current Photo:<br>
    <?php if (!empty($data['photo']) && file_exists('uploads/'.$data['photo'])): ?>
      <img src="uploads/<?php echo rawurlencode($data['photo']); ?>" style="max-width:120px;"><br>
    <?php else: ?>
      (no photo) <br>
    <?php endif; ?>
    Replace Photo: <input type="file" name="photo" accept="image/*"><br><br>

    <button type="submit" name="submit">Update</button>
  </form>

  <p><a href="index.php">Back to list</a></p>
</body>
</html>