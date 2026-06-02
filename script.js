document.addEventListener('DOMContentLoaded', () => {
    console.log("Room Renting System Loaded Successfully");

    // 1. Highlight the current page in the Navbar
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('.nav-links a');
    
    navLinks.forEach(link => {
        if (link.getAttribute('href') === currentPath.split('/').pop()) {
            link.style.borderBottom = "2px solid white";
        }
    });

    // 2. Confirmation for Deleting Rooms (Admin/Landlord)
    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            if (!confirm("Are you sure you want to delete this listing?")) {
                e.preventDefault();
            }
        });
    });

    // 3. Price Filter feedback
    const sortBtn = document.querySelector('.sort-btn');
    if (sortBtn) {
        sortBtn.addEventListener('click', () => {
            console.log("Sorting rooms using Merge Sort Algorithm...");
        });
    }
});