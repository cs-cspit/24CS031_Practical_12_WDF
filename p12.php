<?php 
$conn = new mysqli("localhost", "root", "");
if ($conn->connect_error)
{
    die("Failed to connect!");
}

$conn->query("CREATE DATABASE IF NOT EXISTS eventsdb");
$conn->select_db("eventsdb");
$conn->query("CREATE TABLE IF NOT EXISTS events 
(
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    event_date DATE,
    status ENUM('open','closed') DEFAULT 'open'
)");

$msg = '';
if (isset($_POST['save'])) 
{
    $id = intval($_POST['id']);
    $name = $conn->real_escape_string($_POST['name']);
    $desc = $conn->real_escape_string($_POST['description']);
    $date = $conn->real_escape_string($_POST['event_date']);
    $status = $conn->real_escape_string($_POST['status']);
    if ($id > 0) 
    {
        $ok = $conn->query("UPDATE events SET name='$name', description='$desc', event_date='$date', status='$status' WHERE id=$id");
        $msg = $ok ? "Event updated!" : "Update failed!";
    }
    else
    {
        $ok = $conn->query("INSERT INTO events (name, description, event_date, status) VALUES ('$name', '$desc', '$date', '$status')");
        $msg = $ok ? "Event added!" : "Add failed!";
    }
}

if (isset($_GET['delete'])) 
{
    $id = intval($_GET['delete']);
    $ok = $conn->query("DELETE FROM events WHERE id=$id");
    if ($ok)
    {
        $countRes = $conn->query("SELECT COUNT(*) AS cnt FROM events");
        $row = $countRes->fetch_assoc();
        if ($row['cnt'] == 0) 
        {
            $conn->query("TRUNCATE TABLE events");
            $msg = "Event deleted!";
        }
        else
        {
            $msg = "Event deleted!";
        }
    }
    else
    {
        $msg = "Delete failed!";
    }
}

$events = $conn->query("SELECT * FROM events ORDER BY id DESC");
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Event Manager</title>
        <style>
            *
            {
                font-family: cursive;
            }
            body 
            {
                background:#f0f8ff;
                margin:0; 
            }
            #main
            {
                display:flex;
                flex-direction:column;
                align-items:center;
                padding:40px 0; 
            }
            .table 
            { 
                width:1000px; 
                box-shadow:0 3px 16px #8fb9ff77; 
                border-radius:15px; 
                background:#fff;
                margin-top:25px; 
                border-collapse:collapse; 
            }
            th,td 
            { 
                padding:12px 10px; 
                border-bottom:1px solid #eee; 
                font-size:17px; 
            }
            th 
            { 
                background:#1565c0;
                color:#fff; 
            }
            .btn 
            { 
                border:none; 
                background:none; 
                font-size:22px; 
                cursor:pointer; 
                margin:0 7px; 
            }
            .btn.edit 
            { 
                color:#0288d1;
            }
            .btn.delete 
            { 
                color:#d32f2f;
            }
            .add-btn 
            { 
                background:#1565c0; 
                color:#fff; 
                font-size:18px; 
                border-radius:7px; 
                font-weight:bold;
                padding:8px 24px; 
                border:none; 
                margin-bottom:12px; 
                cursor:pointer;
            }
            .add-btn:hover 
            { 
                background:#0d47a1;
            }
            .msg 
            { 
                margin:1em 0; 
                color:#1565c0; 
                font-weight:bold; 
                font-size:16px; 
                text-align:center; 
            }
            .modal 
            { 
                display:none; 
                position:fixed; 
                z-index:10; 
                left:0; 
                top:0; 
                width:100vw; 
                height:100vh;
                background:rgba(0,0,0,0.5); 
                align-items:center; 
                justify-content:center; 
            }
            .modal-content 
            { 
                background:#1565c0; 
                color:#fff; 
                border-radius:15px; 
                padding:28px 26px;
                width:330px; 
                box-shadow:0 6px 24px #0d47a177; 
                position:relative; 
                font-family:cursive;
            }
            .modal-content label 
            { 
                margin-top:12px; 
                display:block; 
            }
            .modal-content input, .modal-content textarea, .modal-content select 
            {
                width:96%; 
                margin-top:7px; 
                border-radius:5px; 
                border:none; 
                height:35px;
                padding:7px 10px; 
                font-size:15px;
            }
            .modal-content textarea 
            { 
                height:55px;
            }
            .close 
            { 
                position:absolute; 
                right:14px; 
                top:7px; 
                font-size:27px; 
                cursor:pointer; 
                background:none; 
                border:none; 
                color:#fff;
            }
        </style>
        <script>
            function openModal(id="", name="", desc="", date="", status="open") 
            {
                document.getElementById("eventModal").style.display = "flex";
                document.getElementById("event_id").value = id;
                document.getElementById("event_name").value = name;
                document.getElementById("event_desc").value = desc;
                document.getElementById("event_date").value = date;
                document.getElementById("event_status").value = status;
            }
            function closeModal() 
            { 
                document.getElementById("eventModal").style.display = "none"; 
            }
        </script>
    </head>
    <body>
        <div id="main">
            <h2 style="color:#1565c0;">Event Management</h2>
            <?php 
                if ($msg)
                {
                    echo "<div class='msg'>$msg</div>";
                }
            ?>
            <button class="add-btn" onclick="openModal()">+ Add Event</button>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                        if($events && $events->num_rows>0): 
                            while($row = $events->fetch_assoc()):
                    ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                        <td><?php echo htmlspecialchars($row['event_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                        <td>
                            <button class="btn edit" title="Edit"
                                 onclick="openModal(
                                    '<?php echo $row['id']; ?>',
                                    '<?php echo htmlspecialchars(addslashes($row['name'])); ?>',
                                    '<?php echo htmlspecialchars(addslashes($row['description'])); ?>',
                                    '<?php echo $row['event_date']; ?>',
                                    '<?php echo $row['status']; ?>')">&#9998;</button>
                            <a href="?delete=<?php echo $row['id']; ?>" class="btn delete" title="Delete"
                                onclick="return confirm('Delete this event?')">&#128465;</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align:center">No events found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="modal" id="eventModal">
            <div class="modal-content">
                <button class="close" onclick="closeModal()">&times;</button>
                <form method="post">
                    <input type="hidden" name="id" id="event_id" value="0">
                    <label>Event Name:</label>
                    <input type="text" name="name" id="event_name" required>
                    <label>Description:</label>
                    <textarea name="description" id="event_desc"></textarea>
                    <label>Date:</label>
                    <input type="date" name="event_date" id="event_date" required>
                    <label>Status:</label>
                    <select name="status" id="event_status" style="width: 102%;">
                        <option value="open">Open</option>
                        <option value="closed">Closed</option>
                    </select>
                    <button type="submit" name="save" style="margin-top:18px;width:102%;height:30px;border-radius:5px;background:#00acc1;">Save</button>
                </form>
            </div>
        </div>
        <script>
        window.onclick = function(e) 
        {
            var modal = document.getElementById("eventModal");
            if (e.target == modal) closeModal();
        }
        </script>
    </body>
</html>
