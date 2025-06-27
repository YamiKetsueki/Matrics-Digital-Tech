<?php
require_once 'config/database.php';
require_once 'config/session.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$page_title = 'Contact Details - USB Store';

// Get user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([getCurrentUserId()]);
$user = $stmt->fetch();

// Get cart items
$stmt = $pdo->prepare("
    SELECT c.*, p.name, p.price 
    FROM cart c 
    JOIN products p ON c.product_id = p.product_id 
    WHERE c.session_id = ?
");
$stmt->execute([$_SESSION['cart_session_id']]);
$cart_items = $stmt->fetchAll();

if (empty($cart_items)) {
    header('Location: cart.php');
    exit;
}

$total_amount = 0;
foreach ($cart_items as $item) {
    $total_amount += $item['price'] * $item['quantity'];
}

// Indian states and cities
$states_cities = [
    'Andhra Pradesh' => ['Visakhapatnam', 'Vijayawada', 'Guntur', 'Nellore', 'Kurnool'],
    'Assam' => ['Guwahati', 'Silchar', 'Dibrugarh', 'Jorhat', 'Nagaon'],
    'Bihar' => ['Patna', 'Gaya', 'Bhagalpur', 'Muzaffarpur', 'Purnia'],
    'Delhi' => ['New Delhi', 'North Delhi', 'South Delhi', 'East Delhi', 'West Delhi'],
    'Gujarat' => ['Ahmedabad', 'Surat', 'Vadodara', 'Rajkot', 'Bhavnagar'],
    'Haryana' => ['Gurgaon', 'Faridabad', 'Panipat', 'Ambala', 'Yamunanagar'],
    'Karnataka' => ['Bangalore', 'Mysore', 'Hubli', 'Mangalore', 'Belgaum'],
    'Kerala' => ['Kochi', 'Thiruvananthapuram', 'Kozhikode', 'Thrissur', 'Kollam'],
    'Madhya Pradesh' => ['Bhopal', 'Indore', 'Gwalior', 'Jabalpur', 'Ujjain'],
    'Maharashtra' => ['Mumbai', 'Pune', 'Nagpur', 'Nashik', 'Aurangabad'],
    'Odisha' => ['Bhubaneswar', 'Cuttack', 'Rourkela', 'Berhampur', 'Sambalpur'],
    'Punjab' => ['Chandigarh', 'Ludhiana', 'Amritsar', 'Jalandhar', 'Patiala'],
    'Rajasthan' => ['Jaipur', 'Jodhpur', 'Kota', 'Bikaner', 'Ajmer'],
    'Tamil Nadu' => ['Chennai', 'Coimbatore', 'Madurai', 'Tiruchirappalli', 'Salem'],
    'Telangana' => ['Hyderabad', 'Warangal', 'Nizamabad', 'Khammam', 'Karimnagar'],
    'Uttar Pradesh' => ['Lucknow', 'Kanpur', 'Ghaziabad', 'Agra', 'Meerut'],
    'West Bengal' => ['Kolkata', 'Howrah', 'Durgapur', 'Asansol', 'Siliguri']
];

include 'includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1>Contact & Delivery Details</h1>
    </div>
</div>

<section class="contact-form-section">
    <div class="container">
        <form action="confirm_order.php" method="POST" class="contact-form">
            <div class="form-section">
                <h3>Personal Information</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="full_name">Full Name *</label>
                        <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="mobile">Mobile Number *</label>
                        <input type="tel" id="mobile" name="mobile" value="<?php echo htmlspecialchars($user['mobile']); ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="secondary_mobile">Secondary Mobile Number</label>
                    <input type="tel" id="secondary_mobile" name="secondary_mobile" value="<?php echo htmlspecialchars($user['secondary_mobile'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-section">
                <h3>Delivery Address</h3>
                <div class="form-group">
                    <label for="address">Street Address *</label>
                    <textarea id="address" name="address" rows="3" required><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="state">State *</label>
                        <select id="state" name="state" required onchange="updateCities()">
                            <option value="">Select State</option>
                            <?php foreach ($states_cities as $state => $cities): ?>
                                <option value="<?php echo $state; ?>" <?php echo $state == ($user['state'] ?? '') ? 'selected' : ''; ?>>
                                    <?php echo $state; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="city">City *</label>
                        <select id="city" name="city" required>
                            <option value="">Select City</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="pin_code">PIN Code *</label>
                    <input type="text" id="pin_code" name="pin_code" pattern="[0-9]{6}" value="<?php echo htmlspecialchars($user['pin_code'] ?? ''); ?>" required>
                </div>
            </div>

            <div class="form-section">
                <h3>Order Summary</h3>
                <div class="order-summary">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="summary-item">
                            <span><?php echo htmlspecialchars($item['name']); ?> (Qty: <?php echo $item['quantity']; ?>)</span>
                            <span>₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                        </div>
                    <?php endforeach; ?>
                    <div class="summary-total">
                        <strong>Total: ₹<?php echo number_format($total_amount, 2); ?></strong>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <a href="cart.php" class="btn btn-secondary">Back to Cart</a>
                <button type="submit" class="btn btn-primary">Proceed to Payment</button>
            </div>
        </form>
    </div>
</section>

<script>
const statesCities = <?php echo json_encode($states_cities); ?>;

function updateCities() {
    const stateSelect = document.getElementById('state');
    const citySelect = document.getElementById('city');
    const selectedState = stateSelect.value;
    
    // Clear existing cities
    citySelect.innerHTML = '<option value="">Select City</option>';
    
    if (selectedState && statesCities[selectedState]) {
        statesCities[selectedState].forEach(city => {
            const option = document.createElement('option');
            option.value = city;
            option.textContent = city;
            citySelect.appendChild(option);
        });
    }
}

// Initialize cities on page load
document.addEventListener('DOMContentLoaded', function() {
    const currentState = '<?php echo $user['state'] ?? ''; ?>';
    const currentCity = '<?php echo $user['city'] ?? ''; ?>';
    
    if (currentState) {
        updateCities();
        if (currentCity) {
            document.getElementById('city').value = currentCity;
        }
    }
});
</script>

<?php include 'includes/footer.php'; ?>