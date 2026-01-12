<?php
session_start();
if (!isset($_SESSION['email'])) {
    header('Location: ../login.php');
    exit;
}

// Auto logout after 50 minutes
$timeout = 50 * 60;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
    $_SESSION = [];
    session_destroy();
    header('Location: ../login.php');
    exit;
}
$_SESSION['last_activity'] = time();

// Get error message and form data from session
$errorField = $_SESSION['error_field'] ?? '';
$errorMessage = $_SESSION['error_message'] ?? '';
$formData = $_SESSION['form_data'] ?? [];

// Clear session data after retrieving
unset($_SESSION['error_field']);
unset($_SESSION['error_message']);
unset($_SESSION['form_data']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Employee Form</title>
    <link rel="icon" type="image/png" href="../fi-snsuxx-php-logo.jpg">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #e8f5e9, #ffffff);
            margin: 0;
            min-height: 100vh;
        }
        
        .main-wrapper {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 24px 16px 80px;
        }
        
        .main-wrapper > div {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.08);
            padding: 28px 24px;
            width: 100%;
            max-width: 600px;
        }
        
        form h1 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 1.75rem;
            font-weight: 700;
            color: #111827;
        }

        /* Inline Error Message */
        .field-error {
            background: #fef2f2;
            border-left: 4px solid #dc2626;
            border-radius: 6px;
            padding: 10px 12px;
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 8px;
            animation: slideDown 0.3s ease-out;
        }

        .field-error i {
            color: #dc2626;
            font-size: 16px;
        }

        .field-error-text {
            color: #dc2626;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .input-error {
            border-color: #dc2626 !important;
            background: #fef2f2 !important;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-5px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        form h2 {
            font-size: 0.95rem;
            margin-bottom: 16px;
            color: #111827;
            font-weight: 600;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        
        form h2 input[type="text"],
        form h2 input[type="email"],
        form h2 input[type="tel"],
        form h2 input[type="number"],
        form h2 input[type="date"],
        form h2 select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background: #f9fafb;
            font-size: 0.95rem;
            font-family: Arial, sans-serif;
            transition: border-color 0.2s, background 0.2s;
        }
        
        form h2 input[type="text"]:focus,
        form h2 input[type="email"]:focus,
        form h2 input[type="tel"]:focus,
        form h2 input[type="number"]:focus,
        form h2 input[type="date"]:focus,
        form h2 select:focus {
            outline: none;
            border-color: #68A691;
            background: #ffffff;
        }
        
        form h2 select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23111827' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 18px;
            padding-right: 40px;
        }
        
        form h2 select option[disabled] {
            color: #9ca3af;
        }
        
        button[type="submit"] {
            background: #68A691;
            color: #fff;
            border: none;
            padding: 12px;
            border-radius: 6px;
            cursor: pointer;
            width: 100%;
            font-size: 1.05rem;
            font-weight: 600;
            margin-top: 24px;
            transition: background 0.2s ease, transform 0.15s ease, box-shadow 0.15s ease;
        }
        
        button[type="submit"]:hover {
            background: #4a8970;
            transform: translateY(-1px);
            box-shadow: 0 6px 14px rgba(104, 166, 145, 0.3);
        }
        
        button[type="submit"]:active {
            transform: translateY(0);
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            body {
                padding-top: 80px;
            }
            
            .main-wrapper {
                padding: 20px 12px 60px;
            }
            
            .main-wrapper > div {
                padding: 20px 16px;
            }
            
            form h1 {
                font-size: 1.5rem;
            }
        }
        
        @media (max-width: 480px) {
            body {
                padding-top: 100px;
            }
            
            .main-wrapper {
                padding: 16px 12px 50px;
            }
            
            .main-wrapper > div {
                padding: 16px 12px;
                box-shadow: 0 4px 10px rgba(0,0,0,0.08);
            }
            
            form h1 {
                font-size: 1.3rem;
                margin-bottom: 16px;
            }
            
            form h2 {
                font-size: 0.9rem;
            }
            
            button[type="submit"] {
                font-size: 1rem;
                padding: 10px;
            }
        }
    </style>
</head>

<body>
    <?php
    $pageTitle = 'Employee Form';
    $showExport = false;
    include '../header.php';
    ?>

    <div class="main-wrapper">
        <div>
            <form method="POST" action="send.php">
                <h1>Employees Form</h1>

                <h2>Department ID:
                    <input type="text" name="department_id"
                        placeholder="Enter Department ID"
                        class="<?php echo ($errorField === 'department_id') ? 'input-error' : ''; ?>"
                        maxlength="100" 
