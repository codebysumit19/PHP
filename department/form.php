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
    <title>Department Form</title>
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

        .main-wrapper>div {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
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
            margin-bottom: 6px;
            color: #111827;
            font-weight: 600;
            display: block;
        }

        form h2+input,
        form h2+textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background: #f9fafb;
            font-size: 0.95rem;
            font-family: Arial, sans-serif;
            transition: border-color 0.2s, background 0.2s;
            margin-bottom: 16px;
        }

        form h2+input:focus,
        form h2+textarea:focus {
            outline: none;
            border-color: #68A691;
            background: #ffffff;
        }

        textarea {
            min-height: 100px;
            resize: vertical;
        }

        form h2 label {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 400;
            cursor: pointer;
            margin-right: 20px;
            margin-top: 8px;
            font-size: 0.95rem;
        }

        form h2 label input[type="radio"] {
            cursor: pointer;
            width: 16px;
            height: 16px;
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


        /* Phone Number Row */
        .phone-row {
            display: flex;
            gap: 8px;
            margin-bottom: 16px;
        }

        .phone-row select {
            width: 130px;
            flex-shrink: 0;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background: #f9fafb;
            font-size: 0.95rem;
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23111827' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 8px center;
            background-size: 16px;
            padding-right: 32px;
            transition: border-color 0.2s, background 0.2s;
        }

        .phone-row select:focus {
            outline: none;
            border-color: #68A691;
            background-color: #fff;
        }

        .phone-row input {
            flex: 1;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background: #f9fafb;
            font-size: 0.95rem;
            font-family: Arial, sans-serif;
            transition: border-color 0.2s, background 0.2s;
        }

        .phone-row input:focus {
            outline: none;
            border-color: #68A691;
            background: #fff;
        }

        .phone-row input::placeholder {
            color: #9ca3af;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            body {
                padding-top: 80px;
            }

            .main-wrapper {
                padding: 20px 12px 60px;
            }

            .main-wrapper>div {
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

            .main-wrapper>div {
                padding: 16px 12px;
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
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
    $pageTitle = 'Department Form';
    $showExport = false;
    include '../header.php';
    ?>

    <div class="main-wrapper">
        <div>
            <form action="send.php" method="POST" novalidate>

                <h1>Department Form</h1>

                <h2>Department ID: <span class="required">*</span></h2>
                <input type="text" name="department_id" placeholder="Enter Department ID"
                    maxlength="100"
                    class="<?php echo ($errorField === 'department_id') ? 'input-error' : ''; ?>"
                    value="<?php echo htmlspecialchars($formData['department_id'] ?? '', ENT_QUOTES); ?>" required>
                <?php if ($errorField === 'department_id'): ?>
                    <div class="field-error">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span class="field-error-text"><?php echo htmlspecialchars($errorMessage); ?></span>
                    </div>
                <?php endif; ?>

                <h2>Department Name: <span class="required">*</span></h2>
                <input type="text" name="dname" placeholder="Enter Department" pattern="[A-Za-z\s]+"
                    title="Only letters and spaces allowed"
                    class="<?php echo ($errorField === 'dname') ? 'input-error' : ''; ?>"
                    value="<?php echo htmlspecialchars($formData['dname'] ?? '', ENT_QUOTES); ?>" required>
                <?php if ($errorField === 'dname'): ?>
                    <div class="field-error">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span class="field-error-text"><?php echo htmlspecialchars($errorMessage); ?></span>
                    </div>
                <?php endif; ?>

                <h2>Email: <span class="required">*</span></h2>
                <input type="email" name="email" placeholder="Enter Email"
                    class="<?php echo ($errorField === 'email') ? 'input-error' : ''; ?>"
                    value="<?php echo htmlspecialchars($formData['email'] ?? '', ENT_QUOTES); ?>" required>
                <?php if ($errorField === 'email'): ?>
                    <div class="field-error">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span class="field-error-text"><?php echo htmlspecialchars($errorMessage); ?></span>
                    </div>
                <?php endif; ?>

                <h2>Contact Number: <span class="required">*</span></h2>
                <div class="phone-row">
                    <select name="country_code" required>
                        <option value="+91" <?php echo (!isset($formData['country_code']) || $formData['country_code'] === '+91') ? 'selected' : ''; ?>>+91 (IN)</option>
                        <option value="+1" <?php echo (isset($formData['country_code']) && $formData['country_code'] === '+1') ? 'selected' : ''; ?>>+1 (US)</option>
                        <option value="+44" <?php echo (isset($formData['country_code']) && $formData['country_code'] === '+44') ? 'selected' : ''; ?>>+44 (UK)</option>
                        <option value="+61" <?php echo (isset($formData['country_code']) && $formData['country_code'] === '+61') ? 'selected' : ''; ?>>+61 (AU)</option>
                        <option value="+971" <?php echo (isset($formData['country_code']) && $formData['country_code'] === '+971') ? 'selected' : ''; ?>>+971 (AE)</option>
                    </select>
                    <input type="tel" name="number" id="phone-number" placeholder="Enter 10 digit number"
                        pattern="[0-9]{10}"
                        maxlength="10"
                        class="<?php echo ($errorField === 'number') ? 'input-error' : ''; ?>"
                        value="<?php echo htmlspecialchars($formData['number'] ?? '', ENT_QUOTES); ?>" required>
                </div>
                <?php if ($errorField === 'number'): ?>
                    <div class="field-error">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span class="field-error-text"><?php echo htmlspecialchars($errorMessage); ?></span>
                    </div>
                <?php endif; ?>
                <div class="field-error" id="phone-error" style="display: none; margin-bottom: 16px;">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span class="field-error-text" id="phone-error-text"></span>
                </div>

                <h2>Department Responsibilities: <span class="required">*</span></h2>
                <input type="text" name="resp" placeholder="Enter Responsibilities"
                    value="<?php echo htmlspecialchars($formData['resp'] ?? '', ENT_QUOTES); ?>" required>

                <h2>Annual Budget: <span class="required">*</span></h2>
                <input type="text" name="budget" placeholder="Enter Annual Budget"
                    value="<?php echo htmlspecialchars($formData['budget'] ?? '₹', ENT_QUOTES); ?>" required>

                <h2>Department Status: <span class="required">*</span></h2>
                <label><input type="radio" name="status" value="Active" <?php echo (isset($formData['status']) && $formData['status'] === 'Active') ? 'checked' : ''; ?> required> Active</label>
                <label><input type="radio" name="status" value="Inactive" <?php echo (isset($formData['status']) && $formData['status'] === 'Inactive') ? 'checked' : ''; ?> required> Inactive</label>

                <h2>Description: <span class="required">*</span></h2>
                <textarea name="description" placeholder="Write Description" required><?php echo htmlspecialchars($formData['description'] ?? '', ENT_QUOTES); ?></textarea>


                <button type="submit">Submit</button>
            </form>
        </div>
    </div>
    <script>
        // =============================
        // PHONE NUMBER VALIDATION
        // =============================
        const phoneInput = document.getElementById('phone-number');
        const phoneError = document.getElementById('phone-error');
        const phoneErrorText = document.getElementById('phone-error-text');

        if (phoneInput && phoneError && phoneErrorText) {
            // Real-time validation as user types
            phoneInput.addEventListener('input', function() {
                // Remove non-numeric characters
                this.value = this.value.replace(/[^0-9]/g, '');

                // Limit to 10 digits
                if (this.value.length > 10) {
                    this.value = this.value.substring(0, 10);
                }

                // Reset error
                phoneError.style.display = 'none';
                this.classList.remove('input-error');

                // Show error if less than 10 digits
                if (this.value.length > 0 && this.value.length < 10) {
                    phoneErrorText.textContent = 'Contact Number must be exactly 10 digits!';
                    phoneError.style.display = 'flex';
                    this.classList.add('input-error');
                }
            });
        }

        // =============================
        // FORM SUBMISSION VALIDATION
        // =============================
        const form = document.querySelector('form');

        if (form) {
            // Phone validation on submit (runs first with capture)
            form.addEventListener('submit', function(e) {
                if (phoneInput && phoneInput.value.length > 0 && phoneInput.value.length !== 10) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    phoneErrorText.textContent = 'Contact Number must be exactly 10 digits!';
                    phoneError.style.display = 'flex';
                    phoneInput.classList.add('input-error');
                    phoneInput.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    phoneInput.focus();
                    return false;
                }
            }, {
                capture: true
            });

            // General required fields validation
            form.addEventListener('submit', function(e) {
                // Remove all previous custom errors
                document.querySelectorAll('.custom-required-error').forEach(err => err.remove());

                // Get all required fields EXCEPT those in phone-row
                const requiredFields = Array.from(form.querySelectorAll('[required]')).filter(field => {
                    return !field.closest('.phone-row');
                });

                let firstEmptyField = null;

                // Check each required field
                requiredFields.forEach(field => {
                    field.classList.remove('input-error');

                    const isEmpty = field.value.trim() === '' ||
                        (field.tagName === 'SELECT' && (field.selectedIndex === 0 || field.value === '')) ||
                        (field.type === 'radio' && !form.querySelector(`input[name="${field.name}"]:checked`));

                    if (isEmpty && !firstEmptyField) {
                        firstEmptyField = field;
                    }
                });

                // Check phone number separately
                if (phoneInput && phoneInput.value.trim() === '') {
                    if (!firstEmptyField) {
                        firstEmptyField = phoneInput;
                    }
                }

                // If found empty field, show error
                if (firstEmptyField) {
                    e.preventDefault();

                    firstEmptyField.classList.add('input-error');

                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'field-error custom-required-error';
                    errorDiv.style.marginBottom = '16px';
                    errorDiv.innerHTML = `
                    <i class="fas fa-exclamation-triangle"></i>
                    <span class="field-error-text">This field is required!</span>
                `;

                    // Insert error message
                    const phoneRow = firstEmptyField.closest('.phone-row');
                    if (phoneRow) {
                        phoneRow.parentNode.insertBefore(errorDiv, phoneRow.nextSibling);
                    } else {
                        firstEmptyField.parentNode.insertBefore(errorDiv, firstEmptyField.nextSibling);
                    }

                    // Scroll and focus
                    firstEmptyField.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    firstEmptyField.focus();

                    // Remove error on input
                    firstEmptyField.addEventListener('input', function() {
                        this.classList.remove('input-error');
                        const customError = document.querySelector('.custom-required-error');
                        if (customError) customError.remove();
                    }, {
                        once: true
                    });

                    // Special handling for radio buttons
                    if (firstEmptyField.type === 'radio') {
                        const radioGroup = form.querySelectorAll(`input[name="${firstEmptyField.name}"]`);
                        radioGroup.forEach(radio => {
                            radio.addEventListener('change', function() {
                                radioGroup.forEach(r => r.classList.remove('input-error'));
                                const customError = document.querySelector('.custom-required-error');
                                if (customError) customError.remove();
                            }, {
                                once: true
                            });
                        });
                    }

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

        // =============================
        // AUTO-HIDE SERVER ERRORS
        // =============================
        const serverErrors = document.querySelectorAll('.field-error:not(#phone-error):not(.custom-required-error)');
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

            const errorInput = document.querySelector('.input-error');
            if (errorInput && errorInput.id !== 'phone-number') {
                errorInput.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
                errorInput.focus();
            }
        }
    </script>






    <?php include '../footer.php'; ?>
</body>

</html>