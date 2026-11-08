<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$success = $error = '';

// 1. የተማሪዎች ሠንጠረዥ ማረጋገጫ (dob እና academic_year አምዶችን ጨምሮ)
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS students (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id VARCHAR(50) UNIQUE NOT NULL,
        reference_code VARCHAR(100),
        full_name VARCHAR(150) NOT NULL,
        gender VARCHAR(20),
        dob DATE,
        phone VARCHAR(50),
        email VARCHAR(100),
        department VARCHAR(100),
        year_level VARCHAR(50),
        academic_year VARCHAR(50),
        gpa DECIMAL(3,2) DEFAULT 0.00,
        username VARCHAR(100) UNIQUE,
        password VARCHAR(255),
        document_path VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // አምዶች ከዚህ ቀደም ከሌሉ በALTER ማስተካከል
    $pdo->exec("ALTER TABLE students ADD COLUMN IF NOT EXISTS gender VARCHAR(20)");
    $pdo->exec("ALTER TABLE students ADD COLUMN IF NOT EXISTS dob DATE");
    $pdo->exec("ALTER TABLE students ADD COLUMN IF NOT EXISTS phone VARCHAR(50)");
    $pdo->exec("ALTER TABLE students ADD COLUMN IF NOT EXISTS email VARCHAR(100)");
    $pdo->exec("ALTER TABLE students ADD COLUMN IF NOT EXISTS academic_year VARCHAR(50)");
    $pdo->exec("ALTER TABLE students ADD COLUMN IF NOT EXISTS username VARCHAR(100) UNIQUE");
    $pdo->exec("ALTER TABLE students ADD COLUMN IF NOT EXISTS password VARCHAR(255)");
    $pdo->exec("ALTER TABLE students ADD COLUMN IF NOT EXISTS document_path VARCHAR(255)");
} catch (PDOException $e) {}

// 2. የኢትዮጵያ አቆጣጠር 2019 ዓ.ም መሠረት አድርጎ አውቶማቲክ ስቱደንት አይዲ ማመንጨት
$ethiopian_year = '2019'; 

$next_seq = 1;
try {
    $stmt = $pdo->query("SELECT MAX(CAST(SUBSTRING_INDEX(student_id, '/', -1) AS UNSIGNED)) as max_seq FROM students WHERE student_id LIKE 'BC/" . $ethiopian_year . "/%'");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row && $row['max_seq'] !== null) {
        $next_seq = intval($row['max_seq']) + 1;
    }
} catch (PDOException $e) {}

$next_student_id = 'BC/' . $ethiopian_year . '/' . str_pad($next_seq, 3, '0', STR_PAD_LEFT);

// 3. ተማሪ ሲመዘገብ (dob እና academic_year ን ጨምሮ መቀበል እና መመዝገብ)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student'])) {
    $student_id = trim($_POST['student_id'] ?? '');
    $reference_code = trim($_POST['reference_code'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $dob = trim($_POST['date_of_birth'] ?? NULL);
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $year_level = trim($_POST['year_level'] ?? '');
    $academic_year = trim($_POST['academic_year'] ?? '2019 ዓ.ም');
    $gpa = floatval($_POST['gpa'] ?? 0);
    $document_path = trim($_POST['document_path'] ?? '');

    $username = $student_id;
    $default_password = '123456';
    $hashed_password = password_hash($default_password, PASSWORD_BCRYPT);

    if (empty($reference_code)) {
        $reference_code = 'REF-' . rand(10000, 99999);
    }

    if (!empty($student_id) && !empty($full_name)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO students (student_id, reference_code, full_name, gender, dob, phone, email, department, year_level, academic_year, gpa, username, password, document_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $student_id, 
                $reference_code, 
                $full_name, 
                $gender, 
                ($dob ?: null), 
                $phone, 
                $email, 
                $department, 
                $year_level, 
                $academic_year, 
                $gpa, 
                $username, 
                $hashed_password, 
                $document_path
            ]);
            
            $success = "የተማሪው መረጃ በተሳካ ሁኔታ ተመዝግቧል! <br><strong>Student ID:</strong> {$student_id} <br><strong>Username:</strong> {$username} <br><strong>Default Password:</strong> 123456";
            
            echo "<script>setTimeout(function(){ window.location.href='applications.php'; }, 3000);</script>";
        } catch (PDOException $e) {
            $error = "ምዝገባው አልተሳካም (የተማሪው ID/Username ቀደም ብሎ ተይዟል): " . $e->getMessage();
        }
    } else {
        $error = "እባክዎ የተማሪ መለያ ቁጥር እና ሙሉ ስም ይሙሉ!";
    }
}

