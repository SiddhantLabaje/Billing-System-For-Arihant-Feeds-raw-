<?php
include "../config/db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die("Access denied");
}

$message = "";

/* Fetch customers */
$customers = mysqli_query($conn,
    "SELECT user_id, name FROM users WHERE role='customer'"
);

/* Fetch feeds */
$feeds = mysqli_query($conn,
    "SELECT feed_id, feed_name, price FROM feed"
);

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $user_id = intval($_POST['user_id']);
    $bill_date = $_POST['bill_date'];
    $feed_ids = $_POST['feed_id'];
    $quantities = $_POST['quantity'];

    $grand_total = 0;

    /* Calculate grand total */
    for ($i = 0; $i < count($feed_ids); $i++) {

        if (!empty($feed_ids[$i]) && $quantities[$i] > 0) {

            $feed_data = mysqli_fetch_assoc(
                mysqli_query($conn,
                    "SELECT price FROM feed WHERE feed_id=".$feed_ids[$i]
                )
            );

            $price = $feed_data['price'];
            $total = $price * $quantities[$i];

            $grand_total += $total;
        }
    }

    if ($grand_total > 0) {

        /* Insert into bills */
        mysqli_query($conn,
            "INSERT INTO bills (user_id, bill_date, total_amount)
             VALUES ($user_id, '$bill_date', $grand_total)"
        );

        $bill_id = mysqli_insert_id($conn);

        /* Insert bill items */
        for ($i = 0; $i < count($feed_ids); $i++) {

            if (!empty($feed_ids[$i]) && $quantities[$i] > 0) {

                $feed_data = mysqli_fetch_assoc(
                    mysqli_query($conn,
                        "SELECT price FROM feed WHERE feed_id=".$feed_ids[$i]
                    )
                );

                $price = $feed_data['price'];
                $total = $price * $quantities[$i];

                mysqli_query($conn,
                    "INSERT INTO bill_items
                    (bill_id, feed_id, quantity, price, total)
                    VALUES
                    ($bill_id, ".$feed_ids[$i].", ".$quantities[$i].",
                     $price, $total)"
                );
            }
        }

        /* Get last balance */
        $last = mysqli_query($conn,
            "SELECT balance FROM ledger
             WHERE user_id=$user_id
             ORDER BY ledger_id DESC LIMIT 1"
        );

        $last_balance = 0;

        if (mysqli_num_rows($last) > 0) {
            $row = mysqli_fetch_assoc($last);
            $last_balance = $row['balance'];
        }

        $new_balance = $last_balance + $grand_total;

        /* Insert ledger entry */
        mysqli_query($conn,
            "INSERT INTO ledger
            (user_id, transaction_date, description, debit, credit, balance)
            VALUES
            ($user_id, '$bill_date',
             'Bill #$bill_id Purchase',
             $grand_total, 0, $new_balance)"
        );

        $message = "Bill created successfully!";
    } else {
        $message = "Please add at least one feed item.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Bill</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        table { border-collapse: collapse; width: 100%; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
        th { background: #4f46e5; color: white; }
        button { padding: 6px 12px; cursor: pointer; }
    </style>
</head>
<body>

<h2>Create New Bill</h2>

<?php if ($message) echo "<p><strong>$message</strong></p>"; ?>

<form method="post" id="billForm">

    <select name="user_id" required>
        <option value="">Select Customer</option>
        <?php while($c = mysqli_fetch_assoc($customers)) { ?>
            <option value="<?= $c['user_id']; ?>">
                <?= $c['name']; ?>
            </option>
        <?php } ?>
    </select>

    <br><br>

    Bill Date:
    <input type="date" name="bill_date" required>

    <br><br>

    <table id="billTable">
        <tr>
            <th>Feed</th>
            <th>Price</th>
            <th>Qty</th>
            <th>Total</th>
            <th>Remove</th>
        </tr>
    </table>

    <br>

    <button type="button" onclick="addRow()">Add Feed</button>

    <h3>Grand Total: ₹ <span id="grandTotal">0</span></h3>

    <br>

    <button type="submit">Create Bill</button>
</form>

<script>
let feeds = [
<?php
mysqli_data_seek($feeds, 0);
while($f = mysqli_fetch_assoc($feeds)) {
    echo "{id:".$f['feed_id'].", name:'".$f['feed_name']."', price:".$f['price']."},";
}
?>
];

function addRow() {

    let table = document.getElementById("billTable");
    let row = table.insertRow();

    let feedCell = row.insertCell(0);
    let priceCell = row.insertCell(1);
    let qtyCell = row.insertCell(2);
    let totalCell = row.insertCell(3);
    let removeCell = row.insertCell(4);

    let select = document.createElement("select");
    select.name = "feed_id[]";

    let defaultOption = new Option("Select Feed", "");
    select.appendChild(defaultOption);

    feeds.forEach(feed => {
        let option = new Option(feed.name + " (₹"+feed.price+")", feed.id);
        option.dataset.price = feed.price;
        select.appendChild(option);
    });

    let priceInput = document.createElement("input");
    priceInput.type = "number";
    priceInput.readOnly = true;

    let qtyInput = document.createElement("input");
    qtyInput.type = "number";
    qtyInput.name = "quantity[]";
    qtyInput.min = 1;

    let totalInput = document.createElement("input");
    totalInput.type = "number";
    totalInput.readOnly = true;

    select.onchange = function() {
        let price = this.options[this.selectedIndex].dataset.price || 0;
        priceInput.value = price;
        calculateRowTotal();
    };

    qtyInput.oninput = calculateRowTotal;

    function calculateRowTotal() {
        let total = (priceInput.value || 0) * (qtyInput.value || 0);
        totalInput.value = total;
        calculateGrandTotal();
    }

    removeCell.innerHTML = "<button type='button'>X</button>";
    removeCell.onclick = function() {
        row.remove();
        calculateGrandTotal();
    };

    feedCell.appendChild(select);
    priceCell.appendChild(priceInput);
    qtyCell.appendChild(qtyInput);
    totalCell.appendChild(totalInput);
}

function calculateGrandTotal() {
    let totals = document.querySelectorAll("#billTable input[readonly]");
    let sum = 0;
    totals.forEach(input => sum += Number(input.value));
    document.getElementById("grandTotal").innerText = sum;
}
</script>

<br>
<a href="dashboard.php">Back to Dashboard</a>

</body>
</html>