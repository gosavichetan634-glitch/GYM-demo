<div class="sidebar">
    <div class="brand">
        Star<span>FitnessClub</span>
    </div>
    <div class="sidebar-menu">
        <a href="admin_dashboard.php" class="menu-item">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>
        <a href="manage_members.php" class="menu-item">
            <i class="fas fa-users"></i>
            <span>Members</span>
        </a>
        <a href="manage_trainers.php" class="menu-item">
            <i class="fas fa-dumbbell"></i>
            <span>Trainers</span>
        </a>
        <a href="manage_plans.php" class="menu-item">
            <i class="fas fa-clipboard-list"></i>
            <span>Membership Plans</span>
        </a>
        <a href="manage_equipment.php" class="menu-item">
            <i class="fas fa-dumbbell"></i>
            <span>Equipment</span>
        </a>
        <a href="reports.php" class="menu-item">
            <i class="fas fa-chart-bar"></i>
            <span>Reports</span>
        </a>
        <a href="logout.php" class="menu-item">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</div>

<style>
.sidebar {
    width: 280px;
    background: var(--primary-color);
    padding: 20px;
    color: white;
    min-height: 100vh;
}

.brand {
    font-size: 24px;
    text-align: center;
    padding: 20px 0;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.brand span {
    color: var(--secondary-color);
}

.sidebar-menu {
    margin-top: 20px;
}

.menu-item {
    display: flex;
    align-items: center;
    padding: 15px;
    color: white;
    text-decoration: none;
    transition: all 0.3s ease;
    border-radius: 5px;
    margin-bottom: 5px;
}

.menu-item:hover {
    background: rgba(255,255,255,0.1);
}

.menu-item.active {
    background: var(--secondary-color);
}

.menu-item i {
    margin-right: 10px;
    width: 20px;
    text-align: center;
}

.menu-item span {
    font-size: 16px;
}
</style>