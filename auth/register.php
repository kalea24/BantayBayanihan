<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    if ($_SESSION['role'] === 'responder') {
        header('Location: ../responder/index.php');
    } else {
        header('Location: ../resident/index.php');
    }
    exit;
}

// Get role from URL parameter (optional)
$default_role = isset($_GET['role']) ? $_GET['role'] : 'resident';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Bantay Bayanihan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .form-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-danger">
        <div class="container">
            <a class="navbar-brand" href="../index.php">Bantay Bayanihan</a>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-danger text-white">
                        <h3 class="mb-0">Register for Bantay Bayanihan</h3>
                    </div>
                    <div class="card-body">
                        <div id="alert-container"></div>
                        
                        <form id="registerForm">
                            <!-- Personal Information -->
                            <div class="form-section">
                                <h5 class="mb-3">Personal Information</h5>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">First Name *</label>
                                        <input type="text" class="form-control" id="firstName" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Last Name *</label>
                                        <input type="text" class="form-control" id="lastName" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Email *</label>
                                        <input type="email" class="form-control" id="email" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Phone Number</label>
                                        <input type="tel" class="form-control" id="phone" placeholder="09123456789">
                                    </div>
                                </div>
                            </div>

                            <!-- Address Information -->
                            <div class="form-section">
                                <h5 class="mb-3">Address Information</h5>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Barangay</label>
                                        <input type="text" class="form-control" id="barangay" placeholder="e.g., Central">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Purok/Zone</label>
                                        <input type="text" class="form-control" id="purok" placeholder="e.g., Purok 1">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Street Address</label>
                                    <input type="text" class="form-control" id="street" placeholder="House/Block/Lot, Street">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">City/Municipality</label>
                                    <input type="text" class="form-control" id="city" placeholder="e.g., Manila">
                                </div>
                            </div>

                            <!-- Account Information -->
                            <div class="form-section">
                                <h5 class="mb-3">Account Information</h5>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Password *</label>
                                        <input type="password" class="form-control" id="password" required minlength="6">
                                        <small class="text-muted">At least 6 characters</small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Confirm Password *</label>
                                        <input type="password" class="form-control" id="confirmPassword" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Register as *</label>
                                    <select class="form-select" id="role" required>
                                        <option value="resident" <?= $default_role === 'resident' ? 'selected' : '' ?>>
                                            Resident - Access evacuation maps, drills, and AI assistant
                                        </option>
                                        <option value="responder" <?= $default_role === 'responder' ? 'selected' : '' ?>>
                                            Responder - Manage sites and view community analytics
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <!-- Household Information (Optional) -->
                            <div class="form-section">
                                <h5 class="mb-3">Household Information (Optional)</h5>
                                <div class="mb-3">
                                    <label class="form-label">Number of household members</label>
                                    <input type="number" class="form-control" id="householdSize" min="1" value="1">
                                </div>
                            </div>

                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="agreeTerms" required>
                                <label class="form-check-label" for="agreeTerms">
                                    I agree to share my information for disaster preparedness and emergency response purposes
                                </label>
                            </div>

                            <button type="submit" class="btn btn-danger w-100 py-2">
                                <strong>Create Account</strong>
                            </button>
                        </form>

                        <div class="mt-3 text-center">
                            <p>Already have an account? <a href="login.php">Login here</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.getElementById('registerForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Get form values
        const firstName = document.getElementById('firstName').value.trim();
        const lastName = document.getElementById('lastName').value.trim();
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirmPassword').value;
        const role = document.getElementById('role').value;
        const phone = document.getElementById('phone').value.trim();
        const barangay = document.getElementById('barangay').value.trim();
        const purok = document.getElementById('purok').value.trim();
        const street = document.getElementById('street').value.trim();
        const city = document.getElementById('city').value.trim();
        const householdSize = document.getElementById('householdSize').value;

        // Validation
        if (password !== confirmPassword) {
            showAlert('Passwords do not match', 'danger');
            return;
        }

        if (password.length < 6) {
            showAlert('Password must be at least 6 characters', 'danger');
            return;
        }

        // Prepare data
        const data = {
            first_name: firstName,
            last_name: lastName,
            email: email,
            password: password,
            role: role,
            phone: phone,
            barangay: barangay,
            purok: purok,
            address: {
                street: street,
                barangay: barangay,
                purok: purok,
                city: city
            },
            household_size: householdSize
        };

        try {
            const response = await fetch('../api/auth/register.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                showAlert('Registration successful! Redirecting...', 'success');
                
                // Redirect based on role
                setTimeout(() => {
                    if (result.user.role === 'responder') {
                        window.location.href = '../responder/index.php';
                    } else {
                        window.location.href = '../resident/index.php';
                    }
                }, 1500);
            } else {
                showAlert(result.message || 'Registration failed. Please try again.', 'danger');
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('An error occurred. Please try again.', 'danger');
        }
    });

    function showAlert(message, type) {
        const alertContainer = document.getElementById('alert-container');
        alertContainer.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        // Scroll to top to show alert
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
    </script>
</body>
</html>