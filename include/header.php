<?php
// Prevent multiple inclusions
if (defined('HEADER_INCLUDED')) {
    return;
}
define('HEADER_INCLUDED', true);

require_once __DIR__ . '/session.php';
$cartCount = getCartCount();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Green Furniture Co. - Premium Modern Furniture'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        .header-blur {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }
        
        .nav-link {
            position: relative;
            transition: all 0.3s ease;
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -8px;
            left: 50%;
            background: linear-gradient(90deg, #4A7C59, #7FB069);
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }
        
        .nav-link:hover::after {
            width: 100%;
        }
        
        .cart-bounce {
            animation: cartBounce 0.6s ease-in-out;
        }
        
        @keyframes cartBounce {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        
        .mobile-menu-enter {
            animation: slideDown 0.3s ease-out forwards;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .search-input {
            transition: all 0.3s ease;
        }
        
        .search-input:focus {
            transform: scale(1.02);
            box-shadow: 0 0 0 3px rgba(127, 176, 105, 0.1);
        }
    </style>
</head>
<body class="bg-warm-gray font-display">
    <!-- Top Banner -->
    <div class="bg-gradient-to-r from-dark-green to-medium-green text-white py-2 px-4 text-center text-sm">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <span>🚚 Free shipping on orders over $500</span>
            <span>📞 Call us: (555) 123-4567</span>
        </div>
    </div>

    <!-- Main Header -->
    <header class="header-blur shadow-soft sticky top-0 z-50 border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
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

                <!-- Search Bar (Desktop) -->
                <div class="hidden lg:flex flex-1 max-w-lg mx-8">
                    <div class="relative w-full">
                        <input type="text" 
                               placeholder="Search furniture, brands, categories..." 
                               class="search-input w-full px-4 py-3 pl-12 border border-gray-200 rounded-full focus:outline-none focus:border-light-green bg-white">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <button class="absolute inset-y-0 right-0 pr-4 flex items-center text-medium-green hover:text-dark-green transition-colors">
                            <i class="fas fa-microphone"></i>
                        </button>
                    </div>
                </div>

                <!-- Navigation (Desktop) -->
                <nav class="hidden lg:flex space-x-8">
                    <!-- <a href="index.php" class="nav-link text-gray-700 hover:text-medium-green font-medium py-2">
                        <i class="fas fa-home mr-2"></i>Home
                    </a> -->
                    <a href="products.php" class="nav-link text-gray-700 hover:text-medium-green font-medium py-2">
                        <i class="fas fa-couch mr-2"></i>All Furniture
                    </a>
                    <div class="relative group">
                        <a href="#" class="nav-link text-gray-700 hover:text-medium-green font-medium py-2 flex items-center">
                            <i class="fas fa-list mr-2"></i>Categories
                            <i class="fas fa-chevron-down ml-1 text-xs transition-transform group-hover:rotate-180"></i>
                        </a>
                        <!-- Dropdown Menu -->
                        <div class="absolute top-full left-0 mt-2 w-64 bg-white rounded-xl shadow-soft border border-gray-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 transform translate-y-2 group-hover:translate-y-0">
                            <div class="p-2">
                                <a href="products.php?category=1" class="flex items-center px-4 py-3 text-gray-700 hover:bg-pale-green hover:text-medium-green rounded-lg transition-colors">
                                    <i class="fas fa-couch w-5 mr-3 text-medium-green"></i>Living Room
                                </a>
                                <a href="products.php?category=2" class="flex items-center px-4 py-3 text-gray-700 hover:bg-pale-green hover:text-medium-green rounded-lg transition-colors">
                                    <i class="fas fa-bed w-5 mr-3 text-medium-green"></i>Bedroom
                                </a>
                                <a href="products.php?category=3" class="flex items-center px-4 py-3 text-gray-700 hover:bg-pale-green hover:text-medium-green rounded-lg transition-colors">
                                    <i class="fas fa-utensils w-5 mr-3 text-medium-green"></i>Dining
                                </a>
                                <a href="products.php?category=4" class="flex items-center px-4 py-3 text-gray-700 hover:bg-pale-green hover:text-medium-green rounded-lg transition-colors">
                                    <i class="fas fa-briefcase w-5 mr-3 text-medium-green"></i>Office
                                </a>
                                <a href="products.php?category=5" class="flex items-center px-4 py-3 text-gray-700 hover:bg-pale-green hover:text-medium-green rounded-lg transition-colors">
                                    <i class="fas fa-seedling w-5 mr-3 text-medium-green"></i>Outdoor
                                </a>
                            </div>
                        </div>
                    </div>
                </nav>

                <!-- User Actions -->
                <div class="flex items-center space-x-6">
                    <!-- Search Icon (Mobile) -->
                    <button class="lg:hidden text-gray-700 hover:text-medium-green transition-colors" id="mobile-search-btn">
                        <i class="fas fa-search text-xl"></i>
                    </button>

          

                    <!-- Cart -->
                    <?php if (isLoggedIn()): ?>
                        <a href="cart.php" class="relative text-gray-700 hover:text-medium-green transition-colors group" id="cart-icon">
<img src="images/icons/carts.png" alt="Cart" class="w-5 h-5">
                        
                        <?php if ($cartCount > 0): ?>
                                <span class="absolute -top-2 -right-2 bg-accent-orange text-white text-xs rounded-full h-6 w-6 flex items-center justify-center font-bold shadow-lg">
                                    <?php echo $cartCount; ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    <?php endif; ?>

                    <!-- User Menu -->
                    <?php if (isLoggedIn()): ?>
                        <div class="relative group">
                            <button class="flex items-center space-x-2 text-gray-700 hover:text-medium-green transition-colors">
                                <div class="w-8 h-8 bg-gradient-to-r from-medium-green to-light-green rounded-full flex items-center justify-center">
                                    <i class="fas fa-user text-white text-sm"></i>
                                </div>
                                <i class="fas fa-chevron-down text-xs transition-transform group-hover:rotate-180"></i>
                            </button>
                            
                            <!-- User Dropdown -->
                            <div class="absolute top-full right-0 mt-2 w-56 bg-white rounded-xl shadow-soft border border-gray-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 transform translate-y-2 group-hover:translate-y-0">
                                <div class="p-2">
                                    <a href="profile.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-pale-green hover:text-medium-green rounded-lg transition-colors">
                                        <i class="fas fa-user-circle w-5 mr-3 text-medium-green"></i>My Profile
                                    </a>
                                    <a href="orders.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-pale-green hover:text-medium-green rounded-lg transition-colors">
                                        <i class="fas fa-box w-5 mr-3 text-medium-green"></i>My Orders
                                    </a>
                                    <a href="addresses.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-pale-green hover:text-medium-green rounded-lg transition-colors">
                                        <i class="fas fa-map-marker-alt w-5 mr-3 text-medium-green"></i>Addresses
                                    </a>
                                    <?php if (isAdmin()): ?>
                                        <div class="border-t border-gray-100 my-2"></div>
                                        <a href="admin/" class="flex items-center px-4 py-3 text-accent-orange hover:bg-orange-50 rounded-lg transition-colors font-semibold">
                                            <i class="fas fa-cog w-5 mr-3"></i>Admin Panel
                                        </a>
                                    <?php endif; ?>
                                    <div class="border-t border-gray-100 my-2"></div>
                                    <a href="logout.php" class="flex items-center px-4 py-3 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                        <i class="fas fa-sign-out-alt w-5 mr-3"></i>Sign Out
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="text-gray-700 hover:text-medium-green transition-colors font-medium">
                            <i class="fas fa-sign-in-alt mr-2"></i>Login
                        </a>
                        <a href="register.php" class="bg-gradient-to-r from-medium-green to-light-green text-white px-6 py-2 rounded-full hover:shadow-glow transition-all duration-300 font-medium">
                            <i class="fas fa-user-plus mr-2"></i>Register
                        </a>
                    <?php endif; ?>

                    <!-- Mobile menu button -->
                    <button class="lg:hidden text-gray-700 hover:text-medium-green transition-colors" id="mobile-menu-button">
                        <i class="fas fa-bars text-xl" id="menu-icon"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Search Bar -->
        <div id="mobile-search" class="hidden lg:hidden bg-white border-t border-gray-100 px-4 py-3">
            <div class="relative">
                <input type="text" 
                       placeholder="Search furniture..." 
                       class="w-full px-4 py-3 pl-12 border border-gray-200 rounded-full focus:outline-none focus:border-light-green">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden lg:hidden bg-white border-t border-gray-100">
            <div class="px-4 py-4 space-y-2">
                <a href="index.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-pale-green hover:text-medium-green rounded-lg transition-colors">
                    <i class="fas fa-home w-5 mr-3 text-medium-green"></i>Home
                </a>
                <a href="products.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-pale-green hover:text-medium-green rounded-lg transition-colors">
                    <i class="fas fa-couch w-5 mr-3 text-medium-green"></i>All Furniture
                </a>
                
                <!-- Mobile Categories -->
                <div class="ml-4 space-y-1 border-l-2 border-pale-green pl-4">
                    <a href="products.php?category=1" class="flex items-center px-4 py-2 text-gray-600 hover:bg-pale-green hover:text-medium-green rounded-lg transition-colors text-sm">
                        <i class="fas fa-couch w-4 mr-3"></i>Living Room
                    </a>
                    <a href="products.php?category=2" class="flex items-center px-4 py-2 text-gray-600 hover:bg-pale-green hover:text-medium-green rounded-lg transition-colors text-sm">
                        <i class="fas fa-bed w-4 mr-3"></i>Bedroom
                    </a>
                    <a href="products.php?category=3" class="flex items-center px-4 py-2 text-gray-600 hover:bg-pale-green hover:text-medium-green rounded-lg transition-colors text-sm">
                        <i class="fas fa-utensils w-4 mr-3"></i>Dining
                    </a>
                    <a href="products.php?category=4" class="flex items-center px-4 py-2 text-gray-600 hover:bg-pale-green hover:text-medium-green rounded-lg transition-colors text-sm">
                        <i class="fas fa-briefcase w-4 mr-3"></i>Office
                    </a>
                </div>

                <?php if (isLoggedIn()): ?>
                    <div class="border-t border-gray-100 my-3"></div>
                    <a href="wishlist.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-pale-green hover:text-medium-green rounded-lg transition-colors">
                        <i class="fas fa-heart w-5 mr-3 text-medium-green"></i>Wishlist
                    </a>
                    <a href="orders.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-pale-green hover:text-medium-green rounded-lg transition-colors">
                        <i class="fas fa-box w-5 mr-3 text-medium-green"></i>My Orders
                    </a>
                    <a href="profile.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-pale-green hover:text-medium-green rounded-lg transition-colors">
                        <i class="fas fa-user-circle w-5 mr-3 text-medium-green"></i>Profile
                    </a>
                <?php else: ?>
                    <a href="login.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-pale-green hover:text-medium-green rounded-lg transition-colors">
                        <i class="fas fa-sign-in-alt w-5 mr-3 text-medium-green"></i>Login
                    </a>
                    <a href="register.php" class="flex items-center px-4 py-3 bg-gradient-to-r from-medium-green to-light-green text-white rounded-lg mx-4 justify-center">
                        <i class="fas fa-user-plus w-5 mr-3"></i>Register
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <script>
        // Mobile menu toggle
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');
        const menuIcon = document.getElementById('menu-icon');

        mobileMenuButton.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
            
            if (mobileMenu.classList.contains('hidden')) {
                menuIcon.className = 'fas fa-bars text-xl';
            } else {
                menuIcon.className = 'fas fa-times text-xl';
                mobileMenu.classList.add('mobile-menu-enter');
            }
        });

        // Mobile search toggle
        const mobileSearchBtn = document.getElementById('mobile-search-btn');
        const mobileSearch = document.getElementById('mobile-search');

        mobileSearchBtn.addEventListener('click', function() {
            mobileSearch.classList.toggle('hidden');
        });

        // Cart animation on add to cart (you can trigger this from your add to cart functionality)
        function animateCart() {
            const cartIcon = document.getElementById('cart-icon');
            cartIcon.classList.add('cart-bounce');
            setTimeout(() => {
                cartIcon.classList.remove('cart-bounce');
            }, 600);
        }

        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            const isClickInsideMenu = mobileMenu.contains(event.target);
            const isClickOnMenuButton = mobileMenuButton.contains(event.target);
            
            if (!isClickInsideMenu && !isClickOnMenuButton && !mobileMenu.classList.contains('hidden')) {
                mobileMenu.classList.add('hidden');
                menuIcon.className = 'fas fa-bars text-xl';
            }
        });

        // Sticky header effect
        let lastScrollTop = 0;
        const header = document.querySelector('header');

        window.addEventListener('scroll', function() {
            let scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            
            if (scrollTop > lastScrollTop) {
                // Scrolling down
                header.style.transform = 'translateY(-100%)';
            } else {
                // Scrolling up
                header.style.transform = 'translateY(0)';
            }
            
            lastScrollTop = scrollTop;
        });
    </script>