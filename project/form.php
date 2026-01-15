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
    <title>Project Form</title>
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

        /* Red Asterisk for Required Fields */
        .required {
            color: #dc2626;
            margin-left: 2px;
            font-weight: 400;
        }

        /* Field Group */
        .field-group {
            margin-bottom: 16px;
        }

        .field-group label {
            display: block;
            font-size: 0.95rem;
            margin-bottom: 6px;
            color: #111827;
            font-weight: 600;
        }

        .field-group input[type="text"],
        .field-group input[type="date"],
        .field-group select,
        .field-group textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background: #f9fafb;
            font-size: 0.95rem;
            font-family: Arial, sans-serif;
            transition: border-color 0.2s, background 0.2s;
        }

        .field-group input[type="text"]:focus,
        .field-group input[type="date"]:focus,
        .field-group select:focus,
        .field-group textarea:focus {
            outline: none;
            border-color: #68A691;
            background: #ffffff;
        }

        .field-group select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23111827' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 18px;
            padding-right: 40px;
        }

        .field-group select option[disabled] {
            color: #9ca3af;
        }

        .field-group textarea {
            min-height: 100px;
            resize: vertical;
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
            
            .field-group label {
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
    $pageTitle = 'Project Form';
    $showExport = false;
    include '../header.php';
    ?>

    <div class="main-wrapper">
        <div>
            <form method="POST" action="send.php" novalidate>
                <h1>Project Form</h1>

                <div class="field-group">
                    <label for="department_id">Department ID: <span class="required">*</span></label>
                    <input type="text" id="department_id" name="department_id"
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
                </div>

                <div class="field-group">
                    <label for="pname">Project Name: <span class="required">*</span></label>
                    <input type="text" id="pname" name="pname" placeholder="Project Name" 
                        class="<?php echo ($errorField === 'pname') ? 'input-error' : ''; ?>"
                        value="<?php echo htmlspecialchars($formData['pname'] ?? '', ENT_QUOTES); ?>" required>
                    <?php if ($errorField === 'pname'): ?>
                        <div class="field-error">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span class="field-error-text"><?php echo htmlspecialchars($errorMessage); ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="field-group">
                    <label for="cname">Client / Company Name: <span class="required">*</span></label>
                    <input type="text" id="cname" name="cname" placeholder="Client / Company Name" 
                        value="<?php echo htmlspecialchars($formData['cname'] ?? '', ENT_QUOTES); ?>" required>
                </div>

                <div class="field-group">
                    <label for="pmanager">Project Manager Name: <span class="required">*</span></label>
                    <input type="text" id="pmanager" name="pmanager" pattern="[A-Za-z\s]+"
                        title="Only letters and spaces allowed"
                        placeholder="Project Manager Name" 
                        value="<?php echo htmlspecialchars($formData['pmanager'] ?? '', ENT_QUOTES); ?>" required>
                </div>

                <div class="field-group">
                    <label for="sdate">Start Date: <span class="required">*</span></label>
                    <input type="date" id="sdate" name="sdate" 
                        class="<?php echo ($errorField === 'sdate') ? 'input-error' : ''; ?>"
                        value="<?php echo htmlspecialchars($formData['sdate'] ?? '', ENT_QUOTES); ?>" required>
                    <?php if ($errorField === 'sdate'): ?>
                        <div class="field-error">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span class="field-error-text"><?php echo htmlspecialchars($errorMessage); ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="field-group">
                    <label for="edate">End Date / Deadline: <span class="required">*</span></label>
                    <input type="date" id="edate" name="edate" 
                        class="<?php echo ($errorField === 'edate') ? 'input-error' : ''; ?>"
                        value="<?php echo htmlspecialchars($formData['edate'] ?? '', ENT_QUOTES); ?>" required>
                    <?php if ($errorField === 'edate'): ?>
                        <div class="field-error">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span class="field-error-text"><?php echo htmlspecialchars($errorMessage); ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="field-group">
                    <label for="status">Project Status: <span class="required">*</span></label>
                    <select id="status" name="status" required>
                        <option disabled <?php echo !isset($formData['status']) ? 'selected' : ''; ?>>--Select--</option>
                        <option <?php echo (isset($formData['status']) && $formData['status'] === 'Planning') ? 'selected' : ''; ?>>Planning</option>
                        <option <?php echo (isset($formData['status']) && $formData['status'] === 'In Progress') ? 'selected' : ''; ?>>In Progress</option>
                        <option <?php echo (isset($formData['status']) && $formData['status'] === 'On Hold') ? 'selected' : ''; ?>>On Hold</option>
                        <option <?php echo (isset($formData['status']) && $formData['status'] === 'Completed') ? 'selected' : ''; ?>>Completed</option>
                    </select>
                </div>

              <div class="field-group">
    <label for="pdescription">Description: <span class="required">*</span></label>
    <textarea id="pdescription" name="pdescription" placeholder="Description" required><?php echo htmlspecialchars($formData['pdescription'] ?? '', ENT_QUOTES); ?></textarea>
</div>


                <button type="submit">Submit</button>
            </form>
        </div>
    </div>

    <script>
    // Form submission validation with custom inline errors
    const form = document.querySelector('form');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            // Remove all previous custom errors
            document.querySelectorAll('.custom-required-error').forEach(err => err.remove());
            
            // Check all required fields and show custom error on first empty one
            const requiredFields = form.querySelectorAll('[required]');
            let firstEmptyField = null;
            
            requiredFields.forEach(field => {
                // Remove previous error styling
                field.classList.remove('input-error');
                
                const isEmpty = field.value.trim() === '' || 
                               (field.tagName === 'SELECT' && (field.selectedIndex === 0 || field.value === ''));
                
                if (isEmpty && !firstEmptyField) {
                    firstEmptyField = field;
                }
            });
            
            if (firstEmptyField) {
                e.preventDefault();
                
                // Add error styling
                firstEmptyField.classList.add('input-error');
                
                // Create custom error message
                const errorDiv = document.createElement('div');
                errorDiv.className = 'field-error custom-required-error';
                errorDiv.innerHTML = `
                    <i class="fas fa-exclamation-triangle"></i>
                    <span class="field-error-text">This field is required!</span>
                `;
                
                // Insert error in field-group
                const fieldGroup = firstEmptyField.closest('.field-group');
                if (fieldGroup) {
                    fieldGroup.appendChild(errorDiv);
                }
                
                // Scroll to the field
                firstEmptyField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstEmptyField.focus();
                
                // Remove error when user starts typing
                firstEmptyField.addEventListener('input', function() {
                    this.classList.remove('input-error');
                    const customError = fieldGroup.querySelector('.custom-required-error');
                    if (customError) customError.remove();
                }, { once: true });
                
                // Auto-hide after 5 seconds
                setTimeout(() => {
                    if (errorDiv && errorDiv.parentNode) {
                        errorDiv.style.opacity = '0';
                        errorDiv.style.transition = 'opacity 0.3s ease';
                        setTimeout(() => {
                            if (errorDiv.parentNode) errorDiv.remove();
                            firstEmptyField.classList.remove('input-error');
                        }, 300);
                    }
                }, 5000);
                
                return false;
            }
        });
    }

    // Auto-hide server-side field errors after 5 seconds
    const serverErrors = document.querySelectorAll('.field-error:not(.custom-required-error)');
    if (serverErrors.length > 0) {
        setTimeout(() => {
            serverErrors.forEach(error => {
                error.style.opacity = '0';
                error.style.transition = 'opacity 0.3s ease';
                setTimeout(() => {
                    error.style.display = 'none';
                    const input = error.previousElementSibling;
                    if (input) {
                        input.classList.remove('input-error');
                    }
                }, 300);
            });
        }, 5000);

        // Scroll to server error field
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
