<?php
$pageTitle = 'Pink Home - Modern Furniture Store';
require_once __DIR__ . '/include/functions.php';
require_once __DIR__ . '/include/header.php';

// Get featured products
$conn = getConnection();
$stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC LIMIT 8");
$stmt->execute();
$featuredProducts = $stmt->fetchAll();

// Get categories
$stmt = $conn->prepare("SELECT * FROM categories ORDER BY name");
$stmt->execute();
$categories = $stmt->fetchAll();
?>

<link rel="stylesheet" href="css/styles.css">

<!-- Enhanced Hero Section -->
<section class="relative min-h-screen flex items-center justify-center overflow-hidden">
    <!-- Background Image with Parallax Effect -->
    <div class="absolute inset-0 bg-cover bg-center bg-fixed transform scale-110 transition-transform duration-1000 ease-out"
         style="background-image: url('https://greenfc.com/app/uploads/2021/09/leaf-lamp-pendant-1-2048x1152.jpg');">
    </div>
    
    <!-- Gradient Overlay -->
    <div class="absolute inset-0 bg-gradient-to-br from-dark-green/80 via-medium-green/70 to-light-green/60"></div>
    
    <!-- Animated Particles -->
    <div class="absolute inset-0 overflow-hidden">
        <div class="particle absolute w-2 h-2 bg-light-green/30 rounded-full animate-float" style="top: 20%; left: 10%; animation-delay: 0s;"></div>
        <div class="particle absolute w-1 h-1 bg-pale-green/40 rounded-full animate-float" style="top: 60%; left: 80%; animation-delay: 2s;"></div>
        <div class="particle absolute w-3 h-3 bg-medium-green/20 rounded-full animate-float" style="top: 30%; left: 70%; animation-delay: 4s;"></div>
        <div class="particle absolute w-1.5 h-1.5 bg-light-green/50 rounded-full animate-float" style="top: 80%; left: 20%; animation-delay: 1s;"></div>
        <div class="particle absolute w-2 h-2 bg-pale-green/30 rounded-full animate-float" style="top: 40%; left: 90%; animation-delay: 3s;"></div>
    </div>
    
    <!-- Main Content -->
    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="space-y-8">
            <!-- Badge -->
            <div class="inline-flex items-center px-4 py-2 bg-white/10 backdrop-blur-sm border border-white/20 rounded-full text-white text-sm font-medium opacity-0 animate-fade-in-down">
                <i class="fas fa-leaf mr-2 text-light-green"></i>
                Eco-Friendly • Premium Quality • Modern Design
            </div>
            
            <!-- Main Heading -->
            <h1 class="text-5xl md:text-7xl lg:text-8xl font-bold text-white leading-tight opacity-0 animate-fade-in-up">
                Beautiful Furniture for
                <span class="block bg-gradient-to-r from-light-green via-pale-green to-light-green bg-clip-text text-transparent animate-pulse-slow">
                    Modern Homes
                </span>
            </h1>
            
            <!-- Subtitle -->
            <p class="text-xl md:text-2xl text-white/90 max-w-3xl mx-auto leading-relaxed opacity-0 animate-fade-in-up" style="animation-delay: 0.3s;">
                Discover our curated collection of stylish furniture pieces that transform your space into a 
                <span class="text-light-green font-semibold">green paradise</span>. 
                Quality craftsmanship meets modern design.
            </p>
            
            <!-- Statistics -->
            <div class="flex flex-wrap justify-center gap-8 md:gap-12 py-8 opacity-0 animate-fade-in-up" style="animation-delay: 0.6s;">
                <div class="text-center">
                    <div class="text-3xl md:text-4xl font-bold text-light-green">5000+</div>
                    <div class="text-white/80 text-sm">Happy Customers</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl md:text-4xl font-bold text-light-green">1000+</div>
                    <div class="text-white/80 text-sm">Premium Products</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl md:text-4xl font-bold text-light-green">15+</div>
                    <div class="text-white/80 text-sm">Years Experience</div>
                </div>
            </div>
            
            <!-- CTA Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center opacity-0 animate-fade-in-up" style="animation-delay: 0.9s;">
                <a href="products.php" class="group relative inline-flex items-center px-8 py-4 bg-gradient-to-r from-light-green to-medium-green text-white font-semibold text-lg rounded-full shadow-xl hover:shadow-2xl transform hover:scale-105 transition-all duration-300 overflow-hidden">
                    <span class="absolute inset-0 bg-gradient-to-r from-medium-green to-dark-green transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left"></span>
                    <span class="relative flex items-center">
                        <i class="fas fa-shopping-bag mr-3"></i>
                        Shop Collection
                        <i class="fas fa-arrow-right ml-3 transform group-hover:translate-x-1 transition-transform"></i>
                    </span>
                </a>
                
                <a href="#featured" class="group inline-flex items-center px-8 py-4 bg-white/10 backdrop-blur-sm border-2 border-white/30 text-white font-semibold text-lg rounded-full hover:bg-white/20 hover:border-light-green transition-all duration-300">
                    <i class="fas fa-play mr-3 text-light-green"></i>
                    Watch Story
                    <div class="ml-3 w-2 h-2 bg-light-green rounded-full animate-pulse"></div>
                </a>
            </div>
            
            <!-- Feature Highlights -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-16 opacity-0 animate-fade-in-up" style="animation-delay: 1.2s;">
                <div class="group bg-white/5 backdrop-blur-sm border border-white/10 rounded-2xl p-6 hover:bg-white/10 transition-all duration-300 transform hover:-translate-y-2">
                    <div class="w-16 h-16 bg-gradient-to-br from-light-green to-medium-green rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                        <i class="fas fa-truck text-white text-2xl"></i>
                    </div>
                    <h3 class="text-white font-semibold text-lg mb-2">Free Delivery</h3>
                    <p class="text-white/70 text-sm">Free shipping on orders over $500 nationwide</p>
                </div>
                
                <div class="group bg-white/5 backdrop-blur-sm border border-white/10 rounded-2xl p-6 hover:bg-white/10 transition-all duration-300 transform hover:-translate-y-2">
                    <div class="w-16 h-16 bg-gradient-to-br from-light-green to-medium-green rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                        <i class="fas fa-shield-alt text-white text-2xl"></i>
                    </div>
                    <h3 class="text-white font-semibold text-lg mb-2">Quality Guarantee</h3>
                    <p class="text-white/70 text-sm">5-year warranty on all premium furniture</p>
                </div>
                
                <div class="group bg-white/5 backdrop-blur-sm border border-white/10 rounded-2xl p-6 hover:bg-white/10 transition-all duration-300 transform hover:-translate-y-2">
                    <div class="w-16 h-16 bg-gradient-to-br from-light-green to-medium-green rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                        <i class="fas fa-leaf text-white text-2xl"></i>
                    </div>
                    <h3 class="text-white font-semibold text-lg mb-2">Eco-Friendly</h3>
                    <p class="text-white/70 text-sm">Sustainable materials and responsible crafting</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scroll Down Indicator -->
    <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 text-white/80 animate-bounce-gentle">
        <div class="flex flex-col items-center space-y-2">
            <span class="text-sm font-medium">Discover More</span>
            <div class="w-6 h-10 border-2 border-white/40 rounded-full flex justify-center">
                <div class="w-1 h-3 bg-light-green rounded-full mt-2 animate-bounce"></div>
            </div>
        </div>
    </div>
    
    <!-- Floating Social Proof -->
    <!-- <div class="absolute bottom-20 right-8 hidden lg:block opacity-0 animate-fade-in-left">
        <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl p-4 text-white">
            <div class="flex items-center space-x-3 mb-2">
                <div class="flex -space-x-2">
                    <img class="w-8 h-8 rounded-full border-2 border-light-green" src="https://randomuser.me/api/portraits/women/1.jpg" alt="Customer">
                    <img class="w-8 h-8 rounded-full border-2 border-light-green" src="https://randomuser.me/api/portraits/men/2.jpg" alt="Customer">
                    <img class="w-8 h-8 rounded-full border-2 border-light-green" src="https://randomuser.me/api/portraits/women/3.jpg" alt="Customer">
                </div>
                <div class="flex text-yellow-400">
                    <i class="fas fa-star text-sm"></i>
                    <i class="fas fa-star text-sm"></i>
                    <i class="fas fa-star text-sm"></i>
                    <i class="fas fa-star text-sm"></i>
                    <i class="fas fa-star text-sm"></i>
                </div>
            </div>
            <p class="text-sm text-white/90 font-medium">4.9/5 from 2,500+ reviews</p>
            <p class="text-xs text-white/70">Latest: "Amazing quality!" - Sarah M.</p>
        </div>
    </div> -->
