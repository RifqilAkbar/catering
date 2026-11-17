/* =========================================================
   Dapur Catering - Bootstrap demo statis
   Router hash, event delegation, dan alur pemesanan.
   ========================================================= */
(function () {
  'use strict';

  var DC = window.DC;
  var D = DC.data;
  var app = document.getElementById('app');
  var adminCss = document.getElementById('admin-css');

  /* ---------------- Router ---------------- */
  function parseHash() {
    var raw = location.hash.replace(/^#/, '');
    if (!raw || raw === '/') {
      return { path: '/', parts: [], params: new URLSearchParams() };
    }
    var path = raw;
    var query = '';
    var qi = raw.indexOf('?');
    if (qi > -1) {
      path = raw.slice(0, qi);
      query = raw.slice(qi + 1);
    }
    if (path.charAt(0) !== '/') path = '/' + path;
    var parts = [];
    path.split('/').forEach(function (segment) {
      if (segment !== '') {
        try { parts.push(decodeURIComponent(segment)); } catch (err) { parts.push(segment); }
      }
    });
    return { path: path, parts: parts, params: new URLSearchParams(query) };
  }

  function render(keepScroll) {
    var y = window.scrollY;
    var route = parseHash();
    var isAdmin = route.parts[0] === 'admin';
    var page = route.parts[0] || 'home';

    if (adminCss) adminCss.disabled = !isAdmin;

    if (page !== 'checkout') DC.ui.checkout = null;
    if (page !== 'login') DC.ui.loginError = '';
    if (page !== 'register') DC.ui.register = null;
    if (page !== 'admin') DC.ui.adminLoginError = '';

    var result;
    if (isAdmin) {
      document.body.className = 'admin-body';
      result = DC.admin.render(route);
      app.innerHTML = result.html;
      document.title = result.title + ' · Admin Dapur Catering';
    } else {
      document.body.className = '';
      result = DC.public.render(route);
      app.innerHTML = DC.publicHeader(route) + (route.path === '/' ? '' : DC.demoStrip()) + '<main>' + result.html + '</main>' + DC.publicFooter();
      document.body.classList.toggle('has-ig-sticky', !!app.querySelector('.ig-sticky'));
      document.title = result.title + ' · Dapur Catering';
    }

    DC.updateBadge();
    DC.initReveal();

    if (keepScroll) {
      window.scrollTo(0, y);
      return;
    }

    if (route.path === '/' && route.params.get('go')) {
      var target = document.getElementById(route.params.get('go'));
      if (target) {
        requestAnimationFrame(function () {
          target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
        return;
      }
    }
    window.scrollTo(0, 0);
  }

  function rerender() { render(true); }

  function navigate(hash) {
    if (location.hash === hash) {
      render(false);
    } else {
      location.hash = hash;
    }
  }

  /* ---------------- Produk ---------------- */
  function updateProductTotal() {
    var form = document.querySelector('[data-product-form]');
    if (!form) return;
    var base = Number(form.getAttribute('data-base-price')) || 0;
    var variant = form.querySelector('input[name="variant"]:checked');
    var unit = variant ? Number(variant.getAttribute('data-price')) || 0 : base;
    form.querySelectorAll('input[name="addons"]:checked').forEach(function (input) {
      unit += Number(input.getAttribute('data-price')) || 0;
    });
    var qtyInput = form.querySelector('[data-product-qty]');
    var qty = Math.max(1, parseInt(qtyInput ? qtyInput.value : '1', 10) || 1);
    var total = form.querySelector('[data-product-total]');
    if (total) total.textContent = DC.rupiah(unit * qty);
  }

  /* ---------------- Carousel iklan ---------------- */
  function setAdSlide(carousel, index) {
    var slides = carousel.querySelectorAll('[data-ad-slide]');
    var dots = carousel.querySelectorAll('[data-ad-dot]');
    if (!slides.length) return;
    var next = ((index % slides.length) + slides.length) % slides.length;
    slides.forEach(function (slide, i) {
      slide.classList.toggle('is-active', i === next);
    });
    dots.forEach(function (dot, i) {
      dot.classList.toggle('is-active', i === next);
      if (i === next) {
        dot.setAttribute('aria-current', 'true');
      } else {
        dot.removeAttribute('aria-current');
      }
    });
    var current = carousel.querySelector('[data-ad-current]');
    if (current) current.textContent = String(next + 1);
    carousel.setAttribute('data-ad-index', String(next));
  }

  /* ---------------- Submit ---------------- */
  document.addEventListener('submit', function (ev) {
    var form = ev.target;

    if (form.matches('[data-product-form]')) {
      ev.preventDefault();
      var fd = new FormData(form);
      var product = DC.productById(Number(form.getAttribute('data-pid')));
      if (!product) return;
      var variantIndex = parseInt(fd.get('variant'), 10);
      var variant = product.variants && !isNaN(variantIndex) ? product.variants[variantIndex] : null;
      var addonIndexes = fd.getAll('addons').map(function (value) { return parseInt(value, 10); });
      var addons = (product.addons || []).filter(function (addon, index) {
        return addonIndexes.indexOf(index) > -1;
      });
      var qty = Math.max(1, parseInt(fd.get('quantity'), 10) || 1);
      var note = String(fd.get('note') || '').trim();
      var unit = (variant ? Number(variant.price) : Number(product.base_price)) +
        addons.reduce(function (sum, addon) { return sum + Number(addon.price); }, 0);
      DC.addToCart({
        pid: product.id,
        name: product.name,
        slug: product.slug,
        image: product.image,
        variantName: variant ? variant.name : '',
        unitPrice: unit,
        addons: addons.map(function (addon) { return { name: addon.name, price: Number(addon.price) }; }),
        note: note,
        qty: qty
      });
      DC.toast(product.name + ' ditambahkan ke keranjang.');
      var buyNow = ev.submitter && ev.submitter.getAttribute('data-buy') === '1';
      navigate(buyNow ? '#/checkout' : '#/cart');
      return;
    }

    if (form.matches('[data-menu-search]')) {
      ev.preventDefault();
      var menuData = new FormData(form);
      var menuParams = new URLSearchParams();
      var category = String(menuData.get('category') || '');
      var keyword = String(menuData.get('q') || '').trim();
      if (category) menuParams.set('category', category);
      if (keyword) menuParams.set('q', keyword);
      navigate('#/menu' + (menuParams.toString() ? '?' + menuParams.toString() : ''));
      return;
    }

    if (form.matches('[data-checkout-form]')) {
      ev.preventDefault();
      handleCheckout(form);
      return;
    }

    if (form.matches('[data-login-form]')) {
      ev.preventDefault();
      handleLogin(form);
      return;
    }

    if (form.matches('[data-register-form]')) {
      ev.preventDefault();
      handleRegister(form);
      return;
    }

    if (form.matches('[data-admin-login-form]')) {
      ev.preventDefault();
      handleAdminLogin(form);
      return;
    }

    if (form.matches('[data-admin-order-status]')) {
      ev.preventDefault();
      updateOrderStatus(form);
      return;
    }

    if (form.matches('[data-admin-order-payment]')) {
      ev.preventDefault();
      updateOrderPayment(form);
      return;
    }

    if (form.matches('[data-admin-product-filter]')) {
      ev.preventDefault();
      var productData = new FormData(form);
      var productParams = new URLSearchParams();
      var productQ = String(productData.get('q') || '').trim();
      var productCategory = String(productData.get('category') || '0');
      if (productQ) productParams.set('q', productQ);
      if (productCategory && productCategory !== '0') productParams.set('category', productCategory);
      navigate('#/admin/products' + (productParams.toString() ? '?' + productParams.toString() : ''));
      return;
    }

    if (form.matches('[data-admin-order-filter]')) {
      ev.preventDefault();
      var orderData = new FormData(form);
      var orderParams = new URLSearchParams();
      var orderQ = String(orderData.get('q') || '').trim();
      var orderStatus = String(orderData.get('status') || '');
      if (orderQ) orderParams.set('q', orderQ);
      if (orderStatus) orderParams.set('status', orderStatus);
      navigate('#/admin/orders' + (orderParams.toString() ? '?' + orderParams.toString() : ''));
      return;
    }

    if (form.matches('[data-admin-customer-filter]')) {
      ev.preventDefault();
      var customerData = new FormData(form);
      var customerQ = String(customerData.get('q') || '').trim();
      navigate('#/admin/customers' + (customerQ ? '?q=' + encodeURIComponent(customerQ) : ''));
    }
  });

  function handleCheckout(form) {
    var data = new FormData(form);
    var values = {};
    ['customer_name', 'customer_phone', 'address', 'delivery_date', 'delivery_time', 'order_type', 'notes', 'payment_method'].forEach(function (key) {
      values[key] = String(data.get(key) || '').trim();
    });

    var errors = {};
    if (values.customer_name.length < 2) {
      errors.customer_name = 'Nama lengkap minimal 2 karakter.';
    }
    if (!/^[0-9+\-\s()]{8,20}$/.test(values.customer_phone)) {
      errors.customer_phone = 'Nomor WhatsApp tidak valid (8-20 digit).';
    }
    if (values.address.length < 10) {
      errors.address = 'Alamat pengiriman minimal 10 karakter.';
    }
    if (!values.delivery_date || values.delivery_date < DC.todayISO()) {
      errors.delivery_date = 'Tanggal pengiriman tidak boleh di masa lalu.';
    }
    if (!/^([01]\d|2[0-3]):[0-5]\d$/.test(values.delivery_time)) {
      errors.delivery_time = 'Jam pengiriman tidak valid.';
    }
    if (D.orderTypes.indexOf(values.order_type) === -1) {
      errors.order_type = 'Jenis pesanan tidak valid.';
    }
    if (D.paymentMethods.indexOf(values.payment_method) === -1) {
      errors.payment_method = 'Metode pembayaran tidak valid.';
    }
    if (values.notes.length > 500) {
      errors.notes = 'Catatan maksimal 500 karakter.';
    }

    if (Object.keys(errors).length) {
      DC.ui.checkout = { values: values, errors: errors };
      rerender();
      var firstError = document.querySelector('.error-text');
      if (firstError) firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
      return;
    }

    var order = DC.createOrder(values);
    DC.ui.checkout = null;
    DC.toast('Pesanan berhasil dibuat.');
    navigate('#/success/' + encodeURIComponent(order.order_number));
  }

  function handleLogin(form) {
    var data = new FormData(form);
    var email = String(data.get('email') || '').trim().toLowerCase();
    var password = String(data.get('password') || '');
    var user = DC.getUsers().filter(function (u) {
      return String(u.email).toLowerCase() === email && u.password === password;
    })[0];

    if (!user) {
      DC.ui.loginError = 'Email atau password salah.';
      rerender();
      return;
    }

    DC.setUser(user.id);
    DC.ui.loginError = '';
    DC.toast('Selamat datang kembali, ' + user.name.split(' ')[0] + '!');
    navigate('#/account');
  }

  function handleRegister(form) {
    var data = new FormData(form);
    var values = {
      name: String(data.get('name') || '').trim(),
      email: String(data.get('email') || '').trim(),
      phone: String(data.get('phone') || '').trim()
    };
    var password = String(data.get('password') || '');
    var confirm = String(data.get('password_confirm') || '');
    var errors = {};

    if (values.name.length < 2) errors.name = 'Nama minimal 2 karakter.';
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(values.email)) errors.email = 'Format email tidak valid.';
    if (values.phone && !/^[0-9+\-\s()]{8,20}$/.test(values.phone)) errors.phone = 'Nomor WhatsApp tidak valid.';
    if (password.length < 6) errors.password = 'Password minimal 6 karakter.';
    if (password !== confirm) errors.password_confirm = 'Konfirmasi password tidak sama.';

    var users = DC.getUsers();
    if (!errors.email && users.some(function (u) { return String(u.email).toLowerCase() === values.email.toLowerCase(); })) {
      errors.email = 'Email sudah terdaftar. Silakan login.';
    }

    if (Object.keys(errors).length) {
      DC.ui.register = { values: values, errors: errors };
      rerender();
      return;
    }

    var nextId = users.reduce(function (max, u) { return Math.max(max, Number(u.id) || 0); }, 0) + 1;
    var user = {
      id: nextId,
      name: values.name,
      email: values.email,
      phone: values.phone || null,
      password: password,
      created_at: DC.nowStamp()
    };
    users.push(user);
    DC.saveUsers(users);
    DC.setUser(user.id);
    DC.ui.register = null;
    DC.toast('Akun berhasil dibuat. Selamat datang!');
    navigate('#/account');
  }

  function handleAdminLogin(form) {
    var data = new FormData(form);
    var email = String(data.get('email') || '').trim().toLowerCase();
    var password = String(data.get('password') || '');

    if (email === D.demoAdmin.email.toLowerCase() && password === D.demoAdmin.password) {
      DC.setAdmin(true);
      DC.ui.adminLoginError = '';
      DC.toast('Selamat datang, ' + D.demoAdmin.name + '.');
      navigate('#/admin');
    } else {
      DC.ui.adminLoginError = 'Email atau password admin salah.';
      rerender();
    }
  }

  function updateOrderStatus(form) {
    var id = Number(form.getAttribute('data-id'));
    var data = new FormData(form);
    var status = String(data.get('status') || '');
    if (D.statuses.indexOf(status) === -1) return;

    var orders = DC.getOrders().map(function (order) {
      return order.id === id ? Object.assign({}, order, { status: status }) : order;
    });
    DC.saveOrders(orders);
    DC.toast('Status pesanan diubah menjadi ' + status + '.');
    rerender();
  }

  function updateOrderPayment(form) {
    var id = Number(form.getAttribute('data-id'));
    var data = new FormData(form);
    var status = String(data.get('payment_status') || '');
    if (D.paymentStatuses.indexOf(status) === -1) return;

    var orders = DC.getOrders().map(function (order) {
      if (order.id !== id) return order;
      var updated = Object.assign({}, order, { payment_status: status });
      updated.paid_at = status === 'Lunas' ? DC.nowStamp() : null;
      return updated;
    });
    DC.saveOrders(orders);
    DC.toast('Status pembayaran diperbarui menjadi ' + status + '.');
    rerender();
  }

  /* ---------------- Klik ---------------- */
  document.addEventListener('click', function (ev) {
    var navToggle = ev.target.closest('[data-nav-toggle]');
    if (navToggle) {
      var navMenu = document.querySelector('[data-nav-menu]');
      if (navMenu) navMenu.classList.toggle('open');
      return;
    }

    var adControl = ev.target.closest('[data-ad-prev], [data-ad-next], [data-ad-dot]');
    if (adControl) {
      var carousel = adControl.closest('[data-ad-carousel]');
      if (!carousel) return;
      var index = parseInt(carousel.getAttribute('data-ad-index'), 10) || 0;
      if (adControl.hasAttribute('data-ad-prev')) {
        index -= 1;
      } else if (adControl.hasAttribute('data-ad-next')) {
        index += 1;
      } else {
        index = parseInt(adControl.getAttribute('data-ad-dot'), 10) || 0;
      }
      setAdSlide(carousel, index);
      return;
    }

    var trigger = ev.target.closest('[data-action]');
    if (!trigger) return;
    var action = trigger.getAttribute('data-action');

    if (action === 'logout') {
      ev.preventDefault();
      DC.setUser(0);
      DC.toast('Anda telah logout.');
      navigate('#/');
      return;
    }

    if (action === 'admin-logout') {
      ev.preventDefault();
      DC.setAdmin(false);
      DC.toast('Logout admin berhasil.');
      navigate('#/admin/login');
      return;
    }

    if (action === 'admin-toggle') {
      var sidebar = document.querySelector('[data-admin-sidebar]');
      if (sidebar) sidebar.classList.toggle('open');
      return;
    }

    if (action === 'remove-item') {
      var removeIndex = parseInt(trigger.getAttribute('data-index'), 10);
      DC.removeCartItem(removeIndex);
      DC.toast('Item dihapus dari keranjang.');
      rerender();
      return;
    }

    if (action === 'clear-cart') {
      if (!confirm('Kosongkan seluruh keranjang?')) return;
      DC.clearCart();
      DC.toast('Keranjang berhasil dikosongkan.');
      rerender();
      return;
    }

    if (action === 'cart-dec' || action === 'cart-inc') {
      var index = parseInt(trigger.getAttribute('data-index'), 10);
      var item = DC.getCart()[index];
      if (!item) return;
      DC.updateCartQty(index, item.qty + (action === 'cart-inc' ? 1 : -1));
      rerender();
      return;
    }

    if (action === 'product-step') {
      var stepForm = trigger.closest('[data-product-form]');
      if (!stepForm) return;
      var qtyInput = stepForm.querySelector('[data-product-qty]');
      var step = parseInt(trigger.getAttribute('data-step'), 10) || 0;
      qtyInput.value = Math.max(1, (parseInt(qtyInput.value, 10) || 1) + step);
      updateProductTotal();
      return;
    }

    if (action === 'toggle-product') {
      var toggleId = Number(trigger.getAttribute('data-id'));
      var products = DC.getProducts().map(function (product) {
        return product.id === toggleId
          ? Object.assign({}, product, { is_available: product.is_available ? 0 : 1 })
          : product;
      });
      DC.saveProducts(products);
      DC.toast('Status ketersediaan produk diperbarui.');
      rerender();
      return;
    }

    if (action === 'feature-product') {
      var featureId = Number(trigger.getAttribute('data-id'));
      var featured = DC.getProducts().map(function (product) {
        return product.id === featureId
          ? Object.assign({}, product, { is_featured: product.is_featured ? 0 : 1 })
          : product;
      });
      DC.saveProducts(featured);
      DC.toast('Status menu terlaris diperbarui.');
      rerender();
    }
  });

  /* ---------------- Input ---------------- */
  document.addEventListener('input', function (ev) {
    if (ev.target.matches('[data-product-qty]')) {
      updateProductTotal();
    }
  });

  document.addEventListener('change', function (ev) {
    var target = ev.target;

    if (target.matches('[data-cart-qty]')) {
      var index = parseInt(target.getAttribute('data-index'), 10);
      DC.updateCartQty(index, target.value);
      rerender();
      return;
    }

    if (target.matches('[data-product-qty]') || target.name === 'variant' || target.name === 'addons') {
      updateProductTotal();
    }
  });

  /* ---------------- Start ---------------- */
  DC.seed();
  render(false);

  window.addEventListener('hashchange', function () { render(false); });
})();
