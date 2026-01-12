<?php
session_start();
include 'config/plugins.php';
require 'config/dbcon.php';
$status = '';
if (isset($_SESSION['status'])) { 
    $status = $_SESSION['status']; 
    unset($_SESSION['status']); 
}
$sql = "SELECT * FROM accounts";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Users Management</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
body {
    background: #f1f5f9;
    font-family: system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
}

/* MAIN CONTENT */
.main.container {
    margin-left:80px; /* keep space for sidebar */
    padding: 150px;
    display: flex;
    justify-content: center; /* center the card */
}

/* PAGE CARD */
.page-card {
    background: #fff;
    border-radius: 18px;
    padding: 25px;
    box-shadow: 0 10px 25px rgba(0,0,0,.06);
    width: 100%;
    max-width: 1000px; /* optional max width */
}

/* HEADER */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.page-header h1 {
    font-size: 26px;
    font-weight: 700;
}

/* STATUS MESSAGE */
.status-msg {
    padding: 12px 16px;
    border-radius: 10px;
    background: #dcfce7;
    color: #166534;
    margin-bottom: 15px;
}

/* TABLE */
table {
    width: 100%;
    border-collapse: collapse;
    text-align: center; /* align text to center */
}

thead {
    background: #0f172a;
    color: #fff;
}

th, td {
    padding: 14px 16px;
}

tbody tr {
    border-bottom: 1px solid #e5e7eb;
}

tbody tr:hover {
    background: #f8fafc;
}

/* BUTTONS */
.btn {
    border: none;
    padding: 10px 14px;
    border-radius: 10px;
    cursor: pointer;
    font-size: 14px;
}

.btn-success {
    background: #22c55e;
    color: #fff;
}

.btn-primary {
    background: #3b82f6;
    color: #fff;
}

.btn-danger {
    background: #ef4444;
    color: #fff;
}

.btn:hover {
    opacity: .9;
}

/* ICON BUTTONS */
.action-btn {
    padding: 8px 10px;
    border-radius: 8px;
}

/* MODAL */
.modal {
    display:none;
    position:fixed;
    inset:0;
    background:rgba(0,0,0,.5);
    z-index:999;
}

.modal-box {
    background:#fff;
    width:380px;
    margin:10% auto;
    padding:25px;
    border-radius:16px;
    box-shadow:0 15px 40px rgba(0,0,0,.3);
}

.modal-box h2 {
    margin-bottom:15px;
}

.modal-close {
    float:right;
    font-size:20px;
    cursor:pointer;
}

input {
    width:100%;
    padding:10px;
    border-radius:10px;
    border:1px solid #d1d5db;
    margin-top:6px;
}
</style>
</head>
<body>

<?php include __DIR__ . '/sidebar.php'; ?>

<div class="main container">
    <div class="page-card">

        <div class="page-header">
            <div>
                <h1>Users Management</h1>
                <p>Manage administrative user accounts.</p>
            </div>
            <button id="addBtn" class="btn btn-success">
                <i class="fa-solid fa-user-plus"></i>
            </button>
        </div>

        <?php if($status): ?>
            <div class="status-msg"><?= htmlspecialchars($status); ?></div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Username</th>
                    <th>Password</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php
            if ($result && $result->num_rows > 0) {
              $i = 1;
              while($row = $result->fetch_assoc()) {
                $uid = (int)$row['id'];
                $uname = htmlspecialchars($row['username'], ENT_QUOTES);
                echo "
                <tr>
                    <td>{$i}</td>
                    <td>{$uname}</td>
                    <td>********</td>
                    <td>
                        <button class='btn btn-primary action-btn edit' data-id='{$uid}' data-username='{$uname}'>
                            <i class='fa-solid fa-pen-to-square'></i>
                        </button>
                        <button class='btn btn-danger action-btn delete' data-id='{$uid}'>
                            <i class='fa-solid fa-trash'></i>
                        </button>
                    </td>
                </tr>";
                $i++;
              }
            } else {
              echo "<tr><td colspan='4'>No users found.</td></tr>";
            }
            ?>
            </tbody>
        </table>

    </div>
</div>

<!-- ADD MODAL -->
<div id="addModal" class="modal">
    <div class="modal-box">
        <span class="modal-close" id="closeModal">&times;</span>
        <h2>Add Admin</h2>
        <form id="addUserForm" method="POST" action="config/addAccount.php">
            <label>Username</label>
            <input id="usernameInput" name="username" required placeholder="username">
            <small>Suffix <b>@admin</b> is automatic</small>

            <label style="margin-top:10px;">Password</label>
            <input type="password" name="password" required>

            <div style="text-align:right;margin-top:15px;">
                <button class="btn btn-success">Add</button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT MODAL -->
<div id="editModal" class="modal">
    <div class="modal-box">
        <span class="modal-close" id="closeEdit">&times;</span>
        <h2>Edit Admin</h2>
        <form id="editUserForm" method="POST" action="config/editAccount.php">
            <input type="hidden" name="id" id="editId">

            <label>Username</label>
            <input id="editUsername" name="username" required>

            <label style="margin-top:10px;">New Password</label>
            <input type="password" name="password">

            <div style="text-align:right;margin-top:15px;">
                <button class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<form id="deleteForm" method="POST" action="config/deleteAccount.php" style="display:none;">
    <input type="hidden" name="id" id="deleteId">
</form>

<script>
const addModal = document.getElementById('addModal');
const closeModal = document.getElementById('closeModal');
const editModal = document.getElementById('editModal');
const closeEdit = document.getElementById('closeEdit');
const addUserForm = document.getElementById('addUserForm');
const usernameInput = document.getElementById('usernameInput');
const editId = document.getElementById('editId');
const editUsername = document.getElementById('editUsername');
const deleteForm = document.getElementById('deleteForm');
const deleteId = document.getElementById('deleteId');

document.getElementById('addBtn').onclick = () => addModal.style.display = 'block';
closeModal.onclick = () => addModal.style.display = 'none';
closeEdit.onclick = () => editModal.style.display = 'none';

window.onclick = e => {
    if(e.target === addModal) addModal.style.display = 'none';
    if(e.target === editModal) editModal.style.display = 'none';
};

// Ensure @admin
addUserForm.onsubmit = () => {
    if(!usernameInput.value.endsWith('@admin'))
        usernameInput.value += '@admin';
};

// EDIT
document.querySelectorAll('.edit').forEach(btn=>{
    btn.onclick = ()=>{
        editId.value = btn.dataset.id;
        editUsername.value = btn.dataset.username.replace('@admin','');
        editModal.style.display = 'block';
    };
});

// DELETE
document.querySelectorAll('.delete').forEach(btn=>{
    btn.onclick = ()=>{
        if(confirm('Delete this user?')){
            deleteId.value = btn.dataset.id;
            deleteForm.submit();
        }
    };
});
</script>

</body>
</html>
