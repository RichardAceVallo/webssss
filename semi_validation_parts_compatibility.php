<?php
include 'config.php';

session_start();

function sanitizeData($data){
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
    $_SESSION['compatibility_issues'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //Adding to the cart
    if (isset($_POST['add_to_cart'])) {
        $item_id = intval($_POST['item_id']);
        $item_type = sanitizeData($_POST['item_type']);

        //Validation Trigger
        if (isCompatible($conn, $item_id, $item_type)) { //Is validated
            $_SESSION['cart'][] = [ 
                'item_id' => $item_id, 
                'item_type' => $item_type 
            ]; 
        } 
        else {// invalidated
            $recommended_item = getRecommendedItem($conn, $item_id, $item_type); 
            $_SESSION['compatibility_issues'] = [ 
                'item_id' => $item_id, 
                'item_type' => $item_type, 
                'recommended_item' => $recommended_item 
            ]; 
        }   
    }
    //Removing from cart 
    elseif (isset($_POST['remove_from_cart'])) {
        $item_index = intval($_POST['item_index']); 
        if (isset($_SESSION['cart'][$item_index])) {
            unset($_SESSION['cart'][$item_index]); 
            $_SESSION['cart'] = array_values($_SESSION['cart']); 
        } 
    }
    //Modal Recommendation
    elseif (isset($_POST['add_recommended_to_cart'])) { 
        $recommended_item = $_SESSION['compatibility_issues']['recommended_item']; 
        $_SESSION['cart'][] = [ 
            'item_id' => $recommended_item['id'], 
            'item_type' => $recommended_item['type'] 
        ]; 
        $_SESSION['compatibility_issues'] = []; 
    } 
    elseif (isset($_POST['ignore_recommendation'])) { 
        $_SESSION['compatibility_issues'] = []; 
    }
}

function isCompatible($conn, $item_id, $item_type) { 
    $item_socket_type = getSocketType($conn, $item_id, $item_type);
    $compatible = true;

    foreach ($_SESSION['cart'] as $cart_item) { 
        if($cart_item['item_type'] !== $item_type){
            $cart_socket_type = getSocketType($conn, $cart_item['item_id'], $cart_item['item_type']); 
            if ($item_socket_type !== $cart_socket_type) { 
                $compatible = false;
            }
            else {
                $compatible = true;
                break;
            } 
        }
    } 
    return $compatible;
}

function getSocketType($conn, $item_id, $item_type) { 
    if ($item_type === 'CPU') { 
        $sql = "SELECT socket_type FROM cpus WHERE id = ?"; 
    } 
    elseif ($item_type === 'motherboard') { 
        $sql = "SELECT socket_type FROM motherboards WHERE id = ?"; 
    } 
    else { 
        return null; 
    } 
    $stmt = $conn->prepare($sql); 
    $stmt->bind_param("i", $item_id); 
    $stmt->execute(); 
    $result = $stmt->get_result(); 
    $row = $result->fetch_assoc(); 
    return $row['socket_type']; 
}

function getRecommendedItem($conn, $item_id, $item_type) { 
    $socket_type = getSocketType($conn, $item_id, $item_type); 
    
    if ($item_type === 'CPU') { 
        $sql = "SELECT * FROM motherboards WHERE socket_type = ?"; 
        $recommended_type = 'motherboard'; 
    } 
    else { 
        $sql = "SELECT * FROM cpus WHERE socket_type = ?"; 
        $recommended_type = 'CPU'; 
    } 
    $stmt = $conn->prepare($sql); 
    $stmt->bind_param("s", $socket_type); 
    $stmt->execute(); 
    $result = $stmt->get_result(); 
    return $result->fetch_assoc() + ['type' => $recommended_type]; 
}

function getItemDetails($conn, $item_id, $item_type) { 
    if ($item_type === 'CPU') {
         $sql = "SELECT * FROM cpus WHERE id = ?"; 
    } 
    elseif ($item_type === 'motherboard') {
         $sql = "SELECT * FROM motherboards WHERE id = ?"; 
    } 
    else {
        return null; 
    } 
    $stmt = $conn->prepare($sql); 
    $stmt->bind_param("i", $item_id); 
    $stmt->execute(); 
    $result = $stmt->get_result(); 
    return $result->fetch_assoc(); 
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CPU shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<style>
    .list-group-item img {
        border-radius: 5px;
        margin-right: 15px;
    }
</style>
<body>
<div class="card">
    <h2>CPU & Motherboard</h2>
</div>
<div class="container-fluid">
    <div class>
        <h2>Select Parts</h2>
    </div>
    <div class="row align-items-start">
        <div class="card bg-secondary col-4">
            <h3>CPUs</h3>
            <ul class="list-group">
                <?php
                $cpu_sql = "SELECT * FROM cpus";
                $cpu_result = $conn->query($cpu_sql);
                while ($cpu_row = $cpu_result->fetch_assoc()) {
                    echo "<li class='list-group-item d-flex align-items-center'>
                    <img src='" . $cpu_row['image_url'] . "' alt='" . $cpu_row['name'] . "' class='me-3' style='width: 100px; height: 100px; object-fit: cover;'>
                    <div>
                        <strong>" . $cpu_row['name'] . "</strong> <br> $" . number_format($cpu_row['price'], 2, '.', ',') . "  USD, ₱" . number_format(($cpu_row['price']*58), 2, '.', ',') . " in pesos
                    </div>
                    <form method='POST' class='ms-auto'>
                        <input type='hidden' name='item_id' value='" . $cpu_row['id'] . "'>
                        <input type='hidden' name='item_type' value='CPU'>
                        <button type='submit' name='add_to_cart' class='btn btn-primary btn-sm'>Add to Cart</button>
                    </form>
                  </li>";
                }
                ?>
            </ul>
        </div>
        <div class="card col-4">
            <h3>Motherboards</h3>
            <ul class="list-group">
                <?php
                $mb_sql = "SELECT * FROM motherboards";
                $mb_result = $conn->query($mb_sql);
                while ($mb_row = $mb_result->fetch_assoc()) {
                    echo "<li class='list-group-item d-flex align-items-center'>
                    <img src='" . $mb_row['image_url'] . "' alt='" . $mb_row['name'] . "' class='me-3' style='width: 100px; height: 100px; object-fit: cover;'>
                    <div>
                        <strong>" . $mb_row['name'] . "</strong> <br> $" . number_format($mb_row['price'], 2, '.', ',') . "  USD, ₱" . number_format(($mb_row['price']*58), 2, '.', ',') . " in pesos
                    </div>
                    <form method='POST' class='ms-auto'>
                        <input type='hidden' name='item_id' value='" . $mb_row['id'] . "'>
                        <input type='hidden' name='item_type' value='motherboard'>
                        <button type='submit' name='add_to_cart' class='btn btn-primary btn-sm'>Add to Cart</button>
                    </form>
                  </li>";
                }
                ?>
            </ul>
        </div>
        <div class="card col-4"> 
            <h2>Shopping Cart</h2> 
            <ul class="list-group list-group-flush"> 
                <?php 
                    foreach ($_SESSION['cart'] as $index => $cart_item) {
                        $item_details = getItemDetails($conn, $cart_item['item_id'], $cart_item['item_type']); 
                        if ($item_details) { 
                            echo "
                            <li class='list-group-item d-flex align-items-center'> 
                                <img src='" . $item_details['image_url'] . "' alt='" . $item_details['name'] . "' class='me-3' style='width: 100px; height: 100px; object-fit: cover;'> 
                                <div> 
                                    <strong>" . $item_details['name'] . "</strong> 
                                    <br> $" . number_format($item_details['price'], 2, '.', ',') . " USD, 
                                    ₱" . number_format(($item_details['price']*58), 2, '.', ',') . " in pesos 
                                </div> 
                                <form method='POST' class='ms-auto'> 
                                    <input type='hidden' name='item_index' value='" . $index . "'> 
                                    <button type='submit' name='remove_from_cart' class='btn btn-danger btn-sm'>Remove</button> 
                                </form> 
                            </li>"; }
                    } 
                ?> 
            </ul> 
        </div> 
    </div>
</div>

<!-- Modal for compatibility issues --> 
    <div class="modal fade" id="compatibilityModal" tabindex="-1" aria-labelledby="compatibilityModalLabel" aria-hidden="true"> 
        <div class="modal-dialog"> 
            <div class="modal-content"> 
                <div class="modal-header"> 
                    <h5 class="modal-title" id="compatibilityModalLabel">Compatibility Issue</h5> 
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> 
                </div> 
                <div class="modal-body"> 
                    <?php 
                    if (!empty($_SESSION['compatibility_issues'])) { 
                        $recommended_item = $_SESSION['compatibility_issues']['recommended_item']; 
                            echo "<p>The selected item is not compatible with your current cart. 
                            We recommend the following item instead:</p> 
                            <div class='d-flex align-items-center'> 
                                <img src='" . $recommended_item['image_url'] . "' alt='" . $recommended_item['name'] . "' style='width: 100px; height: 100px; object-fit: cover;'> 
                                <div class='ms-3'> 
                                    <strong>" . $recommended_item['name'] . "</strong> <br> 
                                    $" . number_format($recommended_item['price'], 2, '.', ',') . " USD, 
                                    ₱" . number_format(($recommended_item['price']*58), 2, '.', ',') . " in pesos 
                                </div> 
                            </div>"; 
                    } 
                    ?> 
                </div> 
                <div class="modal-footer"> 
                    <form method="POST"> 
                        <button type="submit" name="ignore_recommendation" class="btn btn-secondary" data-bs-dismiss="modal">Ignore</button> 
                        <button type="submit" name="add_recommended_to_cart" class="btn btn-primary">Add to Cart</button> 
                    </form> 
                </div> 
            </div> 
        </div> 
    </div>
<script> 
    document.addEventListener('DOMContentLoaded', function() {
        <?php if (!empty($_SESSION['compatibility_issues'])): ?> 
            var myModal = new bootstrap.Modal(document.getElementById('compatibilityModal'), {}); 
            myModal.show(); 
        <?php endif; ?> 
    }); 
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>    
</body>
</html>