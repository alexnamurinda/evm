// mobile-menu.js
document.addEventListener('DOMContentLoaded', function() {
    // Create menu toggler button
    const menuToggler = document.createElement('button');
    menuToggler.className = 'menu-toggler';
    menuToggler.innerHTML = '<i class="fas fa-bars"></i>';
    document.body.appendChild(menuToggler);
    
    // Create sidebar overlay
    const sidebarOverlay = document.createElement('div');
    sidebarOverlay.className = 'sidebar-overlay';
    document.body.appendChild(sidebarOverlay);
    
    // Get the left sidebar
    const leftSidebar = document.querySelector('.left-sidebar');
    
    // Function to toggle sidebar
    function toggleSidebar() {
        leftSidebar.classList.toggle('active');
        sidebarOverlay.classList.toggle('active');
    }
    
    // Toggle sidebar when menu button is clicked
    menuToggler.addEventListener('click', toggleSidebar);
    
    // Hide sidebar when overlay is clicked
    sidebarOverlay.addEventListener('click', toggleSidebar);
    
    // Hide sidebar when a link is clicked
    const sidebarLinks = document.querySelectorAll('.left-sidebar .links a');
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function() {
            // Only hide if we're in mobile view
            if (window.innerWidth <= 991) {
                toggleSidebar();
            }
        });
    });
    
    // Update sidebar visibility on window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 991) {
            leftSidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
        }
    });
});