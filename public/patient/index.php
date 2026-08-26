<?php
session_start();
require_once '../../app/config/databaseconnection.php';

// Access Control Guard
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'patient') {
    header("Location: ../login.php");
    exit;
}

$userName = $_SESSION['name'] ?? 'Patient';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Dashboard - HMS</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, sans-serif; }
        body { display: flex; flex-direction: column; height: 100vh; background-color: #f4f7f6; }

        /* HEADER */
        header {
            height: 60px;
            background-color: #1f2937;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        header .logo { font-size: 20px; font-weight: bold; letter-spacing: 1px; }
        header .logout-btn {
            color: #f87171;
            text-decoration: none;
            font-weight: bold;
            padding: 8px 12px;
            border: 1px solid #f87171;
            border-radius: 4px;
            transition: 0.2s;
        }
        header .logout-btn:hover { background-color: #f87171; color: #fff; }

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
            background-color: #eff6ff;
            color: #2563eb;
            border-left-color: #2563eb;
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
        <div class="logo">HMS</div>
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
                <a href="#" class="nav-link" data-page="admission">Admission</a>
                <a href="#" class="nav-link" data-page="consultation">Consultation</a>
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

            // Default route: load dashboard on initial land
            loadSection('dashboard');
        });
    </script>
</body>
</html>