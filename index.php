<?php
// index.php
require_once 'db.php';

// Messages
$message = '';

// CREATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $name  = trim($_POST['name']);
    $email = trim($_POST['email']);

    if ($name === '' || $email === '') {
        $message = 'Name and Email are required.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO students (name, email) VALUES (:name, :email)");
        try {
            $stmt->execute([':name' => $name, ':email' => $email]);
            $message = 'Student created';
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // duplicate email
                $message = 'Email already exists.';
            } else {
                $message = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

// UPDATE - show form populated & save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $id    = (int)$_POST['id'];
    $name  = trim($_POST['name']);
    $email = trim($_POST['email']);

    if ($name === '' || $email === '') {
        $message = 'Name and Email are required.';
    } else {
        $stmt = $pdo->prepare("UPDATE students SET name = :name, email = :email WHERE id = :id");
        try {
            $stmt->execute([':name' => $name, ':email' => $email, ':id' => $id]);
            $message = 'Student updated';
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $message = 'Email already exists.';
            } else {
                $message = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

// DELETE
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM students WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $message = 'Student deleted';
}

// Get a student for editing (if editing)
$edit_student = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $edit_student = $stmt->fetch();
}

// Fetch all students
$stmt = $pdo->query("SELECT * FROM students ORDER BY id DESC");
$students = $stmt->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Student CRUD</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <!-- Bootstrap CDN for quick styling -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(120deg, #f8fafc 60%, #e0e7ff 100%);
      min-height: 100vh;
    }
    .main-title {
      font-weight: 800;
      letter-spacing: -1px;
      color: #1e293b;
      margin-bottom: 2rem;
      text-shadow: 0 2px 8px rgba(30,64,175,0.07);
    }
    .card {
      border-radius: 1.2rem;
      box-shadow: 0 6px 32px rgba(30,64,175,0.08);
      border: none;
    }
    .card-title {
      font-weight: 700;
      color: #2563eb;
    }
    .form-label {
      font-weight: 500;
      color: #1e293b;
    }
    .btn-primary {
      background: linear-gradient(90deg, #2563eb 0%, #1e40af 100%);
      border: none;
      font-weight: 600;
      letter-spacing: 1px;
    }
    .btn-primary:hover {
      background: linear-gradient(90deg, #1e40af 0%, #2563eb 100%);
    }
    .btn-outline-primary {
      border-color: #2563eb;
      color: #2563eb;
    }
    .btn-outline-primary:hover {
      background: #2563eb;
      color: #fff;
    }
    .btn-outline-danger {
      border-color: #ef4444;
      color: #ef4444;
    }
    .btn-outline-danger:hover {
      background: #ef4444;
      color: #fff;
    }
    .table th, .table td {
      vertical-align: middle;
    }
    .alert-info {
      background: #e0e7ff;
      color: #1e40af;
      border: none;
      font-weight: 500;
    }
    @media (max-width: 991px) {
      .row > div {
        margin-bottom: 2rem;
      }
    }
  </style>
</head>
<body>
<div class="container py-5">
  <h1 class="main-title text-center">Student CRUD</h1>

  <?php if ($message): ?>
    <div class="alert alert-info text-center"><?=htmlspecialchars($message)?></div>
  <?php endif; ?>

  <div class="row justify-content-center">
    <div class="col-lg-5 col-md-7">
      <div class="card mb-4">
        <div class="card-body">
          <h5 class="card-title mb-4"><?= $edit_student ? 'Edit Student' : 'Add New Student' ?></h5>
          <form method="post" action="index.php">
            <?php if ($edit_student): ?>
              <input type="hidden" name="action" value="update">
              <input type="hidden" name="id" value="<?= (int)$edit_student['id'] ?>">
            <?php else: ?>
              <input type="hidden" name="action" value="create">
            <?php endif; ?>

            <div class="mb-3">
              <label class="form-label">Name</label>
              <input type="text" name="name" class="form-control" placeholder="e.g. Sandun Dilhara"
                     value="<?= $edit_student ? htmlspecialchars($edit_student['name']) : '' ?>">
            </div>

            <div class="mb-3">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-control" placeholder="e.g. Sandun@example.com"
                     value="<?= $edit_student ? htmlspecialchars($edit_student['email']) : '' ?>">
            </div>

            <button type="submit" class="btn btn-primary px-4">
              <?= $edit_student ? 'Update' : 'Create' ?>
            </button>
            <?php if ($edit_student): ?>
              <a href="index.php" class="btn btn-secondary ms-2 px-4">Cancel</a>
            <?php endif; ?>
          </form>
        </div>
      </div>
    </div>

    <div class="col-lg-7 col-md-10">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title mb-4">Students</h5>
          <?php if (count($students) === 0): ?>
            <p class="text-muted">No students yet.</p>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Created</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($students as $s): ?>
                    <tr>
                      <td><?= (int)$s['id'] ?></td>
                      <td><?= htmlspecialchars($s['name']) ?></td>
                      <td><?= htmlspecialchars($s['email']) ?></td>
                      <td>
                        <?= isset($s['created_at']) ? htmlspecialchars($s['created_at']) : '<span class="text-muted">N/A</span>' ?>
                      </td>
                      <td>
                        <a href="index.php?edit=<?= (int)$s['id'] ?>" class="btn btn-sm btn-outline-primary me-1">Edit</a>
                        <a href="index.php?delete=<?= (int)$s['id'] ?>"
                           onclick="return confirm('Delete this student?')"
                           class="btn btn-sm btn-outline-danger">Delete</a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>