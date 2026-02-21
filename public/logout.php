<?php
// This file now handles the actual logout after confirmation
session_start();

// Check if this is a GET request (showing the page) or POST request (actual logout)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // User confirmed logout - destroy session
    session_destroy();
    header('Location: index.php');
    exit;
} else {
    // Show confirmation page
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Logout - Video Annotation</title>
        <link rel="stylesheet" href="../assets/css/style.css">
    </head>
    <body>
        <div class="auth-container">
            <div class="auth-card" style="text-align: center;">
                <div style="font-size: 4rem; margin-bottom: var(--space-md);">👋</div>
                <h1 style="margin-bottom: var(--space-md);">Ready to leave?</h1>
                <p style="color: var(--gray); margin-bottom: var(--space-xl);">Are you sure you want to log out?</p>
                
                <div style="display: flex; gap: var(--space-md);">
                    <form method="POST" style="flex: 1;">
                        <button type="submit" class="btn btn-primary" style="width: 100%;">Yes, Logout</button>
                    </form>
                    <a href="javascript:history.back()" class="btn btn-secondary" style="flex: 1; text-align: center;">Cancel</a>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}
?>