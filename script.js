/* =========================================================
   Dapur Mbak Khotim — versi dummy konsep
   Hanya HTML, CSS, dan JavaScript. Tanpa foto dan tanpa server.

   Isi file ini:
   1. Data menu dummy
   2. Filter kategori
   3. Keranjang dan perhitungan total
   4. Simulasi checkout
   ========================================================= */

'use strict';

/* ---------------------------------------------------------
   1. DATA DUMMY
   Data ini hanya contoh. Tidak ada koneksi ke database.
   --------------------------------------------------------- */
const MENU = [
  {
    id: 1,
    category: 'nasi-kotak',
    name: 'Nasi Kotak Ekonomis',
    description: 'Nasi, lauk pilihan, sayuran, sambal, dan kerupuk.',
    price: 13000
  },
  {
    id: 2,
    category: 'nasi-kotak',
    name: 'Nasi Kotak Standar',
    description: 'Nasi, ayam, mie atau urap, sambal goreng, dan kerupuk udang.',
    price: 15000
  },
  {
    id: 3,
    category: 'nasi-kotak',
    name: 'Nasi Kotak Premium',
    description: 'Porsi lebih besar dengan nugget dan lauk yang lebih lengkap.',
    price: 18000
  },
  {
    id: 4,
    category: 'nasi-kotak',
    name: 'Nasi Kotak Spesial',
    description: 'Ayam porsi besar, telur, nugget, dan kerupuk udang.',
    price: 20000
  },
  {
    id: 5,
    category: 'tumpeng',
    name: 'Tumpeng Mini',
    description: 'Tumpeng untuk 4–5 orang dengan lauk pendamping lengkap.',
    price: 85000
  },
  {
    id: 6,
    category: 'tumpeng',
    name: 'Tumpeng Nampan',
    description: 'Tumpeng untuk 6–8 orang, cocok untuk arisan dan syukuran.',
    price: 150000
  },
  {
    id: 7,
    category: 'tumpeng',
    name: 'Tumpeng 10 Orang',
    description: 'Aneka lauk, sambal goreng, dan dekorasi yang rapi.',
    price: 250000
  },
  {
    id: 8,
    category: 'ayam-geprek',
    name: 'Ayam Geprek Original',
    description: 'Ayam renyah dengan sambal original, level pedas bisa dipilih.',
    price: 10000
  },
  {
    id: 9,
    category: 'ayam-geprek',
    name: 'Ayam Geprek Sambal Bawang',
    description: 'Sambal bawang harum dan gurih yang bikin nambah nasi.',
    price: 12000
  },
  {
    id: 10,
    category: 'ayam-geprek',
    name: 'Ayam Geprek Keju',
    description: 'Topping keju melimpah, gurih dan pedas dalam satu gigitan.',
    price: 15000
  },
  {
    id: 11,
    category: 'ayam-geprek',
    name: 'Paket Ayam Geprek Komplit',
    description: 'Ayam geprek dengan nasi, telur, lalapan, dan minuman.',
    price: 20000
  }
];

const CATEGORY_LABELS = {
  'nasi-kotak': 'Nasi Kotak',
  tumpeng: 'Tumpeng',
  'ayam-geprek': 'Ayam Geprek'
};

const STORAGE_KEY = 'dapur-mbak-khotim-dummy-cart';

/* ---------------------------------------------------------
   2. STATE
   cart berbentuk objek: { idMenu: jumlah }
   --------------------------------------------------------- */
let activeCategory = 'semua';
let cart = loadCart();

/* Ambil elemen yang dibutuhkan dari halaman. */
const menuList = document.getElementById('menu-list');
const menuCount = document.getElementById('menu-count');
const filterBar = document.getElementById('filters');
const cartItems = document.getElementById('cart-items');
const cartTotal = document.getElementById('cart-total');
const cartMessage = document.getElementById('cart-message');
const clearCartButton = document.getElementById('clear-cart');
const checkoutButton = document.getElementById('checkout');

/* ---------------------------------------------------------
   3. UTILITAS
   --------------------------------------------------------- */
function formatRupiah(value) {
  return 'Rp' + value.toLocaleString('id-ID');
}

function findMenu(id) {
  return MENU.find(function (item) {
    return item.id === id;
  });
}

function loadCart() {
  try {
    const saved = JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}');
    return saved && typeof saved === 'object' ? saved : {};
  } catch (error) {
    return {};
  }
}

function saveCart() {
  try {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(cart));
  } catch (error) {
    /* localStorage bisa diblokir di beberapa browser; demo tetap berjalan. */
  }
}

/* ---------------------------------------------------------
   4. DAFTAR MENU DAN FILTER
   --------------------------------------------------------- */
