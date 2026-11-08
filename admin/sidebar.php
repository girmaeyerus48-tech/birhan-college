<?php
// የአድሚን ሳይድባር (Admin Sidebar)
$current_page = basename($_SERVER['PHP_SELF']);
?>
<style>
    .admin-sidebar {
        width: 280px !important;
        background: #0f172a !important;
        color: #cbd5e1 !important;
        display: flex !important;
        flex-direction: column !important;
        height: 100vh !important;
        position: fixed !important;
        left: 0 !important;
        top: 0 !important;
        z-index: 1000 !important;
        box-shadow: 4px 0 10px rgba(0,0,0,0.1) !important;
    }
    .admin-sidebar-brand {
        padding: 20px 25px !important;
        background: #1e293b !important;
        color: #fff !important;
        font-size: 1.1rem !important;
        font-weight: 700 !important;
        display: flex !important;
        align-items: center !important;
        gap: 10px !important;
        border-bottom: 1px solid #334155 !important;
    }
    .admin-sidebar-brand i {
        color: #38bdf8 !important;
        font-size: 1.3rem !important;
    }
    .admin-sidebar-menu {
        flex: 1 !important;
        overflow-y: auto !important;
        padding: 15px 0 !important;
    }
    .admin-sidebar-menu::-webkit-scrollbar {
        width: 5px !important;
    }
    .admin-sidebar-menu::-webkit-scrollbar-thumb {
        background: #334155 !important;
        border-radius: 10px !important;
    }
    .admin-menu-category {
        font-size: 0.7rem !important;
        text-transform: uppercase !important;
        color: #64748b !important;
        padding: 12px 25px 5px !important;
        font-weight: 700 !important;
        letter-spacing: 0.5px !important;
    }
    .admin-sidebar-menu a {
        display: flex !important;
        align-items: center !important;
        gap: 12px !important;
        padding: 10px 25px !important;
        color: #94a3b8 !important;
        text-decoration: none !important;
        font-size: 0.85rem !important;
        font-weight: 500 !important;
        transition: all 0.3s ease !important;
        border-left: 4px solid transparent !important;
    }
    .admin-sidebar-menu a:hover, 
    .admin-sidebar-menu a.active {
        color: #fff !important;
        background: #1e293b !important;
        border-left: 4px solid #38bdf8 !important;
    }
    .admin-sidebar-menu a i {
        width: 20px !important;
        text-align: center !important;
        font-size: 0.95rem !important;
    }
    .admin-sidebar-footer {
        padding: 15px 25px !important;
        background: #090d16 !important;
        border-top: 1px solid #1e293b !important;
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
    }
    .admin-sidebar-footer span {
        font-size: 0.8rem !important;
        color: #94a3b8 !important;
    }
    .admin-logout-btn {
        color: #ef4444 !important;
        text-decoration: none !important;
        font-size: 0.9rem !important;
        font-weight: 600 !important;
        display: flex !important;
        align-items: center !important;
        gap: 6px !important;
    }
    .admin-logout-btn:hover {
        color: #dc2626 !important;
    }
</style>

<div class="admin-sidebar">
    <div class="admin-sidebar-brand">
        <i class="fa-solid fa-shield-halved"></i>
        <span>ሲስተም አስተዳዳሪ (Admin)</span>
    </div>
    
    <div class="admin-sidebar-menu">
        <div class="admin-menu-category">አጠቃላይ (Main)</div>
        <a href="dashboard.php" class="<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-house"></i> Admin Dashboard
        </a>

        <div class="admin-menu-category">ተጠቃሚዎች እና ፈቃድ (Users)</div>
        <a href="users.php" class="<?php echo ($current_page == 'users.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-users-gear"></i> Users Management
        </a>
        <a href="roles_permissions.php" class="<?php echo ($current_page == 'roles_permissions.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-key"></i> Roles & Permissions
        </a>

        <div class="admin-menu-category">ሰው ሃብት እና ተማሪዎች (HR & Students)</div>
        <a href="students_management.php" class="<?php echo ($current_page == 'students_management.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-user-graduate"></i> Students Management
        </a>
        <a href="staff.php" class="<?php echo ($current_page == 'staff.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-chalkboard-user"></i> Teachers and Staff
        </a>

        <div class="admin-menu-category">አካዳሚክ አወቃቀር (Academics)</div>
        <a href="departments.php" class="<?php echo ($current_page == 'departments.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-building-columns"></i> Departments Management
        </a>
        <a href="edit-program.php" class="<?php echo ($current_page == 'edit-program.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-graduation-cap"></i> Programs Management
        </a>
        <a href="courses_management.php" class="<?php echo ($current_page == 'courses_management.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-book"></i> Courses Management
        </a>
        <a href="academic_semesters.php" class="<?php echo ($current_page == 'academic_semesters.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-calendar-days"></i> Years & Semesters
        </a>

        <div class="admin-menu-category">አስተዳደራዊ አገልግሎቶች (Operations)</div>
        <a href="academics.php" class="<?php echo ($current_page == 'academics.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-user-plus"></i> Admissions Management
        </a>
        <a href="announcements_management.php" class="<?php echo ($current_page == 'announcements_management.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-bullhorn"></i> Announcements Management
        </a>
        <a href="documents_gallery.php" class="<?php echo ($current_page == 'documents_gallery.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-folder-open"></i> Documents & Gallery
        </a>

        <div class="admin-menu-category3" style="font-size: 0.7rem; text-transform: uppercase; color: #64748b; padding: 12px 25px 5px; font-weight: 700;">ስርዓት እና ደህንነት (System)</div>
        <a href="reports.php" class="<?php echo ($current_page == 'reports.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-chart-pie"></i> Reports & Analytics
        </a>
        <a href="audit_logs.php" class="<?php echo ($current_page == 'audit_logs.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-clipboard-list"></i> Audit Logs
        </a>
        <a href="backup_recovery.php" class="<?php echo ($current_page == 'backup_recovery.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-database"></i> Backup & Recovery
        </a>
        <a href="system_settings.php" class="<?php echo ($current_page == 'system_settings.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-gear"></i> System Settings
        </a>
    </div>

    <div class="admin-sidebar-footer">
        <span>ብርሃን ኮሌጅ v1.0</span>
        <a href="logout.php" class="admin-logout-btn"><i class="fa-solid fa-power-off"></i> ውጣ</a>
    </div>
</div>