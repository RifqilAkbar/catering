/* =========================================================
   Dapur Catering - Halaman Admin (versi statis)
   ========================================================= */
(function () {
  'use strict';

  var DC = window.DC;
  var D = DC.data;
  var e = DC.e;
  var rupiah = DC.rupiah;

  function adminIcon(name) {
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">' + (DC.icons[name] || '') + '</svg>';
  }

  var NAV = [
    ['dashboard', '#/admin', 'Dashboard', 'dashboard'],
    ['products', '#/admin/products', 'Produk', 'products'],
    ['categories', '#/admin/categories', 'Kategori', 'categories'],
    ['orders', '#/admin/orders', 'Pesanan', 'orders'],
    ['customers', '#/admin/customers', 'Pelanggan', 'customers']
  ];

  function adminLogin() {
    var html = '<div class="admin-auth"><div class="admin-auth-card">' +
      '<a class="admin-brand" href="#/">' + DC.icons.brand + '<span>Dapur Catering<small>Admin Panel</small></span></a>' +
      '<h1>Login Admin</h1><p class="sub">Masuk untuk mengelola produk, pesanan, dan pelanggan.</p>' +
      (DC.ui.adminLoginError ? '<div class="alert alert-danger">' + e(DC.ui.adminLoginError) + '</div>' : '') +
      '<form data-admin-login-form>' +
        '<div class="field"><label for="admin-email">Email Admin</label><input type="email" id="admin-email" name="email" required></div>' +
        '<div class="field"><label for="admin-password">Password</label><input type="password" id="admin-password" name="password" required></div>' +
        '<button type="submit" class="btn btn-primary btn-block">Login ke Dashboard</button>' +
      '</form>' +
      '<div class="demo-note"><strong>Akun admin default:</strong><br>Email: admin@dapurcatering.com<br>Password: admin123</div>' +
      '<p class="auth-alt" style="margin-top:16px"><a href="#/">Kembali ke situs</a></p>' +
    '</div></div>';

    return { title: 'Login Admin', html: html };
  }

  function sidebar(active) {
    var links = NAV.map(function (item) {
      return '<a href="' + item[1] + '" class="' + (active === item[0] ? 'active' : '') + '">' +
        adminIcon(item[3]) + '<span>' + item[2] + '</span></a>';
    }).join('');

    return '<aside class="admin-sidebar" data-admin-sidebar>' +
      '<a class="admin-brand" href="#/admin">' + DC.icons.brand + '<span>Dapur Catering<small>Admin Panel</small></span></a>' +
      '<nav class="admin-nav"><span class="admin-nav-label">Menu Utama</span>' + links + '</nav>' +
      '<div class="admin-sidebar-footer">' +
        '<a href="#/">' + adminIcon('site') + '<span>Lihat Situs</span></a>' +
        '<a href="#/admin/login" data-action="admin-logout">' + adminIcon('logout') + '<span>Logout</span></a>' +
      '</div>' +
    '</aside>';
  }

  function shell(content, opts) {
    return '<div class="admin-shell">' + sidebar(opts.active) +
      '<div class="admin-main">' +
        '<div class="admin-demo-strip">Mode demo statis &mdash; perubahan tersimpan di browser. <a href="#/">Lihat situs</a> &middot; <a href="https://github.com/RifqilAkbar/catering" target="_blank" rel="noopener">Kode PHP + MySQL</a></div>' +
        '<header class="admin-topbar">' +
          '<button type="button" class="admin-toggle" data-action="admin-toggle" aria-label="Menu">' +
            '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg>' +
          '</button>' +
          '<div><h1>' + e(opts.title) + '</h1>' + (opts.subtitle ? '<div class="sub">' + e(opts.subtitle) + '</div>' : '') + '</div>' +
          '<div class="admin-user"><span class="avatar">A</span><span class="name">' + e(D.demoAdmin.name) + '</span></div>' +
        '</header>' +
        '<main class="admin-content">' + content + '</main>' +
      '</div>' +
    '</div>';
  }

  function statCard(color, icon, label, value) {
    return '<div class="stat-card"><span class="stat-icon ' + color + '">' + adminIcon(icon) + '</span>' +
      '<div><div class="label">' + label + '</div><div class="value">' + value + '</div></div></div>';
  }

  function emptyRow(colspan, text) {
    return '<tr><td colspan="' + colspan + '" class="empty-row">' + e(text) + '</td></tr>';
  }

  /* ---------------- Dashboard ---------------- */
  function dashboard() {
    var orders = DC.getOrders();
    var users = DC.getUsers();
    var products = DC.getProducts();
    var revenue = orders.reduce(function (sum, o) {
      return o.status === 'Dibatalkan' ? sum : sum + Number(o.total);
    }, 0);
    var pending = orders.filter(function (o) { return o.status === 'Menunggu'; }).length;
    var recent = orders.slice(0, 8);

    var recentRows = recent.length ? recent.map(function (o) {
      return '<tr><td><a href="#/admin/order/' + o.id + '"><strong>' + e(o.order_number) + '</strong></a></td>' +
        '<td>' + e(o.customer_name) + '</td><td>' + e(rupiah(o.total)) + '</td>' +
        '<td><span class="badge ' + DC.badgeClass(o.status) + '">' + e(o.status) + '</span></td>' +
        '<td class="nowrap">' + e(DC.formatDate(o.created_at)) + '</td></tr>';
    }).join('') : emptyRow(5, 'Belum ada pesanan masuk.');

    var topMap = {};
    orders.forEach(function (o) {
      (o.items || []).forEach(function (item) {
        if (!topMap[item.product_name]) topMap[item.product_name] = { qty: 0, revenue: 0 };
        topMap[item.product_name].qty += Number(item.quantity) || 0;
        topMap[item.product_name].revenue += Number(item.subtotal) || 0;
      });
    });
    var top = Object.keys(topMap).map(function (name) {
      return { name: name, qty: topMap[name].qty, revenue: topMap[name].revenue };
    }).sort(function (a, b) { return b.qty - a.qty; }).slice(0, 5);

    var topRows = top.length ? top.map(function (p) {
      return '<tr><td><strong>' + e(p.name) + '</strong></td><td>' + p.qty + ' pcs</td><td>' + e(rupiah(p.revenue)) + '</td></tr>';
    }).join('') : emptyRow(3, 'Belum ada data penjualan.');

    var html = '<div class="stat-grid">' +
      statCard('green', 'orders', 'Total Pesanan', orders.length) +
      statCard('orange', 'box', 'Total Pendapatan', e(rupiah(revenue))) +
      statCard('blue', 'customers', 'Pelanggan', users.length) +
      statCard('purple', 'products', 'Produk Menu', products.length) +
    '</div>' +
    (pending > 0 ? '<div class="alert alert-warning">Ada <strong>' + pending + ' pesanan</strong> dengan status <strong>Menunggu</strong> yang perlu diproses. <a href="#/admin/orders?status=Menunggu">Lihat pesanan</a></div>' : '') +
    '<div class="grid-2">' +
      '<div class="panel"><div class="panel-head"><h2>Pesanan Terbaru</h2><a href="#/admin/orders" class="btn btn-light btn-sm">Lihat Semua</a></div>' +
        '<div class="panel-body flush"><div class="table-wrap"><table class="table"><thead><tr>' +
          '<th>No. Pesanan</th><th>Pelanggan</th><th>Total</th><th>Status</th><th>Tanggal</th>' +
        '</tr></thead><tbody>' + recentRows + '</tbody></table></div></div></div>' +
      '<div class="panel"><div class="panel-head"><h2>Produk Terlaris</h2></div>' +
        '<div class="panel-body flush"><div class="table-wrap"><table class="table"><thead><tr>' +
          '<th>Produk</th><th>Terjual</th><th>Pendapatan</th>' +
        '</tr></thead><tbody>' + topRows + '</tbody></table></div></div></div>' +
    '</div>' +
    '<div class="panel"><div class="panel-head"><h2>Akses Cepat</h2></div>' +
      '<div class="panel-body" style="display:flex;gap:10px;flex-wrap:wrap">' +
        '<a href="#/admin/products" class="btn btn-primary">Kelola Produk</a>' +
        '<a href="#/admin/categories" class="btn btn-light">Kelola Kategori</a>' +
        '<a href="#/admin/orders" class="btn btn-light">Kelola Pesanan</a>' +
        '<a href="#/admin/customers" class="btn btn-light">Data Pelanggan</a>' +
      '</div></div>';

    return { title: 'Dashboard', subtitle: 'Ringkasan performa Dapur Catering', active: 'dashboard', html: html };
  }

  /* ---------------- Produk ---------------- */
  function products(route) {
    var q = (route.params.get('q') || '').trim();
    var categoryId = Number(route.params.get('category') || 0);
    var needle = q.toLowerCase();
    var list = DC.getProducts().filter(function (p) {
      var matchQ = !needle || p.name.toLowerCase().indexOf(needle) > -1;
      var matchCat = !categoryId || p.category_id === categoryId;
      return matchQ && matchCat;
    });

    var rows = list.length ? list.map(function (p) {
      var category = DC.categoryById(p.category_id);
      return '<tr><td><div style="display:flex;gap:12px;align-items:center">' +
        '<img class="table-thumb" src="' + e(p.image) + '" alt="' + e(p.name) + '">' +
        '<div><strong>' + e(p.name) + '</strong>' +
          (p.is_featured ? '<span class="badge badge-warning" style="margin-left:4px">Terlaris</span>' : '') +
          '<div class="muted" style="font-size:.76rem">' + (p.variants ? p.variants.length : 0) + ' varian &middot; ' + (p.addons ? p.addons.length : 0) + ' addon</div>' +
        '</div></div></td>' +
        '<td>' + e(category ? category.name : '-') + '</td>' +
        '<td class="nowrap">' + e(DC.priceLabel(p)) + '</td>' +
        '<td>' + Number(p.stock) + '</td>' +
        '<td>' + (p.is_available ? '<span class="badge badge-success">Tersedia</span>' : '<span class="badge badge-danger">Nonaktif</span>') + '</td>' +
        '<td class="text-right"><div class="row-actions" style="justify-content:flex-end">' +
          '<a href="#/product/' + encodeURIComponent(p.slug) + '" target="_blank" rel="noopener" class="btn btn-light btn-sm">Lihat</a>' +
          '<button type="button" class="btn btn-light btn-sm" data-action="feature-product" data-id="' + p.id + '">' + (p.is_featured ? 'Batal Terlaris' : 'Jadikan Terlaris') + '</button>' +
          '<button type="button" class="btn btn-light btn-sm" data-action="toggle-product" data-id="' + p.id + '">' + (p.is_available ? 'Nonaktifkan' : 'Aktifkan') + '</button>' +
        '</div></td></tr>';
    }).join('') : emptyRow(6, 'Belum ada produk yang cocok.');

    var html = '<div class="toolbar">' +
      '<form data-admin-product-filter>' +
        '<input type="search" name="q" value="' + e(q) + '" placeholder="Cari nama produk...">' +
        '<select name="category"><option value="0">Semua Kategori</option>' +
          DC.getCategories().map(function (c) {
            return '<option value="' + c.id + '"' + DC.selected(c.id, categoryId) + '>' + e(c.name) + '</option>';
          }).join('') +
        '</select><button type="submit" class="btn btn-light btn-sm">Filter</button>' +
      '</form>' +
      '<span class="demo-badge">Tambah/edit produk dinonaktifkan di demo</span>' +
    '</div>' +
    '<div class="panel"><div class="panel-head"><h2>Daftar Produk (' + list.length + ')</h2></div>' +
      '<div class="panel-body flush"><div class="table-wrap"><table class="table"><thead><tr>' +
        '<th>Produk</th><th>Kategori</th><th>Harga</th><th>Stok</th><th>Status</th><th class="text-right">Aksi</th>' +
      '</tr></thead><tbody>' + rows + '</tbody></table></div></div></div>';

    return { title: 'Kelola Produk', subtitle: 'Atur menu, harga, varian, dan ketersediaan', active: 'products', html: html };
  }

  /* ---------------- Kategori ---------------- */
  function categories() {
    var products = DC.getProducts();
    var rows = DC.getCategories().map(function (category) {
      var count = products.filter(function (p) { return p.category_id === category.id; }).length;
      return '<tr><td><img class="table-thumb" src="' + e(category.image) + '" alt="' + e(category.name) + '"></td>' +
        '<td><strong>' + e(category.name) + '</strong></td>' +
        '<td><code>' + e(category.slug) + '</code></td>' +
        '<td>' + count + '</td></tr>';
    }).join('');

    var html = '<div class="alert alert-info">Demo statis: tambah, edit, dan hapus kategori dinonaktifkan. Data kategori mengikuti <code>database/seed.sql</code>.</div>' +
      '<div class="panel"><div class="panel-head"><h2>Daftar Kategori</h2></div>' +
        '<div class="panel-body flush"><div class="table-wrap"><table class="table"><thead><tr>' +
          '<th>Gambar</th><th>Nama</th><th>Slug</th><th>Produk</th>' +
        '</tr></thead><tbody>' + rows + '</tbody></table></div></div></div>';

    return { title: 'Kelola Kategori', subtitle: 'Tambah, ubah, atau hapus kategori menu', active: 'categories', html: html };
  }

  /* ---------------- Pesanan ---------------- */
  function orders(route) {
    var statusFilter = route.params.get('status') || '';
    if (D.statuses.indexOf(statusFilter) === -1) statusFilter = '';
    var q = (route.params.get('q') || '').trim();
    var needle = q.toLowerCase();

    var all = DC.getOrders();
    var counts = {};
    all.forEach(function (o) { counts[o.status] = (counts[o.status] || 0) + 1; });

    var list = all.filter(function (o) {
      var matchStatus = !statusFilter || o.status === statusFilter;
      var matchQ = !needle || (o.order_number + ' ' + o.customer_name + ' ' + o.customer_phone).toLowerCase().indexOf(needle) > -1;
      return matchStatus && matchQ;
    });

    var rows = list.length ? list.map(function (o) {
      var qty = (o.items || []).reduce(function (sum, i) { return sum + Number(i.quantity); }, 0);
      return '<tr><td><a href="#/admin/order/' + o.id + '"><strong>' + e(o.order_number) + '</strong></a>' +
        '<div class="muted" style="font-size:.74rem">' + e(DC.formatDate(o.created_at, true)) + '</div></td>' +
        '<td>' + e(o.customer_name) + '<div class="muted" style="font-size:.74rem">' + e(o.customer_phone) + '</div></td>' +
        '<td class="nowrap">' + e(DC.formatDate(o.delivery_date)) + '<div class="muted" style="font-size:.74rem">' + e(o.delivery_time) + ' WIB</div></td>' +
        '<td>' + qty + ' pcs</td><td class="nowrap">' + e(rupiah(o.total)) + '</td>' +
        '<td><span class="badge ' + (o.payment_status === 'Lunas' ? 'badge-success' : 'badge-muted') + '">' + e(o.payment_status || 'Belum Dibayar') + '</span></td>' +
        '<td><span class="badge ' + DC.badgeClass(o.status) + '">' + e(o.status) + '</span></td>' +
        '<td class="text-right"><a href="#/admin/order/' + o.id + '" class="btn btn-light btn-sm">Detail</a></td></tr>';
    }).join('') : emptyRow(8, 'Belum ada pesanan.');

    var statusButtons = '<a href="#/admin/orders" class="btn btn-sm ' + (statusFilter === '' ? 'btn-primary' : 'btn-light') + '">Semua (' + all.length + ')</a>' +
      D.statuses.map(function (status) {
        return '<a href="#/admin/orders?status=' + encodeURIComponent(status) + '" class="btn btn-sm ' + (statusFilter === status ? 'btn-primary' : 'btn-light') + '">' + e(status) + ' (' + (counts[status] || 0) + ')</a>';
      }).join('');

    var html = '<div class="toolbar"><form data-admin-order-filter>' +
        '<input type="search" name="q" value="' + e(q) + '" placeholder="Cari no. pesanan / nama / telepon...">' +
        '<select name="status"><option value="">Semua Status</option>' +
          D.statuses.map(function (s) { return '<option value="' + e(s) + '"' + DC.selected(s, statusFilter) + '>' + e(s) + '</option>'; }).join('') +
        '</select><button type="submit" class="btn btn-light btn-sm">Filter</button></form></div>' +
      '<div class="toolbar" style="justify-content:flex-start">' + statusButtons + '</div>' +
      '<div class="panel"><div class="panel-head"><h2>Daftar Pesanan (' + list.length + ')</h2></div>' +
        '<div class="panel-body flush"><div class="table-wrap"><table class="table"><thead><tr>' +
          '<th>No. Pesanan</th><th>Pelanggan</th><th>Diantar</th><th>Item</th><th>Total</th><th>Pembayaran</th><th>Status</th><th class="text-right">Aksi</th>' +
        '</tr></thead><tbody>' + rows + '</tbody></table></div></div></div>';

    return { title: 'Kelola Pesanan', subtitle: 'Pantau dan ubah status pesanan pelanggan', active: 'orders', html: html };
  }

  /* ---------------- Detail pesanan ---------------- */
  function orderDetail(id) {
    var order = DC.orderById(id);
    if (!order) {
      return {
        title: 'Detail Pesanan',
        subtitle: '',
        active: 'orders',
        html: '<div class="panel"><div class="panel-body">' +
          '<p>Pesanan tidak ditemukan.</p><a href="#/admin/orders" class="btn btn-light">&larr; Semua Pesanan</a>' +
        '</div></div>'
      };
    }

    var qty = (order.items || []).reduce(function (sum, i) { return sum + Number(i.quantity); }, 0);
    var itemRows = order.items.map(function (item) {
      return '<tr><td><strong>' + e(item.product_name) + '</strong>' +
        (item.variant_name ? '<div class="muted" style="font-size:.76rem">Paket: ' + e(item.variant_name) + '</div>' : '') +
        (item.addons && item.addons.length ? '<div class="muted" style="font-size:.76rem">+ ' + e(item.addons.join(', ')) + '</div>' : '') +
        '</td><td>' + e(rupiah(item.price)) + '</td><td>' + item.quantity + '</td><td class="text-right">' + e(rupiah(item.subtotal)) + '</td></tr>';
    }).join('');

    var waNumber = String(order.customer_phone).replace(/\D+/g, '');
    if (waNumber.charAt(0) === '0') waNumber = '62' + waNumber.slice(1);

    var html = '<div class="toolbar">' +
      '<a href="#/admin/orders" class="btn btn-light btn-sm">&larr; Semua Pesanan</a>' +
      '<a href="https://wa.me/' + e(waNumber) + '?text=' + encodeURIComponent('Halo ' + order.customer_name + ', mengenai pesanan ' + order.order_number) + '" target="_blank" rel="noopener" class="btn btn-accent btn-sm">Hubungi Pelanggan</a>' +
    '</div>' +
    '<div class="stat-grid" style="grid-template-columns:repeat(3,1fr)">' +
      '<div class="stat-card"><div><div class="label">Total Pesanan</div><div class="value">' + e(rupiah(order.total)) + '</div></div></div>' +
      '<div class="stat-card"><div><div class="label">Status Pesanan</div><div class="value" style="font-size:1.1rem"><span class="badge ' + DC.badgeClass(order.status) + '">' + e(order.status) + '</span></div></div></div>' +
      '<div class="stat-card"><div><div class="label">Pembayaran</div><div class="value" style="font-size:1.1rem">' + e(order.payment_status || 'Belum Dibayar') + '</div></div></div>' +
    '</div>' +
    '<div class="grid-2"><div>' +
      '<div class="panel"><div class="panel-head"><h2>Item Pesanan</h2></div>' +
        '<div class="panel-body flush"><div class="table-wrap"><table class="table"><thead><tr>' +
          '<th>Produk</th><th>Harga</th><th>Qty</th><th class="text-right">Subtotal</th>' +
        '</tr></thead><tbody>' + itemRows + '</tbody></table></div>' +
        '<div class="panel-body" style="border-top:1px solid var(--line)">' +
          '<div style="display:flex;justify-content:space-between;font-size:.9rem;margin-bottom:6px"><span class="muted">Subtotal</span><span>' + e(rupiah(order.subtotal)) + '</span></div>' +
          '<div style="display:flex;justify-content:space-between;font-weight:700;font-size:1.1rem"><span>Total</span><span>' + e(rupiah(order.total)) + '</span></div>' +
        '</div></div></div>' +
      '<div class="panel"><div class="panel-head"><h2>Data Pengiriman</h2></div><div class="panel-body"><div class="form-grid">' +
        '<div class="field"><label>Nama Pemesan</label><div>' + e(order.customer_name) + '</div></div>' +
        '<div class="field"><label>Nomor WhatsApp</label><div>' + e(order.customer_phone) + '</div></div>' +
        '<div class="field"><label>Tanggal Pengiriman</label><div>' + e(DC.formatDate(order.delivery_date)) + '</div></div>' +
        '<div class="field"><label>Jam Pengiriman</label><div>' + e(order.delivery_time) + ' WIB</div></div>' +
        '<div class="field"><label>Jenis Pesanan</label><div>' + e(order.order_type) + '</div></div>' +
        '<div class="field"><label>Metode Pembayaran</label><div>' + e(order.payment_method) + '</div></div>' +
        '<div class="field full"><label>Alamat</label><div>' + e(order.address) + '</div></div>' +
        (order.notes ? '<div class="field full"><label>Catatan</label><div>' + e(order.notes) + '</div></div>' : '') +
      '</div>' +
      (order.user_id ? '<div class="alert alert-info" style="margin-top:16px;margin-bottom:0">Pesanan dibuat oleh akun <strong>' + e((DC.getUsers().filter(function (u) { return u.id === Number(order.user_id); })[0] || {}).name || 'Pelanggan') + '</strong>.</div>'
        : '<div class="alert alert-warning" style="margin-top:16px;margin-bottom:0">Pesanan tamu (tanpa akun).</div>') +
      '</div></div>' +
    '</div><div>' +
      '<div class="panel"><div class="panel-head"><h2>Ubah Status Pesanan</h2></div><div class="panel-body">' +
        '<form data-admin-order-status data-id="' + order.id + '"><div class="field"><label for="order-status">Status</label>' +
          '<select id="order-status" name="status">' + D.statuses.map(function (s) {
            return '<option value="' + e(s) + '"' + DC.selected(s, order.status) + '>' + e(s) + '</option>';
          }).join('') + '</select></div>' +
          '<button type="submit" class="btn btn-primary btn-block mt-2">Simpan Status</button></form>' +
      '</div></div>' +
      '<div class="panel"><div class="panel-head"><h2>Status Pembayaran</h2></div><div class="panel-body">' +
        '<form data-admin-order-payment data-id="' + order.id + '"><div class="field"><label for="payment-status">Pembayaran</label>' +
          '<select id="payment-status" name="payment_status">' + D.paymentStatuses.map(function (s) {
            return '<option value="' + e(s) + '"' + DC.selected(s, order.payment_status) + '>' + e(s) + '</option>';
          }).join('') + '</select></div>' +
          '<div class="field"><label>Metode</label><div>' + e(order.payment_method) + '</div></div>' +
          '<button type="submit" class="btn btn-accent btn-block mt-2">Simpan Pembayaran</button></form>' +
      '</div></div>' +
      '<div class="panel"><div class="panel-head"><h2>Ringkasan</h2></div><div class="panel-body">' +
        '<div style="display:flex;justify-content:space-between;font-size:.85rem;margin-bottom:8px"><span class="muted">Dibuat</span><span>' + e(DC.formatDate(order.created_at, true)) + '</span></div>' +
        '<div style="display:flex;justify-content:space-between;font-size:.85rem;margin-bottom:8px"><span class="muted">Jumlah item</span><span>' + qty + ' pcs</span></div>' +
        '<div style="display:flex;justify-content:space-between;font-size:.85rem;margin-bottom:8px"><span class="muted">Tipe</span><span>' + e(order.order_type) + '</span></div>' +
      '</div></div>' +
    '</div></div>';

    return { title: 'Detail Pesanan', subtitle: order.order_number, active: 'orders', html: html };
  }

  /* ---------------- Pelanggan ---------------- */
  function customers(route) {
    var q = (route.params.get('q') || '').trim();
    var needle = q.toLowerCase();
    var orders = DC.getOrders();
    var users = DC.getUsers();
    var list = users.filter(function (u) {
      return !needle || (u.name + ' ' + u.email + ' ' + (u.phone || '')).toLowerCase().indexOf(needle) > -1;
    });
    var guestOrders = orders.filter(function (o) { return !o.user_id; }).length;

    var rows = list.length ? list.map(function (user) {
      var userOrders = orders.filter(function (o) { return Number(o.user_id) === user.id; });
      var spent = userOrders.reduce(function (sum, o) { return o.status === 'Dibatalkan' ? sum : sum + Number(o.total); }, 0);
      var last = userOrders.length ? userOrders[0].created_at : '';
      return '<tr><td><div style="display:flex;gap:10px;align-items:center">' +
        '<span class="admin-user"><span class="avatar">' + e(DC.initials(user.name)) + '</span></span>' +
        '<strong>' + e(user.name) + '</strong></div></td>' +
        '<td>' + e(user.email) + '</td><td>' + e(user.phone || '-') + '</td>' +
        '<td>' + userOrders.length + ' pesanan</td><td class="nowrap">' + e(rupiah(spent)) + '</td>' +
        '<td class="nowrap">' + (last ? e(DC.formatDate(last)) : '-') + '</td></tr>';
    }).join('') : emptyRow(6, 'Belum ada pelanggan terdaftar.');

    var html = '<div class="stat-grid" style="grid-template-columns:repeat(3,1fr)">' +
      statCard('green', 'customers', 'Pelanggan Terdaftar', list.length) +
      statCard('blue', 'orders', 'Pesanan Tamu', guestOrders) +
      statCard('orange', 'box', 'Total Pelanggan', users.length + guestOrders) +
    '</div>' +
    '<div class="toolbar"><form data-admin-customer-filter>' +
      '<input type="search" name="q" value="' + e(q) + '" placeholder="Cari nama, email, telepon...">' +
      '<button type="submit" class="btn btn-light btn-sm">Cari</button></form></div>' +
    '<div class="panel"><div class="panel-head"><h2>Daftar Pelanggan (' + list.length + ')</h2></div>' +
      '<div class="panel-body flush"><div class="table-wrap"><table class="table"><thead><tr>' +
        '<th>Pelanggan</th><th>Email</th><th>WhatsApp</th><th>Pesanan</th><th>Total Belanja</th><th>Terakhir Pesan</th>' +
      '</tr></thead><tbody>' + rows + '</tbody></table></div></div></div>';

    return { title: 'Data Pelanggan', subtitle: 'Daftar pelanggan terdaftar dan riwayat belanjanya', active: 'customers', html: html };
  }

  function render(route) {
    var page = route.parts[1] || 'dashboard';

    if (page === 'login' || !DC.isAdmin()) {
      return adminLogin();
    }

    var view;
    switch (page) {
      case 'dashboard': view = dashboard(); break;
      case 'products': view = products(route); break;
      case 'categories': view = categories(); break;
      case 'orders': view = orders(route); break;
      case 'order': view = orderDetail(Number(route.parts[2] || 0)); break;
      case 'customers': view = customers(route); break;
      default:
        view = { title: 'Halaman Admin', subtitle: '', active: 'dashboard', html: '<div class="panel"><div class="panel-body"><p>Halaman admin tidak ditemukan.</p><a href="#/admin" class="btn btn-primary">Ke Dashboard</a></div></div>' };
    }

    return { title: view.title, html: shell(view.html, view) };
  }

  DC.admin = { render: render };
})();
