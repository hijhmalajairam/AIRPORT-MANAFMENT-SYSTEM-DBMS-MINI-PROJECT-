<?php
$page = 'dashboard'; // Set the active page
include 'header.php'; // Include the header

// --- Set timezone and get TODAY'S date ---
date_default_timezone_set('Asia/Kolkata');
$today = date('Y-m-d'); // This is the filter

// --- Add auto-refresh meta tag ---
echo '<meta http-equiv="refresh" content="60">';

// --- Helper Function for Countdown (No change) ---
function get_countdown_string($flight_time_str, $status, $type = 'departure') {
    $now = new DateTime("now", new DateTimeZone('Asia/Kolkata'));
    $flight_time = new DateTime($flight_time_str);
    $interval = $now->diff($flight_time);
    $total_minutes = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;
    
    $text = ''; $class = 'on-time';
    if ($interval->invert) { // In the past
        if ($status == 'Landed' || $status == 'Arrived') {
            $text = ($type == 'arrival' ? 'Landed ' : 'Landed ') . $interval->format('%h h %i m ago'); $class = 'landed';
        } elseif ($status == 'Departed' || $status == 'In Air') {
            $text = ($type == 'departure' ? 'Departed ' : 'Departed ') . $interval->format('%h h %i m ago'); $class = 'departed';
        } elseif ($status == 'Cancelled') {
            $text = 'Cancelled'; $class = 'cancelled';
        } else {
            $text = 'Delayed'; $class = 'delayed';
        }
    } else { // In the future
        if ($total_minutes < 45) {
            $text = ($type == 'departure' ? 'Boarding' : 'Final Approach'); $class = 'boarding';
        } elseif ($total_minutes < 1440) {
            $text = ($type == 'departure' ? 'Departs in ' : 'Arrives in ') . $interval->format('%h h %i m'); $class = 'on-time';
        } else {
            $text = ($type == 'departure' ? 'Departs in ' : 'Arrives in ') . $interval->format('%a days, %h h'); $class = 'on-time';
        }
    }
    return ['text' => $text, 'class' => $class];
}

// --- Stat Card Queries (WITH TODAY'S DATE FILTER) ---
$sql_dep = "SELECT COUNT(*) AS total FROM Flights WHERE FlightStatus IN ('Scheduled', 'Delayed', 'Boarding') AND DATE(ScheduledDeparture) = ?";
$stmt_dep = $conn->prepare($sql_dep);
$stmt_dep->bind_param("s", $today);
$stmt_dep->execute();
$total_departures = $stmt_dep->get_result()->fetch_assoc()['total'];
$stmt_dep->close();

$sql_arr = "SELECT COUNT(*) AS total FROM Flights WHERE FlightStatus IN ('Scheduled', 'Delayed', 'In Air') AND DATE(ScheduledArrival) = ?";
$stmt_arr = $conn->prepare($sql_arr);
$stmt_arr->bind_param("s", $today);
$stmt_arr->execute();
$total_arrivals = $stmt_arr->get_result()->fetch_assoc()['total'];
$stmt_arr->close();

$total_gates = $conn->query("SELECT COUNT(*) AS total FROM Gates WHERE Status = 'Available'")->fetch_assoc()['total'];
?>

<div class="page-header">
    <h3><i class="fas fa-tachometer-alt"></i> Live Airport Status (Today)</h3>
    <span style="color: #64748b; font-weight: 500;">
        <i class="fas fa-sync-alt"></i> Page auto-refreshes every 60 seconds
    </span>
</div>

<input type="text" id="dashboardSearch" class="dashboard-search-bar" placeholder="🔍 Search today's flights by number, airline, or city...">

<div class="stat-card-container" style="margin-bottom: 2rem; margin-top: 1rem;">
    <div class="stat-card" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
        <i class="fas fa-plane-departure"></i>
        <div class="stat-info">
            <span class="stat-value"><?php echo $total_departures; ?></span>
            <span class="stat-label">Today's Departures</span>
        </div>
    </div>
    <div class="stat-card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
        <i class="fas fa-plane-arrival"></i>
        <div class="stat-info">
            <span class="stat-value"><?php echo $total_arrivals; ?></span>
            <span class="stat-label">Today's Arrivals</span>
        </div>
    </div>
    <div class="stat-card" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
        <i class="fas fa-door-open"></i>
        <div class="stat-info">
            <span class="stat-value"><?php echo $total_gates; ?></span>
            <span class="stat-label">Available Gates</span>
        </div>
    </div>
</div>

