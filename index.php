<?php
require_once 'connection.php';

$errors = [];
$firstName = "";
$lastName = "";
$email = "";
$age = "";
$gender = "";
$phone = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $firstName       = trim($_POST["firstName"] ?? "");
    $lastName        = trim($_POST["lastName"] ?? "");
    $email           = trim($_POST["email"] ?? "");
    $password        = $_POST["password"] ?? "";
    $confirmPassword = $_POST["confirmPassword"] ?? "";
    $age             = trim($_POST["age"] ?? "");
    $gender          = $_POST["gender"] ?? "";
    $phone           = trim($_POST["phone"] ?? "");

    if (empty($firstName)) {
        $errors["firstName"] = "First name is required.";
    } elseif (!preg_match("/^[a-zA-Z ]+$/", $firstName)) {
        $errors["firstName"] = "First name can only contain letters and spaces.";
    }

    if (empty($lastName)) { 
        $errors["lastName"] = "Last name is required.";
    } elseif (!preg_match("/^[a-zA-Z ]+$/", $lastName)) {
        $errors["lastName"] = "Last name can only contain letters and spaces.";
    }

    if (empty($email)) {
        $errors["email"] = "Email address is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors["email"] = "Please enter a valid email address.";
    }

    if (empty($age)) {
        $errors["age"] = "Age is required.";
    } elseif (!is_numeric($age) || intval($age) < 1 || intval($age) > 120) {
        $errors["age"] = "Please enter a valid age between 1 and 120.";
    }

    $allowedGenders = ["Male", "Female", "Other", "Prefer not to say"];
    if (empty($gender)) {
        $errors["gender"] = "Please select your gender.";
    } elseif (!in_array($gender, $allowedGenders)) {
        $errors["gender"] = "Invalid gender selected.";
    }

    if (empty($phone)) {
        $errors["phone"] = "Phone number is required.";
    } elseif (!preg_match("/^\+?[0-9\(\)\-\s]{7,20}$/", $phone)) {
        $errors["phone"] = "Please enter a valid phone number (7 to 20 digits, spaces/dashes/parentheses allowed).";
    }

    if (empty($password)) {
        $errors["password"] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $errors["password"] = "Password must be at least 6 characters.";
    }

    if (empty($confirmPassword)) {
        $errors["confirmPassword"] = "Please confirm your password.";
    } elseif ($password !== $confirmPassword) {
        $errors["confirmPassword"] = "Passwords do not match.";
    }

    if (empty($errors)) {
      try {
        $hashedPass = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (FirstName, LastName, Email, Password, Age, Gender, Phone) VALUES (:firstName, :lastName, :email, :password, :age, :gender, :phone)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':firstName' => $firstName,
            ':lastName'  => $lastName,
            ':email'     => $email,
            ':password' => $hashedPass,
            ':age'      => (int)$age,
            ':gender'   => $gender,
            ':phone'    => $phone
        ]);

        $successMessage = htmlspecialchars($firstName) . " " . htmlspecialchars($lastName) . ", Your account was created successfully!";

        $firstName = "";
        $lastName = "";
        $email = "";
        $age = "";
        $gender = "";
        $phone = "";
      } catch (PDOException $e) {
        $errors["db"] = "Database error: " . $e->getMessage();
      }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container d-flex align-items-center justify-content-center min-vh-100">
  <div class="col-11 col-sm-8 col-md-5 col-lg-4">

    <h3 class="mb-1 text-center">Create account</h3>

    <?php if (!empty($successMessage)): ?>
      <div class="alert alert-success"><?= htmlspecialchars($successMessage) ?></div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>

      <div class="alert alert-danger">
        <ul class="mb-0 ps-3">

          <?php foreach ($errors as $error): ?>
            <li><?= htmlspecialchars($error) ?></li>
          <?php endforeach; ?>

        </ul>
      </div>
    <?php endif; ?>

    <form method="POST">

      <div class="mb-3">
        <label for="firstName" class="form-label">First name</label>
        <input type="text"
               class="form-control <?= isset($errors["firstName"]) ? "is-invalid" : "" ?>" id="firstName" name="firstName" value="<?= htmlspecialchars($firstName) ?>" placeholder="First name">
        <?php if (isset($errors["firstName"])): ?>
          <div class="invalid-feedback"><?= $errors["firstName"] ?></div>
        <?php endif; ?>
      </div>

      <div class="mb-3">
        <label for="lastName" class="form-label">Last name</label>
        <input type="text"
               class="form-control <?= isset($errors["lastName"]) ? "is-invalid" : "" ?>" id="lastName" name="lastName" value="<?= htmlspecialchars($lastName) ?>" placeholder="Last name">
        <?php if (isset($errors["lastName"])): ?>
          <div class="invalid-feedback"><?= $errors["lastName"] ?></div>
        <?php endif; ?>
      </div>

      <div class="mb-3">
        <label for="email" class="form-label">Email address</label>
        <input type="email" class="form-control <?= isset($errors["email"]) ? "is-invalid" : "" ?>" id="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="Email">
        <?php if (isset($errors["email"])): ?>
          <div class="invalid-feedback"><?= $errors["email"] ?></div>
        <?php endif; ?>
      </div>

      <div class="mb-3">
        <label for="phone" class="form-label">Phone number</label>
        <input type="text" class="form-control <?= isset($errors["phone"]) ? "is-invalid" : "" ?>" id="phone" name="phone" value="<?= htmlspecialchars($phone) ?>" placeholder="Phone number">
        <?php if (isset($errors["phone"])): ?>
          <div class="invalid-feedback"><?= $errors["phone"] ?></div>
        <?php endif; ?>
      </div>

      <div class="mb-3">
        <label for="age" class="form-label">Age</label>
        <input type="number" class="form-control <?= isset($errors["age"]) ? "is-invalid" : "" ?>" id="age" name="age" value="<?= htmlspecialchars($age) ?>" placeholder="Age" min="1" max="120">
        <?php if (isset($errors["age"])): ?>
          <div class="invalid-feedback"><?= $errors["age"] ?></div>
        <?php endif; ?>
      </div>

      <div class="mb-3">
        <label for="gender" class="form-label">Gender</label>
        <select class="form-select <?= isset($errors["gender"]) ? "is-invalid" : "" ?>" id="gender" name="gender">
          <option value="" disabled <?= empty($gender) ? "selected" : "" ?>>Select Gender</option>
          <option value="Male" <?= $gender === "Male" ? "selected" : "" ?>>Male</option>
          <option value="Female" <?= $gender === "Female" ? "selected" : "" ?>>Female</option>
        </select>
        <?php if (isset($errors["gender"])): ?>
          <div class="invalid-feedback"><?= $errors["gender"] ?></div>
        <?php endif; ?>
      </div>

      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control <?= isset($errors["password"]) ? "is-invalid" : "" ?>" id="password" name="password" placeholder="Password">
        <?php if (isset($errors["password"])): ?>
          <div class="invalid-feedback"><?= $errors["password"] ?></div>
        <?php endif; ?>
      </div>

      <div class="mb-3">
        <label for="confirmPassword" class="form-label">Confirm password</label>
        <input type="password" class="form-control <?= isset($errors["confirmPassword"]) ? "is-invalid" : "" ?>" id="confirmPassword" name="confirmPassword" placeholder="Confirm password">
        <?php if (isset($errors["confirmPassword"])): ?>
          <div class="invalid-feedback"><?= $errors["confirmPassword"] ?></div>
        <?php endif; ?>
      </div>

      <button type="submit" class="btn btn-primary w-100">Create account</button>
    </form>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