function renderMenu() {
  const items = activeCategory === 'semua'
    ? MENU
    : MENU.filter(function (item) {
        return item.category === activeCategory;
      });

  menuList.innerHTML = items.map(function (item) {
    return (
      '<article class="menu-item">' +
        '<div class="menu-main">' +
          '<span class="menu-category">' + CATEGORY_LABELS[item.category] + '</span>' +
          '<h3>' + item.name + '</h3>' +
          '<p>' + item.description + '</p>' +
        '</div>' +
        '<div class="menu-side">' +
          '<strong>' + formatRupiah(item.price) + '</strong>' +
          '<button class="add-btn" type="button" data-add="' + item.id + '">Tambah</button>' +
        '</div>' +
      '</article>'
    );
  }).join('');

  menuCount.textContent = 'Menampilkan ' + items.length + ' dari ' + MENU.length + ' menu dummy.';
}

function setActiveCategory(category) {
  activeCategory = category;

  const buttons = filterBar.querySelectorAll('.filter');
  buttons.forEach(function (button) {
    button.classList.toggle('is-active', button.dataset.category === category);
  });

  renderMenu();
}

filterBar.addEventListener('click', function (event) {
  const button = event.target.closest('.filter');
  if (!button) return;
  setActiveCategory(button.dataset.category);
});

/* ---------------------------------------------------------
   5. KERANJANG
   --------------------------------------------------------- */
function addToCart(id) {
  cart[id] = (cart[id] || 0) + 1;
  saveCart();
  renderCart();
}

function changeQuantity(id, change) {
  if (!cart[id]) return;

  cart[id] += change;

  if (cart[id] <= 0) {
    delete cart[id];
  }

  saveCart();
  renderCart();
}

function getCartSummary() {
  let totalItems = 0;
  let totalPrice = 0;

  Object.keys(cart).forEach(function (id) {
    const item = findMenu(Number(id));
    if (!item) return;
    totalItems += cart[id];
    totalPrice += item.price * cart[id];
  });

  return { totalItems: totalItems, totalPrice: totalPrice };
}

function renderCart() {
  const ids = Object.keys(cart);

  if (ids.length === 0) {
    cartItems.innerHTML =
      '<p class="cart-empty">Keranjang masih kosong. Tambahkan menu dari daftar di atas.</p>';
  } else {
    cartItems.innerHTML = ids.map(function (id) {
      const item = findMenu(Number(id));
      if (!item) return '';
      const quantity = cart[id];

      return (
        '<div class="cart-item">' +
          '<div class="cart-item-info">' +
            '<strong>' + item.name + '</strong>' +
            '<span>' + formatRupiah(item.price) + ' &times; ' + quantity + '</span>' +
          '</div>' +
          '<div class="quantity">' +
            '<button type="button" data-change="-1" data-id="' + item.id + '" aria-label="Kurangi ' + item.name + '">&minus;</button>' +
            '<span>' + quantity + '</span>' +
            '<button type="button" data-change="1" data-id="' + item.id + '" aria-label="Tambah ' + item.name + '">+</button>' +
          '</div>' +
        '</div>'
      );
    }).join('');
  }

  cartTotal.textContent = formatRupiah(getCartSummary().totalPrice);
}

/* ---------------------------------------------------------
   6. EVENT KERANJANG DAN CHECKOUT
   --------------------------------------------------------- */
menuList.addEventListener('click', function (event) {
  const button = event.target.closest('[data-add]');
  if (!button) return;

  const id = Number(button.dataset.add);
  addToCart(id);

  const originalText = button.textContent;
  button.textContent = 'Ditambahkan';
  setTimeout(function () {
    button.textContent = originalText;
  }, 1000);
});

cartItems.addEventListener('click', function (event) {
  const button = event.target.closest('[data-change]');
  if (!button) return;

  changeQuantity(Number(button.dataset.id), Number(button.dataset.change));
});

clearCartButton.addEventListener('click', function () {
  cart = {};
  saveCart();
  renderCart();
  hideMessage();
});

checkoutButton.addEventListener('click', function () {
  const summary = getCartSummary();

  if (summary.totalItems === 0) {
    showMessage('Keranjang masih kosong. Tambahkan menu terlebih dahulu.');
    return;
  }

  showMessage(
    'Simulasi checkout berhasil: ' +
    summary.totalItems + ' item dengan total ' +
    formatRupiah(summary.totalPrice) +
    '. Tidak ada data yang dikirim ke server.'
  );

  cart = {};
  saveCart();
  renderCart();
});

function showMessage(text) {
  cartMessage.textContent = text;
  cartMessage.hidden = false;
}

function hideMessage() {
  cartMessage.textContent = '';
  cartMessage.hidden = true;
}

/* ---------------------------------------------------------
   7. JALANKAN SAAT HALAMAN DIBUKA
   --------------------------------------------------------- */
renderMenu();
renderCart();
