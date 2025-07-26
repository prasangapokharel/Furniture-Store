<!-- Footer -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'dark-green': '#2D4A32',
                        'medium-green': '#4A7C59',
                        'light-green': '#7FB069',
                        'pale-green': '#E8F5E8',
                        'warm-gray': '#F8F9FA',
                        'accent-orange': '#FF6B35',
                    },
                    fontFamily: {
                        'display': ['Inter', 'system-ui', 'sans-serif'],
                    },
                    boxShadow: {
                        'soft': '0 2px 15px -3px rgba(0, 0, 0, 0.07), 0 10px 20px -2px rgba(0, 0, 0, 0.04)',
                        'glow': '0 0 20px rgba(127, 176, 105, 0.3)',
                    }
                }
            }
        }
    </script>
<footer class="bg-warm-gray font-display text-medium-green mt-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <div>
                 <!-- Logo -->
                <div class="flex items-center flex-shrink-0">
                    <a href="index.php" class="flex items-center group transition-transform hover:scale-105">
                        <!-- <img src="https://greenfc.com/app/uploads/2021/04/GFC_Primary_logo_RGB_pos.svg" 
                             alt="Green Furniture Co." class="h-12 w-auto mr-3 transition-all group-hover:brightness-110"> -->
                        <div class="hidden sm:block">
                            <div class="text-xl font-bold text-dark-green">Green Furniture Co.</div>
                            <div class="text-xs text-gray-500 -mt-1">Premium Modern Furniture</div>
                        </div>
                    </a>
                </div>

                <p class="text-medium-green mb-4">
                    Your destination for beautiful, modern furniture that transforms your home into a stylish sanctuary.
                </p>
                <div class="flex space-x-4">
                    <a href="#" class="text-medium-green hover:text-pink-primary transition-colors">📘</a>
                    <a href="#" class="text-medium-green hover:text-pink-primary transition-colors">📷</a>
                    <a href="#" class="text-medium-green hover:text-pink-primary transition-colors">🐦</a>
                </div>
            </div>
            <div>
                <h4 class="text-lg font-semibold mb-4">Shop by Room</h4>
                <ul class="space-y-2">
                    <li><a href="products.php?category=1" class="text-medium-green hover:text-pink-primary transition-colors">Living Room</a></li>
                    <li><a href="products.php?category=2" class="text-medium-green hover:text-pink-primary transition-colors">Bedroom</a></li>
                    <li><a href="products.php?category=3" class="text-medium-green hover:text-pink-primary transition-colors">Dining Room</a></li>
                    <li><a href="products.php?category=4" class="text-medium-green hover:text-pink-primary transition-colors">Office</a></li>
                    <li><a href="products.php?category=5" class="text-medium-green hover:text-pink-primary transition-colors">Outdoor</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-lg font-semibold mb-4">Customer Service</h4>
                <ul class="space-y-2">
                    <li><a href="#" class="text-medium-green hover:text-pink-primary transition-colors">Contact Us</a></li>
                    <li><a href="#" class="text-medium-green hover:text-pink-primary transition-colors">Shipping Info</a></li>
                    <li><a href="#" class="text-medium-green hover:text-pink-primary transition-colors">Returns & Exchanges</a></li>
                    <li><a href="#" class="text-medium-green hover:text-pink-primary transition-colors">Assembly Service</a></li>
                    <li><a href="#" class="text-medium-green hover:text-pink-primary transition-colors">FAQ</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-lg font-semibold mb-4">Contact Info</h4>
                <div class="text-medium-green space-y-2">
                    <p>📧 hello@pinkhome.com</p>
                    <p>📞 (555) 123-4567</p>
                    <p>📍 123 Furniture St, Design City</p>
                    <p>🕒 Mon-Sat: 9AM-8PM</p>
                    <p>🕒 Sunday: 11AM-6PM</p>
                </div>
            </div>
        </div>
        <div class="border-t border-gray-700 mt-8 pt-8 text-center">
            <p class="text-medium-green">&copy; 2024 Pink Home. All rights reserved. | Privacy Policy | Terms of Service</p>
        </div>
    </div>
</footer>

</body>
</html>