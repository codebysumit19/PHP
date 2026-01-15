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
    die("No department ID provided.");
}

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM departments WHERE id = ?");
$stmt->bind_param("s", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if (!$row) {
    die("Department not found!");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Update Department</title>
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
            background: linear-gradient(135deg, #e8f5e9, #fff);
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

        .field-group {
            margin-bottom: 16px;
            display: flex;
            flex-direction: column;
        }

        .field-group label {
            margin-bottom: 6px;
            font-weight: 600;
            color: #111827;
            font-size: 0.95rem;
        }

        .field-group input,
        .field-group textarea {
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background: #f9fafb;
            font-size: 0.95rem;
            transition: border-color 0.2s, background 0.2s;
            font-family: Arial, sans-serif;
        }

        .field-group input:focus,
        .field-group textarea:focus {
            outline: none;
            border-color: #68A691;
            background: #fff;
        }

        .field-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .radio-group {
            display: flex;
            gap: 20px;
            align-items: center;
            margin-top: 6px;
        }

        .radio-group label {
            font-weight: 400;
            font-size: 0.95rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .radio-group input[type="radio"] {
            width: 16px;
            height: 16px;
            cursor: pointer;
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
    $pageTitle = 'Update Department Data';
    $showExport = false;
    include '../header.php';
    ?>

    <div class="main-wrapper">
        <div class="form-card">
            <h1>Update Department Data</h1>
            <form action="get.php" method="POST" id="update-department-form" novalidate>
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8'); ?>">

                <div class="field-group">
                    <label for="department-deptid">Department ID: <span class="required">*</span></label>
                    <input type="text" name="department_id" id="department-deptid" 
                        value="<?php echo htmlspecialchars($row['department_id'], ENT_QUOTES, 'UTF-8'); ?>" 
                        maxlength="100" required>
                </div>

                <div class="field-group">
                    <label for="department-name">Department Name: <span class="required">*</span></label>
                    <input type="text" name="dname" id="department-name" 
                        value="<?php echo htmlspecialchars($row['dname'], ENT_QUOTES, 'UTF-8'); ?>" 
                        pattern="[A-Za-z\s]+" required>
                </div>

                <div class="field-group">
                    <label for="department-email">Email: <span class="required">*</span></label>
                    <input type="email" name="email" id="department-email" 
                        value="<?php echo htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>

               <div class="field-group">
    <label for="department-number">Contact Number: <span class="required">*</span></label>
    <div class="phone-row">
        <select name="country_code" required>
            <option value="+91" <?php echo (!isset($row['country_code']) || $row['country_code'] === '+91') ? 'selected' : ''; ?>>+91 (IN)</option>
            <option value="+1" <?php echo (isset($row['country_code']) && $row['country_code'] === '+1') ? 'selected' : ''; ?>>+1 (US)</option>
            <option value="+44" <?php echo (isset($row['country_code']) && $row['country_code'] === '+44') ? 'selected' : ''; ?>>+44 (UK)</option>
            <option value="+61" <?php echo (isset($row['country_code']) && $row['country_code'] === '+61') ? 'selected' : ''; ?>>+61 (AU)</option>
            <option value="+971" <?php echo (isset($row['country_code']) && $row['country_code'] === '+971') ? 'selected' : ''; ?>>+971 (AE)</option>
        </select>
        <input type="tel" name="number" id="department-number" placeholder="Enter 10 digit number" 
            pattern="[0-9]{10}"
            maxlength="10"
            value="<?php echo htmlspecialchars($row['number'], ENT_QUOTES, 'UTF-8'); ?>" required>
    </div>
</div>


               <div class="field-group">
    <label>Number of Employees:</label>
    <div style="background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 6px; padding: 12px 14px; font-size: 0.95rem; color: #0c4a6e; font-weight: 600; display: flex; align-items: center; gap: 10px;">
        <i class="fas fa-users" style="color: #0ea5e9; font-size: 18px;"></i>
        <span><?php 
            // Calculate current employee count for this department
            $empCountStmt = $conn->prepare("SELECT COUNT(*) as count FROM employees WHERE department_id = ?");
            $empCountStmt->bind_param("s", $row['department_id']);
            $empCountStmt->execute();
            $empCountResult = $empCountStmt->get_result();
            $empCount = $empCountResult->fetch_assoc()['count'];
            $empCountStmt->close();
            echo $empCount;
        ?> employees (calculated automatically)</span>
    </div>
</div>


                <div class="field-group">
                    <label for="department-resp">Responsibilities: <span class="required">*</span></label>
                    <input type="text" name="resp" id="department-resp" 
                        value="<?php echo htmlspecialchars($row['resp'], ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>

                <div class="field-group">
                    <label for="department-budget">Annual Budget: <span class="required">*</span></label>
                    <input type="text" name="budget" id="department-budget" 
                        value="<?php echo htmlspecialchars($row['budget'], ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>

                <div class="field-group">
                    <label>Status: <span class="required">*</span></label>
                    <div class="radio-group">
                        <label><input type="radio" name="status" value="Active" <?php if($row['status']==='Active') echo 'checked'; ?> id="status-active" required> Active</label>
                        <label><input type="radio" name="status" value="Inactive" <?php if($row['status']==='Inactive') echo 'checked'; ?> id="status-inactive" required> Inactive</label>
                    </div>
                </div>

              <div class="field-group">
    <label for="department-description">Description: <span class="required">*</span></label>
    <textarea name="description" id="department-description" required><?php echo htmlspecialchars($row['description'], ENT_QUOTES, 'UTF-8'); ?></textarea>
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
    // PHONE NUMBER VALIDATION
    // =============================
    const phoneInput = document.querySelector('#department-number');
    
    if (phoneInput) {
        const phoneError = document.createElement('div');
        phoneError.className = 'field-error';
        phoneError.id = 'phone-error';
        phoneError.style.display = 'none';
        phoneError.innerHTML = `
            <i class="fas fa-exclamation-triangle"></i>
            <span class="field-error-text" id="phone-error-text"></span>
        `;
        
        const fieldGroup = phoneInput.closest('.field-group');
        if (fieldGroup) {
            fieldGroup.appendChild(phoneError);
        }

        phoneInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
            
            if (this.value.length > 10) {
                this.value = this.value.substring(0, 10);
            }
            
            phoneError.style.display = 'none';
            this.classList.remove('input-error');
            
            if (this.value.length > 0 && this.value.length < 10) {
                const phoneErrorText = document.getElementById('phone-error-text');
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
        // Phone validation on submit (runs first)
        form.addEventListener('submit', function(e) {
            if (phoneInput && phoneInput.value.length > 0 && phoneInput.value.length !== 10) {
                e.preventDefault();
                e.stopImmediatePropagation();
                const phoneErrorText = document.getElementById('phone-error-text');
                phoneErrorText.textContent = 'Contact Number must be exactly 10 digits!';
                const phoneError = document.getElementById('phone-error');
                if (phoneError) phoneError.style.display = 'flex';
                phoneInput.classList.add('input-error');
                phoneInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                phoneInput.focus();
                return false;
            }
        }, { capture: true });

        // General required fields validation
        form.addEventListener('submit', function(e) {
            document.querySelectorAll('.custom-required-error').forEach(err => err.remove());
            
            const requiredFields = Array.from(form.querySelectorAll('[required]')).filter(field => {
                return !field.closest('.phone-row');
            });
            
            let firstEmptyField = null;
            
            requiredFields.forEach(field => {
                field.classList.remove('input-error');
                
                const isEmpty = field.value.trim() === '' || 
                               (field.type === 'radio' && !form.querySelector(`input[name="${field.name}"]:checked`));
                
                if (isEmpty && !firstEmptyField) {
                    firstEmptyField = field;
                }
            });
            
            if (phoneInput && phoneInput.value.trim() === '') {
                if (!firstEmptyField) {
                    firstEmptyField = phoneInput;
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
                
                firstEmptyField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstEmptyField.focus();
                
                firstEmptyField.addEventListener('input', function() {
                    this.classList.remove('input-error');
                    const customError = fieldGroup.querySelector('.custom-required-error');
                    if (customError) customError.remove();
                }, { once: true });
                
                if (firstEmptyField.type === 'radio') {
                    const radioGroup = form.querySelectorAll(`input[name="${firstEmptyField.name}"]`);
                    radioGroup.forEach(radio => {
                        radio.addEventListener('change', function() {
                            radioGroup.forEach(r => r.classList.remove('input-error'));
                            const customError = fieldGroup.querySelector('.custom-required-error');
                            if (customError) customError.remove();
                        }, { once: true });
                    });
                }
                
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
</script>




    <?php include '../footer.php'; ?>
</body>

</html>