// 4. ያልተመዘገቡ አመልካቾችን ማምጣት
$applicants = [];
try {
    $query = "SELECT a.* FROM admissions a 
              LEFT JOIN students s ON a.reference_code = s.reference_code OR a.id = s.reference_code 
              WHERE s.id IS NULL 
              ORDER BY a.id DESC";
    $stmt = $pdo->query($query);
    $applicants = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    try {
        $query = "SELECT a.* FROM applications a 
                  LEFT JOIN students s ON a.reference_code = s.reference_code OR a.id = s.reference_code 
                  WHERE s.id IS NULL 
                  ORDER BY a.id DESC";
        $stmt = $pdo->query($query);
        $applicants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $ex) {}
}

// 5. የተመዘገቡ ተማሪዎች ዝርዝር
$students = [];
try {
    $students = $pdo->query("SELECT * FROM students ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}
?>
<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <title>የተማሪና አካዳሚክ መረጃ - ብርሃን ኮሌጅ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f7fc; display: flex; height: 100vh; overflow: hidden; }
        .main-container { flex: 1; display: flex; flex-direction: column; margin-left: 280px; overflow: hidden; width: calc(100% - 280px); }
        .topbar { background: #fff; padding: 18px 30px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; }
        .topbar h3 { font-size: 1.15rem; color: #0f172a; font-weight: 700; }
        .content-body { padding: 30px; overflow-y: auto; flex: 1; }
        .card { background: #fff; padding: 25px; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 25px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); }
        .card h3 { color: #0f172a; margin-bottom: 18px; font-size: 1.05rem; display: flex; align-items: center; gap: 10px; }
        .card h3 i { color: #2563eb; }
        label { font-size: 0.85rem; font-weight: 600; color: #475569; display: block; margin-bottom: 4px; }
        input, select { width: 100%; padding: 10px 12px; margin-top: 2px; margin-bottom: 15px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem; outline: none; }
        input:focus, select:focus { border-color: #2563eb; }
        button[type="submit"] { background: #2563eb; color: #fff; border: none; padding: 11px 22px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 8px; }
        button[type="submit"]:hover { background: #1d4ed8; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; white-space: nowrap; }
        th, td { padding: 12px 15px; border-bottom: 1px solid #e2e8f0; text-align: left; font-size: 0.88rem; }
        th { background: #f8fafc; color: #475569; font-weight: 600; }
        .alert-success { background: #d1fae5; color: #065f46; padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; line-height: 1.6; }
        .alert-error { background: #fee2e2; color: #991b1b; padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; }
        .doc-link { color: #2563eb; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 5px; }
        .doc-link:hover { text-decoration: underline; }
    </style>
    <script>
        function fillApplicantData(selectElement) {
            var selectedOption = selectElement.options[selectElement.selectedIndex];
            if(selectedOption.value !== "") {
                document.getElementById('full_name').value = selectedOption.getAttribute('data-name');
                document.getElementById('reference_code').value = selectedOption.getAttribute('data-ref');
                document.getElementById('gender').value = selectedOption.getAttribute('data-gender');
                document.getElementById('date_of_birth').value = selectedOption.getAttribute('data-dob');
                document.getElementById('phone').value = selectedOption.getAttribute('data-phone');
                document.getElementById('email').value = selectedOption.getAttribute('data-email');
                document.getElementById('department').value = selectedOption.getAttribute('data-prog');
                
                var docPath = selectedOption.getAttribute('data-doc');
                document.getElementById('document_path').value = docPath;
                
                var docPreview = document.getElementById('doc_preview');
                if(docPath) {
                    docPreview.innerHTML = '<a href="../' + docPath + '" target="_blank" class="doc-link"><i class="fa-solid fa-file-pdf"></i> የተሰቀለውን ዶክመንት ክፈት / ይመልከቱ</a>';
                } else {
                    docPreview.innerHTML = '<span style="color:#94a3b8; font-size:0.85rem;">ምንም ዶክመንት አልተሰቀለም</span>';
                }
            }
        }
    </script>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-container">
        <div class="topbar">
            <h3>የተማሪ እና አካዳሚክ መረጃ (Student & Academics)</h3>
            <span style="font-weight: 600; color: #2563eb;"><i class="fa-solid fa-graduation-cap"></i> 2019 ዓ.ም Registration</span>
        </div>
        <div class="content-body">
            <?php if(!empty($success)): ?><div class="alert-success"><i class="fa-solid fa-circle-check"></i> <?php echo $success; ?></div><?php endif; ?>
            <?php if(!empty($error)): ?><div class="alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?></div><?php endif; ?>

            <div class="card">
                <h3><i class="fa-solid fa-user-plus"></i> አመልካቹን ፈልጎ እንደ ተማሪ መዝግብ (2019 Academic Year)</h3>
                <form method="POST">
                    <input type="hidden" id="document_path" name="document_path">
                    
                    <div style="margin-bottom: 15px;">
                        <label><strong>ከተመዝጋቢዎች/አመልካቾች መምረጫ (Select Applicant)</strong></label>
                        <select onchange="fillApplicantData(this)">
                            <option value="">-- ከማመልከቻዎች ዝርዝር ውስጥ ይምረጡ --</option>
                            <?php if(empty($applicants)): ?>
                                <option value="" disabled>አዲስ የተመዘገበ አመልካች የለም (ሁሉም ተመዝግበዋል)</option>
                            <?php else: ?>
                                <?php foreach($applicants as $app): ?>
                                    <?php 
                                        $ref = $app['reference_code'] ?? $app['id'] ?? '';
                                        $name = $app['full_name'] ?? $app['name'] ?? '';
                                        $gender = $app['gender'] ?? $app['sex'] ?? '';
                                        $dob = $app['date_of_birth'] ?? $app['dob'] ?? '';
                                        $phone = $app['phone'] ?? $app['phone_number'] ?? '';
                                        $email = $app['email'] ?? '';
                                        $prog = $app['program'] ?? $app['department'] ?? '';
                                        $doc = $app['document_path'] ?? $app['file_path'] ?? $app['document'] ?? '';
                                    ?>
                                    <option value="<?php echo $ref; ?>" 
                                            data-name="<?php echo htmlspecialchars($name); ?>" 
                                            data-ref="<?php echo htmlspecialchars($ref); ?>" 
                                            data-gender="<?php echo htmlspecialchars($gender); ?>" 
                                            data-dob="<?php echo htmlspecialchars($dob); ?>" 
                                            data-phone="<?php echo htmlspecialchars($phone); ?>" 
                                            data-email="<?php echo htmlspecialchars($email); ?>" 
                                            data-prog="<?php echo htmlspecialchars($prog); ?>" 
                                            data-doc="<?php echo htmlspecialchars($doc); ?>">
                                        <?php echo htmlspecialchars($name); ?> (Ref: <?php echo htmlspecialchars($ref); ?> - <?php echo htmlspecialchars($prog); ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 15px;">
                        <div>
                            <label>የተማሪ መለያ ቁጥር (Auto Student ID - 2019)</label>
                            <input type="text" name="student_id" value="<?php echo $next_student_id; ?>" style="background:#f1f5f9; font-weight:bold; color:#2563eb;" required>
                        </div>
                        <div>
                            <label>የመግቢያ ሪፈረንስ ኮድ (Reference Code)</label>
                            <input type="text" id="reference_code" name="reference_code" placeholder="ከላይ ይመረጣል ወይም በእጅ ይጻፋል" required>
                        </div>
                        <div>
                            <label>የተማሪ ሙሉ ስም (Full Name)</label>
                            <input type="text" id="full_name" name="full_name" required>
                        </div>
                        <div>
                            <label>ፆታ (Gender)</label>
                            <select id="gender" name="gender" required>
                                <option value="">ፆታ ይምረጡ</option>
                                <option value="Male">ወንድ (Male)</option>
                                <option value="Female">ሴት (Female)</option>
                            </select>
                        </div>
                        <div>
                            <label>የትውልድ ቀን (Date of Birth)</label>
                            <input type="date" id="date_of_birth" name="date_of_birth">
                        </div>
                        <div>
                            <label>ስልክ ቁጥር (Phone Number)</label>
                            <input type="text" id="phone" name="phone" placeholder="0911234567">
                        </div>
                        <div>
                            <label>ኢሜይል (Email Address)</label>
                            <input type="email" id="email" name="email" placeholder="example@gmail.com">
                        </div>
                        <div>
                            <label>የሚማሩበት የትምህርት ፕሮግራም (Program)</label>
                            <input type="text" id="department" name="department" placeholder="ምሳሌ፦ Accounting">
                        </div>
                        <div>
                            <label>የክፍል ደረጃ / ዓመት (Year Level)</label>
                            <select name="year_level" required>
                                <option value="">የክፍል ደረጃ ይምረጡ</option>
                                <option value="1st Year (አንደኛ ዓመት)">1st Year (አንደኛ ዓመት)</option>
                                <option value="2nd Year (ሁለተኛ ዓመት)">2nd Year (ሁለተኛ ዓመት)</option>
                                <option value="3rd Year (ሶስተኛ ዓመት)">3rd Year (ሶስተኛ ዓመት)</option>
                                <option value="4th Year (አራተኛ ዓመት)">4th Year (አራተኛ ዓመት)</option>
                                <option value="Extension / Continuing">Extension / Continuing</option>
                            </select>
                        </div>
                        <div>
                            <label>አካዳሚክ ዓመት (Academic Year)</label>
                            <input type="text" name="academic_year" value="2019 ዓ.ም" required>
                        </div>
                        <div style="grid-column: 1 / -1;">
                            <label>የተሰቀለ የትምህርት ማስረጃ / ዶክመንት</label>
                            <div id="doc_preview" style="padding:10px; background:#f8fafc; border:1px solid #cbd5e1; border-radius:6px; min-height:42px; display:flex; align-items:center;">
                                <span style="color:#94a3b8; font-size:0.85rem;">አመልካች ሲመረጥ ዶክመንቱ እዚህ ይታያል</span>
                            </div>
                        </div>
                    </div>
                    <button type="submit" name="add_student" style="margin-top: 15px;"><i class="fa-solid fa-user-plus"></i> እንደ ተማሪ መዝግብ (Generate Login)</button>
                </form>
            </div>

            <div class="card">
                <h3><i class="fa-solid fa-users"></i> የተመዘገቡ ተማሪዎች ዝርዝር (Students Table)</h3>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>ተ.ቁ</th>
                                <th>Student ID (Username)</th>
                                <th>ሪፈረንስ ኮድ</th>
                                <th>ሙሉ ስም</th>
                                <th>ፆታ</th>
                                <th>የትውልድ ቀን</th>
                                <th>ስልክ ቁጥር</th>
                                <th>ኢሜይል</th>
                                <th>ፕሮግራም</th>
                                <th>ደረጃ</th>
                                <th>አካዳሚክ ዓመት</th>
                                <th>የተሰቀለ ዶክመንት</th>
                                <th>የመግቢያ ሁኔታ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($students)): ?>
                                <tr><td colspan="13" style="text-align:center; color:#64748b; padding:25px;">ምንም ተማሪ አልተመዘገበም።</td></tr>
                            <?php else: ?>
                                <?php $i=1; foreach($students as $st): ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><strong><?php echo htmlspecialchars($st['student_id']); ?></strong></td>
                                    <td><span style="background:#e0f2fe; color:#0369a1; padding:3px 8px; border-radius:4px; font-size:0.8rem;"><?php echo htmlspecialchars($st['reference_code'] ?? 'N/A'); ?></span></td>
                                    <td><?php echo htmlspecialchars($st['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($st['gender'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($st['dob'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($st['phone'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($st['email'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($st['department']); ?></td>
                                    <td><?php echo htmlspecialchars($st['year_level']); ?></td>
                                    <td><?php echo htmlspecialchars($st['academic_year'] ?? '2019 ዓ.ም'); ?></td>
                                    <td>
                                        <?php if(!empty($st['document_path'])): ?>
                                            <a href="../<?php echo htmlspecialchars($st['document_path']); ?>" target="_blank" class="doc-link"><i class="fa-solid fa-file-arrow-down"></i> ዶክመንት ክፈት</a>
                                        <?php else: ?>
                                            <span style="color:#94a3b8; font-size:0.80rem;">የለም</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span style="background:#d1fae5; color:#065f46; padding:3px 8px; border-radius:4px; font-size:0.78rem; font-weight:600;"><i class="fa-solid fa-check-circle"></i> Login Active</span></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>