value="<?php echo htmlspecialchars($formData['department_id'] ?? '', ENT_QUOTES); ?>" required>
                    <?php if ($errorField === 'department_id'): ?>
                        <div class="field-error">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span class="field-error-text"><?php echo htmlspecialchars($errorMessage); ?></span>
                        </div>
                    <?php endif; ?>
                </h2>

                <h2>Full Name:
                    <input type="text" name="ename" pattern="[A-Za-z\s]+" placeholder="Enter full name" 
                        value="<?php echo htmlspecialchars($formData['ename'] ?? '', ENT_QUOTES); ?>" required>
                </h2>

                <h2>Date of Birth:
                    <input type="date" name="dob" 
                        value="<?php echo htmlspecialchars($formData['dob'] ?? '', ENT_QUOTES); ?>" required>
                </h2>

                <h2>Gender:
                    <select name="gender" required>
                        <option disabled <?php echo !isset($formData['gender']) ? 'selected' : ''; ?>>--Select--</option>
                        <option <?php echo (isset($formData['gender']) && $formData['gender'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                        <option <?php echo (isset($formData['gender']) && $formData['gender'] === 'Female') ? 'selected' : ''; ?>>Female</option>
                    </select>
                </h2>

                <h2>Email:
                    <input type="email" name="email" placeholder="Enter email" 
                        class="<?php echo ($errorField === 'email') ? 'input-error' : ''; ?>"
                        value="<?php echo htmlspecialchars($formData['email'] ?? '', ENT_QUOTES); ?>" required>
                    <?php if ($errorField === 'email'): ?>
                        <div class="field-error">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span class="field-error-text"><?php echo htmlspecialchars($errorMessage); ?></span>
                        </div>
                    <?php endif; ?>
                </h2>

                <h2>Phone Number:
                    <input type="tel" name="pnumber" minlength="10" maxlength="13"
                        placeholder="Enter phone number" 
                        class="<?php echo ($errorField === 'pnumber') ? 'input-error' : ''; ?>"
                        value="<?php echo htmlspecialchars($formData['pnumber'] ?? '', ENT_QUOTES); ?>" required>
                    <?php if ($errorField === 'pnumber'): ?>
                        <div class="field-error">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span class="field-error-text"><?php echo htmlspecialchars($errorMessage); ?></span>
                        </div>
                    <?php endif; ?>
                </h2>

                <h2>Address:
                    <input type="text" name="address" placeholder="Enter address" 
                        value="<?php echo htmlspecialchars($formData['address'] ?? '', ENT_QUOTES); ?>" required>
                </h2>

                <h2>Designation:
                    <input type="text" name="designation" pattern="[A-Za-z\s]+"
                        title="Only letters and spaces allowed" placeholder="Enter designation" 
                        value="<?php echo htmlspecialchars($formData['designation'] ?? '', ENT_QUOTES); ?>" required>
                </h2>

                <h2>Salary:
                    <input type="number" step="0.01" name="salary" placeholder="Enter salary" 
                        class="<?php echo ($errorField === 'salary') ? 'input-error' : ''; ?>"
                        value="<?php echo htmlspecialchars($formData['salary'] ?? '', ENT_QUOTES); ?>" required>
                    <?php if ($errorField === 'salary'): ?>
                        <div class="field-error">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span class="field-error-text"><?php echo htmlspecialchars($errorMessage); ?></span>
                        </div>
                    <?php endif; ?>
                </h2>

                <h2>Date of Joining:
                    <input type="date" name="joining_date" 
                        value="<?php echo htmlspecialchars($formData['joining_date'] ?? '', ENT_QUOTES); ?>" required>
                </h2>

                <h2>Aadhar Number:
                    <input type="text" name="aadhar" maxlength="12" placeholder="Write Aadhar Number" 
                        class="<?php echo ($errorField === 'aadhar') ? 'input-error' : ''; ?>"
                        value="<?php echo htmlspecialchars($formData['aadhar'] ?? '', ENT_QUOTES); ?>">
                    <?php if ($errorField === 'aadhar'): ?>
                        <div class="field-error">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span class="field-error-text"><?php echo htmlspecialchars($errorMessage); ?></span>
                        </div>
                    <?php endif; ?>
                </h2>

                <button type="submit">Submit</button>
            </form>
        </div>
    </div>

    <script>
        // Auto-hide field error after 5 seconds
        const fieldErrors = document.querySelectorAll('.field-error');
        if (fieldErrors.length > 0) {
            setTimeout(() => {
                fieldErrors.forEach(error => {
                    error.style.opacity = '0';
                    error.style.transition = 'opacity 0.3s ease';
                    setTimeout(() => {
                        error.style.display = 'none';
                        // Remove error class from input
                        const input = error.previousElementSibling;
                        if (input) {
                            input.classList.remove('input-error');
                        }
                    }, 300);
                });
            }, 5000);

            // Scroll to error field
            const errorInput = document.querySelector('.input-error');
            if (errorInput) {
                errorInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                errorInput.focus();
            }
        }
    </script>

    <?php include '../footer.php'; ?>
</body>

</html>
