    </main>
    
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5><i class="fas fa-book"></i> BookStore</h5>
                    <p>Your one-stop shop for all books. From classics to bestsellers.</p>
                </div>
                <div class="col-md-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-white-50">Home</a></li>
                        <li><a href="index.php?page=books" class="text-white-50">Books</a></li>
                        <li><a href="index.php?page=login" class="text-white-50">Login</a></li>
                        <li><a href="index.php?page=register" class="text-white-50">Register</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Contact Us</h5>
                    <p><i class="fas fa-envelope"></i> support@bookstore.com</p>
                    <p><i class="fas fa-phone"></i> +1 234 567 8900</p>
                </div>
            </div>
            <hr class="bg-light">
            <div class="text-center">
                <p>&copy; <?php echo date('Y'); ?> Online Book Store. All rights reserved.</p>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    // Global functions
    function addToCart(bookId) {
        fetch('api/cart.php?action=add&book_id=' + bookId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Book added to cart!');
                    location.reload();
                } else {
                    if (data.redirect) {
                        window.location.href = 'index.php?page=login';
                    } else {
                        alert(data.message || 'Failed to add to cart');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Network error. Please try again.');
            });
    }
    </script>
</body>
</html>