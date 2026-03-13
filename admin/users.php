<?php
if (!isLoggedIn() || !isAdmin()) {
    header('Location: /');
    exit();
}

$pageTitle = "Manage Users";
$breadcrumbs = [
    ['link' => '/admin', 'text' => 'Dashboard'],
    ['link' => '/admin/users', 'text' => 'Users']
];

require_once '../includes/header.php';
?>

<div class="container">
    <div class="admin-header">
        <h1>Manage Users</h1>
        <div class="admin-actions">
            <a href="/admin/users?action=add" class="btn btn-primary">
                <i class="fas fa-user-plus"></i> Add New User
            </a>
            <a href="/admin" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>
    
    <!-- User List -->
    <div class="admin-content">
        <div class="card">
            <div class="card-header">
                <h3>All Users</h3>
                <div class="search-box">
                    <input type="text" id="searchUsers" placeholder="Search users...">
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table" id="usersTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Profile</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Full Name</th>
                                <th>User Type</th>
                                <th>Orders</th>
                                <th>Joined</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $pdo->query("
                                SELECT u.*, 
                                       COUNT(o.order_id) as order_count 
                                FROM users u 
                                LEFT JOIN orders o ON u.user_id = o.user_id 
                                GROUP BY u.user_id 
                                ORDER BY u.created_at DESC
                            ");
                            $users = $stmt->fetchAll();
                            
                            foreach ($users as $user):
                            ?>
                            <tr>
                                <td>#<?php echo $user['user_id']; ?></td>
                                <td>
                                    <img src="/assets/images/users/<?php echo $user['profile_picture']; ?>" 
                                         alt="<?php echo $user['full_name']; ?>"
                                         style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                                </td>
                                <td>
                                    <strong><?php echo $user['username']; ?></strong>
                                    <br><small class="text-muted">ID: <?php echo $user['user_id']; ?></small>
                                </td>
                                <td><?php echo $user['email']; ?></td>
                                <td><?php echo $user['full_name'] ?: 'Not set'; ?></td>
                                <td>
                                    <span class="badge <?php echo $user['user_type'] == 'admin' ? 'bg-danger' : 'bg-info'; ?>">
                                        <?php echo ucfirst($user['user_type']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-primary"><?php echo $user['order_count']; ?></span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <span class="badge bg-success">Active</span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="/admin/users?action=edit&id=<?php echo $user['user_id']; ?>" 
                                           class="btn btn-sm btn-outline">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button onclick="deleteUser(<?php echo $user['user_id']; ?>)" 
                                                class="btn btn-sm btn-danger"
                                                <?php echo $user['user_id'] == $_SESSION['user_id'] ? 'disabled title="Cannot delete yourself"' : ''; ?>>
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <a href="/profile?user=<?php echo $user['user_id']; ?>" 
                                           target="_blank"
                                           class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Search functionality
document.getElementById('searchUsers').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('#usersTable tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

// Delete user function
function deleteUser(userId) {
    if (confirm('Are you sure you want to delete this user? All their data will be permanently removed.')) {
        fetch(`/api/admin.php?action=delete_user&id=${userId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message);
                }
            });
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>