<div class="status-board-container">
    <div class="status-board">
        <h3 class="departures"><i class="fas fa-plane-departure"></i> Departures</h3>
        <ul class="status-board-list" id="departuresList">
            <?php
            // Get today's departures
            $sql_departures = "SELECT F.FlightNumber, A.AirlineName, F.ArrivalAirport, F.ScheduledDeparture, G.GateNumber, F.FlightStatus
                FROM Flights F
                JOIN Airlines A ON F.AirlineID = A.AirlineID
                LEFT JOIN Gates G ON F.DepartureGateID = G.GateID
                WHERE F.FlightStatus IN ('Scheduled', 'Delayed', 'Boarding') AND DATE(ScheduledDeparture) = ?
                ORDER BY F.ScheduledDeparture ASC";
            $stmt_dep_list = $conn->prepare($sql_departures);
            $stmt_dep_list->bind_param("s", $today);
            $stmt_dep_list->execute();
            $departures_result = $stmt_dep_list->get_result();

            if ($departures_result->num_rows > 0):
                while($flight = $departures_result->fetch_assoc()):
                    $search_term = strtolower($flight['FlightNumber'] . ' ' . $flight['AirlineName'] . ' ' . $flight['ArrivalAirport']);
                    $countdown = get_countdown_string($flight['ScheduledDeparture'], $flight['FlightStatus'], 'departure');
            ?>
                <li data-search="<?php echo e($search_term); ?>">
                    <div class="flight-info">
                        <div class="flight-num"><?php echo e($flight['FlightNumber']); ?></div>
                        <div class="airline"><?php echo e($flight['AirlineName']); ?> to <?php echo e($flight['ArrivalAirport']); ?></div>
                    </div>
                    <div class="flight-countdown <?php echo $countdown['class']; ?>">
                        <?php echo $countdown['text']; ?>
                    </div>
                    <div class="flight-gate">
                        Gate <?php echo e($flight['GateNumber'] ?? 'TBA'); ?>
                    </div>
                </li>
            <?php endwhile; ?>
            <?php else: ?>
                <li style="text-align: center; display: block; padding: 30px;">No departures scheduled for today.</li>
            <?php endif; $stmt_dep_list->close(); ?>
        </ul>
    </div>
    
    <div class="status-board">
        <h3 class="arrivals"><i class="fas fa-plane-arrival"></i> Arrivals</h3>
        <ul class="status-board-list" id="arrivalsList">
             <?php
            // Get today's arrivals
            $sql_arrivals = "SELECT F.FlightNumber, A.AirlineName, F.DepartureAirport, F.ScheduledArrival, G.GateNumber, F.FlightStatus
                FROM Flights F
                JOIN Airlines A ON F.AirlineID = A.AirlineID
                LEFT JOIN Gates G ON F.ArrivalGateID = G.GateID
                WHERE F.FlightStatus IN ('Scheduled', 'Delayed', 'In Air') AND DATE(ScheduledArrival) = ?
                ORDER BY F.ScheduledArrival ASC";
            $stmt_arr_list = $conn->prepare($sql_arrivals);
            $stmt_arr_list->bind_param("s", $today);
            $stmt_arr_list->execute();
            $arrivals_result = $stmt_arr_list->get_result();
             
             if ($arrivals_result->num_rows > 0):
                while($flight = $arrivals_result->fetch_assoc()):
                    $search_term = strtolower($flight['FlightNumber'] . ' ' . $flight['AirlineName'] . ' ' . $flight['DepartureAirport']);
                    $countdown = get_countdown_string($flight['ScheduledArrival'], $flight['FlightStatus'], 'arrival');
            ?>
                <li data-search="<?php echo e($search_term); ?>">
                    <div class="flight-info">
                        <div class="flight-num"><?php echo e($flight['FlightNumber']); ?></div>
                        <div class="airline"><?php echo e($flight['AirlineName']); ?> from <?php echo e($flight['DepartureAirport']); ?></div>
                    </div>
                    <div class="flight-countdown <?php echo $countdown['class']; ?>">
                        <?php echo $countdown['text']; ?>
                    </div>
                    <div class="flight-gate">
                        Gate <?php echo e($flight['GateNumber'] ?? 'TBA'); ?>
                    </div>
                </li>
            <?php endwhile; ?>
            <?php else: ?>
                <li style="text-align: center; display: block; padding: 30px;">No arrivals scheduled for today.</li>
            <?php endif; $stmt_arr_list->close(); ?>
        </ul>
    </div>
</div>

<?php include 'footer.php'; ?>

<script>
document.getElementById('dashboardSearch').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    function filterList(listId) {
        let list = document.getElementById(listId);
        let items = list.getElementsByTagName('li');
        for (let i = 0; i < items.length; i++) {
            let item = items[i];
            let searchTerm = item.getAttribute('data-search');
            if (searchTerm) {
                if (searchTerm.includes(filter)) {
                    item.style.display = "";
                } else {
                    item.style.display = "none";
                }
            }
        }
    }
    filterList('departuresList');
    filterList('arrivalsList');
});
</script>