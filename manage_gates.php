<?php
$page = 'gates';
include 'header.php';
?>

<div class="page-header">
    <h3><i class="fas fa-door-open"></i> Manage Gates & Runways</h3>
</div>

<div class="form-grid">
    <div>
        <h3>Airport Gates</h3>
        <table class="clean-table">
            <thead>
                <tr>
                    <th>Gate</th>
                    <th>Terminal</th>
                    <th>Type</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT * FROM Gates ORDER BY Terminal, GateNumber";
                $result = $conn->query($sql);
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['GateNumber']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['Terminal']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['GateType']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['Status']) . "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <div>
        <h3>Airport Runways</h3>
        <table class="clean-table">
            <thead>
                <tr>
                    <th>Runway</th>
                    <th>Length (m)</th>
                    <th>Surface</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT * FROM Runways";
                $result = $conn->query($sql);
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    // THE TYPO WAS HERE. "D." is removed.
                    echo "<td>" . htmlspecialchars($row['RunwayNumber']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['Length']) . "m</td>";
                    echo "<td>" . htmlspecialchars($row['SurfaceType']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['Status']) . "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php
include 'footer.php';
?>