</section>

<!-- Features Section -->
<section style="padding: 64px 0; background-color: white;">
    <div style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 32px;">
            <div class="theme-card" style="text-align: center;">
                <div style="background-color: var(--light-green); border-radius: 50%; width: 64px; height: 64px; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; font-size: 2rem;">
                    🚚
                </div>
                <h3 class="theme-subheading" style="font-size: 1.25rem; margin-bottom: 8px;">Free Delivery</h3>
                <p class="theme-text">Free delivery on orders over Rs 50,000. Fast and reliable shipping to your door.</p>
            </div>
            <div class="theme-card" style="text-align: center;">
                <div style="background-color: var(--light-green); border-radius: 50%; width: 64px; height: 64px; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; font-size: 2rem;">
                    🔧
                </div>
                <h3 class="theme-subheading" style="font-size: 1.25rem; margin-bottom: 8px;">Assembly Service</h3>
                <p class="theme-text">Professional assembly service available. Let our experts set up your furniture.</p>
            </div>
            <div class="theme-card" style="text-align: center;">
                <div style=" border-radius: 50%; width: 84px; height: 84px; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; font-size: 2rem;">
<img src="images/icons/delivery.png" alt="Return" class="">                 
                </div>
                <h3 class="theme-subheading" style="font-size: 1.25rem; margin-bottom: 8px;">30-Day Returns</h3>
                <p class="theme-text">Not satisfied? Return your furniture within 30 days for a full refund.</p>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section style="padding: 64px 0; background-color: var(--pale-green);">
    <div style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
        <h2 class="theme-heading" style="text-align: center; font-size: 2rem; margin-bottom: 48px;">Shop by Room</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 24px;">
            <?php 
            $roomIcons = ['🛋️', '🛏️', '🍽️', '💼', '🌿'];
            foreach ($categories as $index => $category): 
            ?>
                <a href="products.php?category=<?php echo $category['id']; ?>" class="theme-link" style="text-decoration: none;">
                    <div class="theme-card" style="text-align: center; transition: all 0.3s ease; cursor: pointer;">
                        <div style="font-size: 3rem; margin-bottom: 16px;"><?php echo $roomIcons[$index] ?? '🏠'; ?></div>
                        <h3 class="theme-subheading" style="font-size: 1.125rem; margin-bottom: 8px;">
                            <?php echo htmlspecialchars($category['name']); ?>
                        </h3>
                        <p class="theme-text" style="font-size: 0.875rem;">
                            <?php echo htmlspecialchars($category['description']); ?>
                        </p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Products -->
