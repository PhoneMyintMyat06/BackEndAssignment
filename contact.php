<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start(); 
include "db.php";

// Check cookie acceptance on the PHP side as well.
$cookie_accepted = isset($_COOKIE['foodfusion_cookie_accepted']) ? true : false;

$success_msg = $error_msg = "";
$contact_user = null;
$contact_user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
if ($contact_user_id > 0) {
    $user_stmt = $conn->prepare("SELECT first_name, last_name, email FROM users WHERE id = ?");
    $user_stmt->bind_param("i", $contact_user_id);
    $user_stmt->execute();
    $contact_user = $user_stmt->get_result()->fetch_assoc();
}

if (empty($_SESSION['contact_csrf_token'])) {
    $_SESSION['contact_csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['send_message'])) {
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $valid_subjects = ['General Inquiry', 'Feedback', 'Recipe Request', 'Technical Support'];

    if (!$contact_user_id || !$contact_user) {
        $error_msg = "Please log in before sending a message.";
    } elseif (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['contact_csrf_token'], $_POST['csrf_token'])) {
        $error_msg = "Your session expired. Please refresh the page and try again.";
    } elseif (!in_array($subject, $valid_subjects, true) || $message === '' || strlen($message) > 3000) {
        $error_msg = "Please choose a subject and enter a message under 3,000 characters.";
    } else {
        $email = $contact_user['email'];
        $rate_stmt = $conn->prepare("SELECT COUNT(*) AS message_count FROM contact_messages WHERE email = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)");
        $rate_stmt->bind_param("s", $email);
        $rate_stmt->execute();
        $recent_messages = (int)$rate_stmt->get_result()->fetch_assoc()['message_count'];

        if ($recent_messages >= 3) {
            $error_msg = "You have reached the limit of 3 messages per hour. Please try again later.";
        } else {
            $name = trim($contact_user['first_name'] . ' ' . $contact_user['last_name']);
            $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message, status) VALUES (?, ?, ?, ?, 'Pending')");
            $stmt->bind_param("ssss", $name, $email, $subject, $message);
            if ($stmt->execute()) {
                $success_msg = "Your message has been sent successfully!";
            } else {
                $error_msg = "Failed to send message. Please try again.";
            }
            $stmt->close();
        }
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
        <!-- Guest Users (Not Logged In) -->
        <?php if(!isset($_SESSION['user_id'])): ?>
            <a href="index.php">Home</a>
            <a href="about.php">About Us</a>
            <a href="recipes.php">Recipe Collection</a>
            <a href="contact.php" class="active-nav">Contact Us</a>
        <?php endif; ?>

        <!-- Member Role -->
        <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'member'): ?>
            <a href="index.php">Home</a>
            <a href="about.php">About Us</a>
            <a href="recipes.php">Recipe Collection</a>
            <a href="community_cookbook.php">Community Cookbook</a>
            <a href="culinary_resources.php">Culinary Resources</a>
            <a href="educational_resources.php">Educational Resources</a>
            <a href="contact.php" class="active-nav">Contact Us</a>
        <?php endif; ?>

        <!-- Admin Role -->
        <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <a href="admin_dashboard.php">Dashboard</a>
            <a href="manage_cookbook.php">Community Cookbook</a>

            <div class="dropdown">
                <a href="#">Manage Resources ▼</a>
                <div class="dropdown-content">
                    <a href="admin_recipes.php">Recipes Collection</a>
                    <a href="admin_resources.php">Resources</a>
                </div>
            </div>

            <a href="admin_contact.php">Contact Us</a>
        <?php endif; ?>
    </div>

    <!-- User Auth Area -->
    <span class="user-area">
        <?php if(isset($_SESSION['user_id'])): ?>
            Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? $_SESSION['user']); ?>
            <a href="logout.php" class="cta-btn">Logout</a>
        <?php else: ?>
            <button onclick="showLogin()">Login</button>
            <button onclick="showRegister()">Join Us</button>
        <?php endif; ?>
    </span>
</nav>

<div class="hero" style="padding: 40px 20px; text-align: center;">
    <h1>Contact Us</h1>
    <p>Have questions or feedback? Reach out to us or check your message status below.</p>
</div>

