<?php
include 'db.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$limit = 5;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$start = ($page - 1) * $limit;

$like = '%' . $search . '%';
$countSql = "SELECT COUNT(*) AS total FROM students WHERE name LIKE ? OR nis LIKE ?";
$stmt = mysqli_prepare($conn, $countSql);
mysqli_stmt_bind_param($stmt, "ss", $like, $like);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($res);
$total = $row ? (int)$row['total'] : 0;
$pages = ($total > 0) ? ceil($total / $limit) : 1;

$sql = "SELECT * FROM students WHERE name LIKE ? OR nis LIKE ? ORDER BY created_at DESC LIMIT ?, ?";
$stmt2 = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt2, "ssii", $like, $like, $start, $limit);
mysqli_stmt_execute($stmt2);
$result = mysqli_stmt_get_result($stmt2);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>ENTRY STUDENTS</title>
  <style>
    table{border-collapse:collapse;width:100%}
    th,td{border:1px solid #ccc;padding:8px;text-align:left}
    img{max-width:80px;height:auto}
    .pagination a{margin:0 5px;text-decoration:none}
    .current{font-weight:bold}
  </style>
</head>
<body>
  <h2>STUDENTS NAME</h2>

  <form method="GET" style="margin-bottom:10px;">
    <input type="text" name="search" placeholder="Search by name or NIS..." value="<?php echo esc($search); ?>">
    <button type="submit">Search</button>
    <a href="index.php" style="margin-left:10px;">Reset</a>
  </form>

  <a href="add.php">+ Add New Student</a>
  <p>Total records: <?php echo $total; ?></p>

  <table>
    <tr>
      <th>ID</th>
      <th>NIS</th>
      <th>Name</th>
      <th>Major</th>
      <th>Year</th>
      <th>Photo</th>
      <th>Action</th>
    </tr>
    <?php if ($result && mysqli_num_rows($result) > 0): ?>
      <?php while ($r = mysqli_fetch_assoc($result)): ?>
        <tr>
          <td><?php echo esc($r['id']); ?></td>
          <td><?php echo esc($r['nis']); ?></td>
          <td><?php echo esc($r['name']); ?></td>
          <td><?php echo esc($r['major']); ?></td>
          <td><?php echo esc($r['year']); ?></td>
          <td>
            <?php if (!empty($r['photo']) && file_exists('uploads/'.$r['photo'])): ?>
              <img src="uploads/<?php echo rawurlencode($r['photo']); ?>" alt="Photo">
            <?php else: ?>
              -
            <?php endif; ?>
          </td>
          <td>
            <a href="edit.php?id=<?php echo $r['id']; ?>">Edit</a> |
            <a href="delete.php?id=<?php echo $r['id']; ?>" onclick="return confirm('Hapus data ini?')">Delete</a>
          </td>
        </tr>
      <?php endwhile; ?>
    <?php else: ?>
      <tr><td colspan="7">No records found.</td></tr>
    <?php endif; ?>
  </table>

  <div class="pagination" style="margin-top:10px;">
    <?php for ($i=1; $i <= $pages; $i++): ?>
      <?php if ($i == $page): ?>
        <span class="current"><?php echo $i; ?></span>
      <?php else: ?>
        <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
      <?php endif; ?>
    <?php endfor; ?>
  </div>
</body>
</html>