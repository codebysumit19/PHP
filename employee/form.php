<?php
session_start();
if (!isset($_SESSION['email'])) {
    header('Location: ../login.php');
    exit;
}

// Auto logout after 50 minutes of inactivity
$timeout = 50 * 60;

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
    $_SESSION = [];
    session_destroy();
    header('Location: ../login.php');
    exit;
}
$_SESSION['last_activity'] = time();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Employee Form</title>
    <link rel="icon" type="image/png" href="../fi-snsuxx-php-logo.jpg">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        /* html, body{height:100%;} */
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #e8f5e9, #ffffff);
            /* display:flex;
    flex-direction:column; */
            margin: 0;
        }

        .main-wrapper {
            /* flex:1; */
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 40px 16px 80px;
        }

        form {
            background: #fff;
            padding: 24px 20px 28px;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            width: 600px;
            /* max-width: 600px;  */
            /* max-height: 80vh; */
            /* overflow-y: auto; */
        }

        h1 {
            text-align: center;
            margin-bottom: 16px;
            font-size: 1.6rem;
        }

        h2 {
            font-size: 1rem;
            margin-top: 10px
        }

        input[type="text"],
        input[type="date"],
        input[type="email"],
        input[type="number"],
        input[type="tel"],
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background: #f9fafb;
            font-size: 0.95rem;
        }

        button {
            background: #4CAF50;
            color: #fff;
            border: none;
            padding: 12px;
            border-radius: 6px;
            cursor: pointer;
            width: 100%;
            font-size: 1.05rem;
            margin-top: 20px;
            transition: background 0.2s ease, transform 0.15s ease, box-shadow 0.15s ease;
        }

        button:hover {
            background: #249f60;
            transform: translateY(-1px);
            box-shadow: 0 6px 14px rgb(15, 118, 110, 0.3);
        }

        /* Tablets and up */
        @media (min-width: 768px) {
            .main-wrapper {
                padding: 40px 16px;
            }

            form {
                padding: 24px;
            }

            h1 {
                font-size: 1.8rem;
            }
        }

        /* Very small phones */
        @media (max-width: 480px) {
            form {
                padding: 16px;
                max-height: none;
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
            }

            h1 {
                font-size: 1.4rem;
            }

            h2 {
                font-size: 0.95rem;
            }

            button {
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
                        placeholder="Enter Department ID (departments.id)"
                        maxlength="100" required>
                </h2>

                <h2>Full Name:
                    <input type="text" name="ename" pattern="[A-Za-z\s]+" placeholder="Enter full name" required>
                </h2>

                <h2>Date of Birth:
                    <input type="date" name="dob" required>
                </h2>

                <h2>Gender:
                    <select name="gender" required>
                        <option disabled selected>--Select--</option>
                        <option>Male</option>
                        <option>Female</option>
                    </select>
                </h2>

                <h2>Email:
                    <input type="email" name="email" placeholder="Enter email" required>
                </h2>

                <h2>Phone Number:
                    <input type="tel" name="pnumber" minlength="10" maxlength="13"
                        placeholder="Enter phone number" required>
                </h2>

                <h2>Address:
                    <input type="text" name="address" placeholder="Enter address" required>
                </h2>

                <h2>Designation:
                    <input type="text" name="designation" pattern="[A-Za-z\s]+"
                        title="Only letters and spaces allowed" placeholder="Enter designation" required>
                </h2>

                <h2>Salary:
                    <input type="number" step="0.01" name="salary" placeholder="Enter salary" required>
                </h2>

                <h2>Date of Joining:
                    <input type="date" name="joining_date" required>
                </h2>

                <h2>Aadhar Number:
                    <input type="number" name="aadhar" maxlength="12" placeholder="Write Aadhar Number">
                </h2>

                <button type="submit">Submit</button>
            </form>
        </div>
    </div>

    <?php include '../footer.php'; ?>
</body>

</html>