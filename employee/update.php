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

require_once '../db.php';

if (!isset($_GET['id'])) {
    die("No employee ID provided.");
}

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM employees WHERE id = ?");
$stmt->bind_param("s", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if (!$row) {
    die("Employee not found!");
}

// Get error from session
$errorField = $_SESSION['error_field'] ?? '';
$errorMessage = $_SESSION['error_message'] ?? '';
unset($_SESSION['error_field']);
unset($_SESSION['error_message']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Update Employee</title>
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
            min-height: 100vh;
        }

        .main-wrapper {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 24px 16px 80px;
        }

        .form-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
            padding: 28px 24px;
            width: 100%;
            max-width: 600px;
        }

        .form-card h1 {
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

        .field-group {
            margin-bottom: 16px;
            display: flex;
            flex-direction: column;
        }

        .field-group label {
            font-size: 0.95rem;
            margin-bottom: 6px;
            color: #111827;
            font-weight: 600;
        }

        .field-group input,
        .field-group select {
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 0.95rem;
            font-family: Arial, sans-serif;
            background: #f9fafb;
            transition: border-color 0.2s, background 0.2s;
        }

        .field-group input:focus,
        .field-group select:focus {
            outline: none;
            border-color: #68A691;
            background: #fff;
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

        .form-buttons {
            display: flex;
            gap: 12px;
            margin-top: 24px;
        }

        .btn {
            flex: 1;
            padding: 12px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 1rem;
            text-align: center;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-save {
            background: #68A691;
            color: #fff;
            border: none;
        }

        .btn-save:hover {
            background: #4a8970;
            transform: translateY(-1px);
            box-shadow: 0 6px 14px rgba(104, 166, 145, 0.3);
        }

        .btn-cancel {
            background: #f3f4f6;
            color: #111827;
            border: 1px solid #d1d5db;
            display: inline-block;
        }

        .btn-cancel:hover {
            background: #e5e7eb;
        }

        /* Phone Number Row */
        .phone-row {
            display: flex;
            gap: 8px;
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


        @media (max-width: 768px) {
            body {
                padding-top: 80px;
            }

            .main-wrapper {
                padding: 20px 12px 60px;
            }

            .form-card {
                padding: 20px 16px;
            }

            .form-card h1 {
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

            .form-card {
                padding: 16px 12px;
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
            }

            .form-card h1 {
                font-size: 1.3rem;
                margin-bottom: 16px;
            }

            .field-group label {
                font-size: 0.9rem;
            }

            .btn {
                font-size: 1rem;
                padding: 10px;
            }

            .form-buttons {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>

<body>
    <?php
    $pageTitle = 'Update Employee Data';
    $showExport = false;
    include '../header.php';
    ?>

    <div class="main-wrapper">
        <div class="form-card">
            <h1>Update Employee Data</h1>
            <form method="POST" action="get.php" novalidate>
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8'); ?>">

                <div class="field-group">
                    <label for="department_id">Department ID: <span class="required">*</span></label>
                    <input type="text" name="department_id" id="department_id"
                        class="<?php echo ($errorField === 'department_id') ? 'input-error' : ''; ?>"
                        value="<?php echo htmlspecialchars($row['department_id'], ENT_QUOTES, 'UTF-8'); ?>" maxlength="100" required>
                    <?php if ($errorField === 'department_id'): ?>
                        <div class="field-error">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span class="field-error-text"><?php echo htmlspecialchars($errorMessage); ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="field-group">
    <label for="ename">Full Name: <span class="required">*</span></label>
    <input type="text" name="ename" id="ename"
        pattern="[A-Za-z\s]{1,100}"
        maxlength="100"
        title="Only letters and spaces allowed (max 100 characters)"
        value="<?php echo htmlspecialchars($row['ename'], ENT_QUOTES, 'UTF-8'); ?>" required>
</div>


                <div class="field-group">
                    <label for="dob">Date of Birth: <span class="required">*</span></label>
                    <input type="date" name="dob" id="dob"
                        class="<?php echo ($errorField === 'dob') ? 'input-error' : ''; ?>"
                        value="<?php echo htmlspecialchars($row['dob'], ENT_QUOTES, 'UTF-8'); ?>" required>
                    <?php if ($errorField === 'dob'): ?>
                        <div class="field-error">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span class="field-error-text"><?php echo htmlspecialchars($errorMessage); ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="field-error" id="dob-error" style="display: none;">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span class="field-error-text" id="dob-error-text"></span>
                    </div>
                </div>


                <div class="field-group">
                    <label for="gender">Gender: <span class="required">*</span></label>
                    <select name="gender" id="gender" required>
                        <option disabled>--Select--</option>
                        <option value="Male" <?php if ($row['gender'] === 'Male') echo 'selected'; ?>>Male</option>
                        <option value="Female" <?php if ($row['gender'] === 'Female') echo 'selected'; ?>>Female</option>
                    </select>
                </div>

                <div class="field-group">
                    <label for="email">Email: <span class="required">*</span></label>
                    <input type="email" name="email" id="email"
                        class="<?php echo ($errorField === 'email') ? 'input-error' : ''; ?>"
                        value="<?php echo htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8'); ?>" required>
                    <?php if ($errorField === 'email'): ?>
                        <div class="field-error">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span class="field-error-text"><?php echo htmlspecialchars($errorMessage); ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="field-group">
                    <label for="pnumber">Phone Number: <span class="required">*</span></label>
                    <div class="phone-row">
                        <select name="country_code" required>
                            <option value="+91" <?php echo (!isset($row['country_code']) || $row['country_code'] === '+91') ? 'selected' : ''; ?>>+91 (IN)</option>
                            <option value="+1" <?php echo (isset($row['country_code']) && $row['country_code'] === '+1') ? 'selected' : ''; ?>>+1 (US)</option>
                            <option value="+44" <?php echo (isset($row['country_code']) && $row['country_code'] === '+44') ? 'selected' : ''; ?>>+44 (UK)</option>
                            <option value="+61" <?php echo (isset($row['country_code']) && $row['country_code'] === '+61') ? 'selected' : ''; ?>>+61 (AU)</option>
                            <option value="+971" <?php echo (isset($row['country_code']) && $row['country_code'] === '+971') ? 'selected' : ''; ?>>+971 (AE)</option>
                        </select>
                        <input type="tel" name="pnumber" id="pnumber" placeholder="Enter 10 digit number"
                            pattern="[0-9]{10}"
                            maxlength="10"
                            value="<?php echo htmlspecialchars($row['pnumber'], ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                </div>


                <div class="field-group">
                    <label for="address">Address: <span class="required">*</span></label>
                    <input type="text" name="address" id="address"
                        value="<?php echo htmlspecialchars($row['address'], ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>

                <div class="field-group">
                    <label for="designation">Designation: <span class="required">*</span></label>
                    <input type="text" name="designation" id="designation"
                        pattern="[A-Za-z\s]{1,25}"
                        maxlength="25"
                        title="Only letters and spaces allowed (max 25 characters)"
                        value="<?php echo htmlspecialchars($row['designation'], ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>

                <div class="field-group">
                    <label for="salary">Salary: <span class="required">*</span></label>
                    <div class="phone-row">
                        <select name="currency" required>
                            <option value="₹" <?php echo (!isset($row['currency']) || $row['currency'] === '₹') ? 'selected' : ''; ?>>₹ INR</option>
                            <option value="$" <?php echo (isset($row['currency']) && $row['currency'] === '$') ? 'selected' : ''; ?>>$ USD</option>
                            <option value="£" <?php echo (isset($row['currency']) && $row['currency'] === '£') ? 'selected' : ''; ?>>£ GBP</option>
                            <option value="€" <?php echo (isset($row['currency']) && $row['currency'] === '€') ? 'selected' : ''; ?>>€ EUR</option>
                            <option value="د.إ" <?php echo (isset($row['currency']) && $row['currency'] === 'د.إ') ? 'selected' : ''; ?>>د.إ AED</option>
                        </select>
                        <input type="text" name="salary" id="salary"
                            class="<?php echo ($errorField === 'salary') ? 'input-error' : ''; ?>"
                            value="<?php echo htmlspecialchars($row['salary'], ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                    <?php if ($errorField === 'salary'): ?>
                        <div class="field-error">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span class="field-error-text"><?php echo htmlspecialchars($errorMessage); ?></span>
                        </div>
                    <?php endif; ?>
                </div>


                <div class="field-group">
                    <label for="joining_date">Date of Joining: <span class="required">*</span></label>
                    <input type="date" name="joining_date" id="joining_date"
                        value="<?php echo htmlspecialchars($row['joining_date'], ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>

                <div class="field-group">
                    <label for="aadhar">Aadhar Number: <span class="required">*</span></label>
                    <input type="text" name="aadhar" id="aadhar" maxlength="12" placeholder="Enter 12 digit Aadhar Number"
                        pattern="[0-9]{12}"
                        class="<?php echo ($errorField === 'aadhar') ? 'input-error' : ''; ?>"
                        value="<?php echo htmlspecialchars($row['aadhar'], ENT_QUOTES, 'UTF-8'); ?>" required>
                    <?php if ($errorField === 'aadhar'): ?>
                        <div class="field-error">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span class="field-error-text"><?php echo htmlspecialchars($errorMessage); ?></span>
                        </div>
                    <?php endif; ?>
                </div>


                <div class="form-buttons">
                    <button type="submit" class="btn btn-save">Save Changes</button>
                    <a href="get.php" class="btn btn-cancel">Cancel</a>
                </div>
            </form>
        </div>
    </div>



    <script>
        // =============================
        // DOB VALIDATION
        // =============================
        const dobInput = document.getElementById('dob');
        const dobError = document.getElementById('dob-error');
        const dobErrorText = document.getElementById('dob-error-text');

        if (dobInput && dobError && dobErrorText) {
            dobInput.addEventListener('change', function() {
                const selectedDate = new Date(this.value);
                const minDate = new Date('1950-01-01');
                const maxDate = new Date();

                dobError.style.display = 'none';
                this.classList.remove('input-error');

                if (this.value === '') return;

                if (selectedDate < minDate) {
                    dobErrorText.textContent = 'Date of Birth must be after January 1, 1950!';
                    dobError.style.display = 'flex';
                    this.classList.add('input-error');
                } else if (selectedDate > maxDate) {
                    dobErrorText.textContent = 'Date of Birth cannot be in the future!';
                    dobError.style.display = 'flex';
                    this.classList.add('input-error');
                }
            });

            dobInput.addEventListener('blur', function() {
                if (this.value === '') return;

                const selectedDate = new Date(this.value);
                const minDate = new Date('1950-01-01');
                const maxDate = new Date();

                if (selectedDate < minDate || selectedDate > maxDate) {
                    dobError.style.display = 'flex';
                    this.classList.add('input-error');
                }
            });
        }

        // =============================
// FULL NAME VALIDATION
// =============================
const enameInput = document.querySelector('#ename');

if (enameInput) {
    enameInput.addEventListener('input', function() {
        // Remove numbers and special characters, keep only letters and spaces
        this.value = this.value.replace(/[^A-Za-z\s]/g, '');
        
        // Limit to 100 characters
        if (this.value.length > 100) {
            this.value = this.value.substring(0, 100);
        }
    });

    enameInput.addEventListener('blur', function() {
        // Trim extra spaces and remove multiple consecutive spaces
        this.value = this.value.trim().replace(/\s+/g, ' ');
    });
}


        // =============================
        // PHONE NUMBER VALIDATION
        // =============================
        const phoneInputUpdate = document.querySelector('#pnumber');

        if (phoneInputUpdate) {
            const phoneError = document.createElement('div');
            phoneError.className = 'field-error';
            phoneError.id = 'phone-error-update';
            phoneError.style.display = 'none';
            phoneError.innerHTML = `
            <i class="fas fa-exclamation-triangle"></i>
            <span class="field-error-text" id="phone-error-text-update"></span>
        `;

            const fieldGroup = phoneInputUpdate.closest('.field-group');
            if (fieldGroup) {
                fieldGroup.appendChild(phoneError);
            }

            phoneInputUpdate.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');

                if (this.value.length > 10) {
                    this.value = this.value.substring(0, 10);
                }

                phoneError.style.display = 'none';
                this.classList.remove('input-error');

                if (this.value.length > 0 && this.value.length < 10) {
                    const phoneErrorText = document.getElementById('phone-error-text-update');
                    phoneErrorText.textContent = 'Phone Number must be exactly 10 digits!';
                    phoneError.style.display = 'flex';
                    this.classList.add('input-error');
                }
            });
        }

        // =============================
        // SALARY VALIDATION
        // =============================
        const salaryInput = document.querySelector('#salary');

        if (salaryInput) {
            salaryInput.addEventListener('input', function() {
                // Allow only numbers and one decimal point
                this.value = this.value.replace(/[^0-9.]/g, '');

                // Prevent multiple decimal points
                const parts = this.value.split('.');
                if (parts.length > 2) {
                    this.value = parts[0] + '.' + parts.slice(1).join('');
                }

                // Limit decimal places to 2
                if (parts.length === 2 && parts[1].length > 2) {
                    this.value = parts[0] + '.' + parts[1].substring(0, 2);
                }

                // Prevent starting with decimal
                if (this.value.startsWith('.')) {
                    this.value = '0' + this.value;
                }
            });

            salaryInput.addEventListener('blur', function() {
                // Remove if just a decimal point
                if (this.value === '.' || this.value === '0.') {
                    this.value = '';
                }

                // Remove if zero or negative
                if (this.value !== '' && parseFloat(this.value) <= 0) {
                    this.value = '';
                }

                // Format to 2 decimal places if has decimal
                if (this.value !== '' && this.value.includes('.')) {
                    const num = parseFloat(this.value);
                    if (!isNaN(num)) {
                        this.value = num.toFixed(2);
                    }
                }
            });
        }

        // =============================
        // AADHAR NUMBER VALIDATION
        // =============================
        const aadharInput = document.querySelector('#aadhar');

        if (aadharInput) {
            const aadharError = document.createElement('div');
            aadharError.className = 'field-error';
            aadharError.id = 'aadhar-error-update';
            aadharError.style.display = 'none';
            aadharError.innerHTML = `
            <i class="fas fa-exclamation-triangle"></i>
            <span class="field-error-text" id="aadhar-error-text-update"></span>
        `;

            const fieldGroup = aadharInput.closest('.field-group');
            if (fieldGroup) {
                fieldGroup.appendChild(aadharError);
            }

            aadharInput.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');

                if (this.value.length > 12) {
                    this.value = this.value.substring(0, 12);
                }

                aadharError.style.display = 'none';
                this.classList.remove('input-error');

                if (this.value.length > 0 && this.value.length < 12) {
                    const aadharErrorText = document.getElementById('aadhar-error-text-update');
                    aadharErrorText.textContent = 'Aadhar Number must be exactly 12 digits!';
                    aadharError.style.display = 'flex';
                    this.classList.add('input-error');
                }
            });
        }

        // =============================
        // DESIGNATION VALIDATION
        // =============================
        const designationInput = document.querySelector('#designation');

        if (designationInput) {
            designationInput.addEventListener('input', function() {
                // Remove numbers and special characters, keep only letters and spaces
                this.value = this.value.replace(/[^A-Za-z\s]/g, '');

                // Limit to 25 characters
                if (this.value.length > 25) {
                    this.value = this.value.substring(0, 25);
                }
            });

            designationInput.addEventListener('blur', function() {
                // Trim extra spaces
                this.value = this.value.trim().replace(/\s+/g, ' ');
            });
        }


        // =============================
        // FORM SUBMISSION VALIDATION
        // =============================
        const form = document.querySelector('form');

        if (form) {
            form.addEventListener('submit', function(e) {
                if (phoneInputUpdate && phoneInputUpdate.value.length > 0 && phoneInputUpdate.value.length !== 10) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    const phoneErrorText = document.getElementById('phone-error-text-update');
                    phoneErrorText.textContent = 'Phone Number must be exactly 10 digits!';
                    document.getElementById('phone-error-update').style.display = 'flex';
                    phoneInputUpdate.classList.add('input-error');
                    phoneInputUpdate.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    phoneInputUpdate.focus();
                    return false;
                }
            }, {
                capture: true
            });

            form.addEventListener('submit', function(e) {
                if (aadharInput && aadharInput.value.length > 0 && aadharInput.value.length !== 12) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    const aadharErrorText = document.getElementById('aadhar-error-text-update');
                    aadharErrorText.textContent = 'Aadhar Number must be exactly 12 digits!';
                    document.getElementById('aadhar-error-update').style.display = 'flex';
                    aadharInput.classList.add('input-error');
                    aadharInput.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    aadharInput.focus();
                    return false;
                }
            }, {
                capture: true
            });

            form.addEventListener('submit', function(e) {
                document.querySelectorAll('.custom-required-error').forEach(err => err.remove());

                if (dobInput) {
                    if (dobInput.value === '') {
                        e.preventDefault();
                        dobErrorText.textContent = 'Date of Birth is required!';
                        dobError.style.display = 'flex';
                        dobInput.classList.add('input-error');
                        dobInput.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                        dobInput.focus();
                        return false;
                    }

                    const selectedDate = new Date(dobInput.value);
                    const minDate = new Date('1950-01-01');
                    const maxDate = new Date();

                    if (selectedDate < minDate || selectedDate > maxDate) {
                        e.preventDefault();
                        dobInput.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                        dobInput.focus();
                        return false;
                    }
                }

                const requiredFields = Array.from(form.querySelectorAll('[required]')).filter(field => {
                    return !field.closest('.phone-row');
                });

                let firstEmptyField = null;

                requiredFields.forEach(field => {
                    field.classList.remove('input-error');

                    const isEmpty = field.value.trim() === '' ||
                        (field.tagName === 'SELECT' && (field.selectedIndex === 0 || field.value === ''));

                    if (isEmpty && !firstEmptyField) {
                        firstEmptyField = field;
                    }
                });

                if (phoneInputUpdate && phoneInputUpdate.value.trim() === '') {
                    if (!firstEmptyField) {
                        firstEmptyField = phoneInputUpdate;
                    }
                }

                if (salaryInput && salaryInput.value.trim() === '') {
                    if (!firstEmptyField) {
                        firstEmptyField = salaryInput;
                    }
                }

                if (firstEmptyField) {
                    e.preventDefault();

                    firstEmptyField.classList.add('input-error');

                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'field-error custom-required-error';
                    errorDiv.innerHTML = `
                    <i class="fas fa-exclamation-triangle"></i>
                    <span class="field-error-text">This field is required!</span>
                `;

                    const fieldGroup = firstEmptyField.closest('.field-group');
                    if (fieldGroup) {
                        fieldGroup.appendChild(errorDiv);
                    }

                    firstEmptyField.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    firstEmptyField.focus();

                    firstEmptyField.addEventListener('input', function() {
                        this.classList.remove('input-error');
                        const customError = fieldGroup.querySelector('.custom-required-error');
                        if (customError) customError.remove();
                    }, {
                        once: true
                    });

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
        const serverErrors = document.querySelectorAll('.field-error:not(#dob-error):not(#phone-error-update):not(#aadhar-error-update):not(.custom-required-error)');
        if (serverErrors.length > 0) {
            setTimeout(() => {
                serverErrors.forEach(error => {
                    error.style.opacity = '0';
                    error.style.transition = 'opacity 0.3s ease';
                    setTimeout(() => {
                        error.style.display = 'none';
                    }, 300);
                });
            }, 5000);
        }
    </script>


    <?php include '../footer.php'; ?>
</body>

</html>