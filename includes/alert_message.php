<?php

if (isset($_GET['status']) && isset($_GET['message'])) {
    $status = $_GET['status'];
    $message = urldecode($_GET['message']); 
    $alert_class = '';

    if ($status === 'success') {
        $alert_class = 'alert-success';
        $icon = 'check-circle'; 
    } elseif ($status === 'error') {
        $alert_class = 'alert-danger';
        $icon = 'exclamation-triangle'; 
    } elseif ($status === 'warning') {
        $alert_class = 'alert-warning';
        $icon = 'exclamation-circle'; 
    } else {
        return; 
    }
    
    ?>
    <div class="alert <?php echo $alert_class; ?> alert-dismissible fade show" role="alert">
        <i class="fas fa-<?php echo $icon; ?> me-2"></i>
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php
}
?>