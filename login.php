<?php
session_start();
include "db.php";

// အသုံးပြုသူ ရိုက်ထည့်လိုက်သော Email နှင့် Password ကို ရယူခြင်း
$email = trim($_POST['email']);
$password = trim($_POST['password']);

// Login မှားယွင်းမှု အရေအတွက် (failed_attempts) ရှိမရှိ စစ်ဆေးပြီး မရှိလျှင် စတင်ခြင်း
if (!isset($_SESSION['failed_attempts'])) {
    $_SESSION['failed_attempts'] = 0;
    $_SESSION['lock_time'] = null;
}

// ၃ မိနစ် Lock ကျထားခြင်း ရှိမရှိ စစ်ဆေးခြင်း
if ($_SESSION['failed_attempts'] >= 3) {
    $time_passed = time() - $_SESSION['lock_time'];
    if ($time_passed < 180) {
        header("Location: index.php?error=" . urlencode("Too many failed attempts. Please try again after 3 minutes."));
        exit();
    } else {
        $_SESSION['failed_attempts'] = 0;
        $_SESSION['lock_time'] = null;
    }
}

// အသုံးပြုသူ Email ဒေတာဘေ့စ်တွင် ရှိမရှိ စစ်ဆေးရန် SQL ထုတ်ယူခြင်း
$sql = "SELECT * FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $user = $result->fetch_assoc();

    // ဒေတာဘေ့စ်ဘက်တွင် အကောင့် Lock ကျထားမှု ရှိမရှိ စစ်ဆေးခြင်း
    if ($user['failed_attempts'] >= 3) {
        $lock_sql = "SELECT TIMESTAMPDIFF(SECOND, last_failed_login, NOW()) AS seconds_passed FROM users WHERE email = ?";
        $lock_stmt = $conn->prepare($lock_sql);
        $lock_stmt->bind_param("s", $email);
        $lock_stmt->execute();
        $lock_data = $lock_stmt->get_result()->fetch_assoc();

        if ($lock_data['seconds_passed'] < 180) {
            header("Location: index.php?error=" . urlencode("Your account is locked for 3 minutes due to multiple failed attempts."));
            exit();
        } else {
            // ၃ မိနစ်ကျော်သွားပါက အမှားအရေအတွက်ကို ပြန်လည် 0 သို့ ဖြေလျှော့ပေးခြင်း
            $reset_db = "UPDATE users SET failed_attempts = 0, last_failed_login = NULL WHERE email = ?";
            $r_stmt = $conn->prepare($reset_db);
            $r_stmt->bind_param("s", $email);
            $r_stmt->execute();
            $user['failed_attempts'] = 0;
        }
    }

    // Password မှန်ကန်မှု ရှိမရှိ စစ်ဆေးခြင်း
    if (password_verify($password, $user['password'])) {
        // အောင်မြင်စွာ ဝင်ရောက်နိုင်ပါက အမှားမှတ်တမ်းများကို ရှင်းလင်းခြင်း
        $clear_sql = "UPDATE users SET failed_attempts = 0, last_failed_login = NULL WHERE email = ?";
        $c_stmt = $conn->prepare($clear_sql);
        $c_stmt->bind_param("s", $email);
        $c_stmt->execute();

        // Session တန်ဖိုးများ သတ်မှတ်ပေးခြင်း
        $_SESSION['failed_attempts'] = 0;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user'] = $user['first_name'];
        $_SESSION['role'] = $user['role'];

        // Role ပေါ်မူတည်၍ Redirect ခွဲခြားခြင်း
        if ($user['role'] === 'admin') {
            header("Location: admin_dashboard.php?success=" . urlencode("Welcome Admin, " . $user['first_name']));
        } else {
            header("Location: index.php?success=" . urlencode("Login successful! Welcome back, " . $user['first_name']));
        }
        exit();
    }
}

// Login အချက်အလက် မှားယွင်းပါက အမှားအရေအတွက် တိုးမြှင့်ခြင်း
$_SESSION['failed_attempts']++;
$current_attempts = $_SESSION['failed_attempts'];

if ($result->num_rows == 1) {
    $db_attempts = $user['failed_attempts'] + 1;
    $update_db = "UPDATE users SET failed_attempts = ?, last_failed_login = NOW() WHERE email = ?";
    $u_stmt = $conn->prepare($update_db);
    $u_stmt->bind_param("is", $db_attempts, $email);
    $u_stmt->execute();
    if ($db_attempts >= 3) {
        $current_attempts = $db_attempts;
    }
}

// ၃ ကြိမ်ပြည့်ပါက Lock ချရန်နှင့် မပြည့်သေးပါက ကျန်ရှိသည့်အကြိမ်အရေအတွက် ပြသရန်
if ($current_attempts >= 3) {
    $_SESSION['lock_time'] = time();
    header("Location: index.php?error=" . urlencode("Invalid login details. Your account/session is locked for 3 minutes."));
} else {
    header("Location: index.php?error=" . urlencode("Invalid email or password. Attempt $current_attempts of 3."));
}
exit();
?>