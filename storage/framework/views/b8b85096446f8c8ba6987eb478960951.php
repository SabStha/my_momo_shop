<?php $__env->startSection('content'); ?>
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<style>
    html, body {
        margin: 0;
        padding: 0;
        scroll-behavior: smooth;
        font-family: 'Segoe UI', sans-serif;
        background-color: #f1c987; /* Golden beige */
        color: #2f1b12; /* Rich brown */
    }

    nav {
        background-color: #7e2a13 !important; /* Deep red */
    }

    .nav-links a {
        text-decoration: none;
        color: #f6db99;
        font-size: 1.2rem;
        font-weight: 600;
        transition: all 0.3s ease;
        padding: 0.5rem 1rem;
    }

    .nav-links a:hover {
        color: #fff;
        text-shadow: 0 0 6px #f6db99;
        transform: translateY(-2px);
    }

    nav a.btn {
        font-weight: 500;
        transition: all 0.3s ease;
        background-color: #5f1a0a;
        border: none;
        color: #f6db99;
    }

    nav a.btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        background-color: #441107;
        color: white;
    }

    .order-now button,
    .btn-outline-danger,
    .btn-danger {
        background-color: #5f1a0a;
        color: #f6db99;
        border: none;
    }

    .order-now button:hover,
    .btn-outline-danger:hover,
    .btn-danger:hover {
        background-color: #441107;
        color: #fff;
        transform: scale(1.05) translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    }

    .filter-btn {
        background-color: transparent;
        border: 2px solid #5f1a0a;
        color: #5f1a0a;
    }

    .filter-btn.active,
    .filter-btn:hover {
        background-color: #5f1a0a;
        color: #f6db99;
        transform: translateY(-2px);
    }

    .section-title {
        color: #5f1a0a;
    }

    .review-card,
    .menu-box,
    .momo-card,
    .bottom-nav div {
        background-color: #f9e5b1;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }

    .badge {
        background-color: #f2cd52;
        color: #2f1b12;
    }

    .nav-links {
        display: flex;
        justify-content: space-between;
        width: 100%;
        max-width: 700px;
        margin: 0 auto;
    }

    .nav-links a {
        text-decoration: none;
        color: #fff;
        font-size: 1.2rem;
        font-weight: 600;
        transition: all 0.3s ease;
        position: relative;
        padding: 0.5rem 1rem;
        text-align: center;
    }

    .nav-links a:hover {
        color: #ffe0b2;
        font-size: 1.3rem;
        text-shadow: 0 0 6px #ffab91;
        transform: translateY(-2px);
    }

    .hero-slider {
        text-align: center;
        
        margin-top: 0;
        position: relative;
        transition: all 0.3s ease;
    }

    .hero-slider img {
        width: 100%;
        max-height: 400px;
        object-fit: cover;
        border-radius: 0 0 10px 10px;
        transition: all 0.3s ease;
    }

    .hero-slider img:hover {
        transform: scale(1.02);
    }

    .order-now {
        position: absolute;
        bottom: 30px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 2;
        transition: all 0.3s ease;
    }

    .order-now button {
        padding: 14px 28px;
        font-size: 20px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.3);
        transition: all 0.3s ease;
    }

    .order-now button:hover {
        transform: scale(1.05) translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.4);
    }

    .menu-box {
        background: #fff;
        padding: 10px;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        height: 100%;
        transition: all 0.3s ease;
    }

    .menu-box:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }

    .menu-box img {
        width: 100%;
        height: 300px;
        object-fit: cover;
        border-radius: 10px;
        aspect-ratio: 1 / 1;
        transition: all 0.3s ease;
    }

    .menu-box img:hover {
        transform: scale(1.05);
    }

    .reviews {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 20px;
    }

    .review-card {
        background: #fff8f0;
        border-radius: 12px;
        padding: 25px;
        width: 300px;
        min-height: 200px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        text-align: center;
        transition: all 0.3s ease;
    }

    .review-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }

    .review-card img {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        object-fit: cover;
        transition: all 0.3s ease;
    }

    .review-card:hover img {
        transform: scale(1.1);
    }

    .bottom-nav {
        display: flex;
        justify-content: space-around;
        flex-wrap: wrap;
        gap: 20px;
    }

    .bottom-nav div {
        background: #ffe0b2;
        padding: 20px;
        border-radius: 8px;
        flex: 1 1 250px;
        min-height: 120px;
        transition: all 0.3s ease;
    }

    .bottom-nav div:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    .scroll-top, .back-btn {
        position: fixed;
        bottom: 20px;
        z-index: 99;
        background: #d84315;
        color: #fff;
        border: none;
        padding: 10px 15px;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .scroll-top:hover, .back-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }

    .scroll-top {
        right: 20px;
    }

    .back-btn {
        left: 20px;
    }

    .momo-card {
        transition: all 0.3s ease;
    }

    .momo-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }

    .momo-card img {
        transition: all 0.3s ease;
    }

    .momo-card:hover img {
        transform: scale(1.05);
    }

    .momo-card .btn {
        transition: all 0.3s ease;
    }

    .momo-card .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    @media(max-width: 768px) {
        .reviews { flex-direction: column; align-items: center; }
        .bottom-nav { flex-direction: column; align-items: center; }
        .order-now { bottom: 15px; }
    }

    .section-wrapper {
        padding: 60px 20px;
    }

    .menu-section {
        background-color: #f9e5b1;
    }

    .review-section {
        background-color: #ecd6a0;
    }

    .about-section {
        background-color: #dbb378;
    }

    .contact-section {
        background-color: #7e2a13;
        color: #fff;
    }
