            </main>
        </div>
    </div>

    <script>
        // Auto-hide success/error messages after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert-auto-hide');
            alerts.forEach(function(alert) {
                alert.style.display = 'none';
            });
        }, 5000);

        // Confirm delete actions
        function confirmDelete(message) {
            return confirm(message || 'Are you sure you want to delete this item?');
        }

        // Toggle mobile menu
        function toggleMobileMenu() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        }
    </script>
</body>
</html>