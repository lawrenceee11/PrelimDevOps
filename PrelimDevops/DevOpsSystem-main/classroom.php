<?php
SESSION_START();
include 'config/plugins.php';
require 'config/dbcon.php';
?>

<?php include __DIR__ . '/sidebar.php'; ?> 

<div class="container my-4">
  <h1>Class List</h1>

  <!-- FILTER FORM -->
  <form method="get" action="" class="mb-4">
    <div class="row">
      <div class="col-md-3 mb-2">
        <label class="form-label">Course</label>
        <select name="course" class="form-control">
          <option value="">-- All Courses --</option>
          <option value="BS Information Technology" <?= (($_GET['course'] ?? '') === 'BS Information Technology') ? 'selected' : '' ?>>BSIT</option>
          <option value="BS Computer Science" <?= (($_GET['course'] ?? '') === 'BS Computer Science') ? 'selected' : '' ?>>BSCS</option>
          <option value="BS Computer Engineering" <?= (($_GET['course'] ?? '') === 'BS Computer Engineering') ? 'selected' : '' ?>>BS Computer Engineering</option>
        </select>
      </div>

      <div class="col-md-3 mb-2">
        <label class="form-label">Year</label>
        <select name="year" class="form-control">
          <option value="">-- All Years --</option>
          <option value="First Year" <?= (($_GET['year'] ?? '') === 'First Year') ? 'selected' : '' ?>>1st Year</option>
          <option value="Second Year" <?= (($_GET['year'] ?? '') === 'Second Year') ? 'selected' : '' ?>>2nd Year</option>
          <option value="Third Year" <?= (($_GET['year'] ?? '') === 'Third Year') ? 'selected' : '' ?>>3rd Year</option>
          <option value="Fourth Year" <?= (($_GET['year'] ?? '') === 'Fourth Year') ? 'selected' : '' ?>>4th Year</option>
        </select>
      </div>

      <div class="col-md-3 mb-2">
        <label class="form-label">Section</label>
        <select name="section" class="form-control">
          <option value="">-- All Sections --</option>
          <option value="A" <?= (($_GET['section'] ?? '') === 'A') ? 'selected' : '' ?>>A</option>
          <option value="B" <?= (($_GET['section'] ?? '') === 'B') ? 'selected' : '' ?>>B</option>
          <option value="C" <?= (($_GET['section'] ?? '') === 'C') ? 'selected' : '' ?>>C</option>
        </select>
      </div>

      <div class="col-md-3 mb-2 d-flex align-items-end">
        <button type="submit" class="btn btn-primary w-100">Submit</button>
      </div>
    </div>
  </form>

  <!-- CLASS LIST -->
  <h3>Class List</h3>

  <?php if (!empty($_GET['course']) || !empty($_GET['year']) || !empty($_GET['section'])): ?>
    <p>
      <strong>Course:</strong> <?= htmlspecialchars($_GET['course'] ?: 'All') ?>
      &nbsp; <strong>Year:</strong> <?= htmlspecialchars($_GET['year'] ?: 'All') ?>
      &nbsp; <strong>Section:</strong> <?= htmlspecialchars($_GET['section'] ?: 'All') ?>
    </p>
  <?php else: ?>
    <p><em>Showing all enrolled students</em></p>
  <?php endif; ?>

  <table class="table table-striped table-bordered">
    <thead class="table-">
      <tr>
        <th>Name</th>
        <th>Username</th>
        <th>Course</th>
        <th>Year</th>
        <th>Section</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>

<?php
// Detect if section column exists
$has_section = false;
$check = $conn->query("SHOW COLUMNS FROM enroll LIKE 'section'");
if ($check && $check->num_rows > 0) $has_section = true;

// Build filters
$conds = [];
if (!empty($_GET['course'])) $conds[] = "course='".$conn->real_escape_string($_GET['course'])."'";
if (!empty($_GET['year']))   $conds[] = "year='".$conn->real_escape_string($_GET['year'])."'";
if (!empty($_GET['section']) && $has_section)
  $conds[] = "section='".$conn->real_escape_string($_GET['section'])."'";

// Build query
$fields = "firstname, lastname, username, course, year";
if ($has_section) $fields .= ", section";

$sql = "SELECT $fields FROM enroll";
if ($conds) $sql .= " WHERE " . implode(" AND ", $conds);
$sql .= " ORDER BY lastname, firstname";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $username = htmlspecialchars($row['username'], ENT_QUOTES);

    echo "<tr>";
    echo "<td>".htmlspecialchars($row['lastname'].', '.$row['firstname'])."</td>";
    echo "<td>".$username."</td>";
    echo "<td>".htmlspecialchars($row['course'])."</td>";
    echo "<td>".htmlspecialchars($row['year'])."</td>";
    echo "<td>".($has_section ? htmlspecialchars($row['section'] ?? '-') : '-')."</td>";
    echo "<td>
            <a href='view_grades.php?username={$username}' class='btn btn-sm btn-primary'>
              View Grades
            </a>
          </td>";
    echo "</tr>";
  }
} else {
  echo "<tr><td colspan='6' class='text-center'>No students found.</td></tr>";
}
?>

    </tbody>
  </table>
</div>