</style>

<section id="page1">
    <!-- 🍲 TOP NAVBAR -->
    <nav class="py-3 px-4 d-flex justify-content-center align-items-center" style="background: #fff3e0;">
        <div class="fw-bold text-danger fs-4">🍲 MOMO SHOP</div>
    </nav>


    <!-- 🔗 MAIN NAVBAR -->
    <nav class="sticky-top shadow-sm" style="background:rgb(238, 123, 123);">
        <div class="container d-flex flex-wrap justify-content-between align-items-center py-2">
            <div class="mx-auto d-flex gap-3 nav-links">
                <a href="#page1" class="nav-link fw-semibold">Home</a>
                <a href="#menu" class="nav-link fw-semibold">Menu</a>
                <a href="#reviews" class="nav-link fw-semibold">Reviews</a>
                <a href="#page2" class="nav-link fw-semibold">About</a>
                <a href="#page2" class="nav-link fw-semibold">Contact</a>
            </div>
            <div class="d-flex align-items-center gap-2">
                <input class="form-control form-control-sm rounded-pill" type="search" placeholder="Search momo..." style="width: 300px;">
                
            </div>
        </div>
    </nav>

    <div class="hero-slider" data-aos="fade-up">
        <div class="carousel slide" id="heroCarousel" data-bs-ride="carousel">
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img src="<?php echo e(asset('storage/products/momo1.jpg')); ?>" class="d-block w-100" alt="Momo 1">
                </div>
                <div class="carousel-item">
                    <img src="<?php echo e(asset('storage/products/momo2.jpg')); ?>" class="d-block w-100" alt="Momo 2">
                </div>
                <div class="carousel-item">
                    <img src="<?php echo e(asset('storage/products/momo3.jpg')); ?>" class="d-block w-100" alt="Momo 3">
                </div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>
            <div class="order-now">
                <button class="btn btn-danger btn-lg">Order Now</button>
            </div>
        </div>
    </div>

    <div class="section-wrapper menu-section" id="menu">
        <div class="container">
            <h4 class="section-title text-center mb-4">Menu Highlights</h4>
            <div class="text-center mb-3">
                <button class="btn btn-outline-dark btn-sm filter-btn me-2 active" data-filter="all">All</button>
                <button class="btn btn-outline-dark btn-sm filter-btn me-2" data-filter="Spicy">Spicy</button>
                <button class="btn btn-outline-dark btn-sm filter-btn me-2" data-filter="Vegetarian">Vegetarian</button>
                <button class="btn btn-outline-dark btn-sm filter-btn" data-filter="Best Seller">Best Seller</button>
            </div>
            <div class="d-flex overflow-auto px-3 gap-4">
                <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="card border-0 shadow-sm momo-card" data-tag="<?php echo e($product->tag); ?>"
                     style="min-width: 220px; max-width: 220px; border-radius: 20px;">
                    <div class="position-relative">
                        <img src="<?php echo e(asset('storage/' . $product->image)); ?>" class="card-img-top rounded-top" alt="<?php echo e($product->name); ?>">
                        <span class="badge bg-warning text-dark position-absolute top-0 start-0 m-2"><?php echo e($product->tag); ?></span>
                    </div>
                    <div class="card-body p-2">
                        <h6 class="card-title fw-bold mb-1"><?php echo e($product->name); ?></h6>
                        <div class="d-flex align-items-center small text-muted">
                            <i class="bi bi-star-fill text-warning me-1"></i><?php echo e(number_format($product->average_rating, 1)); ?> / 5
                        </div>
                        <button class="btn btn-sm btn-outline-danger w-100 mt-2">Order</button>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>

    <div class="reviews mt-5" id="reviews">
        <div class="review-card" data-aos="fade-right">
            <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="customer">
            <h6>Sita Rai</h6>
            <p>"Best momo in town! Love the spicy sauce!"</p>
            <p>⭐⭐⭐⭐⭐</p>
        </div>
        <div class="review-card" data-aos="fade-up">
            <img src="https://randomuser.me/api/portraits/men/35.jpg" alt="customer">
            <h6>Ram Shrestha</h6>
            <p>"Highly recommended! Customizable momos are a game changer."</p>
            <p>⭐⭐⭐⭐⭐</p>
        </div>
        <div class="review-card" data-aos="fade-left">
            <img src="https://randomuser.me/api/portraits/women/65.jpg" alt="customer">
            <h6>Laxmi Gurung</h6>
            <p>"So fresh and tasty. I order every weekend!"</p>
            <p>⭐⭐⭐⭐⭐</p>
        </div>
    </div>
