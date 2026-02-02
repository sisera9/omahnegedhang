<?php
session_start();
// Koneksi Database untuk ambil data Feedback agar muncul di Marquee
$host = "localhost"; $user = "root"; $pass = "280902"; $db = "dbomah_ne_gedhang";
$conn = new mysqli($host, $user, $pass, $db);

$feedback_list = [];
if ($conn->connect_error === false) {
    $res = $conn->query("SELECT * FROM reviews ORDER BY id DESC LIMIT 10");
    if($res) { while($row = $res->fetch_assoc()) { $feedback_list[] = $row; } }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Omah Ne' Gedhang | Premium Banana Artisan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap');
        :root { --primary: #ff8c00; --dark: #0a0a0a; }
        body { background-color: var(--dark); color: #fff; font-family: 'Plus Jakarta Sans', sans-serif; overflow-x: hidden; scroll-behavior: smooth; }
        .glass { background: rgba(255, 255, 255, 0.03); backdrop-filter: blur(15px); border: 1px solid rgba(255, 255, 255, 0.1); }
        .text-gradient { background: linear-gradient(to right, #ff8c00, #ffba08); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        #navbar.scrolled { background: rgba(10, 10, 10, 0.95); padding: 15px 40px; border-bottom: 1px solid rgba(255, 140, 0, 0.2); transition: 0.4s; }
        .marquee-content { display: inline-flex; animation: marquee 30s linear infinite; gap: 24px; }
        @keyframes marquee { 0% { transform: translateX(0); } 100% { transform: translateX(-50%); } }
        .variant-select { background: #1a1a1a; border: 1px solid #333; color: white; padding: 12px; border-radius: 12px; width: 100%; outline: none; appearance: none; cursor: pointer; }
        /* Animasi Loading Overlay */
        #loader { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.9); z-index: 999; align-items: center; justify-content: center; flex-direction: column; gap: 1rem; }



        /* chat badge removed */

        /* Modal gallery styles */
        .modal-gallery { display:flex; gap:10px; margin-top:10px; }
        .thumb { width:56px; height:56px; border-radius:10px; overflow:hidden; border:2px solid transparent; cursor:pointer; flex:0 0 56px; }
        .thumb img{ width:100%; height:100%; object-fit:cover; display:block; }
        .thumb.active{ border-color: var(--primary); transform: scale(1.03); }
        .nav-arrow{ position:absolute; top:50%; transform:translateY(-50%); background:rgba(0,0,0,0.45); color:#fff; width:44px; height:44px; border-radius:10px; display:flex; align-items:center; justify-content:center; cursor:pointer; border:none; }
        .nav-arrow.left{ left:14px; }
        .nav-arrow.right{ right:14px; }

        /* Secret Admin button (hidden/secret style when owner logged-in) */
        .secret-admin{ position:relative; display:inline-flex; align-items:center; justify-content:center; width:22px; height:22px; border-radius:50%; background:transparent; color:rgba(255,255,255,0.9); border:1px solid rgba(255,255,255,0.03); opacity:0.12; cursor:pointer; transition:transform .12s ease, opacity .18s ease, filter .18s ease; backdrop-filter: blur(2px); }
        .secret-admin:hover{ transform:scale(1.12); opacity:0.95; filter:drop-shadow(0 6px 12px rgba(0,0,0,0.6)); }
        .secret-admin i{ font-size:12px; line-height:1; }
        .secret-admin.footer{ position:absolute; right:18px; bottom:18px; }

        /* About image styling and fallback */
        .about-img{ display:block; width:100%; height:auto; max-height:520px; object-fit:cover; border-radius:18px; box-shadow: 0 20px 40px rgba(0,0,0,0.6); }
        .about-img[hidden]{ display:none; }

        /* Footer styles */
        footer { background: #040404; color: rgba(255,255,255,0.85); padding: 60px 0 36px; border-top: 1px solid rgba(255,255,255,0.03); }
        .footer-grid { max-width: 1200px; margin: 0 auto; display:grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 40px; align-items: start; padding: 0 24px; }
        .footer-logo { font-weight: 900; font-size: 20px; letter-spacing: -0.02em; }
        .footer-logo .accent { color: var(--primary); font-style: italic; }
        .footer-desc { margin-top: 12px; color: rgba(255,255,255,0.45); font-size: 14px; line-height: 1.6; max-width: 320px; }

        .footer-col h4{ color:var(--primary); margin-bottom:12px; font-weight:800; text-transform:uppercase; font-size:12px; letter-spacing:0.08em; }
        .footer-links a { display:block; color: rgba(255,255,255,0.65); text-decoration:none; margin:8px 0; font-size:14px; }
        .footer-links a:hover{ color: var(--primary); }

        .hours .muted{ color: rgba(255,255,255,0.55); font-size:13px; }
        .hours .time{ color:#fff; font-weight:700; margin-top:6px; }
        .hours .closed{ color: var(--primary); font-weight:700; margin-top:6px; }

        .social { display:flex; gap:10px; margin-top:8px; }
        .social a{ width:36px; height:36px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; background: rgba(255,255,255,0.03); color:#fff; text-decoration:none; }

        .footer-address{ color: rgba(255,255,255,0.55); font-size:14px; margin-top:12px; }

        .footer-bottom { text-align:center; margin-top:28px; color: rgba(255,255,255,0.18); font-size:12px; }
        .footer-tagline{ font-style:italic; color: rgba(255,255,255,0.12); display:block; margin-top:6px; }

        /* Toast notifications */
        #toast-container{ position:fixed; right:24px; bottom:24px; display:flex; flex-direction:column; gap:10px; z-index:99999; }
        .toast{ min-width:220px; max-width:380px; background:rgba(10,10,10,0.95); color:#fff; padding:12px 16px; border-radius:12px; box-shadow:0 12px 30px rgba(0,0,0,0.6); display:flex; gap:12px; align-items:center; transform:translateY(10px); opacity:0; transition:all 320ms ease; border-left:4px solid var(--primary); font-weight:600; }
        .toast.show{ transform:translateY(0); opacity:1; }
        .toast.success{ border-left-color:#28a745; }
        .toast.error{ border-left-color:#e02424; }
        .toast .icon{ font-size:18px; opacity:0.95; }

        /* Product card entrance animation */
        .product-card{ opacity:0; transform: translateY(14px) scale(0.995); transition: transform 360ms cubic-bezier(.2,.9,.25,1), opacity 360ms ease; will-change: transform, opacity; }
        .product-card.visible{ opacity:1; transform: translateY(0) scale(1); }

        /* Cart sidebar improvements: flex layout and scrollable body */
        #cart-sidebar{ display:flex; flex-direction:column; }
        #cart-body{ flex:1 1 auto; overflow:auto; -webkit-overflow-scrolling:touch; padding-right:8px; }
        #cart-items{ padding-bottom:12px; }
        /* subtle animation for new cart items */
        #cart-items .glass{ opacity:0; transform: translateX(8px); transition: transform 280ms cubic-bezier(.2,.9,.25,1), opacity 240ms ease; }
        #cart-items .glass.in{ opacity:1; transform: translateX(0); }

        @media (max-width: 900px) {
            .footer-grid { grid-template-columns: 1fr 1fr; gap: 28px; }
        }
        @media (max-width: 480px) {
            .footer-grid { grid-template-columns: 1fr; text-align:center; }
            .footer-desc { margin: 0 auto 12px; }
            .social { justify-content:center; }
        }
    </style>
</head>
<body>

    <div id="loader">
        <div class="w-12 h-12 border-4 border-orange-500 border-t-transparent rounded-full animate-spin"></div>
        <p class="text-orange-500 font-bold tracking-widest text-xs">MEMPROSES PESANAN ANDA...</p>
    </div>

    <nav class="fixed w-full z-[100] py-6 px-10 flex justify-between items-center transition-all duration-500" id="navbar">
        <div class="text-2xl font-extrabold tracking-tighter italic"><span class="text-white">OMAH NE'</span> <span class="text-gradient">GEDHANG</span></div>
        <div class="hidden md:flex space-x-10 font-medium text-xs tracking-widest uppercase">
            <a href="#home" class="hover:text-[#ff8c00] transition">Home</a>
            <a href="#about" class="hover:text-[#ff8c00] transition">Story</a>
            <a href="#products" class="hover:text-[#ff8c00] transition">Shop</a>
            <a href="#reviews" class="hover:text-[#ff8c00] transition">Feedback</a>
            <a href="admin.php" class="hover:text-[#ff8c00] transition">Admin</a>
        </div>
        <button onclick="toggleCart()" class="relative h-12 w-12 glass rounded-full flex items-center justify-center border-[#ff8c00]/30 border">
            <i class="fa-solid fa-basket-shopping text-[#ff8c00]"></i>
            <span id="cart-count" class="absolute -top-1 -right-1 bg-white text-black text-[10px] font-bold h-5 w-5 flex items-center justify-center rounded-full">0</span>
        </button>

    </nav>

    <section id="home" class="relative h-screen flex items-center justify-center overflow-hidden">
        <div class="absolute inset-0 z-0">
            <div class="absolute inset-0 bg-gradient-to-b from-transparent to-black z-10"></div>
            <img src="https://images.unsplash.com/photo-1528823331199-6996480c116a?q=80&w=2070" class="w-full h-full object-cover opacity-50 scale-110">
        </div>
        <div class="relative z-20 text-center px-6" data-aos="zoom-out" data-aos-duration="1500">
            <h5 class="text-[#ff8c00] tracking-[0.5em] font-semibold mb-4 uppercase text-xs">Traditional Recipe • Modern Taste</h5>
            <h1 class="text-6xl md:text-8xl font-extrabold mb-8 leading-tight uppercase">Elevating <br> <span class="text-gradient">Banana Bliss</span></h1>
            <div class="flex flex-col md:flex-row gap-4 justify-center uppercase text-xs font-bold tracking-widest">
                <a href="#products" class="bg-[#ff8c00] text-black px-10 py-4 rounded-full hover:scale-105 transition">Explore Menu</a>
                <a href="#about" class="border border-white/20 hover:bg-white/10 px-10 py-4 rounded-full transition">Our Story</a>
            </div>
        </div>
    </section>

    <section id="about" class="py-32 px-6 container mx-auto">
        <div class="grid md:grid-cols-2 gap-20 items-center">
            <div class="relative" data-aos="fade-right">
                <div class="absolute -inset-4 border border-[#ff8c00]/30 rounded-2xl translate-x-4 translate-y-4"></div>
                <img src="img/profil.jpg" class="about-img grayscale hover:grayscale-0 transition-all duration-1000" alt="Foto Olahan Pisang" onerror="this.onerror=null; this.src='https://via.placeholder.com/900x600?text=No+Image';">
                <!-- If you prefer a local fallback, replace the placeholder URL with e.g. 'img/placeholder.jpg' -->
            </div>
            <div data-aos="fade-left">
                <h2 class="text-4xl font-bold mb-6 italic">Menciptakan Standar Baru dalam <span class="text-[#ff8c00]">Olahan Pisang</span></h2>
                <p class="text-gray-400 text-lg leading-relaxed mb-8">Di Omah Ne' Gedhang, kami tidak hanya menjual camilan. Kami mengkurasi setiap pisang dari penjuak lokal, memastikan tingkat kematangan yang presisi, dan memadukannya dengan bahan yang tentunya fresh.</p>
                <div class="grid grid-cols-2 gap-6">
                    <div class="glass p-6 rounded-2xl border-none"><h4 class="text-[#ff8c00] text-2xl font-bold">100%</h4><p class="text-xs text-gray-500 uppercase tracking-widest">Natural Ingredients</p></div>
                    <div class="glass p-6 rounded-2xl border-none"><h4 class="text-[#ff8c00] text-2xl font-bold">FRESH</h4><p class="text-xs text-gray-500 uppercase tracking-widest">Made by Order</p></div>
                </div>
            </div>
        </div>
    </section>

    <section id="products" class="py-32 bg-[#050505]">
        <div class="container mx-auto px-6 text-center mb-16">
            <h2 class="text-5xl font-extrabold mb-6" data-aos="fade-up">Most <span class="text-gradient">Wanted</span></h2>
            <div class="w-24 h-1 bg-[#ff8c00] mx-auto mb-10"></div>
        </div>
        <div id="product-list" class="container mx-auto px-6 grid grid-cols-1 md:grid-cols-3 gap-12"></div>
    </section>

    <div id="productModal" class="fixed inset-0 z-[150] hidden flex items-center justify-center p-4 bg-black/95">
        <div class="glass w-full max-w-4xl max-h-[90vh] overflow-y-auto rounded-[40px] p-6 md:p-10 relative">
            <button onclick="closeModal()" class="absolute top-6 right-8 text-4xl hover:text-[#ff8c00]">&times;</button>
            <div class="grid md:grid-cols-2 gap-10 mt-6">
                <div class="relative">
                    <img id="modalImg" class="w-full h-80 object-cover rounded-[25px]" alt="Gambar produk utama">
                    <div id="modalGallery" class="modal-gallery mt-4"></div>
                </div>
                <div class="flex flex-col justify-between">
                    <div>
                        <h2 id="modalTitle" class="text-3xl font-bold mb-2"></h2>
                        <p id="modalDesc" class="text-sm text-gray-400 mt-2"></p>
                        <div class="space-y-4">
                            <div><label class="text-[10px] text-gray-500 font-bold uppercase tracking-widest">Pilih Ukuran</label><select id="sizeSelect" class="variant-select" onchange="calculatePrice()"></select></div>
                            <div><label class="text-[10px] text-gray-500 font-bold uppercase tracking-widest">Pilih Rasa</label><select id="flavorSelect" class="variant-select" onchange="calculatePrice()"></select></div>
                            <div><label class="text-[10px] text-gray-500 font-bold uppercase tracking-widest">Extra Topping</label><select id="toppingSelect" class="variant-select" onchange="calculatePrice()"></select></div>
                        </div>
                    </div>
                    <div class="mt-8 flex items-center justify-between border-t border-white/10 pt-6">
                        <div class="flex flex-col"><span id="modalPrice" class="text-3xl font-black text-[#ff8c00]"></span></div>
                        <button onclick="addToCart()" class="bg-[#ff8c00] text-black font-extrabold px-10 py-4 rounded-2xl hover:scale-105 transition">TAMBAH</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <section id="reviews" class="py-32">
        <h2 class="text-center text-3xl font-bold mb-16">Verified <span class="text-gradient">Reviews</span></h2>
        <div class="marquee-container mb-16">
            <div id="marqueeContent" class="marquee-content"></div>
        </div>
        <div class="max-w-xl mx-auto px-6">
            <form id="reviewForm" class="glass p-8 rounded-[30px] space-y-4 border-[#ff8c00]/10 border">
                <input type="text" id="revName" placeholder="Nama Anda" class="w-full bg-white/5 p-4 rounded-xl outline-none focus:border-[#ff8c00] border border-white/5" required>
                <textarea id="revMessage" placeholder="Bagikan kesan Anda..." class="w-full bg-white/5 p-4 rounded-xl h-24 outline-none focus:border-[#ff8c00] border border-white/5" required></textarea>
                <button class="w-full bg-white text-black font-bold py-4 rounded-xl hover:bg-[#ff8c00] transition duration-500">Kirim Review</button>
            </form>
        </div>
    </section>

    <footer style="position:relative;">
        <div class="footer-grid">
            <div>
                <div class="footer-logo">OMAH NE' <span class="accent">GEDHANG</span></div>
                <p class="footer-desc">Menciptakan kebahagiaan melalui olahan pisang terbaik. Bahan lokal, dibuat fresh setiap harinya.</p>
            </div>
            <div>
                <h4>Quick Links</h4>
                <div class="footer-links">
                    <a href="#home">Home</a>
                    <a href="#about">Our Story</a>
                    <a href="#products">Shop</a>
                    <a href="#reviews">Reviews</a>
                </div>
            </div>
            <div class="hours">
                <h4>Hours</h4>
                <div class="muted">Senin - Sabtu:</div>
                <div class="time">06:00 - 20:00</div>
                <div class="muted" style="margin-top:10px;">Minggu:</div>
                <div class="closed">Closed / Pre-Order</div>
            </div>
            <div>
                <h4>Connect</h4>
                <div class="social">
                    <a href="#" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                    <a href="#" aria-label="TikTok"><i class="fa-brands fa-tiktok"></i></a>
                    <a href="#" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="#" aria-label="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
                </div>
                <p class="footer-address">Jl. Priksan GG. Meliwis, <br>Kota Probolinggo</p>
            </div>
        </div>
        <div class="footer-bottom">
            &copy; 2026 Omah Ne' Gedhang. All Rights Reserved.
            <span class="footer-tagline">Crafted for Excellence</span>
        </div>
    </footer>

    <div id="cart-sidebar" class="fixed inset-y-0 right-0 w-full md:w-[450px] bg-black/98 z-[200] translate-x-full transition-transform duration-700 p-8 border-l border-white/10">
        <div class="flex justify-between items-center mb-10"><h3 class="text-2xl font-black italic tracking-tighter">KERANJANG</h3><button onclick="toggleCart()" class="text-3xl">&times;</button></div>
        <div id="cart-items" class="space-y-4 max-h-[40vh] overflow-y-auto pr-2 mb-6"></div>
        <div class="space-y-4 border-t border-white/10 pt-6">
            <input type="text" id="buyerName" placeholder="Nama Anda" class="w-full bg-white/5 border border-white/5 p-4 rounded-xl outline-none focus:border-[#ff8c00]">            <input type="tel" id="buyerPhone" placeholder="Nomor HP (contoh: 08123456789)" class="w-full bg-white/5 border border-white/5 p-4 rounded-xl outline-none focus:border-[#ff8c00]">            <textarea id="buyerAddress" placeholder="Alamat COD" class="w-full bg-white/5 border border-white/5 p-4 rounded-xl h-20 outline-none focus:border-[#ff8c00]"></textarea>
            <div class="flex justify-between text-2xl font-bold"><span>Total:</span><span id="total-price" class="text-[#ff8c00]">Rp 0</span></div>
            <button id="confirmOrderBtn" onclick="checkoutToDB()" class="w-full bg-[#ff8c00] text-black font-bold py-5 rounded-2xl shadow-lg shadow-orange-500/20 uppercase tracking-widest">Konfirmasi Pesanan</button>
        </div>
    </div>

    <div id="toast-container" aria-live="polite" aria-atomic="true"></div>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init();

        // reviews sourced from server-side PHP (falls back to sample entries)
        let reviews = <?php
            if(!empty($feedback_list)){
                echo json_encode(array_map(function($r){ return ['name'=>$r['name'],'msg'=>$r['message']]; }, $feedback_list));
            } else {
                echo json_encode([
                    ['name'=>'Liana','msg'=>'Pisangnya beneran lumer parah!'],
                    ['name'=>'Zahra','msg'=>'Suka banget yang varian Matcha.'],
                    ['name'=>'Bunda','msg'=>'Anak-anak ketagihan pisang kejunya.']
                ]);
            }
        ?>;

        function renderFeedback(){
            const container = document.getElementById('marqueeContent');
            if(!container) return;
            const items = reviews.map(r => `
                <div class="glass p-6 rounded-2xl min-w-[300px]"><p class="text-sm italic">"${r.msg}"</p><p class="text-[10px] font-bold text-[#ff8c00] mt-4 uppercase tracking-widest">— ${r.name}</p></div>
            `).join('');
            container.innerHTML = items + items; // duplicate for smooth marquee
        }

        // initial render of server-provided reviews
        renderFeedback();
        const products = [
            { id: 1, code: 'PN001', name: "Pisang Kriboo", desc: "Pisang dengan baluran kulit pangsit yang renyah dengan berbagai varian rasa yang lumer dan toping melimpah.", images: ["img/pisangkribo.jpeg"], variants: { sizes: { "Paket Small": 5000, "Paket Medium": 7000, "Paket Jumbo": 10000}, flavors: { "Coklat": 0, "Matcha": 0, "Tiramisu": 0}, toppings: { "Polos": 0, "Keju": 2000, "Oreo": 2000 } } },
            { id: 2, code: 'PN002', name: "Pisang Berendam", desc: "Pisang panggang yang lembut dipadukan dengan lumernya vla.", images: ["img/pisangberendam.jpg"], variants: { sizes: { "Porsi Small": 7000, "Porsi Jumbo": 10000 }, flavors: { "Vla Coklat": 0, "Vla Vanila": 0 }, toppings: { "Keju": 0, "Oreo":0 } } },
            { id: 3, code: 'PN003', name: "Puding Pisang", desc: "Puding rasa pisang yang lembut dengan kreamynya vla coklat yang lumer.", images: ["img/puding1.jpeg", "img/puding2.jpeg"], variants: { sizes: { "Porsi Small": 5000 } } }
        ];

        // Helper functions for product codes and cloning (safe to copy product blocks)
        function getProductCode(p){
            if(!p) return 'PN000';
            if(p.code && String(p.code).trim() !== '') return String(p.code);
            return 'PN' + String(p.id).padStart(3,'0');
        }

        function addProduct(product){
            product.id = product.id || (products.length + 1);
            product.code = getProductCode(product);
            product.images = product.images && product.images.length ? product.images : ['https://via.placeholder.com/600x400?text=No+Image'];
            products.push(product);
            renderProducts();
        }

        function cloneProduct(sourceId, overrides = {}){
            const src = products.find(p => p.id === sourceId);
            if(!src) return null;
            const cloned = JSON.parse(JSON.stringify(src));
            cloned.id = overrides.id || (Math.max(...products.map(p=>p.id)) + 1);
            cloned.code = overrides.code || ('PN' + String(cloned.id).padStart(3,'0'));
            Object.assign(cloned, overrides);
            products.push(cloned);
            renderProducts();
            return cloned;
        }

        let cart = [];
        let currentProduct = null;

        // Navbar Effect
        window.addEventListener('scroll', () => {
            const nav = document.getElementById('navbar');
            if(window.scrollY > 50) nav.classList.add('scrolled');
            else nav.classList.remove('scrolled');
        });

        function renderProducts() {
            document.getElementById('product-list').innerHTML = products.map((p, idx) => `
                <div class="glass p-6 product-card rounded-[35px] group cursor-pointer" onclick="openModal(${p.id})" data-idx="${idx}" aria-hidden="false" role="button">
                    <div class="relative h-64 overflow-hidden rounded-[25px] mb-6"><img src="${p.images[0]}" onerror="this.onerror=null;this.src='https://via.placeholder.com/600x400?text=No+Image';" class="w-full h-full object-cover group-hover:scale-110 transition duration-700"></div>
                    <h3 class="text-xl font-bold italic">${p.name}</h3>
                    <p class="text-xs text-gray-400 mt-1">Kode: <span class="font-bold text-sm">${getProductCode(p)}</span></p>
                    <p class="text-[#ff8c00] font-black mt-2 text-lg">Rp ${Math.min(...Object.values(p.variants.sizes)).toLocaleString()}</p>
                </div>
            `).join('');
            // animate product cards in with stagger
            const cards = Array.from(document.querySelectorAll('.product-card'));
            cards.forEach((c,i)=> setTimeout(()=> c.classList.add('visible'), i*80));
        }

        function openModal(id) {
            currentProduct = products.find(p => p.id === id);
            if(!currentProduct){ console.warn('Product not found: ', id); return; }
            document.getElementById('modalTitle').innerText = `${currentProduct.name} (${getProductCode(currentProduct)})`;
            document.getElementById('modalDesc').innerText = currentProduct.desc || ''; 
            // ensure images exist
            if(!Array.isArray(currentProduct.images) || currentProduct.images.length === 0){
                currentProduct.images = ['https://via.placeholder.com/900x600?text=No+Image'];
            }
            // set up gallery
            const gallery = document.getElementById('modalGallery');
            if(gallery){
                gallery.innerHTML = currentProduct.images.map((img, idx) => `
                    <div class="thumb ${idx===0? 'active' : ''}" data-idx="${idx}"><img src="${img}" onerror="this.onerror=null;this.src='https://via.placeholder.com/120x120?text=No+Image'" class="w-full h-full object-cover"></div>
                `).join('');
            }

            // set main image and index state
            modalCurrentIndex = 0;
            setModalImage(modalCurrentIndex);

            // attach click handler for thumbnails (event delegation)
            gallery.onclick = function(e){
                const t = e.target.closest('.thumb');
                if(!t) return;
                const idx = parseInt(t.dataset.idx);
                setModalImage(idx);
            };

            // add navigation arrows (remove any existing first)
            const imgEl = document.getElementById('modalImg');
            const container = imgEl.parentElement;
            // cleanup previous arrows
            container.querySelectorAll('.nav-arrow')?.forEach(n=>n.remove());

            const left = document.createElement('button'); left.className = 'nav-arrow left'; left.innerHTML = '&larr;';
            const right = document.createElement('button'); right.className = 'nav-arrow right'; right.innerHTML = '&rarr;';
            left.addEventListener('click', ()=> setModalImage(Math.max(0, modalCurrentIndex-1)));
            right.addEventListener('click', ()=> setModalImage(Math.min(currentProduct.images.length-1, modalCurrentIndex+1)));
            container.appendChild(left); container.appendChild(right);

            // keyboard support
            document.addEventListener('keydown', modalKeyHandler);

            fillSelect('sizeSelect', currentProduct.variants.sizes);
            fillSelect('flavorSelect', currentProduct.variants.flavors);
            fillSelect('toppingSelect', currentProduct.variants.toppings);
            calculatePrice();
            document.getElementById('productModal').classList.remove('hidden');
        }

        function fillSelect(id, obj) {
            const el = document.getElementById(id);
            if(!el) return;
            obj = obj && typeof obj === 'object' ? obj : {};
            const entries = Object.entries(obj);
            if(entries.length === 0){
                // ensure there's always at least one selectable option so selectedIndex/text access is safe
                el.innerHTML = `<option value="0">Polos</option>`;
                return;
            }
            el.innerHTML = entries.map(([k,v]) => {
                const num = Number(v) || 0;
                return `<option value="${num}">${k} ${num>0? '+' + num.toLocaleString() : ''}</option>`;
            }).join('');
        }

        function calculatePrice() {
            const s = parseInt(document.getElementById('sizeSelect').value) || 0;
            const f = parseInt(document.getElementById('flavorSelect').value) || 0;
            const t = parseInt(document.getElementById('toppingSelect').value) || 0;
            const p = s + f + t;
            document.getElementById('modalPrice').innerText = `Rp ${p.toLocaleString()}`;
        }

        // modal image helpers
        let modalCurrentIndex = 0;
        function setModalImage(idx){
            if(!currentProduct) return;
            modalCurrentIndex = Math.max(0, Math.min(idx, currentProduct.images.length-1));
            const modalImgEl = document.getElementById('modalImg');
            modalImgEl.src = currentProduct.images[modalCurrentIndex];
            modalImgEl.onerror = function(){ this.onerror=null; this.src='https://via.placeholder.com/900x600?text=No+Image'; }
            // update active thumbnail
            const gallery = document.getElementById('modalGallery');
            gallery.querySelectorAll('.thumb').forEach(t=> t.classList.remove('active'));
            const active = gallery.querySelector(`.thumb[data-idx="${modalCurrentIndex}"]`);
            if(active) active.classList.add('active');
            // recalc price just in case
            calculatePrice();
        }

        function modalKeyHandler(e){
            if(document.getElementById('productModal').classList.contains('hidden')) return;
            if(e.key === 'ArrowLeft') setModalImage(modalCurrentIndex - 1);
            if(e.key === 'ArrowRight') setModalImage(modalCurrentIndex + 1);
            if(e.key === 'Escape') closeModal();
        }

        function closeModal() { 
            document.getElementById('productModal').classList.add('hidden');
            // remove keyboard listener
            document.removeEventListener('keydown', modalKeyHandler);
            // remove nav arrows
            const imgEl = document.getElementById('modalImg'); if(imgEl) imgEl.parentElement.querySelectorAll('.nav-arrow')?.forEach(n=>n.remove());
        }

        // Safe helpers for selects (handles empty selects and missing options)
        function getSelectValue(el){
            if(!el) return 0;
            const v = parseInt(el.value);
            return Number.isFinite(v) ? v : 0;
        }
        function getSelectText(el){
            if(!el) return '-';
            if(typeof el.selectedIndex === 'number' && el.selectedIndex >= 0 && el.options[el.selectedIndex]) return el.options[el.selectedIndex].text;
            return el.options && el.options[0] ? el.options[0].text : '-';
        }

        function addToCart() {
            const s = document.getElementById('sizeSelect');
            const f = document.getElementById('flavorSelect');
            const t = document.getElementById('toppingSelect');
            const sizeText = getSelectText(s);
            const flavorText = getSelectText(f);
            const toppingText = getSelectText(t);
            const price = getSelectValue(s) + getSelectValue(f) + getSelectValue(t);
            cart.push({ code: getProductCode(currentProduct), name: currentProduct.name, size: sizeText, flavor: flavorText, topping: toppingText, price: price, img: currentProduct.images[0] });
            updateCart(); closeModal(); toggleCart();
        }

        function updateCart() {
            document.getElementById('cart-count').innerText = cart.length;
            const itemsContainer = document.getElementById('cart-items');
            itemsContainer.innerHTML = cart.map((item, i) => `
                <div class="glass p-4 rounded-2xl flex gap-4 relative">
                    <img src="${item.img}" class="w-16 h-16 object-cover rounded-xl">
                    <div class="flex-1 text-xs"><h4 class="font-bold">${item.name}</h4><p class="text-xs text-gray-400">Kode: <span class="font-bold">${item.code}</span></p><p class="text-gray-400">${item.size}</p><p class="text-[#ff8c00] font-bold">Rp ${item.price.toLocaleString()}</p></div>
                    <button onclick="removeFromCart(${i})" class="absolute top-2 right-4 text-gray-500">&times;</button>
                </div>
            `).join('');
            // animate new items in
            Array.from(itemsContainer.children).forEach((el, idx) => setTimeout(()=> el.classList.add('in'), idx * 80));
            // scroll cart body to bottom for newly added items
            const body = document.getElementById('cart-body'); if(body){ body.scrollTop = body.scrollHeight; }
            const total = cart.reduce((s, i) => s + i.price, 0);
            document.getElementById('total-price').innerText = `Rp ${total.toLocaleString()}`;
        }

        function removeFromCart(i) { cart.splice(i,1); updateCart(); }
        function toggleCart() { document.getElementById('cart-sidebar').classList.toggle('translate-x-full'); }

        // Safe fetch with timeout
        function fetchWithTimeout(url, options = {}, timeout = 12000){
            return Promise.race([
                fetch(url, options),
                new Promise((_, reject) => setTimeout(() => reject(new Error('timeout')), timeout))
            ]);
        }

        // small toast helper
        function showToast(message, type = 'success', timeout = 3500){
            const container = document.getElementById('toast-container');
            if(!container){ alert(message); return; }
            const el = document.createElement('div');
            el.className = `toast ${type}`;
            el.innerHTML = `<div class="icon">${type==='success' ? '&#10004;' : '&#9888;'}</div><div class="msg">${message}</div>`;
            container.appendChild(el);
            // show
            requestAnimationFrame(()=> el.classList.add('show'));
            // remove after timeout
            setTimeout(()=>{ el.classList.remove('show'); setTimeout(()=> el.remove(), 360); }, timeout);
        }

        // Checkout Function (MySQL)
        function checkoutToDB() {
            const name = document.getElementById('buyerName').value;
            const addr = document.getElementById('buyerAddress').value;
            const btn = document.getElementById('confirmOrderBtn');
            if(!name || !addr || cart.length === 0) return showToast("Mohon lengkapi data!", 'error');

            // disable button and show loader
            if(btn){ btn.disabled = true; btn.classList.add('opacity-60','cursor-not-allowed'); }
            document.getElementById('loader').style.display = 'flex';

            let detail = cart.map(i => `${i.code} - ${i.name} (${i.size}, ${i.flavor}, ${i.topping})`).join(', ');
            let total = document.getElementById('total-price').innerText.replace(/[^0-9]/g, '');

            let hp = document.getElementById('buyerPhone').value.trim();
            if(!hp) return showToast('Nomor HP harus diisi', 'error');
            let formData = new FormData();
            formData.append('nama', name);
            formData.append('hp', hp);
            formData.append('alamat', addr);
            formData.append('detail', detail);
            formData.append('total', total);

            fetchWithTimeout('proses.php', { method: 'POST', body: formData }, 12000)
            .then(res => {
                // try to parse JSON safely
                return res.json ? res.json() : Promise.reject(new Error('Invalid server response'));
            })
            .then(data => {
                document.getElementById('loader').style.display = 'none';
                if(btn){ btn.disabled = false; btn.classList.remove('opacity-60','cursor-not-allowed'); }
                if(data && data.status === 'success') { showToast('✅ Pesanan berhasil disimpan', 'success'); cart = []; updateCart(); toggleCart(); }
                else { const msg = data && data.message ? data.message : 'Terjadi kesalahan saat menyimpan pesanan. Silakan coba lagi.'; console.warn('Checkout response error:', data); showToast(msg, 'error'); }
            })
            .catch(err => {
                console.warn('Checkout failed', err);
                document.getElementById('loader').style.display = 'none';
                if(btn){ btn.disabled = false; btn.classList.remove('opacity-60','cursor-not-allowed'); }
                if(err && err.message === 'timeout') showToast('Permintaan memakan waktu terlalu lama. Periksa koneksi Anda atau coba lagi.', 'error');
                else showToast('Terjadi kesalahan jaringan. Silakan coba lagi.', 'error');
            });
        }

        // Feedback Function (MySQL)
        document.getElementById('reviewForm').onsubmit = function(e) {
            e.preventDefault();
            const name = document.getElementById('revName').value.trim();
            const message = document.getElementById('revMessage').value.trim();
            if(!name || !message) return alert('Nama dan pesan harus diisi.');

            // optimistic preview
            reviews.unshift({name, msg: message}); renderFeedback(); document.getElementById('reviewForm').reset();

            // send to server for storage/verification
            let formData = new FormData();
            formData.append('type', 'feedback');
            formData.append('name', name);
            formData.append('message', message);

            fetch('proses.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') { console.log('Feedback saved'); showToast('Terima kasih! Feedback Anda telah terkirim', 'success'); }
                else { showToast('Gagal menyimpan feedback', 'error'); }
            }).catch(()=>{ console.warn('Failed to send feedback to server'); showToast('Gagal mengirim feedback', 'error'); });
        };

        // Live chat feature removed per request — frontend chat/SSE/badge code cleaned up.


        try{ renderProducts(); } catch(e){ console.error('renderProducts failed', e); }
    </script>
</body>
</html>