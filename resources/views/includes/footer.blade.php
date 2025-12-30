    <footer>
        <div class="container">
            <p>&copy; 2025 ST ZK Digital Media. All rights reserved.</p>
        </div>
    </footer>

        
        <script src="js/jQuery.min.js"></script>
        <script src="js/main.js"></script>
        <script src="js/sweetAlert.js"></script>
        <script>
            // Mobile menu functionality
            $(document).ready(function() {
                $('#mobileMenuBtn').click(function() {
                    $('.sidebar').toggleClass('active');
                    $('#sidebarOverlay').toggleClass('active');
                });
                
                $('#sidebarOverlay').click(function() {
                    $('.sidebar').removeClass('active');
                    $('#sidebarOverlay').removeClass('active');
                });
                
                // Close sidebar when clicking on nav items on mobile
                $('.nav-item').click(function() {
                    if (window.innerWidth <= 768) {
                        $('.sidebar').removeClass('active');
                        $('#sidebarOverlay').removeClass('active');
                    }
                });
            });
        </script>
</body>
</html>