</section>

<section id="page2">
    <div class="mb-4 text-center" data-aos="fade-up">
        <h4 class="section-title">About Us</h4>
        <p>We started our momo journey in 2024 with one goal: deliver hot, tasty, customizable momos to your door.</p>
        <img src="<?php echo e(asset('storage/products/hotel.jpg')); ?>" class="d-block w-100" alt="Shop Image">
    </div>

    <div class="bottom-nav mt-5">
        <div data-aos="fade-up"><strong>Address:</strong><br>Kathmandu, Nepal</div>
        <div data-aos="fade-up"><strong>How to Order / FAQs:</strong><br>1. Choose your momo<br>2. Customize it<br>3. Checkout & enjoy</div>
        <div data-aos="fade-up"><strong>Contact:</strong><br>Phone: +977-9812345678</div>
    </div>
</section>

<a href="#page1" class="scroll-top">Top</a>
<a href="javascript:history.back()" class="back-btn">Back</a>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init();

    const filterButtons = document.querySelectorAll('.filter-btn');
    const momoCards = document.querySelectorAll('.momo-card');

    filterButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelector('.filter-btn.active')?.classList.remove('active');
            btn.classList.add('active');

            const filter = btn.getAttribute('data-filter');

            momoCards.forEach(card => {
                const tag = card.getAttribute('data-tag');
                if (filter === 'all' || tag === filter) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\evanh\my_momo\resources\views/home.blade.php ENDPATH**/ ?>