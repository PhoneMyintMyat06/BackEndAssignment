<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start(); 
include "db.php";

$cookie_accepted = isset($_COOKIE['foodfusion_cookie_accepted']) ? true : false;

// မက်ဆေ့ချ် အသစ်ပို့ခြင်းကို လက်ခံရန်
$success_msg = $error_msg = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['send_message'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    if (!empty($name) && !empty($email) && !empty($subject) && !empty($message)) {
        $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message, status) VALUES (?, ?, ?, ?, 'Pending')");
        $stmt->bind_param("ssss", $name, $email, $subject, $message);
        if ($stmt->execute()) {
            $success_msg = "Your message has been sent successfully!";
        } else {
            $error_msg = "Failed to send message. Please try again.";
        }
        $stmt->close();
    } else {
        $error_msg = "All fields are required.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Contact Us - FoodFusion</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>

<body>

<nav>
    <a href="index.php" class="brand-logo">FoodFusion</a>
    <div class="nav-links">
        <a href="index.php">Home</a>
        <a href="about.php">About Us</a>
        <a href="recipes.php">Recipe Collection</a>
        <a href="contact.php" style="color: var(--accent-color);">Contact Us</a>
    </div>
    <span class="user-area">
        <?php if(isset($_SESSION['user_id'])): ?>
            Welcome, <?php echo htmlspecialchars($_SESSION['user']); ?>
            <a href="logout.php" class="cta-btn">Logout</a>
        <?php else: ?>
            <a href="index.php" class="cta-btn">Login / Register</a>
        <?php endif; ?>
    </span>
</nav>

<div class="hero" style="padding: 40px 20px; text-align: center;">
    <h1>Contact Us</h1>
    <p>Have questions or feedback? Reach out to us or check your message status below.</p>
</div>

<div class="main-container" style="display: flex; flex-direction: column; max-width: 900px; margin: 30px auto; padding: 0 20px;">
    
    <!-- မက်ဆေ့ချ် အသစ်ပို့ရန် Form -->
    <div style="background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 40px;">
        <h3 style="margin-bottom: 20px;">Send Us a Message</h3>

        <?php if(!empty($success_msg)): ?>
            <div style="background: #C6F6D5; color: #22543D; padding: 10px; border-radius: 4px; margin-bottom: 15px;"><?php echo $success_msg; ?></div>
        <?php endif; ?>
        <?php if(!empty($error_msg)): ?>
            <div style="background: #FFF5F5; color: #C53030; padding: 10px; border-radius: 4px; margin-bottom: 15px;"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <form action="contact.php" method="POST">
            <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                <input type="text" name="name" placeholder="Your Name" required style="flex: 1; padding: 10px; border: 1px solid #CBD5E0; border-radius: 4px;">
                <input type="email" name="email" placeholder="Your Email" 
                    value="<?php 
                        // Login ဝင်ထားလျှင် Email ကို အလိုအလျောက် ဖြည့်ပေးမည်
                        if(isset($_SESSION['user_id'])) {
                            $uid = $_SESSION['user_id'];
                            $u_q = $conn->prepare("SELECT email FROM users WHERE id = ?");
                            $u_q->bind_param("i", $uid);
                            $u_q->execute();
                            $u_res = $u_q->get_result();
                            if($u_row = $u_res->fetch_assoc()) {
                                echo htmlspecialchars($u_row['email']);
                            }
                        }
                    ?>" required style="flex: 1; padding: 10px; border: 1px solid #CBD5E0; border-radius: 4px;">
            </div>
            
            <!-- Subject Dropdown Menu -->
            <div style="margin-bottom: 15px;">
                <select name="subject" required style="width: 100%; padding: 10px; border: 1px solid #CBD5E0; border-radius: 4px; background: #fff;">
                    <option value="" disabled selected>Select a subject...</option>
                    <option value="General Inquiry">General Inquiry</option>
                    <option value="Feedback">Feedback</option>
                    <option value="Recipe Request">Recipe Request</option>
                    <option value="Technical Support">Technical Support</option>
                </select>
            </div>

            <div style="margin-bottom: 15px;">
                <textarea name="message" placeholder="Your Message..." rows="4" required style="width: 100%; padding: 10px; border: 1px solid #CBD5E0; border-radius: 4px;"></textarea>
            </div>
            <button type="submit" name="send_message" style="background: var(--accent-color); color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-weight: bold;">Send Message</button>
        </form>
    </div>

    <!-- Login ဝင်ထားပါက ပို့ခဲ့သော မက်ဆေ့ချ်များနှင့် Admin Reply များကို ပြရန် -->
    <?php if (isset($_SESSION['user_id'])): ?>
        <?php
        $uid = $_SESSION['user_id'];
        $user_sql = "SELECT email FROM users WHERE id = ?";
        $stmt_u = $conn->prepare($user_sql);
        $stmt_u->bind_param("i", $uid);
        $stmt_u->execute();
        $res_u = $stmt_u->get_result();
        
        if ($row_u = $res_u->fetch_assoc()) {
            $user_email = $row_u['email'];

            $msg_sql = "SELECT * FROM contact_messages WHERE email = ? ORDER BY created_at DESC";
            $stmt_m = $conn->prepare($msg_sql);
            $stmt_m->bind_param("s", $user_email);
            $stmt_m->execute();
            $messages_result = $stmt_m->get_result();
            
            if ($messages_result->num_rows > 0) {
                echo '<div style="background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">';
                echo '<h3>Your Sent Messages & Admin Replies</h3>';
                echo '<div style="overflow-x: auto;"><table style="width: 100%; margin-top: 15px; border-collapse: collapse;">';
                echo '<tr style="background: #f7fafc; border-bottom: 2px solid #e2e8f0; text-align: left;">';
                echo '<th style="padding: 12px;">Subject & Message</th>';
                echo '<th style="padding: 12px;">Status</th>';
                echo '<th style="padding: 12px;">Admin Reply</th>';
                echo '</tr>';
                
                while ($msg = $messages_result->fetch_assoc()) {
                    echo '<tr style="border-bottom: 1px solid #e2e8f0;">';
                    echo '<td style="padding: 12px;">';
                    echo '<strong>' . htmlspecialchars($msg['subject']) . '</strong><br>';
                    echo '<span style="font-size: 14px; color: #4a5568;">' . nl2br(htmlspecialchars($msg['message'])) . '</span><br>';
                    echo '<small style="color: #a0aec0;">Sent on: ' . $msg['created_at'] . '</small>';
                    echo '</td>';
                    
                    echo '<td style="padding: 12px;">';
                    if ($msg['status'] == 'Replied') {
                        echo '<span style="background: #C6F6D5; color: #22543D; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;">Replied</span>';
                    } else {
                        echo '<span style="background: #FEFCBF; color: #B7791F; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;">Pending</span>';
                    }
                    echo '</td>';
                    
                    echo '<td style="padding: 12px;">';
                    if (!empty($msg['reply'])) {
                        echo '<div style="background: #edf2f7; padding: 10px; border-radius: 6px; font-size: 14px;">';
                        echo '<p style="margin: 0 0 5px 0; color: #2d3748;">' . nl2br(htmlspecialchars($msg['reply'])) . '</p>';
                        echo '<small style="color: #718096; font-size: 11px;">Replied at: ' . $msg['replied_at'] . '</small>';
                        echo '</div>';
                    } else {
                        echo '<span style="color: #a0aec0; font-style: italic;">No reply yet.</span>';
                    }
                    echo '</td>';
                    
                    echo '</tr>';
                }
                echo '</table></div>';
                echo '</div>';
            }
        }
        ?>
    <?php else: ?>
        <div style="background: #edf2f7; padding: 20px; border-radius: 8px; text-align: center;">
            <p style="margin: 0; color: #4a5568;">Please <a href="index.php" style="color: var(--accent-color); font-weight: bold; text-decoration: none;">login</a> to view your previously sent messages and admin replies.</p>
        </div>
    <?php endif; ?>

</div>

<footer style="text-align: center; padding: 20px; margin-top: 40px; color: #A0AEC0; font-size: 12px;">
    &copy; 2026 FoodFusion. All Rights Reserved.
</footer>

</body>
</html>