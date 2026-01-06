<?php
/**
 * Global Message Display Component
 * Include this file at the top of main content to show session messages
 */

// Success messages
if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success">
        <strong>✓ Success!</strong> <?= htmlspecialchars($_SESSION['success_message']) ?>
    </div>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<?php // Error messages
if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger">
        <strong>✗ Error!</strong> <?= htmlspecialchars($_SESSION['error_message']) ?>
    </div>
    <?php unset($_SESSION['error_message']); ?>
<?php endif; ?>

<?php // Warning messages
if (isset($_SESSION['warning_message'])): ?>
    <div class="alert alert-warning">
        <strong>⚠ Warning!</strong> <?= htmlspecialchars($_SESSION['warning_message']) ?>
    </div>
    <?php unset($_SESSION['warning_message']); ?>
<?php endif; ?>

<style>
.alert {
    padding: 15px 20px;
    margin: 20px 0;
    border-radius: 8px;
    animation: slideIn 0.3s ease-out;
    display: flex;
    align-items: center;
    gap: 10px;
}
.alert strong {
    margin-right: 5px;
}
.alert-success { 
    background: #d4edda; 
    color: #155724; 
    border-left: 4px solid #28a745; 
}
.alert-danger { 
    background: #f8d7da; 
    color: #721c24; 
    border-left: 4px solid #dc3545; 
}
.alert-warning { 
    background: #fff3cd; 
    color: #856404; 
    border-left: 4px solid #ffc107; 
}
@keyframes slideIn {
    from { 
        transform: translateY(-20px); 
        opacity: 0; 
    }
    to { 
        transform: translateY(0); 
        opacity: 1; 
    }
}
</style>

<script>
// Auto-hide messages after 5 seconds
setTimeout(() => {
    document.querySelectorAll('.alert').forEach(alert => {
        alert.style.transition = 'opacity 0.5s';
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 500);
    });
}, 5000);
</script>
