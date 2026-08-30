<?php
session_start();
require_once '../../app/config/databaseconnection.php';

// Access Control Guard
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'doctor') {
    header("Location: ../login.php");
    exit;
}

$userName = $_SESSION['name'] ?? 'Doctor';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard - HMS</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, sans-serif; }
        body { display: flex; flex-direction: column; height: 100vh; background-color: #f4f7f6; }

        /* HEADER - Deep Teal accents */
        header {
            height: 60px;
            background-color: #115e59;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        header .logo { font-size: 20px; font-weight: bold; letter-spacing: 1px; }
        header .logout-btn {
            color: #fca5a5;
            text-decoration: none;
            font-weight: bold;
            padding: 8px 12px;
            border: 1px solid #fca5a5;
            border-radius: 4px;
            transition: 0.2s;
        }
        header .logout-btn:hover { background-color: #fca5a5; color: #115e59; }

        /* BODY CONTAINER */
        .layout-container { display: flex; flex: 1; overflow: hidden; }

        /* SIDEBAR */
        aside {
            width: 240px;
            background-color: #ffffff;
            border-right: 1px solid #e5e7eb;
            display: flex;
            flex-direction: column;
            padding-top: 20px;
        }
        aside nav a {
            display: block;
            padding: 14px 20px;
            color: #374151;
            text-decoration: none;
            font-size: 15px;
            font-weight: 500;
            border-left: 4px solid transparent;
            transition: 0.2s;
        }
        aside nav a:hover { background-color: #f3f4f6; color: #111827; }
        aside nav a.active {
            background-color: #f0fdfa;
            color: #0d9488;
            border-left-color: #0d9488;
            font-weight: bold;
        }

        /* MAIN CONTENT AREA */
        main {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
            background-color: #f9fafb;
        }
        .content-card {
            background: #ffffff;
            padding: 24px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

    <!-- Top Header -->
    <header>
        <div class="logo">HMS - Doctor Portal</div>
        <div>
            <span style="margin-right: 15px; font-size: 14px;">Welcome, <?= htmlspecialchars($userName) ?></span>
            <a href="../login.php?action=logout" class="logout-btn">Logout</a>
        </div>
    </header>

    <div class="layout-container">
        <!-- Left Sidebar -->
        <aside>
            <nav id="sidebar-nav">
                <a href="#" class="nav-link active" data-page="dashboard">Dashboard</a>
                <a href="#" class="nav-link" data-page="consultationreq">Consultation Requests</a>
                <a href="#" class="nav-link" data-page="consultationhistory">Consultation History</a>
                <a href="#" class="nav-link" data-page="admissionreq">Admission Requests</a>
                <a href="#" class="nav-link" data-page="profile">Profile</a>
                <a href="#" class="nav-link" data-page="edit">Edit Profile</a>
            </nav>
        </aside>

        <!-- Dynamic Main Content Area -->
        <main>
            <div id="main-content" class="content-card">
                <!-- Content will load dynamically via JS -->
            </div>
        </main>
    </div>

    <!-- JavaScript Navigation Handling -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const navLinks = document.querySelectorAll('.nav-link');
            const contentArea = document.getElementById('main-content');

            // Function to load content via fetch
            function loadSection(pageName) {
                contentArea.innerHTML = '<p>Loading...</p>';

                fetch(`${pageName}.php`)
                    .then(response => {
                        if (!response.ok) throw new Error('Page not found');
                        return response.text();
                    })
                    .then(html => {
                        contentArea.innerHTML = html;
                        // Execute embedded script tags
                        const scripts = contentArea.querySelectorAll('script');
                        scripts.forEach(oldScript => {
                            const newScript = document.createElement('script');
                            newScript.textContent = oldScript.textContent;
                            oldScript.parentNode.replaceChild(newScript, oldScript);
                        });
                    })
                    .catch(err => {
                        contentArea.innerHTML = `<p style="color:red;">Error loading section: ${err.message}</p>`;
                    });
            }

            // Handle sidebar clicks
            navLinks.forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();

                    navLinks.forEach(l => l.classList.remove('active'));
                    link.classList.add('active');

                    const page = link.getAttribute('data-page');
                    loadSection(page);
                });
            });

            // Handle Edit Profile form submission (delegated)
            contentArea.addEventListener('submit', (e) => {
                if (e.target && e.target.id === 'edit-doctor-form') {
                    e.preventDefault();

                    const form = e.target;
                    const messageBox = document.getElementById('edit-doctor-message');
                    const formData = new FormData(form);

                    fetch('edit.php', {
                        method: 'POST',
                        body: formData
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (messageBox) {
                                messageBox.style.color = data.success ? 'green' : 'red';
                                messageBox.textContent = data.message;
                            }
                        })
                        .catch(() => {
                            if (messageBox) {
                                messageBox.style.color = 'red';
                                messageBox.textContent = 'Something went wrong. Please try again.';
                            }
                        });
                }

                // Handle Record Consultation form submission (delegated)
                if (e.target && e.target.id === 'record-consultation-form') {
                    e.preventDefault();

                    const form = e.target;
                    const messageBox = document.getElementById('consultation-message');
                    const formData = new FormData(form);

                    fetch('consultationreq.php', {
                        method: 'POST',
                        body: formData
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Reload consultation requests to refresh data and show message
                                loadSection('consultationreq');
                            } else {
                                if (messageBox) {
                                    messageBox.style.display = 'block';
                                    messageBox.style.background = '#fee2e2';
                                    messageBox.style.color = '#b91c1c';
                                    messageBox.textContent = data.message;
                                }
                            }
                        })
                        .catch(() => {
                            if (messageBox) {
                                messageBox.style.display = 'block';
                                messageBox.style.background = '#fee2e2';
                                messageBox.style.color = '#b91c1c';
                                messageBox.textContent = 'Something went wrong while saving consultation.';
                            }
                        });
                }
            });

            // Default route: load dashboard on initial land
            loadSection('dashboard');
        });
    </script>
</body>
</html>
