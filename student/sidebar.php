<?php
// የገጽ አድራሻውን በመለየት ንቁውን (Active) ሊንክ ለማሳየት
$current_page = basename($_SERVER['PHP_SELF']);
?>
<style>
    .sidebar { width: 280px; background: #0f172a; color: #cbd5e1; display: flex; flex-direction: column; height: 100vh; position: fixed; left: 0; top: 0; z-index: 100; box-shadow: 4px 0 10px rgba(0,0,0,0.05); }
    .sidebar-brand { padding: 20px 25px; background: #1e293b; color: #fff; font-size: 1.1rem; font-weight: 700; display: flex; align-items: center; gap: 10px; border-bottom: 1px solid #334155; }
    .sidebar-brand i { color: #38bdf8; font-size: 1.3rem; }
    .sidebar-menu { flex: 1; overflow-y: auto; padding: 15px 0; }
    .sidebar-menu::-webkit-scrollbar { width: 5px; }
    .sidebar-menu::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
    .menu-category { font-size: 0.7rem; text-transform: uppercase; color: #64748b; padding: 10px 25px 5px; font-weight: 700; letter-spacing: 0.5px; }
    .sidebar-menu a { display: flex; align-items: center; gap: 12px; padding: 10px 25px; color: #94a3b8; text-decoration: none; font-size: 0.85rem; font-weight: 500; transition: all 0.3s ease; }
    .sidebar-menu a:hover, .sidebar-menu a.active { color: #fff; background: #1e293b; border-left: 4px solid #38bdf8; }
    .sidebar-menu a i { width: 20px; text-align: center; font-size: 0.95rem; }
    .sidebar-footer { padding: 15px 25px; background: #090d16; border-top: 1px solid #1e293b; display: flex; justify-content: space-between; align-items: center; }
    .sidebar-footer span { font-size: 0.8rem; color: #94a3b8; }
    .logout-btn { color: #ef4444; text-decoration: none; font-size: 0.9rem; font-weight: 600; display: flex; align-items: center; gap: 6px; }
    .logout-btn:hover { color: #dc2626; }
</style>

<div class="sidebar">
    <div class="sidebar-brand">
        <i class="fa-solid fa-graduation-cap"></i>
        <span>የተማሪ ፖርታል (Student)</span>
    </div>
    
    <div class="sidebar-menu">
        <div class="menu-category">አጠቃላይ (Main)</div>
        <a href="dashboard.php" class="<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>"><i class="fa-solid fa-house"></i> Student Dashboard</a>
        <a href="profile.php" class="<?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>"><i class="fa-solid fa-user"></i> My Profile</a>
        <a href="academic_info.php" class="<?php echo ($current_page == 'academic_info.php') ? 'active' : ''; ?>"><i class="fa-solid fa-id-card"></i> My Academic Info</a>

        <div class="menu-category">ትምህርት (Academics)</div>
        <a href="course_registration.php" class="<?php echo ($current_page == 'course_registration.php') ? 'active' : ''; ?>"><i class="fa-solid fa-file-pen"></i> Course Registration</a>
        <a href="my_courses.php" class="<?php echo ($current_page == 'my_courses.php') ? 'active' : ''; ?>"><i class="fa-solid fa-book-open"></i> My Courses</a>
        <a href="timetable.php" class="<?php echo ($current_page == 'timetable.php') ? 'active' : ''; ?>"><i class="fa-solid fa-calendar-days"></i> My Timetable</a>
        <a href="attendance.php" class="<?php echo ($current_page == 'attendance.php') ? 'active' : ''; ?>"><i class="fa-solid fa-clipboard-user"></i> My Attendance</a>

        <div class="menu-category">ውጤት እና ማረጋገጫ (Results)</div>
        <a href="grades.php" class="<?php echo ($current_page == 'grades.php') ? 'active' : ''; ?>"><i class="fa-solid fa-star"></i> My Grades</a>
        <a href="gpa.php" class="<?php echo ($current_page == 'gpa.php') ? 'active' : ''; ?>"><i class="fa-solid fa-chart-line"></i> GPA / CGPA</a>
        <a href="transcript.php" class="<?php echo ($current_page == 'transcript.php') ? 'active' : ''; ?>"><i class="fa-solid fa-award"></i> My Transcript</a>

        <div class="menu-category">ፋይናንስ (Finance)</div>
        <a href="payments.php" class="<?php echo ($current_page == 'payments.php') ? 'active' : ''; ?>"><i class="fa-solid fa-wallet"></i> My Payments</a>
        <a href="receipts.php" class="<?php echo ($current_page == 'receipts.php') ? 'active' : ''; ?>"><i class="fa-solid fa-receipt"></i> My Receipts</a>

        <div class="menu-category">አገልግሎቶች (Services)</div>
        <a href="documents.php" class="<?php echo ($current_page == 'documents.php') ? 'active' : ''; ?>"><i class="fa-solid fa-folder-open"></i> Document Requests</a>
        <a href="announcements.php" class="<?php echo ($current_page == 'announcements.php') ? 'active' : ''; ?>"><i class="fa-solid fa-bullhorn"></i> Announcements</a>
        <a href="materials.php" class="<?php echo ($current_page == 'materials.php') ? 'active' : ''; ?>"><i class="fa-solid fa-download"></i> Course Materials</a>
        <a href="appeals.php" class="<?php echo ($current_page == 'appeals.php') ? 'active' : ''; ?>"><i class="fa-solid fa-circle-exclamation"></i> Complaints & Appeals</a>
    </div>

    <div class="sidebar-footer">
        <span>ብርሃን ኮሌጅ v1.0</span>
        <a href="logout.php" class="logout-btn"><i class="fa-solid fa-power-off"></i> ውጣ</a>
    </div>
</div>