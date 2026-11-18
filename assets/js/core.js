/* =========================================================
   Dapur Catering - Core Demo (GitHub Pages)
   Util, penyimpanan localStorage, komponen bersama.
   ========================================================= */
(function () {
  'use strict';

  var D = window.DC_DATA;
  var DC = window.DC = window.DC || {};
  var PREFIX = 'dc_demo_';

  DC.data = D;
  DC.ui = { checkout: null, loginError: '', register: null, adminLoginError: '' };

  /* ---------------- Util ---------------- */
  function e(value) {
    return String(value === null || value === undefined ? '' : value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function rupiah(value) {
    return 'Rp' + Math.round(Number(value) || 0).toLocaleString('id-ID');
  }

  function pad(n) {
    return n < 10 ? '0' + n : String(n);
  }

  function localDate(date) {
    return date.getFullYear() + '-' + pad(date.getMonth() + 1) + '-' + pad(date.getDate());
  }

  function todayISO() { return localDate(new Date()); }

  function tomorrowISO() {
    var d = new Date();
    d.setDate(d.getDate() + 1);
    return localDate(d);
  }

  function dateOffset(days) {
    var d = new Date();
    d.setDate(d.getDate() + days);
    return d;
  }

  function nowStamp() {
    var d = new Date();
    return localDate(d) + ' ' + pad(d.getHours()) + ':' + pad(d.getMinutes()) + ':' + pad(d.getSeconds());
  }

  function formatDate(value, withTime) {
    if (!value) return '-';
    var raw = String(value).trim();
    var date = new Date(raw.indexOf(' ') > -1 ? raw.replace(' ', 'T') : raw);
    if (isNaN(date.getTime())) return raw;
    var label = date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
    if (withTime) {
      label += ' ' + pad(date.getHours()) + ':' + pad(date.getMinutes());
    }
    return label;
  }

  function initials(name) {
    return String(name || 'A').trim().charAt(0).toUpperCase();
  }

  function badgeClass(status) {
    return {
      'Menunggu': 'badge-warning',
      'Diproses': 'badge-info',
      'Dikirim': 'badge-primary',
      'Selesai': 'badge-success',
      'Dibatalkan': 'badge-danger'
    }[status] || 'badge-muted';
  }

  function selected(value, current) {
    return String(value) === String(current) ? ' selected' : '';
  }

  DC.e = e;
  DC.rupiah = rupiah;
  DC.pad = pad;
  DC.localDate = localDate;
  DC.todayISO = todayISO;
  DC.tomorrowISO = tomorrowISO;
  DC.dateOffset = dateOffset;
  DC.nowStamp = nowStamp;
  DC.formatDate = formatDate;
  DC.initials = initials;
  DC.badgeClass = badgeClass;
  DC.selected = selected;

  /* ---------------- Storage ---------------- */
  function read(key, fallback) {
    try {
      var raw = localStorage.getItem(PREFIX + key);
      return raw === null ? fallback : JSON.parse(raw);
    } catch (err) {
      return fallback;
    }
  }

  function write(key, value) {
    try {
      localStorage.setItem(PREFIX + key, JSON.stringify(value));
    } catch (err) { /* localStorage penuh / diblokir */ }
  }

  DC.read = read;
  DC.write = write;

  /* ---------------- Produk & kategori ---------------- */
  DC.getCategories = function () { return D.categories; };

  DC.categoryById = function (id) {
    return D.categories.filter(function (c) { return c.id === Number(id); })[0] || null;
  };

  DC.categoryBySlug = function (slug) {
    return D.categories.filter(function (c) { return c.slug === slug; })[0] || null;
  };

  DC.getProducts = function () { return read('products', D.products); };
  DC.saveProducts = function (list) { write('products', list); };

  DC.productById = function (id) {
    return DC.getProducts().filter(function (p) { return p.id === Number(id); })[0] || null;
  };

  DC.productBySlug = function (slug) {
    return DC.getProducts().filter(function (p) { return p.slug === slug; })[0] || null;
  };

  DC.isAvailable = function (product) {
    return !!product && !!product.is_available && Number(product.stock) > 0;
  };

  DC.priceLabel = function (product) {
    var variants = product.variants && product.variants.length
      ? product.variants
      : [{ price: product.base_price }];
    var prices = variants.map(function (v) { return Number(v.price) || 0; });
    var min = Math.min.apply(null, prices);
    var max = Math.max.apply(null, prices);
    return max > min ? rupiah(min) + ' - ' + rupiah(max) : rupiah(min);
  };

  /* ---------------- Pengguna & sesi ---------------- */
  DC.getUsers = function () { return read('users', []); };
  DC.saveUsers = function (list) { write('users', list); };

  DC.getUserId = function () { return Number(read('user', 0)) || 0; };

  DC.getUser = function () {
    var id = DC.getUserId();
    if (!id) return null;
    return DC.getUsers().filter(function (u) { return u.id === id; })[0] || null;
  };

  DC.setUser = function (id) { write('user', Number(id) || 0); };

  DC.isAdmin = function () { return read('admin', false) === true; };
  DC.setAdmin = function (value) { write('admin', !!value); };

  /* ---------------- Keranjang ---------------- */
  DC.getCart = function () { return read('cart', []); };
  DC.saveCart = function (cart) {
    write('cart', cart);
    DC.updateBadge();
  };

  DC.cartCount = function () {
    return DC.getCart().reduce(function (sum, item) { return sum + (Number(item.qty) || 0); }, 0);
  };

  DC.cartSubtotal = function () {
    return DC.getCart().reduce(function (sum, item) {
      return sum + (Number(item.unitPrice) || 0) * (Number(item.qty) || 0);
    }, 0);
  };

  DC.addToCart = function (item) {
    var cart = DC.getCart();
    var addonKey = (item.addons || []).map(function (a) { return a.name; }).sort().join('|');
    var key = item.pid + '||' + (item.variantName || '') + '||' + addonKey + '||' + (item.note || '');
    var existing = null;
    cart.forEach(function (row) {
      var rowKey = row.pid + '||' + (row.variantName || '') + '||' +
        (row.addons || []).map(function (a) { return a.name; }).sort().join('|') + '||' + (row.note || '');
      if (rowKey === key) existing = row;
    });
    if (existing) {
      existing.qty += Number(item.qty) || 1;
    } else {
      item.qty = Number(item.qty) || 1;
      cart.push(item);
    }
    DC.saveCart(cart);
    return cart;
  };

  DC.updateCartQty = function (index, qty) {
    var cart = DC.getCart();
    if (!cart[index]) return;
    cart[index].qty = Math.max(1, Number(qty) || 1);
    DC.saveCart(cart);
  };

  DC.removeCartItem = function (index) {
    var cart = DC.getCart();
    cart.splice(index, 1);
    DC.saveCart(cart);
  };

  DC.clearCart = function () {
    DC.saveCart([]);
  };

  DC.updateBadge = function () {
    var count = DC.cartCount();
    document.querySelectorAll('[data-cart-count]').forEach(function (el) {
      el.textContent = count;
      el.setAttribute('data-empty', count > 0 ? 'false' : 'true');
    });
  };

  /* ---------------- Pesanan ---------------- */
  DC.getOrders = function () {
    return read('orders', []).slice().sort(function (a, b) {
      return String(b.created_at).localeCompare(String(a.created_at));
    });
  };

  DC.saveOrders = function (list) { write('orders', list); };

  DC.findOrder = function (number) {
    return DC.getOrders().filter(function (o) { return o.order_number === number; })[0] || null;
  };

  DC.orderById = function (id) {
    return DC.getOrders().filter(function (o) { return o.id === Number(id); })[0] || null;
  };

  DC.makeOrderNumber = function () {
    var d = new Date();
    var stamp = '' + d.getFullYear() + pad(d.getMonth() + 1) + pad(d.getDate());
    var number;
    do {
      number = 'DC-' + stamp + '-' + String(Math.floor(1000 + Math.random() * 9000));
    } while (DC.findOrder(number));
    return number;
  };

  DC.createOrder = function (values) {
    var items = DC.getCart();
    var subtotal = DC.cartSubtotal();
    var orders = DC.getOrders();
    var nextId = orders.reduce(function (max, o) { return Math.max(max, Number(o.id) || 0); }, 0) + 1;
    var order = {
      id: nextId,
      order_number: DC.makeOrderNumber(),
      user_id: DC.getUserId() || null,
      customer_name: values.customer_name,
      customer_phone: values.customer_phone,
      address: values.address,
      delivery_date: values.delivery_date,
      delivery_time: values.delivery_time,
      order_type: values.order_type,
      notes: values.notes || null,
      subtotal: subtotal,
      total: subtotal,
      payment_method: values.payment_method,
      payment_status: 'Belum Dibayar',
      status: 'Menunggu',
      created_at: nowStamp(),
      items: items.map(function (item) {
        return {
          product_id: item.pid,
          product_name: item.name,
          variant_name: item.variantName || null,
          addons: (item.addons || []).map(function (a) { return a.name; }),
          price: Number(item.unitPrice) || 0,
          quantity: Number(item.qty) || 1,
          subtotal: (Number(item.unitPrice) || 0) * (Number(item.qty) || 1)
        };
      })
    };
    orders.unshift(order);
    DC.saveOrders(orders);
    DC.clearCart();
    return order;
  };

  /* ---------------- Toast & reveal ---------------- */
  function toast(message, type) {
    var wrap = document.querySelector('.toast-wrap');
    if (!wrap) {
      wrap = document.createElement('div');
      wrap.className = 'toast-wrap';
      wrap.setAttribute('aria-live', 'polite');
      document.body.appendChild(wrap);
    }
    var node = document.createElement('div');
    node.className = 'toast ' + (type || 'success');
    node.textContent = message;
    wrap.appendChild(node);
    setTimeout(function () {
      node.style.opacity = '0';
      node.style.transition = 'opacity .3s ease';
      setTimeout(function () { node.remove(); }, 320);
    }, 2800);
  }

  function initReveal() {
    var items = document.querySelectorAll('.reveal');
    if (!items.length) return;
    if (!('IntersectionObserver' in window)) {
      items.forEach(function (el) { el.classList.add('visible'); });
      return;
    }
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12 });
    items.forEach(function (el) { io.observe(el); });
  }

  DC.toast = toast;
  DC.initReveal = initReveal;

  /* ---------------- Komponen ikon ---------------- */
  var BRAND_MARK = '<span class="brand-mark" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 17h16"/><path d="M5 17a7 7 0 0 1 14 0"/><path d="M12 6V3"/><path d="M8.5 8.5c1-1.5 2.2-2 3.5-2s2.5.5 3.5 2"/></svg></span>';

  var CART_ICON = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="20" r="1.4"/><circle cx="18" cy="20" r="1.4"/><path d="M2.5 3h2.2l2.2 11.2a1.6 1.6 0 0 0 1.6 1.3h9.1a1.6 1.6 0 0 0 1.6-1.3l1.4-7.2H6"/></svg>';

  var USER_ICON = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="3.5"/><path d="M5 20a7 7 0 0 1 14 0"/></svg>';

  DC.icons = {
    brand: BRAND_MARK,
    cart: CART_ICON,
    user: USER_ICON,
    dashboard: '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
    products: '<path d="M3 7.5 12 3l9 4.5v9L12 21l-9-4.5v-9Z"/><path d="m3 7.5 9 4.5 9-4.5"/><path d="M12 12v9"/>',
    categories: '<path d="M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h6v6h-6z"/>',
    orders: '<path d="M9 5h10a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h4"/><path d="M9 3h6v4H9z"/><path d="M8 12h8M8 16h5"/>',
    customers: '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0"/><path d="M16 4.5a3.5 3.5 0 0 1 0 7"/><path d="M17.5 13.5a6.5 6.5 0 0 1 4 6"/>',
    site: '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a15 15 0 0 1 0 18a15 15 0 0 1 0-18Z"/>',
    logout: '<path d="M9 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/>',
    search: '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
    box: '<path d="M9 5h10a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h4"/><path d="M9 3h6v4H9z"/>'
  };

  DC.svg = function (name, size) {
    return '<svg width="' + (size || 24) + '" height="' + (size || 24) + '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">' + (DC.icons[name] || '') + '</svg>';
  };

  /* ---------------- Komponen halaman ---------------- */
  function productCard(product) {
    var category = DC.categoryById(product.category_id);
    var available = DC.isAvailable(product);
    var link = '#/product/' + encodeURIComponent(product.slug);
    return '<article class="product-card reveal' + (available ? '' : ' stock-out') + '">' +
      '<div class="product-thumb">' +
        '<a href="' + link + '" aria-label="' + e(product.name) + '">' +
          '<img src="' + e(product.image) + '" alt="' + e(product.name) + '" loading="lazy">' +
        '</a>' +
        (product.is_featured ? '<span class="product-flag">Terlaris</span>' : '') +
        (!available ? '<span class="product-flag" style="background:var(--danger)">Habis</span>' : '') +
      '</div>' +
      '<div class="product-body">' +
        '<span class="product-cat">' + e(category ? category.name : '') + '</span>' +
        '<h3><a href="' + link + '">' + e(product.name) + '</a></h3>' +
        '<p class="product-desc">' + e(product.description) + '</p>' +
        '<div class="product-price"><small>mulai dari</small><br>' + e(DC.priceLabel(product)) + '</div>' +
        '<div class="product-actions"><a class="btn btn-primary btn-sm" href="' + link + '">Pesan Sekarang</a></div>' +
      '</div>' +
    '</article>';
  }

  function publicHeader(route) {
    var user = DC.getUser();
    var page = route.parts[0] || 'home';
    var activeMenu = page === 'menu' || page === 'product';
    var count = DC.cartCount();
    return '<header class="site-header"><div class="container"><nav class="navbar">' +
      '<a class="brand" href="#/">' + BRAND_MARK +
        '<span>Dapur Catering<small>Rasa Rumahan, Sajian Istimewa</small></span></a>' +
      '<div class="nav-menu" data-nav-menu>' +
        '<ul class="nav-links">' +
          '<li><a href="#/" class="' + (page === 'home' ? 'active' : '') + '">Beranda</a></li>' +
          '<li><a href="#/menu" class="' + (activeMenu ? 'active' : '') + '">Menu Catering</a></li>' +
          '<li><a href="#/?go=tentang">Tentang Kami</a></li>' +
          '<li><a href="#/?go=kontak">Kontak</a></li>' +
        '</ul>' +
        '<div class="nav-actions">' +
          '<a class="icon-btn" href="#/cart" aria-label="Keranjang belanja">' + CART_ICON +
            '<span>Keranjang</span>' +
            '<span class="cart-badge" data-cart-count data-empty="' + (count > 0 ? 'false' : 'true') + '">' + count + '</span>' +
          '</a>' +
          (user
            ? '<a class="icon-btn" href="#/account" title="Akun saya">' + USER_ICON + '<span>' + e(user.name.split(' ')[0]) + '</span></a>' +
              '<a class="icon-btn" href="#/" data-action="logout">Logout</a>'
            : '<a class="icon-btn" href="#/login">Login</a>') +
        '</div>' +
      '</div>' +
      '<button type="button" class="nav-toggle" data-nav-toggle aria-label="Buka menu navigasi">' +
        '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg>' +
      '</button>' +
    '</nav></div></header>';
  }

  function publicFooter() {
    var year = new Date().getFullYear();
    return '<footer class="site-footer" id="kontak"><div class="container">' +
      '<div class="footer-grid">' +
        '<div>' +
          '<a class="brand" href="#/">' + BRAND_MARK + '<span>Dapur Catering<small>Rasa Rumahan, Sajian Istimewa</small></span></a>' +
          '<p>Melayani pesanan nasi kotak, tumpeng, dan ayam geprek untuk berbagai acara dengan bahan segar dan cita rasa rumahan yang istimewa.</p>' +
          '<div class="social-row">' +
            '<a href="https://wa.me/' + D.whatsapp + '" target="_blank" rel="noopener" aria-label="WhatsApp"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 0 0-8.6 15L2 22l5.2-1.4A10 10 0 1 0 12 2Zm0 18a8 8 0 0 1-4.1-1.1l-.3-.2-3 .8.8-2.9-.2-.3A8 8 0 1 1 12 20Zm4.4-5.9c-.2-.1-1.4-.7-1.6-.8s-.4-.1-.6.1-.6.8-.8 1-.3.2-.6.1a6.5 6.5 0 0 1-1.9-1.2 7.2 7.2 0 0 1-1.3-1.7c-.1-.2 0-.4.1-.5l.4-.5a1.8 1.8 0 0 0 .3-.5.5.5 0 0 0 0-.5c0-.1-.6-1.4-.8-1.9s-.4-.4-.6-.4h-.5a1 1 0 0 0-.7.3A2.9 2.9 0 0 0 8 9.3a5 5 0 0 0 1 2.7 11.4 11.4 0 0 0 4.4 3.8c1.6.6 1.6.4 1.9.4a2.5 2.5 0 0 0 1.7-1.2 2.1 2.1 0 0 0 .1-1.2c0-.1-.2-.1-.4-.2Z"/></svg></a>' +
            '<a href="https://instagram.com" target="_blank" rel="noopener" aria-label="Instagram"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/></svg></a>' +
            '<a href="https://facebook.com" target="_blank" rel="noopener" aria-label="Facebook"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M14 9h3V6h-3c-2.2 0-4 1.8-4 4v2H7v3h3v6h3v-6h3l1-3h-4v-2c0-.6.4-1 1-1Z"/></svg></a>' +
          '</div>' +
        '</div>' +
        '<div><h4>Menu</h4><ul class="footer-links">' +
          '<li><a href="#/menu">Semua Menu</a></li>' +
          '<li><a href="#/menu?category=nasi-kotak">Nasi Kotak</a></li>' +
          '<li><a href="#/menu?category=tumpeng">Tumpeng</a></li>' +
          '<li><a href="#/menu?category=ayam-geprek">Ayam Geprek</a></li>' +
        '</ul></div>' +
        '<div><h4>Informasi</h4><ul class="footer-links">' +
          '<li><a href="#/?go=tentang">Tentang Kami</a></li>' +
          '<li><a href="#/?go=keunggulan">Keunggulan</a></li>' +
          '<li><a href="#/login">Login Pelanggan</a></li>' +
          '<li><a href="#/register">Daftar Akun</a></li>' +
          '<li><a href="#/admin/login">Login Admin</a></li>' +
        '</ul></div>' +
        '<div><h4>Hubungi Kami</h4><ul class="footer-contact">' +
          '<li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 21s7-5.5 7-11a7 7 0 1 0-14 0c0 5.5 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg><span>Jl. Rasa Lezat No. 12, Kota Anda</span></li>' +
          '<li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 2 .7 2.9a2 2 0 0 1-.4 2.1L8.1 10a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.9.6 2.9.7a2 2 0 0 1 1.6 2Z"/></svg><a href="https://wa.me/' + D.whatsapp + '" style="color:inherit">0812-3456-7890</a></li>' +
          '<li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg><a href="mailto:halo@dapurcatering.id" style="color:inherit">halo@dapurcatering.id</a></li>' +
          '<li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg><span>Setiap hari, 07.00 - 21.00 WIB</span></li>' +
        '</ul></div>' +
      '</div>' +
      '<div class="footer-bottom"><span>&copy; ' + year + ' Dapur Catering. Semua hak dilindungi.</span>' +
        '<span>Demo statis &middot; <a href="https://github.com/RifqilAkbar/catering" target="_blank" rel="noopener" style="color:inherit;text-decoration:underline">kode sumber</a></span></div>' +
    '</div></footer><div class="toast-wrap" aria-live="polite"></div>';
  }

  function demoStrip() {
    return '<div class="demo-strip"><div class="container">' +
      '<span><strong>Mode demo statis</strong> (GitHub Pages) &mdash; data tersimpan di browser Anda.</span>' +
      '<span><a href="https://github.com/RifqilAkbar/catering" target="_blank" rel="noopener">Lihat kode PHP + MySQL</a></span>' +
      '<span>&middot;</span>' +
      '<span><a href="#/admin/login">Demo admin</a></span>' +
    '</div></div>';
  }

  function pageHero(crumbs, title, description) {
    return '<section class="page-hero"><div class="container">' +
      (crumbs ? '<div class="breadcrumb">' + crumbs + '</div>' : '') +
      '<h1>' + e(title) + '</h1><p>' + e(description) + '</p>' +
    '</div></section>';
  }

  function emptyState(iconName, title, text, actionHtml) {
    return '<div class="empty-state">' +
      '<div class="empty-icon">' + DC.svg(iconName || 'search') + '</div>' +
      '<h3>' + e(title) + '</h3><p>' + e(text) + '</p>' +
      (actionHtml || '') +
    '</div>';
  }

  DC.productCard = productCard;
  DC.publicHeader = publicHeader;
  DC.publicFooter = publicFooter;
  DC.demoStrip = demoStrip;
  DC.pageHero = pageHero;
  DC.emptyState = emptyState;

  /* ---------------- Seed data awal ---------------- */
  function seedItem(productId, variantName, qty, addonNames) {
    var product = D.products.filter(function (p) { return p.id === productId; })[0];
    var variant = (product.variants || []).filter(function (v) { return v.name === variantName; })[0] || { name: variantName, price: product.base_price };
    var addons = (product.addons || []).filter(function (a) { return (addonNames || []).indexOf(a.name) > -1; });
    var unit = Number(variant.price) + addons.reduce(function (sum, a) { return sum + Number(a.price); }, 0);
    return {
      product_id: product.id,
      product_name: product.name,
      variant_name: variant.name,
      addons: addons.map(function (a) { return a.name; }),
      price: unit,
      quantity: qty,
      subtotal: unit * qty
    };
  }

  function seedOrderNumber(offset, code) {
    var d = dateOffset(offset);
    return 'DC-' + d.getFullYear() + pad(d.getMonth() + 1) + pad(d.getDate()) + '-' + code;
  }

  function seedOrders() {
    var o1Items = [seedItem(2, 'Reguler', 25, []), seedItem(10, 'Level 2', 10, ['Topping Keju'])];
    var o2Items = [seedItem(7, 'Standar', 1, []), seedItem(14, 'Level 1', 5, [])];
    var o3Items = [seedItem(4, 'Reguler', 12, []), seedItem(10, 'Level 1', 6, [])];
    function total(items) {
      return items.reduce(function (sum, i) { return sum + i.subtotal; }, 0);
    }
    return [
      {
        id: 3, order_number: seedOrderNumber(-1, '1846'), user_id: 1,
        customer_name: 'Pelanggan Demo', customer_phone: '081234567890',
        address: 'Jl. Rasa Lezat No. 12, Kota Anda', delivery_date: localDate(dateOffset(2)),
        delivery_time: '10:00', order_type: 'Acara Pribadi', notes: null,
        subtotal: total(o3Items), total: total(o3Items), payment_method: 'COD',
        payment_status: 'Belum Dibayar', status: 'Menunggu',
        created_at: localDate(dateOffset(-1)) + ' 14:20:00', items: o3Items
      },
      {
        id: 2, order_number: seedOrderNumber(-2, '1532'), user_id: null,
        customer_name: 'Budi Santoso', customer_phone: '081377889900',
        address: 'Jl. Kenanga Raya No. 21, Bekasi', delivery_date: localDate(dateOffset(1)),
        delivery_time: '11:30', order_type: 'Ulang Tahun', notes: 'Dekorasi warna hijau, tumpeng premium.',
        subtotal: total(o2Items), total: total(o2Items), payment_method: 'QRIS',
        payment_status: 'Menunggu Konfirmasi', status: 'Diproses',
        created_at: localDate(dateOffset(-2)) + ' 10:05:00', items: o2Items
      },
      {
        id: 1, order_number: seedOrderNumber(-7, '1042'), user_id: null,
        customer_name: 'Rina Nurhaliza', customer_phone: '081298765432',
        address: 'Jl. Melati No. 8, Sukamaju, Cilodong, Depok', delivery_date: localDate(dateOffset(-6)),
        delivery_time: '10:00', order_type: 'Rapat', notes: 'Sambal dipisah, 5 kotak tanpa mie.',
        subtotal: total(o1Items), total: total(o1Items), payment_method: 'Transfer Bank',
        payment_status: 'Lunas', status: 'Selesai',
        created_at: localDate(dateOffset(-7)) + ' 09:12:00', items: o1Items
      }
    ];
  }

  DC.seed = function () {
    if (read('seeded', false)) return;
    write('users', [D.demoUser]);
    write('products', D.products);
    write('orders', seedOrders());
    write('seeded', true);
  };
})();