<div class="main-container" style="display: flex; flex-direction: column; width: 100%; max-width: 900px; margin: 30px auto; padding: 0 20px; box-sizing: border-box;">
    
    <!-- Form for sending a new message -->
    <div style="background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 40px; width: 100%; box-sizing: border-box;">
        <h3 style="margin-bottom: 20px;">Send Us a Message</h3>

        <?php if(!empty($success_msg)): ?>
            <div style="background: #C6F6D5; color: #22543D; padding: 10px; border-radius: 4px; margin-bottom: 15px;"><?php echo $success_msg; ?></div>
        <?php endif; ?>
        <?php if(!empty($error_msg)): ?>
            <div style="background: #FFF5F5; color: #C53030; padding: 10px; border-radius: 4px; margin-bottom: 15px;"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <?php if (!$contact_user): ?>
            <div style="padding: 18px; border-radius: 10px; background: #FFF7F6; color: #475467; text-align: center;">
                <p style="margin-bottom: 12px;">Please log in to send feedback. Your message will use the email address saved to your account.</p>
                <button type="button" class="contactLoginContinueBtn" onclick="showLogin()"><i class="fa-solid fa-right-to-bracket"></i> Login to continue</button>
            </div>
        <?php else: ?>
            <form action="contact.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['contact_csrf_token']); ?>">
                <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                    <input type="text" value="<?php echo htmlspecialchars(trim($contact_user['first_name'] . ' ' . $contact_user['last_name'])); ?>" readonly aria-label="Account name" style="flex: 1; padding: 10px; border: 1px solid #CBD5E0; border-radius: 4px; background: #F7FAFC;">
                    <input type="email" value="<?php echo htmlspecialchars($contact_user['email']); ?>" readonly aria-label="Account email" style="flex: 1; padding: 10px; border: 1px solid #CBD5E0; border-radius: 4px; background: #F7FAFC;">
                </div>

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
                    <textarea name="message" placeholder="Your Message..." rows="4" maxlength="3000" required style="width: 100%; padding: 10px; border: 1px solid #CBD5E0; border-radius: 4px; box-sizing: border-box;"></textarea>
                </div>
                <button type="submit" name="send_message" style="background: var(--accent-color); color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-weight: bold;">Send Message</button>
                <p style="margin-top: 10px; color: #718096; font-size: 12px;">Limit: 3 messages per account each hour.</p>
            </form>
        <?php endif; ?>
    </div>

    <!-- Show submitted messages and admin replies when the user is logged in -->
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
                echo '<div style="background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); width: 100%; box-sizing: border-box;">';
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
            <p style="margin: 0; color: #4a5568;">Please <a href="#loginForm" onclick="showLogin(); return false;" style="color: var(--accent-color); font-weight: bold; text-decoration: none;">login</a> to view your previously sent messages and admin replies.</p>
        </div>
    <?php endif; ?>

</div>

<div id="registerForm" class="popup">
    <h2>Join Us</h2>
    <form action="register.php" method="POST">
        <input type="text" name="first_name" placeholder="First Name" required><br><br>
        <input type="text" name="last_name" placeholder="Last Name" required><br><br>
        <input type="email" name="email" placeholder="Email" required><br><br>
        <input type="password" name="password" placeholder="Password" required><br><br>
        <button type="submit">Register</button>
    </form>
    <br>
    <button onclick="closeAll()">Close</button>
</div>

<div id="loginForm" class="popup">
    <h2>Login</h2>
    <form action="login.php" method="POST">
        <input type="email" name="email" placeholder="Email" required><br><br>
        <input type="password" name="password" placeholder="Password" required><br><br>
        <button type="submit">Login</button>
    </form>
    <br>
    <button onclick="closeAll()">Close</button>
</div>

<!-- Cookie Banner -->
<div class="cookie-banner" id="cookieBanner" style="display: <?php echo $cookie_accepted ? 'none' : 'flex'; ?>;">
    <p style="font-size: 14px;">We use cookies to improve your user experience. 
        <a href="cookie_policy.php" style="color: var(--accent-color); text-decoration: none;">Cookie Policy</a>
    </p>
    <button onclick="acceptCookies()">Accept</button>
</div>

<footer>
    <div class="social-links" style="margin-bottom: 15px;">
        <a href="https://facebook.com" target="_blank"><i class="fa-brands fa-facebook"></i></a>
        <a href="https://instagram.com" target="_blank"><i class="fa-brands fa-instagram"></i></a>
        <a href="https://pinterest.com" target="_blank"><i class="fa-brands fa-pinterest"></i></a>
        <a href="https://twitter.com" target="_blank"><i class="fa-brands fa-twitter"></i></a>
    </div>
    <div class="footer-links">
        <a href="privacy.php">Privacy Policy</a> | <a href="cookie_policy.php">Cookie Policy</a>
    </div>
    <p style="margin-top: 15px; font-size: 12px; color: #A0AEC0;">&copy; 2026 FoodFusion. All Rights Reserved.</p>
</footer>

<script>
function showRegister() {
    document.getElementById("registerForm").style.display = "block";
    document.getElementById("loginForm").style.display = "none";
}

function showLogin() {
    document.getElementById("loginForm").style.display = "block";
    document.getElementById("registerForm").style.display = "none";
}

function closeAll() {
    document.getElementById("registerForm").style.display = "none";
    document.getElementById("loginForm").style.display = "none";
}

function acceptCookies() {
    document.cookie = "foodfusion_cookie_accepted=true; max-age=" + 60*60*24*30 + "; path=/";
    document.getElementById('cookieBanner').style.display = 'none';
}

</script>

</body>
</html>