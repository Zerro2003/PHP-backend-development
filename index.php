<?php
session_start();

// Predefined recipients
$recipients = [
    "0781111111" => "Ange Uwimana",
    "0782222222" => "Bruno Kamanzi",
    "0783333333" => "Clara Niyonzima",
    "0784444444" => "Jean Paul Mugisha",
    "0785555555" => "Emma Mukamana"
];

// Default balance
if (!isset($_SESSION['balance'])) {
    $_SESSION['balance'] = 10000;
}
$balance = $_SESSION['balance'];

// Current step
$step = isset($_POST['step']) ? (int)$_POST['step'] : 0;
$input = isset($_POST['option']) ? trim($_POST['option']) : "";

// Handle "00" (restart)
if ($input === "00") {
    $step = 0;
}

// Handle "0" (go back one step)
if ($input === "0" && $step > 1) {
    $step -= 2; // because after submit it adds +1
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>MTN MoMo</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="mtn-header">
            <img src="mtn-logo.png" alt="MTN Logo" height="40">
            <h2>MTN Mobile Money</h2>
        </div>
        <?php
        // STEP 0: Must dial *182#
        if ($step == 0) {
            echo '<form method="post">
                    <h2>Enter USSD Code</h2>
                    <input type="text" name="option" placeholder="*182#" required>
                    <input type="hidden" name="step" value="1">
                    <button type="submit">Dial</button>
                  </form>';
        }
        // STEP 1: Main Menu
        elseif ($step == 1) {
            if ($input !== "*182#") {
                echo '<div class="message error">❌ Unknown USSD code.</div>';
                echo '<form method="post">
                        <input type="hidden" name="step" value="0">
                        <button type="submit">Try Again</button>
                      </form>';
            } else {
                echo '<form method="post">
                        <h2>Main Menu</h2>
                        <div class="menu-option">1. Send Money</div>
                        <div class="menu-option">2. Payments</div>
                        <div class="menu-option">3. Airtime</div>
                        <input type="text" name="option" required placeholder="Enter option">
                        <input type="hidden" name="step" value="2">
                        <button type="submit">Next</button>
                      </form>';
            }
        }
        // STEP 2: Send Money
        elseif ($step == 2) {
            if ($input == "1") {
                echo '<form method="post">
                        <h2>Send Money</h2>
                        <p>1. MoMo User</p>
                        <input type="text" name="option" required>
                        <input type="hidden" name="step" value="3">
                        <button type="submit">Next</button>
                      </form>';
            } else {
                echo '<div class="message error">❌ Invalid option.</div>';
            }
        }
        // STEP 3: Enter Recipient
        elseif ($step == 3) {
            if ($input == "1") {
                echo '<form method="post">
                        <h2>Enter Recipient Number</h2>
                        <label>Format: 07xxxxxxxx</label>
                        <input type="text" name="recipient" required>
                        <input type="hidden" name="step" value="4">
                        <button type="submit">Next</button>
                      </form>';
            } else {
                echo '<div class="message error">❌ Invalid option.</div>';
            }
        }
        // STEP 4: Enter Amount
        elseif ($step == 4) {
            $recipient = $_POST['recipient'];
            if (!array_key_exists($recipient, $recipients)) {
                echo '<div class="message error">❌ Transaction has faild. Number provided is incorrect. Please check the number and try again.</div>';
            } else {
                echo '<form method="post">
                        <h2>Enter Amount</h2>
                        <input type="number" name="amount" required>
                        <input type="hidden" name="recipient" value="' . $recipient . '">
                        <input type="hidden" name="step" value="5">
                        <button type="submit">Next</button>
                      </form>';
            }
        }
        // STEP 5: Summary & PIN
        elseif ($step == 5) {
            $recipient = $_POST['recipient'];
            $amount = $_POST['amount'];
            $charge = ceil($amount * 0.02);
            $total = $amount + $charge;

            if ($total > $balance) {
                echo '<div class="message error">❌ Not enough funds to perform transaction.</div>';
            } else {
                echo '<form method="post">
                        <h2>Confirm Transaction</h2>
                        <p>You entered: ' . $recipients[$recipient] . ', ' . $recipient . ', ' . $amount . ' RWF</p>
                        <p>A Fee of RWF ' . $charge . ' will be applicable.</p>
                        <p>Total: ' . $total . ' RWF</p>
                        <label>Enter PIN:</label>
                        <input type="password" name="pin" required>
                        <input type="hidden" name="recipient" value="' . $recipient . '">
                        <input type="hidden" name="amount" value="' . $amount . '">
                        <input type="hidden" name="charge" value="' . $charge . '">
                        <input type="hidden" name="step" value="6">
                        <button type="submit">Send</button>
                      </form>';
            }
        }
        // STEP 6: Final Result
        elseif ($step == 6) {
            $recipient = $_POST['recipient'];
            $amount = $_POST['amount'];
            $charge = $_POST['charge'];
            $pin = $_POST['pin'];
            $total = $amount + $charge;

            if ($total > $balance) {
                echo '<div class="message error">❌ Not enough funds to perform transaction.</div>';
            } elseif ($pin == "1234") {
                $_SESSION['balance'] -= $total;
                $balance = $_SESSION['balance'];
                echo '<div class="message success">
                        ✅ You have sent RWF ' . $amount . ' to ' . $recipients[$recipient] . ' (' . $recipient . ').<br>
                        Fee: RWF ' . $charge . '<br>
                        Your new balance is RWF ' . $balance . '
                      </div>';
            } else {
                echo '<div class="message error">❌ Invalid PIN. Transaction failed.</div>';
            }
        }
        ?>
        <div style="text-align: center; margin-top: 20px; color: #666;">
            <small>0: Back • 00: Main Menu</small>
        </div>
    </div>
</body>
</html>
