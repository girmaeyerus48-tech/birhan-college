<?php
// የገጽ አድራሻውን በመለየት ንቁውን (Active) ሊንክ ለማሳየት
$current_page = basename($_SERVER['PHP_SELF']);
?>
<style>
    :root {
        --sb-bg: #0b1329;
        --sb-brand-bg: #070c1b;
        --sb-hover: #1e293b;
        --sb-active-bg: rgba(37, 99, 235, 0.15);
        --sb-text: #94a3b8;
        --sb-text-light: #f8fafc;
        --sb-accent: #38bdf8;
        --sb-border: #1e293b;
    }

    .sidebar { 
        width: 280px; 
        background: var(--sb-bg); 
        color: var(--sb-text); 
        display: flex; 
        flex-direction: column; 
        height: 100vh; 
        position: fixed; 
        left: 0; 
        top: 0; 
        z-index: 100; 
        box-shadow: 4px 0 15px rgba(0,0,0,0.1); 
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .sidebar-brand { 
        padding: 22px 25px; 
        background: var(--sb-brand-bg); 
        color: var(--sb-text-light); 
        font-size: 1.1rem; 
        font-weight: 700; 
        display: flex; 
        align-items: center; 
        gap: 12px; 
        border-bottom: 1px solid var(--sb-border); 
    }

    .sidebar-brand i { 
        color: var(--sb-accent); 
        font-size: 1.4rem; 
    }

    .sidebar-menu { 
        flex: 1; 
        overflow-y: auto; 
        padding: 15px 0; 
    }

    .sidebar-menu::-webkit-scrollbar { 
        width: 5px; 
    }
    .sidebar-menu::-webkit-scrollbar-thumb { 
        background: var(--sb-border); 
        border-radius: 10px; 
    }

    .menu-category { 
        font-size: 0.72rem; 
        text-transform: uppercase; 
        color: #64748b; 
        padding: 15px 25px 6px; 
        font-weight: 700; 
        letter-spacing: 0.8px; 
    }

    .sidebar-menu a { 
        display: flex; 
        align-items: center; 
        gap: 14px; 
        padding: 11px 25px; 
        color: var(--sb-text); 
        text-decoration: none; 
        font-size: 0.88rem; 
        font-weight: 500; 
        transition: all 0.25s ease; 
        border-left: 4px solid transparent;
        margin: 2px 0;
    }

    .sidebar-menu a i { 
        width: 22px; 
        text-align: center; 
        font-size: 1rem; 
        transition: transform 0.2s;
    }

    .sidebar-menu a:hover { 
        color: var(--sb-text-light); 
        background: var(--sb-hover); 
        border-left-color: var(--sb-accent); 
    }

    .sidebar-menu a:hover i {
        transform: scale(1.1);
    }

    .sidebar-menu a.active { 
        color: var(--sb-text-light); 
        background: var(--sb-active-bg); 
        border-left-color: #2563eb; 
        font-weight: 600;
    }
    
    .sidebar-menu a.active i {
        color: var(--sb-accent);
    }

    .sidebar-footer { 
        padding: 16px 25px; 
        background: var(--sb-brand-bg); 
        border-top: 1px solid var(--sb-border); 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
    }

    .sidebar-footer span { 
        font-size: 0.8rem; 
        color: #64748b; 
    }

    .logout-btn { 
        color: #f87171; 
        text-decoration: none; 
        font-size: 0.88rem; 
        font-weight: 600; 
        display: flex; 
        align-items: center; 
        gap: 6px; 
        transition: color 0.2s;
    }

    .logout-btn:hover { 
        color: #ef4444; 
    }
</style>

<div class="sidebar">
    <div class="sidebar-brand">
        <i class="fa-solid fa-chalkboard-user"></i>
        <span>የመምህራን ፖርታል (Teacher)</span>
    </div>
    
    <div class="sidebar-menu">
        <div class="menu-category">አጠቃላይ (Main)</div>
        <a href="dashboard.php" class="<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>"><i class="fa-solid fa-house"></i> Teacher Dashboard</a>
        <a href="my_courses.php" class="<?php echo ($current_page == 'my_courses.php') ? 'active' : ''; ?>"><i class="fa-solid fa-book-open"></i> My Teaching Courses</a>
        <a href="class_lists.php" class="<?php echo ($current_page == 'class_lists.php') ? 'active' : ''; ?>"><i class="fa-solid fa-users"></i> My Class Lists</a>

        <div class="menu-category">አካዳሚክ እና ግምገማ (Academics)</div>
        <a href="attendance.php" class="<?php echo ($current_page == 'attendance.php') ? 'active' : ''; ?>"><i class="fa-solid fa-clipboard-user"></i> Attendance Management</a>
        <a href="grades.php" class="<?php echo ($current_page == 'grades.php') ? 'active' : ''; ?>"><i class="fa-solid fa-pen-to-square"></i> Grade Entry</a>
        <a href="grade_submission.php" class="<?php echo ($current_page == 'grade_submission.php') ? 'active' : ''; ?>"><i class="fa-solid fa-file-shield"></i> Grade Submission Status</a>
        <a href="assignments.php" class="<?php echo ($current_page == 'assignments.php') ? 'active' : ''; ?>"><i class="fa-solid fa-file-lines"></i> Assignments</a>

        <div class="menu-category">ሰነዶች እና መርሐ ግብር (Resources)</div>
        <a href="materials.php" class="<?php echo ($current_page == 'materials.php') ? 'active' : ''; ?>"><i class="fa-solid fa-cloud-arrow-up"></i> Course Materials</a>
        <a href="timetable.php" class="<?php echo ($current_page == 'timetable.php') ? 'active' : ''; ?>"><i class="fa-solid fa-calendar-days"></i> Class Timetable</a>
        <a href="exam_schedule.php" class="<?php echo ($current_page == 'exam_schedule.php') ? 'active' : ''; ?>"><i class="fa-solid fa-clock"></i> Examination Schedule</a>

        <div class="menu-category">ሪፖርት እና ማስታወቂያ (Reports)</div>
        <a href="performance_reports.php" class="<?php echo ($current_page == 'performance_reports.php') ? 'active' : ''; ?>"><i class="fa-solid fa-chart-line"></i> Student Performance Reports</a>
        <a href="notifications.php" class="<?php echo ($current_page == 'notifications.php') ? 'active' : ''; ?>"><i class="fa-solid fa-bell"></i> Notifications</a>
    </div>

    <div class="sidebar-footer">
        <span>ብርሃን ኮሌጅ v1.0</span>
        <a href="logout.php" class="logout-btn"><i class="fa-solid fa-power-off"></i> ውጣ</a>
    </div>
</div>