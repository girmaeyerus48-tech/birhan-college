<?php
require_once 'config/config.php';

$success = '';
$error = '';
$ref_code = '';

// 1. admissions ሠንጠረዥ መኖሩን ማረጋገጥ እና አዳዲስ አምዶችን በራስ-ሰር መጨመር (Alter Table)
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS admissions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        reference_code VARCHAR(100) UNIQUE NOT NULL,
        full_name VARCHAR(150) NOT NULL,
        email VARCHAR(150),
        phone VARCHAR(50) UNIQUE NOT NULL,
        gender VARCHAR(20),
        dob DATE,
        program VARCHAR(150) NOT NULL,
        document_path VARCHAR(255),
        status VARCHAR(30) DEFAULT 'Pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // ነባር ሠንጠረዥ ካለ አዳዲሶቹን አምዶች በራስ-ሰር ይጨምራል
    $pdo->exec("ALTER TABLE admissions ADD COLUMN IF NOT EXISTS gender VARCHAR(20)");
    $pdo->exec("ALTER TABLE admissions ADD COLUMN IF NOT EXISTS dob DATE");
    $pdo->exec("ALTER TABLE admissions ADD COLUMN IF NOT EXISTS document_path VARCHAR(255)");
} catch (Exception $e) {}

// 2. ፕሮግራሞችን ከዲፓርትመንት/ፕሮግራም ሠንጠረዥ ማምጣት
$programs_list = [];
try {
    $stmt = $pdo->query("SELECT title FROM programs");
    $programs_list = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {}

if (empty($programs_list)) {
    $programs_list = [
        'Accounting and Finance',
        'Computer Science / IT',
        'Management',
        'Marketing Management',
        'Economics'
    ];
}

// 3. ማመልከቻ ሲገባ (Form Submission)
if ($_SERVER['REQUEST_METHOD'] === 'POST' AND isset($_POST['submit_application'])) {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $dob = trim($_POST['dob'] ?? '');
    $program = trim($_POST['program'] ?? '');
    
    $document_path = '';

    if (!empty($full_name) AND !empty($phone) AND !empty($program) AND !empty($gender) AND !empty($dob)) {
        
        // ሀ. ስልክ ቁጥሩ ቀደም ብሎ መመዝገቡን ማረጋገጥ (Duplicate Check)
        $check_stmt = $pdo->prepare("SELECT id FROM admissions WHERE phone = ?");
        $check_stmt->execute([$phone]);
        
        if ($check_stmt->rowCount() > 0) {
            $error = "ይህ ስልክ ቁጥር ቀደም ብሎ በማመልከቻ ሰፍሯል! በአንድ ስልክ ቁጥር አንድ ጊዜ ብቻ ማመልከት ይቻላል።";
        } else {
            
            // ለ. የሰነድ ፋይል አፕሎድ ማስተዳደር (Document Upload)
            if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
                $file_tmp = $_FILES['document']['tmp_name'];
                $file_name = $_FILES['document']['name'];
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                
                $allowed_extensions = ['pdf', 'jpg', 'jpeg', 'png', 'docx'];
                
                if (in_array($file_ext, $allowed_extensions)) {
                    $upload_dir = 'uploads/documents/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    $new_file_name = 'DOC_' . time() . '_' . rand(1000, 9999) . '.' . $file_ext;
                    $document_path = $upload_dir . $new_file_name;
                    
                    move_uploaded_file($file_tmp, $document_path);
                } else {
                    $error = "የተፈቀዱ የፋይል አይነቶች PDF, JPG, PNG ወይም DOCX ብቻ ናቸው!";
                }
            }

            if (empty($error)) {
                // ሐ. ለየት ያለ ሪፈረንስ ኮድ ማመንጨት
                $ref_code = 'BC-' . (date('n') >= 9 ? (date('Y') - 8) : (date('Y') - 9)) . '-' . strtoupper(substr(uniqid(), -5));

                try {
                    $stmt = $pdo->prepare("INSERT INTO admissions (reference_code, full_name, email, phone, gender, dob, program, document_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$ref_code, $full_name, $email, $phone, $gender, $dob, $program, $document_path]);
                    
                    // መ. ኢሜይል ማስገቢያ ከተሞላ የሪፈረንስ ኮዱን መላክ
                    if (!empty($email) AND filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $to = $email;
                        $subject = "የብርሃን ኮሌጅ ማመልከቻ ሪፈረንስ ኮድ";
                        $message = "ሰላም ክቡር/ክቡርቲ " . $full_name . ",\n\n"
                                 . "ብርሃን ኮሌጅ ስለመረጡ እናመሰግናለን። ማመልከቻዎ እና የትምህርት ማስረጃዎ በተሳካ ሁኔታ ተቀብለናል።\n\n"
                                 . "የእርስዎ ማመልከቻ ሪፈረንስ ኮድ (Reference Code): " . $ref_code . "\n"
                                 . "የተመረጠው ፕሮግራም: " . $program . "\n\n"
                                 . "ይህንን ኮድ በመያዝ ምዝገባዎን መከታተል ይችላሉ።\n\n"
                                 . "ከክብርት ሰላምታ ጋር,\nብርሃን ኮሌጅ ሬጅስትራር";
                        
                        $headers = "From: no-reply@birhancollege.edu.et\r\n" .
                                   "Reply-to: support@birhancollege.edu.et\r\n" .
                                   "X-Mailer: PHP/" . phpversion();
                        
                        @mail($to, $subject, $message, $headers);
                    }
                    
                    $success = "ማመልከቻዎ ከነሰነዱ በተሳካ ሁኔታ ተመዝግቧል! ሪፈረንስ ኮድዎ ከዚህ በታች ተሰጥቷል።";
                } catch (Exception $e) {
                    $error = "ማመልከቻውን ማስገባት አልተቻለም: " . $e->getMessage();
                }
            }
        }
    } else {
        $error = "እባክዎ ሙሉ ስም፣ ስልክ ቁጥር፣ ጾታ፣ የትውልድ ቀን እና የትምህርት ፕሮግራም በትክክል ይሙሉ!";
    }
}
?>
<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ተማሪዎች ማመልከቻ - ብርሃን ኮሌጅ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f7fc; display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 20px; }
        .container { background: #fff; width: 100%; max-width: 650px; padding: 30px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; margin: 20px 0; }
        h2 { color: #0f172a; margin-bottom: 20px; text-align: center; font-size: 1.5rem; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: 600; color: #334155; margin-bottom: 5px; font-size: 0.9rem; }
        input, select { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.95rem; outline: none; background: #f8fafc; }
        input:focus, select:focus { border-color: #2563eb; background: #fff; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        @media(max-width: 600px) { .grid-2 { grid-template-columns: 1fr; } }
        button { width: 100%; background: #2563eb; color: #fff; border: none; padding: 12px; border-radius: 6px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: background 0.2s; margin-top: 10px; }
        button:hover { background: #1d4ed8; }
        .alert-success { background: #d1fae5; color: #065f46; padding: 15px; border-radius: 6px; margin-bottom: 20px; text-align: center; }
        .alert-error { background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 6px; margin-bottom: 20px; }
        .ref-box { background: #f8fafc; border: 2px dashed #38bdf8; padding: 15px; text-align: center; border-radius: 8px; margin-top: 15px; }
        .ref-code { font-size: 1.3rem; font-weight: bold; color: #2563eb; letter-spacing: 1px; }
        .back-link { display: block; text-align: center; margin-top: 20px; color: #64748b; text-decoration: none; font-size: 0.9rem; }
        .back-link:hover { color: #2563eb; }
    </style>
</head>
<body>

    <div class="container">
        <h2>ብርሃን ኮሌጅ - የመግቢያ ማመልከቻ ፎርም</h2>

        <?php if(!empty($success)): ?>
            <div class="alert-success">
                <?php echo $success; ?>
                <div class="ref-box">
                    የእርስዎ ማመልከቻ ሪፈረንስ ኮድ (Reference Code):<br>
                    <span class="ref-code"><?php echo htmlspecialchars($ref_code); ?></span>
                    <p style="font-size:0.8rem; color:#64748b; margin-top:5px;">ይህንን ኮድ እና የኢሜይል ማስታወሻ በመያዝ ምዝገባዎን መከታተል ይችላሉ።</p>
                </div>
            </div>
        <?php endif; ?>

        <?php if(!empty($error)): ?>
            <div class="alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>ሙሉ ስም (Full Name)</label>
                <input type="text" name="full_name" placeholder="ሙሉ ስምዎን ያስገቡ" required>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label>ጾታ (Gender)</label>
                    <select name="gender" required>
                        <option value="">-- ጾታ ይምረጡ --</option>
                        <option value="Male">ወንድ (Male)</option>
                        <option value="Female">ሴት (Female)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>የትውልድ ቀን (Date of Birth)</label>
                    <input type="date" name="dob" required>
                </div>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label>ስልክ ቁጥር (Phone Number)</label>
                    <input type="text" name="phone" placeholder="ምሳሌ፦ 0911234567" required>
                </div>

                <div class="form-group">
                    <label>ኢሜይል (Email Address)</label>
                    <input type="email" name="email" placeholder="example@gmail.com" required>
                </div>
            </div>

            <div class="form-group">
                <label>የሚማሩበት የትምህርት ፕሮግራም (Select Program)</label>
                <select name="program" required>
                    <option value="">-- ፕሮግራም ይምረጡ --</option>
                    <?php foreach($programs_list as $prog): ?>
                        <option value="<?php echo htmlspecialchars($prog); ?>"><?php echo htmlspecialchars($prog); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>የትምህርት ማስረጃ ሰነድ አታች ያድርጉ (Transcript / Document - PDF, JPG, PNG)</label>
                <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png,.docx" required>
            </div>

            <button type="submit" name="submit_application"><i class="fa-solid fa-paper-plane"></i> ማመልከቻ ላክ (Submit Application)</button>
        </form>

        <a href="index.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> ወደ መነሻ ገጽ ተመለስ</a>
    </div>

</body>
</html>