/* =========================================================
   Dapur Catering - Halaman Publik (versi statis)
   ========================================================= */
(function () {
  'use strict';

  var DC = window.DC;
  var D = DC.data;
  var e = DC.e;
  var rupiah = DC.rupiah;

  function fieldError(errors, key) {
    return errors && errors[key] ? '<div class="error-text">' + e(errors[key]) + '</div>' : '';
  }

  function categorySlugOf(product) {
    var category = DC.categoryById(product.category_id);
    return category ? category.slug : '';
  }

  /* ---------------- Beranda ---------------- */
  function home() {
    var categories = DC.getCategories();
    var products = DC.getProducts();
    var featured = products.filter(function (p) { return p.is_featured && DC.isAvailable(p); }).slice(0, 8);

    var categoryCards = categories.map(function (category) {
      var count = products.filter(function (p) {
        return p.category_id === category.id && DC.isAvailable(p);
      }).length;
      return '<a class="category-card reveal" href="#/menu?category=' + encodeURIComponent(category.slug) + '">' +
        '<img src="' + e(category.image) + '" alt="' + e(category.name) + '" loading="lazy">' +
        '<div class="category-body"><h3>' + e(category.name) + '</h3>' +
          '<p>' + e(category.description) + '</p>' +
          '<span class="text-link">' + count + ' pilihan menu &rarr;</span>' +
        '</div></a>';
    }).join('');

    var productCards = featured.map(DC.productCard).join('');

    var features = [
      ['Bahan Segar &amp; Halal', 'Kami menggunakan bahan pilihan yang segar dan halal untuk setiap hidangan.', '<path d="M12 3l7 3v6c0 4.4-3 7.4-7 9-4-1.6-7-4.6-7-9V6l7-3Z"/><path d="m9 12 2 2 4-4"/>'],
      ['Tepat Waktu', 'Pesanan diantar sesuai jadwal yang Anda tentukan, tanpa membuat tamu menunggu.', '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>'],
      ['Harga Terjangkau', 'Paket fleksibel mulai dari nasi kotak ekonomis hingga tumpeng premium.', '<path d="M4 20V10l8-6 8 6v10"/><path d="M9 20v-6h6v6"/>'],
      ['Pelayanan Ramah', 'Tim kami siap membantu konsultasi menu dan kebutuhan acara Anda.', '<path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v8Z"/>']
    ].map(function (f) {
      return '<div class="feature-card reveal"><div class="feature-icon">' +
        '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' + f[2] + '</svg>' +
        '</div><h3>' + f[0] + '</h3><p>' + f[1] + '</p></div>';
    }).join('');

    var adPhotos = [
      { src: 'assets/images/27k.jpg', alt: 'Nasi kotak dengan ayam bakar, telur, dan tumis mie' },
      { src: 'assets/images/13k.jpg', alt: 'Nasi kotak kuning dengan ayam bakar dan mie goreng' },
      { src: 'assets/images/geprek-10k.jpg', alt: 'Ayam geprek crispy dalam kotak makan' },
      { src: 'assets/images/20k.jpg', alt: 'Puluhan nasi kotak siap dikirim untuk acara' }
    ];

    var adSlides = adPhotos.map(function (photo, index) {
      return '<img class="ig-slide' + (index === 0 ? ' is-active' : '') + '" src="' + e(photo.src) + '" alt="' + e(photo.alt) + '"' +
        (index === 0 ? '' : ' loading="lazy"') + ' data-ad-slide="' + index + '">';
    }).join('');

    var adDots = adPhotos.map(function (photo, index) {
      return '<button type="button" class="ig-dot' + (index === 0 ? ' is-active' : '') + '" data-ad-dot="' + index + '" aria-label="Foto ' + (index + 1) + '"' +
        (index === 0 ? ' aria-current="true"' : '') + '></button>';
    }).join('');

    var promoCards = [
      { image: 'assets/images/13k.jpg', tag: 'Paling dicari', title: 'Nasi Kotak', text: 'Praktis untuk rapat, seminar, dan syukuran.', price: 'Rp13.000', link: '#/menu?category=nasi-kotak' },
      { image: 'assets/images/geprek-10k.jpg', tag: 'Hemat', title: 'Ayam Geprek', text: 'Sambal khas dengan level pedas pilihan.', price: 'Rp10.000', link: '#/menu?category=ayam-geprek' },
      { image: 'assets/images/bg4.jpg', tag: 'Acara spesial', title: 'Tumpeng', text: 'Tampilan megah untuk momen berharga.', price: 'Rp85.000', link: '#/menu?category=tumpeng' }
    ].map(function (promo) {
      return '<a class="promo-card reveal" href="' + promo.link + '">' +
        '<span class="promo-bg" style="background-image:url(' + e(promo.image) + ')" aria-hidden="true"></span>' +
        '<span class="promo-tag">' + promo.tag + '</span>' +
        '<div class="promo-body"><h3>' + promo.title + '</h3><p>' + promo.text + '</p>' +
          '<span class="promo-price">Mulai <strong>' + promo.price + '</strong><span class="promo-arrow" aria-hidden="true">&rarr;</span></span>' +
        '</div></a>';
    }).join('');

    var tickerItems = ['Promo minggu ini', 'Nasi kotak mulai Rp13.000', 'Ayam geprek mulai Rp10.000', 'Tumpeng mulai Rp85.000', 'Gratis konsultasi menu', 'Antar tepat waktu'];
    var tickerSet = tickerItems.map(function (item) {
      return '<span>' + item + '</span><i aria-hidden="true"></i>';
    }).join('');

    var commentHtml = [
      { initials: 'RN', name: 'rina.nurhaliza', text: 'Nasi kotaknya enak dan porsinya pas. Acara rapat kantor kami berjalan lancar karena pengirimannya tepat waktu.', meta: 'Rapat kantor &middot; 2 hari lalu' },
      { initials: 'BS', name: 'budi.santoso', text: 'Tumpengnya cantik dan rasanya luar biasa. Keluarga besar puas, dekorasinya juga rapi. Pasti pesan lagi.', meta: 'Syukuran keluarga &middot; 1 minggu lalu' },
      { initials: 'DA', name: 'dewi.anggraini', text: 'Ayam gepreknya pedasnya pas dan masih hangat saat sampai. Paket komplitnya worth it banget untuk makan siang.', meta: 'Pelanggan setia &middot; 1 minggu lalu' }
    ].map(function (comment) {
      return '<div class="ig-comment">' +
        '<span class="ig-comment-avatar">' + comment.initials + '</span>' +
        '<div class="ig-comment-body"><p><strong>' + comment.name + '</strong> ' + comment.text + '</p>' +
          '<span class="ig-comment-meta">' + comment.meta + ' &middot; Suka &middot; Balas</span></div>' +
      '</div>';
    }).join('');

    var hero = '<section class="ig-hero"><div class="container"><div class="ig-hero-grid">' +
      '<div class="ig-hero-copy">' +
        '<span class="ig-sponsored"><span></span>Iklan &middot; Sponsored</span>' +
        '<h1>Nasi kotak &amp; tumpeng <em>mulai Rp13.000</em></h1>' +
        '<p>Untuk rapat kantor, syukuran, sampai acara keluarga. Dimasak segar setiap hari, dikemas rapi, dan diantar tepat waktu ke lokasi Anda.</p>' +
        '<div class="ig-price-row">' +
          '<div class="ig-price-card"><span>Nasi kotak</span><strong>Rp13.000</strong></div>' +
          '<div class="ig-price-card"><span>Ayam geprek</span><strong>Rp10.000</strong></div>' +
          '<div class="ig-price-card"><span>Tumpeng</span><strong>Rp85.000</strong></div>' +
        '</div>' +
        '<div class="hero-actions">' +
          '<a href="#/menu" class="btn btn-accent">Pesan Sekarang</a>' +
          '<a href="#/?go=kategori" class="btn btn-outline">Lihat Kategori</a>' +
        '</div>' +
        '<div class="ig-proof">' +
          '<div class="ig-avatars" aria-hidden="true"><span>RN</span><span>BS</span><span>DA</span><span>+</span></div>' +
          '<div class="ig-proof-text"><strong>4.9/5 &#9733;</strong><span>dari 500+ pesanan pelanggan</span></div>' +
        '</div>' +
      '</div>' +
      '<div class="ig-hero-post">' +
        '<div class="ig-post">' +
          '<div class="ig-post-head">' +
            '<span class="ig-avatar">DC</span>' +
            '<div class="ig-post-user"><strong>dapurcatering</strong><span>Sponsored &middot; Catering</span></div>' +
            '<span class="ig-post-more" aria-hidden="true">&bull;&bull;&bull;</span>' +
          '</div>' +
          '<div class="ig-carousel" data-ad-carousel data-ad-index="0">' +
            '<div class="ig-slides">' + adSlides + '</div>' +
            '<span class="ig-counter"><span data-ad-current>1</span>/' + adPhotos.length + '</span>' +
            '<button type="button" class="ig-nav ig-nav--prev" data-ad-prev aria-label="Foto sebelumnya">' +
              '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg></button>' +
            '<button type="button" class="ig-nav ig-nav--next" data-ad-next aria-label="Foto berikutnya">' +
              '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg></button>' +
            '<div class="ig-dots">' + adDots + '</div>' +
          '</div>' +
          '<div class="ig-actions">' +
            '<span class="ig-action ig-action--like"><svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 21s-7.5-4.6-9.5-8.4A5.4 5.4 0 0 1 12 6.3a5.4 5.4 0 0 1 9.5 6.3C19.5 16.4 12 21 12 21Z"/></svg><span class="ig-likes-inline">1.248</span></span>' +
            '<span class="ig-action"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"><path d="M21 11.5a8.4 8.4 0 0 1-9 8.4 9.5 9.5 0 0 1-3.8-.8L3 21l1.9-5.1A8.4 8.4 0 1 1 21 11.5Z"/></svg></span>' +
            '<span class="ig-action"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg></span>' +
            '<span class="ig-action ig-action--save"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2Z"/></svg></span>' +
          '</div>' +
          '<div class="ig-likes">1.248 suka</div>' +
          '<p class="ig-caption"><strong>dapurcatering</strong> Nasi kotak mulai Rp13.000, ayam geprek mulai Rp10.000. Bisa request menu dan jumlah sesuai acara. <span class="ig-tag">#dapurcatering</span> <span class="ig-tag">#nasikotak</span> <span class="ig-tag">#tumpeng</span></p>' +
          '<p class="ig-comments-link">Lihat semua 87 komentar</p>' +
          '<a class="ig-cta" href="#/menu"><span>Pesan Sekarang</span><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg></a>' +
        '</div>' +
      '</div>' +
    '</div></div></section>';

    var ticker = '<div class="ads-ticker" aria-hidden="true"><div class="ads-ticker-track">' +
      '<div class="ads-ticker-set">' + tickerSet + '</div>' +
      '<div class="ads-ticker-set">' + tickerSet + '</div>' +
    '</div></div>';

    var promoSection = '<section class="promo-section"><div class="container">' +
      '<div class="section-head reveal"><span class="eyebrow">Promo minggu ini</span>' +
      '<h2>Pilih paket, langsung pesan</h2>' +
      '<p>Harga mulai dari, bisa disesuaikan dengan jumlah tamu dan kebutuhan acara Anda.</p></div>' +
      '<div class="promo-grid">' + promoCards + '</div>' +
    '</div></section>';

    var html = hero + ticker + promoSection +

    '<section class="section section--cream" id="kategori"><div class="container">' +
      '<div class="section-head reveal"><span class="eyebrow">Pilihan Kategori</span>' +
      '<h2>Sajian untuk Berbagai Momen</h2>' +
      '<p>Pilih kategori yang sesuai kebutuhan acara Anda. Semua menu dapat disesuaikan dengan jumlah tamu dan selera.</p></div>' +
      '<div class="category-grid">' + categoryCards + '</div>' +
    '</div></section>' +

    '<section class="section section--white" id="menu-terlaris"><div class="container">' +
      '<div class="section-head reveal"><span class="eyebrow">Menu Terlaris</span>' +
      '<h2>Paling Sering Dipesan</h2>' +
      '<p>Menu favorit pelanggan Dapur Catering yang siap memanjakan lidah para tamu Anda.</p></div>' +
      '<div class="product-grid">' + (productCards || '<p class="muted">Belum ada menu tersedia.</p>') + '</div>' +
      '<div class="text-center mt-4"><a href="#/menu" class="btn btn-primary">Lihat Semua Menu</a></div>' +
    '</div></section>' +

    '<section class="section section--cream" id="tentang"><div class="container"><div class="split">' +
      '<div class="split-media reveal"><img src="assets/images/bg7.jpg" alt="Dapur Catering menyiapkan hidangan">' +
        '<div class="split-badge"><strong>Sejak 2018</strong><span>Melayani dengan sepenuh hati</span></div></div>' +
      '<div class="reveal"><span class="eyebrow">Tentang Kami</span>' +
        '<h2>Dapur Rumahan dengan Standar Profesional</h2>' +
        '<p>Dapur Catering berawal dari dapur kecil keluarga yang senang berbagi masakan rumahan. Kini kami melayani ratusan pesanan setiap bulan dengan dapur yang bersih, bahan segar, dan tim yang berpengalaman.</p>' +
        '<ul class="check-list">' +
          '<li>Bahan segar dipilih setiap hari, tanpa pengawet.</li>' +
          '<li>Dikemas higienis dan rapi, siap disajikan.</li>' +
          '<li>Pengiriman tepat waktu sesuai jadwal acara Anda.</li>' +
          '<li>Harga transparan dan bisa disesuaikan dengan anggaran.</li>' +
        '</ul><a href="#/menu" class="btn btn-primary">Jelajahi Menu Kami</a>' +
      '</div>' +
    '</div></div></section>' +

    '<section class="section section--white" id="keunggulan"><div class="container">' +
      '<div class="section-head reveal"><span class="eyebrow">Mengapa Memilih Kami</span>' +
      '<h2>Keunggulan Layanan Dapur Catering</h2>' +
      '<p>Kami tidak hanya menyajikan makanan, tetapi juga ketenangan pikiran untuk acara Anda.</p></div>' +
      '<div class="feature-grid">' + features + '</div>' +
    '</div></section>' +

    '<section class="section section--cream" id="testimoni"><div class="container">' +
      '<div class="section-head reveal"><span class="eyebrow">Kata Pelanggan</span>' +
      '<h2>Kolom komentar yang bikin lapar</h2>' +
      '<p>Cerita singkat dari pelanggan yang sudah memesan untuk acara mereka.</p></div>' +
      '<div class="ig-comment-card reveal">' +
        '<div class="ig-comment-head">' +
          '<span class="ig-avatar lg">DC</span>' +
          '<div><strong>dapurcatering</strong><span>1.248 suka &middot; 87 komentar</span></div>' +
          '<a class="ig-comment-more" href="#/menu">Pesan menu ini</a>' +
        '</div>' +
        commentHtml +
      '</div>' +
    '</div></section>' +

    '<section class="section section--white"><div class="container"><div class="cta-band reveal">' +
      '<div><h2>Siap memesan untuk acara Anda?</h2>' +
      '<p>Konsultasikan kebutuhan catering Anda sekarang, kami bantu pilih paket terbaik.</p></div>' +
      '<div style="display:flex;gap:12px;flex-wrap:wrap">' +
        '<a href="#/menu" class="btn btn-light">Pesan Sekarang</a>' +
        '<a href="https://wa.me/' + D.whatsapp + '" target="_blank" rel="noopener" class="btn btn-outline" style="color:#fff">Chat WhatsApp</a>' +
      '</div>' +
    '</div></div></section>' +
    '<div class="ig-sticky">' +
      '<div class="ig-sticky-info"><strong>Mulai Rp10.000</strong><span>Nasi kotak &middot; geprek &middot; tumpeng</span></div>' +
      '<a href="#/menu" class="btn btn-accent btn-sm">Pesan Sekarang</a>' +
    '</div>';

    return { title: 'Nasi Kotak & Tumpeng Mulai Rp13.000', html: html };
  }

  /* ---------------- Menu ---------------- */
  function menu(route) {
    var categorySlug = route.params.get('category') || '';
    var activeCategory = DC.categoryBySlug(categorySlug);
    if (!activeCategory) categorySlug = '';
    var search = (route.params.get('q') || '').trim();
    var needle = search.toLowerCase();
    var products = DC.getProducts().filter(function (p) {
      var matchCategory = !categorySlug || categorySlugOf(p) === categorySlug;
      var matchSearch = !needle || (p.name + ' ' + p.description).toLowerCase().indexOf(needle) > -1;
      return DC.isAvailable(p) && matchCategory && matchSearch;
    });

    var chips = '<a class="chip ' + (categorySlug === '' ? 'active' : '') + '" href="#/menu">Semua</a>' +
      DC.getCategories().map(function (category) {
        return '<a class="chip ' + (categorySlug === category.slug ? 'active' : '') + '" href="#/menu?category=' + encodeURIComponent(category.slug) + '">' + e(category.name) + '</a>';
      }).join('');

    var searchForm = '<form class="search-form" data-menu-search>' +
      (categorySlug ? '<input type="hidden" name="category" value="' + e(categorySlug) + '">' : '') +
      '<input type="search" name="q" value="' + e(search) + '" placeholder="Cari menu..." aria-label="Cari menu">' +
      '<button class="btn btn-primary btn-sm" type="submit">Cari</button></form>';

    var resultNote = search
      ? '<p class="muted" style="margin-top:-12px;margin-bottom:24px">Hasil pencarian untuk <strong>' + e(search) + '</strong> &middot; ' + products.length + ' menu ditemukan. <a href="#/menu' + (categorySlug ? '?category=' + encodeURIComponent(categorySlug) : '') + '">Hapus pencarian</a></p>'
      : '';

    var body = products.length
      ? '<div class="product-grid">' + products.map(DC.productCard).join('') + '</div>'
      : DC.emptyState('search', 'Menu tidak ditemukan', 'Coba kata kunci lain atau lihat semua menu yang tersedia.',
          '<a href="#/menu" class="btn btn-primary mt-2">Lihat Semua Menu</a>');

    var title = activeCategory ? activeCategory.name : 'Menu Catering';
    var description = activeCategory ? activeCategory.description : 'Pilih menu favorit Anda, tentukan paket, jumlah, dan tambahan sesuai kebutuhan acara.';

    return {
      title: title,
      html: DC.pageHero('<a href="#/">Beranda</a> / Menu Catering', title, description) +
        '<section class="section section--cream"><div class="container">' +
          '<div class="toolbar"><div class="filter-chips">' + chips + '</div>' + searchForm + '</div>' +
          resultNote + body +
        '</div></section>'
    };
  }

  /* ---------------- Detail produk ---------------- */
  function product(slug) {
    var p = DC.productBySlug(slug);
    if (!p) {
      return {
        title: 'Menu Tidak Ditemukan',
        html: '<section class="section section--cream"><div class="container">' +
          DC.emptyState('search', 'Menu tidak ditemukan', 'Menu yang Anda cari mungkin sudah tidak tersedia.',
            '<a href="#/menu" class="btn btn-primary mt-2">Kembali ke Menu</a>') +
        '</div></section>'
      };
    }

    var category = DC.categoryById(p.category_id);
    var variants = p.variants || [];
    var addons = p.addons || [];
    var basePrice = variants.length ? Number(variants[0].price) : Number(p.base_price);
    var available = DC.isAvailable(p);

    var variantHtml = variants.length
      ? '<div class="option-group"><label class="option-label">Pilih Paket / Ukuran</label><div class="choice-grid">' +
        variants.map(function (variant, index) {
          return '<label class="choice"><input type="radio" name="variant" value="' + index + '" data-price="' + variant.price + '"' + (index === 0 ? ' checked' : '') + '>' +
            '<span>' + e(variant.name) + '<small>' + e(rupiah(variant.price)) + '</small></span></label>';
        }).join('') + '</div></div>'
      : '';

    var addonHtml = addons.length
      ? '<div class="option-group"><label class="option-label">Tambahan (opsional)</label><div class="choice-grid">' +
        addons.map(function (addon, index) {
          return '<label class="choice"><input type="checkbox" name="addons" value="' + index + '" data-price="' + addon.price + '">' +
            '<span>' + e(addon.name) + '<small>+' + e(rupiah(addon.price)) + '</small></span></label>';
        }).join('') + '</div></div>'
      : '';

    var formHtml = available
      ? '<form data-product-form data-pid="' + p.id + '" data-base-price="' + basePrice + '">' +
          variantHtml + addonHtml +
          '<div class="option-group"><label class="option-label" for="product-qty">Jumlah Pesanan</label>' +
            '<div class="qty-stepper">' +
              '<button type="button" data-action="product-step" data-step="-1" aria-label="Kurangi">&minus;</button>' +
              '<input id="product-qty" type="number" name="quantity" value="1" min="1" data-product-qty>' +
              '<button type="button" data-action="product-step" data-step="1" aria-label="Tambah">+</button>' +
            '</div></div>' +
          '<div class="option-group"><label class="option-label" for="product-note">Catatan (opsional)</label>' +
            '<textarea id="product-note" class="detail-note" name="note" rows="2" placeholder="Contoh: sambal dipisah, tanpa mie, dll."></textarea></div>' +
          '<div class="detail-total"><span>Total estimasi</span><strong data-product-total>' + e(rupiah(basePrice)) + '</strong></div>' +
          '<div class="detail-actions">' +
            '<button type="submit" class="btn btn-ghost" data-buy="0">' + DC.svg('cart', 18) + ' Tambah ke Keranjang</button>' +
            '<button type="submit" class="btn btn-accent" data-buy="1">Pesan Sekarang</button>' +
          '</div>' +
          '<ul class="meta-list">' +
            '<li><span>Kategori</span><strong>' + e(category ? category.name : '-') + '</strong></li>' +
            '<li><span>Ketersediaan</span><strong>Stok tersedia (' + Number(p.stock) + ')</strong></li>' +
            '<li><span>Pemesanan</span><strong>Minimal 1 hari sebelum acara</strong></li>' +
          '</ul>' +
        '</form>'
      : '<div class="alert alert-warning" style="box-shadow:none">Menu ini sedang tidak tersedia. Silakan pilih menu lain.</div>';

    var html = DC.pageHero(
      '<a href="#/">Beranda</a> / <a href="#/menu?category=' + encodeURIComponent(category ? category.slug : '') + '">' + e(category ? category.name : 'Menu') + '</a> / ' + e(p.name),
      p.name, 'Atur paket, jumlah, dan tambahan sesuai kebutuhan pesanan Anda.'
    ) +
    '<section class="section section--cream"><div class="container"><div class="product-detail">' +
      '<div class="detail-media reveal"><img src="' + e(p.image) + '" alt="' + e(p.name) + '"></div>' +
      '<div class="reveal">' +
        '<span class="detail-cat">' + e(category ? category.name : '') + '</span>' +
        '<h1 class="detail-title">' + e(p.name) + '</h1>' +
        '<div class="detail-price">' + e(DC.priceLabel(p)) + ' <small>' + (variants.length ? 'sesuai paket' : 'per porsi') + '</small></div>' +
        '<p class="detail-desc">' + e(p.description) + '</p>' +
        formHtml +
      '</div>' +
    '</div></div></section>';

    return { title: p.name, html: html };
  }

  /* ---------------- Keranjang ---------------- */
  function cart() {
    var items = DC.getCart();
    var title = 'Keranjang Belanja';
    var hero = DC.pageHero('<a href="#/">Beranda</a> / Keranjang', title, 'Periksa kembali pesanan Anda sebelum melanjutkan ke checkout.');

    if (!items.length) {
      return {
        title: title,
        html: hero + '<section class="section section--cream"><div class="container">' +
          DC.emptyState('cart', 'Keranjang Anda masih kosong', 'Yuk pilih menu catering favorit Anda terlebih dahulu.',
            '<a href="#/menu" class="btn btn-primary mt-2">Lihat Menu Catering</a>') +
        '</div></section>'
      };
    }

    var rows = items.map(function (item, index) {
      var addonText = item.addons && item.addons.length
        ? '<div class="addons">Tambahan: ' + e(item.addons.map(function (a) { return a.name; }).join(', ')) + '</div>' : '';
      return '<div class="cart-item">' +
        '<img src="' + e(item.image) + '" alt="' + e(item.name) + '">' +
        '<div><h3><a href="#/product/' + encodeURIComponent(item.slug) + '">' + e(item.name) + '</a></h3>' +
          (item.variantName ? '<div class="variant">Paket: ' + e(item.variantName) + '</div>' : '') +
          addonText +
          (item.note ? '<div class="note">Catatan: ' + e(item.note) + '</div>' : '') +
          '<div class="unit">' + e(rupiah(item.unitPrice)) + ' / item</div>' +
        '</div>' +
        '<div class="cart-item-side">' +
          '<div class="line-total">' + e(rupiah(item.unitPrice * item.qty)) + '</div>' +
          '<div class="qty-form">' +
            '<button type="button" data-action="cart-dec" data-index="' + index + '" aria-label="Kurangi">&minus;</button>' +
            '<input type="number" value="' + item.qty + '" min="1" data-cart-qty data-index="' + index + '" aria-label="Jumlah">' +
            '<button type="button" data-action="cart-inc" data-index="' + index + '" aria-label="Tambah">+</button>' +
          '</div>' +
          '<button type="button" class="link-danger" data-action="remove-item" data-index="' + index + '" style="margin-top:8px">Hapus</button>' +
        '</div>' +
      '</div>';
    }).join('');

    var subtotal = DC.cartSubtotal();
    var html = hero + '<section class="section section--cream"><div class="container"><div class="cart-layout">' +
      '<div class="cart-list">' + rows +
        '<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin-top:6px">' +
          '<a href="#/menu" class="btn btn-ghost btn-sm">&larr; Lanjut Belanja</a>' +
          '<button type="button" class="link-danger" data-action="clear-cart">Kosongkan Keranjang</button>' +
        '</div>' +
      '</div>' +
      '<aside class="summary-card"><h2>Ringkasan Belanja</h2>' +
        '<div class="summary-row"><span>Jumlah item</span><span>' + DC.cartCount() + ' item</span></div>' +
        '<div class="summary-row"><span>Subtotal</span><span>' + e(rupiah(subtotal)) + '</span></div>' +
        '<div class="summary-row"><span>Pengiriman</span><span>Dihitung saat konfirmasi</span></div>' +
        '<div class="summary-row total"><span>Total</span><strong>' + e(rupiah(subtotal)) + '</strong></div>' +
        '<a href="#/checkout" class="btn btn-accent btn-block mt-3">Lanjut ke Checkout</a>' +
        '<p class="muted text-center" style="font-size:.78rem;margin:14px 0 0">Pembayaran aman &middot; Konfirmasi pesanan via WhatsApp</p>' +
      '</aside>' +
    '</div></div></section>';

    return { title: title, html: html };
  }

  /* ---------------- Checkout ---------------- */
  function checkout() {
    var items = DC.getCart();
    var title = 'Checkout';
    var hero = DC.pageHero('<a href="#/">Beranda</a> / <a href="#/cart">Keranjang</a> / Checkout', 'Checkout Pesanan', 'Lengkapi data pengiriman dan konfirmasi pesanan catering Anda.');

    if (!items.length) {
      return {
        title: title,
        html: hero + '<section class="section section--cream"><div class="container">' +
          DC.emptyState('cart', 'Keranjang masih kosong', 'Silakan pilih menu terlebih dahulu sebelum checkout.',
            '<a href="#/menu" class="btn btn-primary mt-2">Lihat Menu Catering</a>') +
        '</div></section>'
      };
    }

    var user = DC.getUser();
    var state = DC.ui.checkout;
    var values = state ? state.values : {
      customer_name: user ? user.name : '',
      customer_phone: user ? (user.phone || '') : '',
      address: '',
      delivery_date: DC.tomorrowISO(),
      delivery_time: '10:00',
      order_type: 'Acara Pribadi',
      notes: '',
      payment_method: 'Transfer Bank'
    };
    var errors = state ? state.errors : {};

    var itemRows = items.map(function (item) {
      return '<li><div><strong>' + item.qty + 'x</strong> ' + e(item.name) +
        (item.variantName ? '<div class="oi-addons">Paket: ' + e(item.variantName) + '</div>' : '') +
        (item.addons && item.addons.length ? '<div class="oi-addons">+ ' + e(item.addons.map(function (a) { return a.name; }).join(', ')) + '</div>' : '') +
        '</div><span>' + e(rupiah(item.unitPrice * item.qty)) + '</span></li>';
    }).join('');

    var subtotal = DC.cartSubtotal();

    var html = hero + '<section class="section section--cream"><div class="container">' +
      (errors.general ? '<div class="alert alert-danger" style="margin-bottom:20px">' + e(errors.general) + '</div>' : '') +
      '<div class="cart-layout">' +
        '<form class="form-card" data-checkout-form novalidate>' +
          '<div class="form-section"><h2 class="fieldset-title">Data Pemesan</h2><div class="form-grid">' +
            '<div class="field"><label for="customer_name">Nama Lengkap <span class="req">*</span></label>' +
              '<input type="text" id="customer_name" name="customer_name" value="' + e(values.customer_name) + '" required>' + fieldError(errors, 'customer_name') + '</div>' +
            '<div class="field"><label for="customer_phone">Nomor WhatsApp <span class="req">*</span></label>' +
              '<input type="tel" id="customer_phone" name="customer_phone" value="' + e(values.customer_phone) + '" placeholder="0812xxxxxxx" required>' + fieldError(errors, 'customer_phone') + '</div>' +
            '<div class="field full"><label for="address">Alamat Pengiriman <span class="req">*</span></label>' +
              '<textarea id="address" name="address" rows="3" required placeholder="Nama jalan, nomor rumah, kelurahan, kecamatan, kota, dan patokan">' + e(values.address) + '</textarea>' + fieldError(errors, 'address') + '</div>' +
          '</div></div>' +
          '<div class="form-section"><h2 class="fieldset-title">Jadwal &amp; Detail Acara</h2><div class="form-grid">' +
            '<div class="field"><label for="delivery_date">Tanggal Pengiriman <span class="req">*</span></label>' +
              '<input type="date" id="delivery_date" name="delivery_date" value="' + e(values.delivery_date) + '" min="' + DC.todayISO() + '" required>' + fieldError(errors, 'delivery_date') + '</div>' +
            '<div class="field"><label for="delivery_time">Jam Pengiriman <span class="req">*</span></label>' +
              '<input type="time" id="delivery_time" name="delivery_time" value="' + e(values.delivery_time) + '" required>' + fieldError(errors, 'delivery_time') + '</div>' +
            '<div class="field"><label for="order_type">Jenis Pesanan <span class="req">*</span></label>' +
              '<select id="order_type" name="order_type" required>' + D.orderTypes.map(function (type) {
                return '<option value="' + e(type) + '"' + DC.selected(type, values.order_type) + '>' + e(type) + '</option>';
              }).join('') + '</select>' + fieldError(errors, 'order_type') + '</div>' +
            '<div class="field"><label for="payment_method">Metode Pembayaran <span class="req">*</span></label>' +
              '<select id="payment_method" name="payment_method" required>' + D.paymentMethods.map(function (method) {
                return '<option value="' + e(method) + '"' + DC.selected(method, values.payment_method) + '>' + e(method) + '</option>';
              }).join('') + '</select>' + fieldError(errors, 'payment_method') + '</div>' +
            '<div class="field full"><label for="notes">Catatan Tambahan</label>' +
              '<textarea id="notes" name="notes" rows="3" maxlength="500" placeholder="Contoh: tanpa sambal, tambah sendok, dekorasi warna hijau, dll.">' + e(values.notes) + '</textarea>' + fieldError(errors, 'notes') + '</div>' +
          '</div></div>' +
          (!user ? '<div class="demo-note" style="margin-top:24px">Anda memesan sebagai tamu. Ingin menyimpan riwayat pesanan? <a href="#/login">Login</a> atau <a href="#/register">daftar akun</a>.</div>' : '') +
          '<button type="submit" class="btn btn-accent btn-block mt-4">Buat Pesanan &middot; ' + e(rupiah(subtotal)) + '</button>' +
        '</form>' +
        '<aside class="summary-card"><h2>Ringkasan Pesanan</h2>' +
          '<ul class="order-items">' + itemRows + '</ul>' +
          '<div class="summary-row" style="margin-top:10px"><span>Subtotal</span><span>' + e(rupiah(subtotal)) + '</span></div>' +
          '<div class="summary-row"><span>Pengiriman</span><span>Konfirmasi admin</span></div>' +
          '<div class="summary-row total"><span>Total</span><strong>' + e(rupiah(subtotal)) + '</strong></div>' +
          '<a href="#/cart" class="btn btn-ghost btn-block mt-3">Ubah Keranjang</a>' +
        '</aside>' +
      '</div>' +
    '</div></section>';

    return { title: title, html: html };
  }

  /* ---------------- Pesanan berhasil ---------------- */
  function success(number) {
    var order = DC.findOrder(number);
    var title = 'Pesanan Berhasil';
    if (!order) {
      return {
        title: 'Pesanan Tidak Ditemukan',
        html: '<section class="section section--cream"><div class="container">' +
          DC.emptyState('box', 'Pesanan tidak ditemukan', 'Kami tidak menemukan detail pesanan tersebut.',
            '<a href="#/menu" class="btn btn-primary mt-2">Kembali ke Menu</a>') +
        '</div></section>'
      };
    }

    var itemRows = order.items.map(function (item) {
      return '<li><div><strong>' + item.quantity + 'x</strong> ' + e(item.product_name) +
        (item.variant_name ? '<div class="oi-addons">Paket: ' + e(item.variant_name) + '</div>' : '') +
        (item.addons && item.addons.length ? '<div class="oi-addons">+ ' + e(item.addons.join(', ')) + '</div>' : '') +
        '</div><span>' + e(rupiah(item.subtotal)) + '</span></li>';
    }).join('');

    var paymentNote = order.payment_method === 'Transfer Bank'
      ? 'Silakan transfer sesuai total ke <strong>BCA 1234567890</strong> a.n. Dapur Catering, lalu konfirmasi bukti transfer via WhatsApp.'
      : order.payment_method === 'QRIS'
        ? 'Pembayaran QRIS akan dikirim admin melalui WhatsApp setelah pesanan dikonfirmasi.'
        : 'Pembayaran dilakukan tunai (COD) saat pesanan diantar. Mohon siapkan uang pas.';

    var user = DC.getUser();
    var waText = encodeURIComponent('Halo, saya ingin konfirmasi pesanan ' + order.order_number);

    var html = '<section class="section section--cream"><div class="container"><div class="success-wrap">' +
      '<div class="text-center"><div class="success-icon">' +
        '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>' +
      '</div><h1 style="margin-bottom:6px">Pesanan Berhasil Dibuat!</h1>' +
      '<p class="muted">Terima kasih, ' + e(order.customer_name) + '. Pesanan Anda sudah kami terima dan akan segera diproses.</p></div>' +
      '<div class="order-box">' +
        '<div class="order-number"><span>Nomor Pesanan</span><strong>' + e(order.order_number) + '</strong></div>' +
        '<div class="info-grid">' +
          '<div><span>Nama Pemesan</span><strong>' + e(order.customer_name) + '</strong></div>' +
          '<div><span>Nomor WhatsApp</span><strong>' + e(order.customer_phone) + '</strong></div>' +
          '<div><span>Tanggal Pengiriman</span><strong>' + e(DC.formatDate(order.delivery_date)) + '</strong></div>' +
          '<div><span>Jam Pengiriman</span><strong>' + e(order.delivery_time) + ' WIB</strong></div>' +
          '<div><span>Jenis Pesanan</span><strong>' + e(order.order_type) + '</strong></div>' +
          '<div><span>Status Pesanan</span><strong><span class="badge ' + DC.badgeClass(order.status) + '">' + e(order.status) + '</span></strong></div>' +
          '<div><span>Metode Pembayaran</span><strong>' + e(order.payment_method) + '</strong></div>' +
          '<div><span>Status Pembayaran</span><strong>' + e(order.payment_status || 'Belum Dibayar') + '</strong></div>' +
          '<div style="grid-column:1/-1"><span>Alamat Pengiriman</span><strong>' + e(order.address) + '</strong></div>' +
          (order.notes ? '<div style="grid-column:1/-1"><span>Catatan</span><strong>' + e(order.notes) + '</strong></div>' : '') +
        '</div>' +
        '<div class="divider"></div><h3>Detail Pesanan</h3><ul class="order-items">' + itemRows + '</ul>' +
        '<div class="summary-row total"><span>Total Pembayaran</span><strong>' + e(rupiah(order.total)) + '</strong></div>' +
        '<div class="demo-note" style="margin-top:20px">' + paymentNote + '</div>' +
        '<div class="detail-actions" style="margin-top:24px">' +
          '<a href="https://wa.me/' + D.whatsapp + '?text=' + waText + '" target="_blank" rel="noopener" class="btn btn-primary">Konfirmasi via WhatsApp</a>' +
          (user ? '<a href="#/account" class="btn btn-ghost">Riwayat Pesanan</a>' : '<a href="#/menu" class="btn btn-ghost">Pesan Lagi</a>') +
        '</div>' +
      '</div>' +
    '</div></div></section>';

    return { title: title, html: html };
  }

  /* ---------------- Login ---------------- */
  function login() {
    var user = DC.getUser();
    if (user) {
      return {
        title: 'Akun Saya',
        html: '<div class="auth-wrap"><div class="auth-card">' +
          '<a class="brand" href="#/">' + DC.icons.brand + '<span>Dapur Catering</span></a>' +
          '<h1>Anda sudah login</h1><p class="subtitle">Masuk sebagai ' + e(user.name) + '.</p>' +
          '<a href="#/account" class="btn btn-primary btn-block">Buka Akun Saya</a>' +
        '</div></div>'
      };
    }

    var html = '<div class="auth-wrap"><div class="auth-card">' +
      '<a class="brand" href="#/">' + DC.icons.brand + '<span>Dapur Catering</span></a>' +
      '<h1>Login Pelanggan</h1><p class="subtitle">Masuk untuk melanjutkan pemesanan dan melihat riwayat.</p>' +
      (DC.ui.loginError ? '<div class="alert alert-danger" style="box-shadow:none;margin-bottom:18px">' + e(DC.ui.loginError) + '</div>' : '') +
      '<form data-login-form novalidate>' +
        '<div class="field" style="margin-bottom:16px"><label for="email">Email</label>' +
          '<input type="email" id="email" name="email" required></div>' +
        '<div class="field" style="margin-bottom:20px"><label for="password">Password</label>' +
          '<input type="password" id="password" name="password" required></div>' +
        '<button type="submit" class="btn btn-primary btn-block">Login</button>' +
      '</form>' +
      '<p class="auth-alt">Belum punya akun? <a href="#/register">Daftar gratis</a></p>' +
      '<div class="demo-note"><strong>Akun demo pelanggan:</strong><br>Email: pelanggan@dapurcatering.com<br>Password: pelanggan123</div>' +
    '</div></div>';

    return { title: 'Login', html: html };
  }

  /* ---------------- Register ---------------- */
  function register() {
    var state = DC.ui.register;
    var values = state ? state.values : { name: '', email: '', phone: '' };
    var errors = state ? state.errors : {};

    var html = '<div class="auth-wrap"><div class="auth-card">' +
      '<a class="brand" href="#/">' + DC.icons.brand + '<span>Dapur Catering</span></a>' +
      '<h1>Buat Akun</h1><p class="subtitle">Daftar untuk memesan lebih mudah dan melihat riwayat pesanan.</p>' +
      '<form data-register-form novalidate>' +
        '<div class="field" style="margin-bottom:16px"><label for="name">Nama Lengkap <span class="req">*</span></label>' +
          '<input type="text" id="name" name="name" value="' + e(values.name) + '" required>' + fieldError(errors, 'name') + '</div>' +
        '<div class="field" style="margin-bottom:16px"><label for="email">Email <span class="req">*</span></label>' +
          '<input type="email" id="email" name="email" value="' + e(values.email) + '" required>' + fieldError(errors, 'email') + '</div>' +
        '<div class="field" style="margin-bottom:16px"><label for="phone">Nomor WhatsApp</label>' +
          '<input type="tel" id="phone" name="phone" value="' + e(values.phone) + '" placeholder="0812xxxxxxx">' + fieldError(errors, 'phone') + '</div>' +
        '<div class="field" style="margin-bottom:16px"><label for="password">Password <span class="req">*</span></label>' +
          '<input type="password" id="password" name="password" required>' + fieldError(errors, 'password') + '</div>' +
        '<div class="field" style="margin-bottom:16px"><label for="password_confirm">Konfirmasi Password <span class="req">*</span></label>' +
          '<input type="password" id="password_confirm" name="password_confirm" required>' + fieldError(errors, 'password_confirm') + '</div>' +
        '<button type="submit" class="btn btn-primary btn-block">Daftar Sekarang</button>' +
      '</form>' +
      '<p class="auth-alt">Sudah punya akun? <a href="#/login">Login di sini</a></p>' +
    '</div></div>';

    return { title: 'Daftar Akun', html: html };
  }

  /* ---------------- Akun ---------------- */
  function account() {
    var user = DC.getUser();
    if (!user) {
      return {
        title: 'Login Diperlukan',
        html: '<div class="auth-wrap"><div class="auth-card">' +
          '<a class="brand" href="#/">' + DC.icons.brand + '<span>Dapur Catering</span></a>' +
          '<h1>Login Diperlukan</h1><p class="subtitle">Silakan login untuk membuka halaman akun.</p>' +
          '<a href="#/login" class="btn btn-primary btn-block">Login Sekarang</a>' +
        '</div></div>'
      };
    }

    var orders = DC.getOrders().filter(function (o) { return Number(o.user_id) === user.id; });
    var totalSpent = orders.reduce(function (sum, o) {
      return o.status === 'Dibatalkan' ? sum : sum + Number(o.total);
    }, 0);
    var activeOrders = orders.filter(function (o) {
      return ['Menunggu', 'Diproses', 'Dikirim'].indexOf(o.status) > -1;
    }).length;

    var table = orders.length
      ? '<div style="overflow-x:auto"><table class="data-table"><thead><tr>' +
          '<th>No. Pesanan</th><th>Tanggal Pesan</th><th>Item</th><th>Total</th><th>Status</th>' +
        '</tr></thead><tbody>' + orders.map(function (o) {
          var qty = o.items.reduce(function (sum, i) { return sum + Number(i.quantity); }, 0);
          return '<tr><td><a href="#/success/' + encodeURIComponent(o.order_number) + '"><strong>' + e(o.order_number) + '</strong></a></td>' +
            '<td>' + e(DC.formatDate(o.created_at)) + '</td>' +
            '<td>' + qty + ' item</td>' +
            '<td>' + e(rupiah(o.total)) + '</td>' +
            '<td><span class="badge ' + DC.badgeClass(o.status) + '">' + e(o.status) + '</span></td></tr>';
        }).join('') + '</tbody></table></div>'
      : DC.emptyState('box', 'Belum ada pesanan', 'Pesanan Anda akan muncul di sini setelah checkout.',
          '<a href="#/menu" class="btn btn-primary btn-sm mt-2">Mulai Pesan</a>');

    var html = DC.pageHero('<a href="#/">Beranda</a> / Akun Saya', 'Halo, ' + user.name, 'Kelola data akun dan pantau riwayat pesanan catering Anda.') +
      '<section class="section section--cream"><div class="container">' +
        '<div class="cart-layout" style="grid-template-columns:1fr 2.2fr">' +
          '<aside class="summary-card" style="position:static"><h2>Profil Saya</h2>' +
            '<div class="info-grid" style="grid-template-columns:1fr">' +
              '<div><span>Nama</span><strong>' + e(user.name) + '</strong></div>' +
              '<div><span>Email</span><strong>' + e(user.email) + '</strong></div>' +
              '<div><span>WhatsApp</span><strong>' + e(user.phone || '-') + '</strong></div>' +
              '<div><span>Bergabung</span><strong>' + e(DC.formatDate(user.created_at)) + '</strong></div>' +
            '</div><div class="divider"></div>' +
            '<div class="summary-row"><span>Total Pesanan</span><strong>' + orders.length + '</strong></div>' +
            '<div class="summary-row"><span>Pesanan Aktif</span><strong>' + activeOrders + '</strong></div>' +
            '<div class="summary-row total"><span>Total Belanja</span><strong>' + e(rupiah(totalSpent)) + '</strong></div>' +
            '<a href="#/menu" class="btn btn-primary btn-block mt-3">Pesan Menu Lagi</a>' +
            '<button type="button" class="btn btn-ghost btn-block mt-2" data-action="logout">Logout</button>' +
          '</aside>' +
          '<div><div class="summary-card" style="position:static">' +
            '<h2 style="margin-bottom:6px">Riwayat Pesanan</h2>' +
            '<p class="muted" style="font-size:.86rem;margin-bottom:18px">Klik nomor pesanan untuk melihat detail lengkap.</p>' +
            table +
          '</div></div>' +
        '</div>' +
      '</div></section>';

    return { title: 'Akun Saya', html: html };
  }

  /* ---------------- 404 ---------------- */
  function notFound() {
    return {
      title: 'Halaman Tidak Ditemukan',
      html: '<section class="section section--cream"><div class="container">' +
        DC.emptyState('search', 'Halaman tidak ditemukan', 'Tautan yang Anda buka tidak tersedia di demo ini.',
          '<a href="#/" class="btn btn-primary mt-2">Kembali ke Beranda</a>') +
      '</div></section>'
    };
  }

  function render(route) {
    var page = route.parts[0] || 'home';
    switch (page) {
      case 'home': return home();
      case 'menu': return menu(route);
      case 'product': return product(route.parts[1] || '');
      case 'cart': return cart();
      case 'checkout': return checkout();
      case 'success': return success(route.parts[1] || '');
      case 'login': return login();
      case 'register': return register();
      case 'account': return account();
      default: return notFound();
    }
  }

  DC.public = { render: render };
})();
