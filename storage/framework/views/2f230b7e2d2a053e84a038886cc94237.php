<?php $__env->startSection('styles'); ?>
<style>
    body {
        
        background-color: #fff8f0;
        font-family: 'Nunito Sans', sans-serif;
        background-color: #fff7ec;
        color: #2f1b12;
    }
    .top-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 10px;
        background-color: #fff7ec;
        border-bottom: 2px solid #f4c542;
    }
    @import url('https://fonts.googleapis.com/css2?family=Tiro+Devanagari+Hindi&display=swap');
    @media (max-width: 480px) {
        .devanagari-logo {
            font-size: 1.2rem;
        }   
    }

    .devanagari-logo {
        font-family: 'Tiro Devanagari Hindi', serif;
        font-size: 1rem; /* 🔒 keeps navbar height stable */
        font-weight: 700;
        color: #a83232;
        text-shadow: 0 0 4px #f4c542, 0 0 6px #f4c542;
        white-space: nowrap;
        line-height: 40px; /* ⬅️ aligns vertically */
        height: 40px; /* same as icon buttons */
        display: flex;
        align-items: center;
    }

    
    .icon-btn {
        width: 40px;
        height: 40px;
        background-color: #f4c542;
        color: #2f1b12;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        font-size: 18px;
    }
    .brand-title {
        display: block;
        width: 100%;
        text-align: center;
        font-weight: 800;
        font-size: 1.2rem;
        color: #a83232;
        text-shadow: 0 0 8px #f4c542, 0 0 12px #f4c542;
        margin: 30px auto 20px;
        letter-spacing: 1px;
        animation: glowPulse 2.5s ease-in-out infinite;
    }
    @keyframes glowPulse {
        0% {
            text-shadow: 0 0 5px #f4c542, 0 0 10px #f4c542;
        }
        50% {
            text-shadow: 0 0 15px #f4c542, 0 0 20px #f4c542;
        }
        100% {
            text-shadow: 0 0 5px #f4c542, 0 0 10px #f4c542;
        }
    }
    .section-title {
        display: block;
        width: 100%;
        text-align: center;
        font-weight: 800;
        font-size: 2.2rem;
        color: #a83232;
        text-shadow: 0 0 8px #f4c542, 0 0 12px #f4c542;
        margin: 30px auto 20px;
        letter-spacing: 1px;
        animation: glowPulse 2.5s ease-in-out infinite;
    }
        
    
    .product-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
        padding: 15px;
    }
    .product-card {
        background: white;
        border-radius: 12px;
        border: 1px solid #f4c542;
        box-shadow: 0 4px 12px rgba(168, 50, 50, 0.1);
        padding: 15px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    .product-card img {
        width: 100%;
        height: 180px;
        object-fit: cover;
        border-radius: 10px;
    }
    .product-card h6 {
        font-size: 0.9rem;
        margin: 10px 0 5px;
    }
    .product-card .price {
        font-weight: bold;
        color: #a83232;
    }
    .product-card .add-to-cart {
        font-size: 0.75rem;
        background-color: #4caf50;
        font-weight: 600;
        color: white;
        padding: 5px 10px;
        border: none;
        border-radius: 30px;
        transition: 0.3s;
        transition: background-color 0.3s;
    }
    .hover-description {
        display: none;
        background-color: #3e8e41;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(0, 0, 0, 0.75);
        color: white;
        padding: 10px;
    }
    @media (min-width: 768px) {
        .product-grid {
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 30px;
            padding: 30px;
        }
        .product-card {
            padding: 20px;
        }
        .product-card img {
            height: 250px;
        }
        .hover-description {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.75);
            color: white;
            padding: 10px;
            opacity: 0;
            transform: translateY(100%);
            transition: all 0.3s ease-in-out;
            font-size: 0.85rem;
            display: block;
        }
        .product-card:hover .hover-description {
            opacity: 1;
            transform: translateY(0);
        }
    }
    .bottom-nav {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: white;
        border-top: 1px solid #ddd;
        display: flex;
        justify-content: space-around;
        padding: 10px 0;
    }
    .bottom-nav .nav-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        font-size: 0.75rem;
        color: #555;
    }
    .full-banner-wrapper {
        width: 100vw;
        position: relative;
        left: 50%;
        right: 50%;
        margin-left: -50vw;
        margin-right: -50vw;
        overflow: hidden;
    }
    .banner-img {
        width: 100%;
        height: 100%;
        max-height: 400px;
        object-fit: cover;
        display: block;
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="top-bar">
    <div class="icon-btn"><i class="fas fa-user"></i></div>

    <div class="brand-title devanagari-logo">आमाको म:म:</div>


    <div class="icon-btn"><i class="fas fa-search"></i></div>
</div>


<div class="full-banner-wrapper">
    <div id="bannerCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="2000">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="<?php echo e(asset('storage/banners/banner1.jpg')); ?>" class="banner-img" alt="Banner 1">
            </div>
            <div class="carousel-item">
                <img src="<?php echo e(asset('storage/banners/banner2.jpg')); ?>" class="banner-img" alt="Banner 2">
            </div>
            <div class="carousel-item">
                <img src="<?php echo e(asset('storage/banners/banner3.jpg')); ?>" class="banner-img" alt="Banner 3">
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#bannerCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#bannerCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>
    </div>
</div>

<h2 class="section-title">Featured</h2>
<div class="product-grid">
    <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="product-card position-relative">
            <img src="<?php echo e(asset('storage/' . $product->image)); ?>" alt="<?php echo e($product->name); ?>">
            <h6 class="fw-bold mt-2"><?php echo e($product->name); ?></h6>
            <div class="price mb-2">rs <?php echo e(number_format($product->price, 2)); ?>~</div>
            <form action="<?php echo e(route('checkout.buyNow', ['product' => $product->id])); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="quantity" value="1">
                <button type="submit" class="add-to-cart btn btn-sm">ADD TO CART</button>
            </form>
            <div class="hover-description">
                <?php echo e($product->description); ?>

            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<div class="bottom-nav">
    <div class="nav-item"><i class="fas fa-home"></i><span>Home</span></div>
    <div class="nav-item"><i class="fas fa-gift"></i><span>Offers</span></div>
    <div class="nav-item"><i class="fas fa-utensils"></i><span>Menu</span></div>
    <div class="nav-item"><i class="fas fa-shopping-cart"></i><span>Cart</span></div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('desktop.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/desktop/home.blade.php ENDPATH**/ ?>