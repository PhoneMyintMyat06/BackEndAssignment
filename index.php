<?php session_start(); ?>

<!DOCTYPE html>
<html>
<head>
    <title>FoodFusion</title>

    <style>
        nav {
            background: #eee;
            padding: 15px;
        }

        nav a {
            margin: 10px;
            text-decoration: none;
        }

        nav button {
            margin: 5px;
        }

        .user-area {
            float: right;
        }

        .popup {
            display: none;
            border: 1px solid #ccc;
            padding: 20px;
            width: 300px;
            background: #f9f9f9;
            position: absolute;
            top: 80px;
            left: 50%;
            transform: translateX(-50%);
        }

        .dropdown {
            display: inline-block;
            position: relative;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            background: white;
            min-width: 220px;
            border: 1px solid #ccc;
            padding: 10px;
            z-index: 1000;
        }

        .dropdown-content a {
            display: block;
            margin: 8px 0;
        }

        .dropdown:hover .dropdown-content {
            display: block;
        }
    </style>
</head>

<body>

<nav>
    <a href="index.php">Home</a>
    <a href="#">About Us</a>

    <!-- Visitor menu -->
    <?php if(!isset($_SESSION['user_id'])): ?>
        <a href="recipes.php">Recipe Collection</a>
    <?php endif; ?>

    <!-- Member menu -->
    <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'member'): ?>
        <a href="recipes.php">Recipe Collection</a>
        <a href="community_cookbook.php">Community Cookbook</a>
        <a href="culinary_resources.php">Culinary Resources</a>
        <a href="educational_resources.php">Educational Resources</a>
    <?php endif; ?>

    <!-- Admin menu -->
    <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
        <a href="community_cookbook.php">Community Cookbook</a>

        <div class="dropdown">
            <a href="#">Manage Resources ▼</a>

            <div class="dropdown-content">
                <a href="admin_recipes.php">Recipes Collection</a>
                <a href="admin_resources.php">Resources</a>
            </div>
        </div>
    <?php endif; ?>

    <a href="#">Contact Us</a>

    <span class="user-area">
        <?php if(isset($_SESSION['user_id'])): ?>

            Welcome, <?php echo htmlspecialchars($_SESSION['user']); ?>
            <a href="logout.php">Logout</a>

        <?php else: ?>

            <button onclick="showRegister()">Join Us</button>
            <button onclick="showLogin()">Login</button>

        <?php endif; ?>
    </span>
</nav>

<h1>Welcome to FoodFusion</h1>

<p>
    FoodFusion is a cooking platform where users can explore recipes,
    join the community, and learn culinary skills.
</p>

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
</script>

</body>
</html>