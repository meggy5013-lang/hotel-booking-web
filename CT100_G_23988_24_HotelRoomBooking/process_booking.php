<?php
/* ============================================================
   Sunrise Hotel — Booking Processing (Server Side)
   Admission No: CT100/G/23988/24
   Individualized Requirement (last digit = 8):
   → Conference Room option at KSh 5,000
   ============================================================ */

$RATES = [
    'Single' => ['rate' => 3500, 'capacity' => 1],
    'Double' => ['rate' => 5000, 'capacity' => 2],
    'Family' => ['rate' => 7500, 'capacity' => 4],
];

$BREAKFAST_RATE  = 700;
$TRANSFER_RATE   = 2000;
$CONFERENCE_RATE = 5000;

$errors = [];

function clean($data) {
    return htmlspecialchars(trim($data ?? ''), ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: booking.html');
    exit;
}

$fullName   = clean($_POST['fullName']   ?? '');
$idNumber   = clean($_POST['idNumber']   ?? '');
$email      = clean($_POST['email']      ?? '');
$phone      = clean($_POST['phone']      ?? '');
$checkIn    = clean($_POST['checkIn']    ?? '');
$nights     = (int) ($_POST['nights']    ?? 0);
$roomType   = clean($_POST['roomType']   ?? '');
$guests     = (int) ($_POST['guests']    ?? 0);
$breakfast  = ($_POST['breakfast']  ?? '') === 'Yes';
$transfer   = ($_POST['transfer']   ?? '') === 'Yes';
$conference = ($_POST['conference'] ?? '') === 'Yes';

// ---------- Server-side validation ----------
if ($fullName === '')                       $errors[] = 'Full name is required.';
if ($idNumber === '')                       $errors[] = 'National ID / Passport number is required.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email address.';
if (!preg_match('/^\d{10}$/', $phone))      $errors[] = 'Phone number must be exactly 10 digits.';
if ($checkIn === '')                        $errors[] = 'Check-in date is required.';
if ($nights < 1 || $nights > 14)            $errors[] = 'Number of nights must be between 1 and 14.';
if ($guests <= 0)                           $errors[] = 'Number of guests must be greater than zero.';

if (!array_key_exists($roomType, $RATES)) {
    $errors[] = 'Please select a valid room type.';
} elseif ($guests > $RATES[$roomType]['capacity']) {
    $errors[] = "{$roomType} Room allows a maximum of "
              . $RATES[$roomType]['capacity'] . " guest(s).";
}

if (!empty($errors)) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Booking Error — Sunrise Hotel</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <header>
            <div class="logo">
                <h1>🌅 Sunrise Hotel</h1>
                <p>Comfort • Elegance • Hospitality</p>
            </div>
            <nav>
                <a href="index.html">Home</a>
                <a href="booking.html">Book a Room</a>
            </nav>
        </header>
        <main>
            <section class="confirmation-section">
                <h2 class="error-page">⚠ Booking Failed</h2>
                <div class="confirmation-details">
                    <ul style="color:#b71c1c; margin-left:1.2rem;">
                        <?php foreach ($errors as $e): ?>
                            <li><?= $e ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <a href="booking.html" class="btn-primary">← Back to Booking Form</a>
            </section>
        </main>
        <footer><p>&copy; 2026 Sunrise Hotel. All rights reserved.</p></footer>
    </body>
    </html>
    <?php
    exit;
}

// ---------- Independent server-side calculation ----------
$roomCost       = $RATES[$roomType]['rate'] * $nights;
$breakfastCost  = $breakfast ? $BREAKFAST_RATE * $guests * $nights : 0;
$transferCost   = $transfer  ? $TRANSFER_RATE : 0;
$conferenceCost = $conference ? $CONFERENCE_RATE : 0;

$total = $roomCost + $breakfastCost + $transferCost + $conferenceCost;

function ksh($n) {
    return 'KSh ' . number_format($n);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed — Sunrise Hotel</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="logo">
            <h1>🌅 Sunrise Hotel</h1>
            <p>Comfort • Elegance • Hospitality</p>
        </div>
        <nav>
            <a href="index.html">Home</a>
            <a href="booking.html">Book a Room</a>
        </nav>
    </header>

    <main>
        <section class="confirmation-section">
            <h2>✅ BOOKING CONFIRMED</h2>

            <div class="confirmation-details">
                <p><span>Customer:</span> <span><?= $fullName ?></span></p>
                <p><span>ID / Passport:</span> <span><?= $idNumber ?></span></p>
                <p><span>Email:</span> <span><?= $email ?></span></p>
                <p><span>Phone:</span> <span><?= $phone ?></span></p>
                <p><span>Check-in Date:</span> <span><?= $checkIn ?></span></p>
                <p><span>Room:</span> <span><?= $roomType ?> Room</span></p>
                <p><span>Guests:</span> <span><?= $guests ?></span></p>
                <p><span>Number of Nights:</span> <span><?= $nights ?></span></p>
                <p><span>Breakfast:</span> <span><?= $breakfast ? 'Yes' : 'No' ?></span></p>
                <p><span>Airport Transfer:</span> <span><?= $transfer ? 'Yes' : 'No' ?></span></p>
                <p><span>Conference Room:</span> <span><?= $conference ? 'Yes' : 'No' ?></span></p>
            </div>

            <h3 style="margin-top:1.5rem; color:#1e3c72;">Bill Breakdown</h3>
            <div class="confirmation-details">
                <p><span>Room Cost:</span> <span><?= ksh($roomCost) ?></span></p>
                <p><span>Breakfast Cost:</span> <span><?= ksh($breakfastCost) ?></span></p>
                <p><span>Airport Transfer:</span> <span><?= ksh($transferCost) ?></span></p>
                <p><span>Conference Room:</span> <span><?= ksh($conferenceCost) ?></span></p>
            </div>

            <div class="total-amount">
                TOTAL AMOUNT: <?= ksh($total) ?>
            </div>

            <p class="thank-you">Thank you for choosing Sunrise Hotel.</p>
            <a href="index.html" class="btn-primary" style="margin-top:1rem;">← Back to Home</a>
        </section>
    </main>

    <footer>
        <p>&copy; 2026 Sunrise Hotel. All rights reserved.</p>
    </footer>
</body>
</html>