<section id="featured" style="padding: 64px 0; background-color: white;">
    <div style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
        <div style="text-align: center; margin-bottom: 48px;">
            <h2 class="theme-heading" style="font-size: 2rem; margin-bottom: 16px;">Featured Products</h2>
            <p class="theme-text" style="max-width: 600px; margin: 0 auto;">Handpicked furniture pieces that combine style, comfort, and quality craftsmanship.</p>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 32px;">
            <?php foreach ($featuredProducts as $product): ?>
                <div class="theme-card" style="overflow: hidden; transition: all 0.3s ease;">
                    <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="theme-link" style="text-decoration: none;">
                        <div style="position: relative; overflow: hidden; margin-bottom: 16px;">
                            <img src="<?php echo getProductImage($product); ?>"
                                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                                 style="width: 100%; height: 200px; object-fit: cover; transition: transform 0.3s ease;"
                                 onmouseover="this.style.transform='scale(1.05)'"
                                 onmouseout="this.style.transform='scale(1)'"
                                 onerror="this.src='images/placeholder.jpg'">
                            <?php if ($product['stock_quantity'] <= 2): ?>
                                <span style="position: absolute; top: 8px; left: 8px; background-color: #ef4444; color: white; padding: 4px 8px; font-size: 0.75rem; border-radius: 4px;">Low Stock</span>
                            <?php endif; ?>
                        </div>
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                                <h3 class="theme-subheading" style="font-size: 1.125rem; flex: 1;">
                                    <?php echo htmlspecialchars($product['name']); ?>
                                </h3>
                                <span style="font-size: 0.75rem; color: var(--medium-green); background-color: var(--pale-green); padding: 4px 8px; border-radius: 4px; margin-left: 8px;">
                                    <?php echo htmlspecialchars($product['category_name']); ?>
                                </span>
                            </div>
                            <p class="theme-text" style="font-size: 0.875rem; margin-bottom: 12px; line-height: 1.4;">
                                <?php echo htmlspecialchars(substr($product['description'], 0, 100)) . '...'; ?>
                            </p>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                <span class="theme-heading" style="font-size: 1.5rem; color: var(--medium-green);">
                                    <?php echo formatPrice($product['price']); ?>
                                </span>
                                <span class="theme-text" style="font-size: 0.875rem;">
                                    <?php echo htmlspecialchars($product['material']); ?>
                                </span>
                            </div>
                            <div class="theme-text" style="font-size: 0.75rem; margin-bottom: 8px;">
                                📏 <?php echo htmlspecialchars($product['dimensions']); ?>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        <div style="text-align: center; margin-top: 48px;">
            <a href="products.php" class="theme-button" style="padding: 12px 32px; text-decoration: none; border-radius: 8px;">
                View All Products
            </a>
        </div>
    </div>
</section>

<!-- Newsletter Section -->
<section style="padding: 64px 0; background-color: var(--light-green);">
    <div style="max-width: 800px; margin: 0 auto; padding: 0 20px; text-align: center;">
        <h2 class="theme-heading" style="font-size: 2rem; margin-bottom: 16px;">Stay Updated</h2>
        <p class="theme-text" style="margin-bottom: 32px;">Get the latest furniture trends, exclusive offers, and design tips delivered to your inbox.</p>
        <form style="display: flex; flex-wrap: wrap; gap: 16px; max-width: 400px; margin: 0 auto;">
            <input type="email" placeholder="Enter your email" style="flex: 1; padding: 12px 16px; border-radius: 8px; border: 2px solid var(--medium-green); outline: none; min-width: 200px;">
            <button type="submit" class="theme-button" style="padding: 12px 24px; border-radius: 8px; border: none; cursor: pointer;">
                Subscribe
            </button>
        </form>
    </div>
</section>

<?php require_once __DIR__ . '/include/footer.php'; ?>