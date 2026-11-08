<?php
// የገጽ አድራሻውን በመለየት ንቁውን (Active) ሊንክ ለማሳየት
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
        <i class="fa-solid fa-graduation-cap"></i>
        <span>ዲፓርትመንት ኃላፊ (Dept Head)</span>
    </div>
    
    <div class="admin-sidebar-menu">
        <div class="admin-menu-category">አጠቃላይ (Main)</div>
        <a href="dashboard.php" class="<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-house"></i> Dept Dashboard
        </a>
        <a href="profile.php" class="<?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-user-tie"></i> Department Profile
        </a>

        <div class="admin-menu-category">አስተዳደር (Management)</div>
        <a href="students.php" class="<?php echo ($current_page == 'students.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-users"></i> Department Students
        </a>
        <a href="teachers.php" class="<?php echo ($current_page == 'teachers.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-chalkboard-user"></i> Department Teachers
        </a>

        <div class="admin-menu-category">አካዳሚክ እና ምደባ (Academics & Allocation)</div>
        <a href="course_allocation.php" class="<?php echo ($current_page == 'course_allocation.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-tasks"></i> Course Allocation
        </a>
        <a href="teacher_workload.php" class="<?php echo ($current_page == 'teacher_workload.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-scale-balanced"></i> Teacher Workload
        </a>
        <a href="grade_approval.php" class="<?php echo ($current_page == 'grade_approval.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-clipboard-check"></i> Grade Approval
        </a>
        <a href="timetable_review.php" class="<?php echo ($current_page == 'timetable_review.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-calendar-days"></i> Timetable Review
        </a>

        <div class="admin-menu-category">ሪፖርት እና ማስታወቂያ (Reports & Comms)</div>
        <a href="department_reports.php" class="<?php echo ($current_page == 'department_reports.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-chart-line"></i> Department Reports
        </a>
        <a href="announcements.php" class="<?php echo ($current_page == 'announcements.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-bullhorn"></i> Announcements
        </a>
    </div>

    <div class="admin-sidebar-footer">
        <span>ብርሃን ኮሌጅ v1.0</span>
        <a href="logout.php" class="admin-logout-btn"><i class="fa-solid fa-power-off"></i> ውጣ</a>
    </div>
</div>