<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Actions</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Import Poppins font from Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Link to external CSS file for styles (create admin-actions.css with the CSS from previous response) -->
    <link rel="stylesheet" href="admin-actions.css">
        <style>
        /* Import Poppins font from Google Fonts */
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

/* CSS Variables for Light Mode */
:root {
    --bg-color: #f8fafc;
    --bg-overlay: rgba(255, 255, 255, 0.9);
    --text-primary: #334155;
    --text-secondary: #64748b;
    --header-bg: linear-gradient(135deg, #1e293b 0%, #3b82f6 100%);
    --header-text: white;
    --section-bg: rgba(255, 255, 255, 0.95);
    --section-border: #e2e8f0;
    --card-bg: #f8fafc;
    --card-border: #e2e8f0;
    --card-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    --card-hover-shadow: 0 12px 32px rgba(0, 0, 0, 0.12);
    --btn-primary-bg: #3b82f6;
    --btn-primary-hover: #2563eb;
    --btn-secondary-bg: transparent;
    --btn-secondary-border: rgba(255, 255, 255, 0.3);
    --btn-secondary-hover-bg: rgba(255, 255, 255, 0.1);
    --btn-secondary-hover-border: rgba(255, 255, 255, 0.5);
    --footer-bg: #1e293b;
    --footer-text: #cbd5e1;
    --link-color: #60a5fa;
    --link-hover: #3b82f6;
    --icon-color: #3b82f6;
    --box-shadow-light: 0 4px 20px rgba(0, 0, 0, 0.06);
    --box-shadow-hover: 0 8px 32px rgba(0, 0, 0, 0.1);
    --focus-color: #3b82f6;
}

/* Dark Mode Overrides */
body.dark {
    --bg-color: #0f172a;
    --bg-overlay: rgba(0, 0, 0, 0.7);
    --text-primary: #f1f5f9;
    --text-secondary: #cbd5e1;
    --header-bg: linear-gradient(135deg, #1e293b 0%, #1d4ed8 100%);
    --header-text: white;
    --section-bg: rgba(30, 41, 59, 0.9);
    --section-border: rgba(148, 163, 184, 0.3);
    --card-bg: rgba(51, 65, 85, 0.8);
    --card-border: rgba(148, 163, 184, 0.2);
    --card-shadow: 0 2px 12px rgba(0, 0, 0, 0.4);
    --card-hover-shadow: 0 12px 32px rgba(0, 0, 0, 0.6);
    --btn-primary-bg: #1d4ed8;
    --btn-primary-hover: #1e40af;
    --btn-secondary-bg: rgba(255, 255, 255, 0.1);
    --btn-secondary-border: rgba(255, 255, 255, 0.2);
    --btn-secondary-hover-bg: rgba(255, 255, 255, 0.2);
    --btn-secondary-hover-border: rgba(255, 255, 255, 0.3);
    --footer-bg: #0f172a;
    --footer-text: #94a3b8;
    --link-color: #93c5fd;
    --link-hover: #60a5fa;
    --icon-color: #60a5fa;
    --box-shadow-light: 0 4px 20px rgba(0, 0, 0, 0.5);
    --box-shadow-hover: 0 8px 32px rgba(0, 0, 0, 0.7);
    --focus-color: #60a5fa;
}

html, body {
    margin: 0;
    padding: 0;
    height: 100%;
    font-family: 'Poppins', sans-serif;
    background-color: var(--bg-color);
    color: var(--text-primary);
    position: relative;
    min-height: 100vh;

    /* Subtle background image for professionalism */
    background-image: linear-gradient(var(--bg-overlay), var(--bg-overlay)), 
                      url('https://images.unsplash.com/photo-1504384308090-c894fdcc538d?auto=format&fit=crop&w=1470&q=80');
    background-size: cover;
    background-repeat: no-repeat;
    background-position: center;
    background-attachment: fixed;
    transition: background-color 0.3s cubic-bezier(0.4, 0, 0.2, 1), color 0.3s ease;
}

/* Header styles - Classic gradient with subtle shadow */
.header {
    background: var(--header-bg);
    color: var(--header-text);
    padding: 1.5rem 1rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    position: sticky;
    top: 0;
    z-index: 50;
    transition: background 0.3s ease;
}

.header h1 {
    font-size: 1.75rem;
    font-weight: 600;
    margin: 0;
    letter-spacing: -0.025em;
    color: var(--header-text);
}

@media (min-width: 768px) {
    .header h1 {
        font-size: 2.25rem;
    }
}

/* Container for header */
.header-container {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* Button base styles - Professional with subtle hover */
.btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 8px;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    user-select: none;
    letter-spacing: 0.025em;
    text-transform: uppercase;
    color: white;
}

.btn-primary {
    background: var(--btn-primary-bg);
    color: white;
}

.btn-primary:hover,
.btn-primary:focus {
    background: var(--btn-primary-hover);
    transform: translateY(-1px);
    box-shadow: 0 4px 16px rgba(59, 130, 246, 0.3);
    outline: none;
}

.btn-secondary {
    background: var(--btn-secondary-bg);
    color: var(--header-text);
    border: 1px solid var(--btn-secondary-border);
}

.btn-secondary:hover {
    background: var(--btn-secondary-hover-bg);
    border-color: var(--btn-secondary-hover-border);
}

/* Main content styles - Clean and spacious */
main {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 0 1rem;
    flex-grow: 1;
    transition: color 0.3s ease;
}

main h2 {
    font-size: 2rem;
    font-weight: 600;
    margin-bottom: 2.5rem;
    text-align: center;
    color: var(--text-primary);
    letter-spacing: -0.025em;
}

@media (min-width: 768px) {
    main h2 {
        font-size: 2.5rem;
        text-align: left;
    }
}

/* Section container - Classic card design */
.section {
    margin-bottom: 3rem;
    border: 1px solid var(--section-border);
    border-radius: 12px;
    background-color: var(--section-bg);
    color: var(--text-primary);
    box-shadow: var(--box-shadow-light);
    padding: 2rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.section:hover {
    box-shadow: var(--box-shadow-hover);
}

.section h3 {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 0.75rem;
    transition: color 0.3s ease;
}

.section h3 i {
    color: var(--icon-color);
}

/* Cards inside sections - Professional hover effects */
.card {
    background-color: var(--card-bg);
    box-shadow: var(--card-shadow);
    border-radius: 12px;
    padding: 1.5rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    height: 100%;
    border: 1px solid var(--card-border);
    color: var(--text-primary);
}

.card:hover {
    transform: translateY(-4px);
    box-shadow: var(--card-hover-shadow);
    border-color: var(--focus-color);
}

.card h4 {
    font-size: 1.125rem;
    font-weight: 500;
    margin-bottom: 1rem;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: color 0.3s ease;
}

.card i {
    color: var(--icon-color);
    font-size: 1.25rem;
    transition: color 0.3s ease;
}

.card p {
    color: var(--text-secondary);
    transition: color 0.3s ease;
}

/* Grid container inside sections */
.grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.5rem;
}

@media (min-width: 768px) {
    .grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 1024px) {
    .grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

/* Footer styles - Clean and minimal */
footer {
    background-color: var(--footer-bg);
    color: var(--footer-text);
    padding: 2rem 1rem;
    text-align: center;
    font-size: 0.875rem;
    box-shadow: 0 -2px 12px rgba(0, 0, 0, 0.1);
    margin-top: 4rem;
    transition: background-color 0.3s ease, color 0.3s ease;
}

footer a {
    color: var(--link-color);
    text-decoration: none;
    transition: color 0.2s ease;
}

footer a:hover {
    color: var(--link-hover);
}

/* Responsive adjustments for small screens */
@media (max-width: 480px) {
    .btn {
        font-size: 0.75rem;
        padding: 0.625rem 1.25rem;
    }

    .section {
        padding: 1.5rem;
    }

    main h2 {
        font-size: 1.75rem;
    }
}

/* Accessibility improvements */
.btn:focus {
    outline: 2px solid var(--focus-color);
    outline-offset: 2px;
}

.section:focus-within {
    outline: none;
}

main h2 i {
    color: var(--icon-color);
}
        </style>
</head>

<body>
    <!-- Header -->
    <header class="header">
        <div class="header-container">
            <h1><i class="fas fa-cogs mr-3"></i>Admin Actions</h1>
            <div>
                <a href="Admin.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i>Back to Admin
                </a>
            </div>
        </div>
    </header>

    <main>
        <h2><i class="fas fa-tasks mr-3"></i>Choose an Action</h2>

        <!-- Primary School Section -->
        <section class="section" tabindex="-1">
            <h3><i class="fas fa-school"></i>Primary School</h3>
            <div class="grid">
                <div class="card" tabindex="0">
                    <h4><i class="fas fa-chalkboard"></i>View Additional Classrooms</h4>
                    <p class="text-sm mb-4">Access reports and details on additional classroom infrastructure.</p>
                    <a href="pview-additional-classrooms.php" class="btn btn-primary">
                        <i class="fas fa-eye"></i>View Details
                    </a>
                </div>
                <div class="card" tabindex="0">
                    <h4><i class="fas fa-restroom"></i>View Additional Toilets</h4>
                    <p class="text-sm mb-4">Review sanitation facilities and additional toilet infrastructure.</p>
                    <a href="pview-additionaltoilets.php" class="btn btn-primary">
                        <i class="fas fa-eye"></i>View Details
                    </a>
                </div>
                <div class="card" tabindex="0">
                    <h4><i class="fas fa-tint"></i>View Water Infrastructure</h4>
                    <p class="text-sm mb-4">Examine water supply and related infrastructure reports.</p>
                    <a href="pwaterdownlod.php" class="btn btn-primary">
                        <i class="fas fa-eye"></i>View Details
                    </a>
                </div>
                <div class="card" tabindex="0">
                    <h4><i class="fas fa-plug"></i>View Electricity Infrastructure</h4>
                    <p class="text-sm mb-4">Check electrical systems and power infrastructure data.</p>
                    <a href="pview-electricity-infrastructur.php" class="btn btn-primary">
                        <i class="fas fa-eye"></i>View Details
                    </a>
                </div>
            </div>
        </section>

        <!-- High School Section -->
        <section class="section" tabindex="-1">
            <h3><i class="fas fa-university"></i>High School</h3>
            <div class="grid">
                <div class="card" tabindex="0">
                    <h4><i class="fas fa-chalkboard"></i>View Additional Classrooms</h4>
                    <p class="text-sm mb-4">Access reports and details on additional classroom infrastructure.</p>
                    <a href="view-additional-classrooms.php" class="btn btn-primary">
                        <i class="fas fa-eye"></i>View Details
                    </a>
                </div>
                <div class="card" tabindex="0">
                    <h4><i class="fas fa-restroom"></i>View Additional Toilets</h4>
                    <p class="text-sm mb-4">Review sanitation facilities and additional toilet infrastructure.</p>
                    <a href="view-additionaltoilets.php" class="btn btn-primary">
                        <i class="fas fa-eye"></i>View Details
                    </a>
                </div>
                <div class="card" tabindex="0">
                    <h4><i class="fas fa-tint"></i>View Water Infrastructure</h4>
                    <p class="text-sm mb-4">Examine water supply and related infrastructure reports.</p>
                    <a href="waterdownlod.php" class="btn btn-primary">
                        <i class="fas fa-eye"></i>View Details
                    </a>
                </div>
                <div class="card" tabindex="0">
                    <h4><i class="fas fa-plug"></i>View Electricity Infrastructure</h4>
                    <p class="text-sm mb-4">Check electrical systems and power infrastructure data.</p>
                    <a href="view-electricity-infrastructur.php" class="btn btn-primary">
                        <i class="fas fa-eye"></i>View Details
                    </a>
                </div>
            </div>
        </section>

        <!-- Pre-School Section (Uncomment if needed)
        <section class="section" tabindex="-1">
            <h3><i class="fas fa-child"></i>Pre-School</h3>
            <div class="grid">
                <div class="card" tabindex="0">
                    <h4><i class="fas fa-chalkboard"></i>View Additional Classrooms</h4>
                    <p class="text-sm mb-4">Access reports and details on additional classroom infrastructure.</p>
                    <a href="view-additional-classrooms.php" class="btn btn-primary">
                        <i class="fas fa-eye"></i>View Details
                    </a>
                </div>
                <div class="card" tabindex="0">
                    <h4><i class="fas fa-restroom"></i>View Additional Toilets</h4>
                    <p class="text-sm mb-4">Review sanitation facilities and additional toilet infrastructure.</p>
                    <a href="view-additionaltoilets.php" class="btn btn-primary">
                        <i class="fas fa-eye"></i>View Details
                    </a>
                </div>
                <div class="card" tabindex="0">
                    <h4><i class="fas fa-tint"></i>View Water Infrastructure</h4>
                    <p class="text-sm mb-4">Examine water supply and related infrastructure reports.</p>
                    <a href="without-Water.php" class="btn btn-primary">
                        <i class="fas fa-eye"></i>View Details
                    </a>
                </div>
                <div class="card" tabindex="0">
                    <h4><i class="fas fa-plug"></i>View Electricity Infrastructure</h4>
                    <p class="text-sm mb-4">Check electrical systems and power infrastructure data.</p>
                    <a href="view-electricity-infrastructur.php" class="btn btn-primary">
                        <i class="fas fa-eye"></i>View Details
                    </a>
                </div>
            </div>
        </section> -->
    </main>

    <!-- Footer -->
    <footer>
        <div class="max-w-6xl mx-auto">
            <p>&copy; 2025 Ministry of Education and Training. All rights reserved.</p>
            <p class="mt-2">Contact: <a href="mailto:info@education.gov">info@education.gov</a> | Designed for Professional Administration</p>
        </div>
    </footer>

    <script>
        // Load theme from localStorage (shared with login/register pages) - No toggle visible
        document.addEventListener('DOMContentLoaded', () => {
            const body = document.body;

            // Apply saved preference from localStorage (chosen on login)
            if (localStorage.getItem('darkMode') === 'enabled') {
                body.classList.add('dark');
            } else {
                // Default to light if no preference saved
                body.classList.remove('dark');
            }
        });
    </script>
</body>

</html>