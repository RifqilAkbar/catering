/* =========================================================
   Dapur Catering - Data Demo (versi statis GitHub Pages)
   Sumber: database/seed.sql
   ========================================================= */
window.DC_DATA = (function () {
  'use strict';

  var categories = [
    {
      id: 1,
      name: 'Nasi Kotak',
      slug: 'nasi-kotak',
      description: 'Praktis, higienis, dan mengenyangkan. Cocok untuk rapat, seminar, syukuran, dan acara harian dengan pilihan lauk lengkap.',
      image: 'assets/images/13k.jpg'
    },
    {
      id: 2,
      name: 'Tumpeng',
      slug: 'tumpeng',
      description: 'Tumpeng syukuran dengan tampilan megah dan cita rasa istimewa. Tersedia dalam berbagai ukuran sesuai jumlah tamu Anda.',
      image: 'assets/images/bg4.jpg'
    },
    {
      id: 3,
      name: 'Ayam Geprek',
      slug: 'ayam-geprek',
      description: 'Ayam geprek renyah dengan sambal khas yang pedasnya bisa disesuaikan. Favorit untuk makan siang dan acara santai.',
      image: 'assets/images/geprek-10k.jpg'
    }
  ];

  var nasiAddons = [
    { name: 'Extra Ayam', price: 8000 },
    { name: 'Extra Telur', price: 3000 },
    { name: 'Kerupuk Udang', price: 2000 },
    { name: 'Air Mineral', price: 3000 }
  ];
  var nasiPremiumAddons = [
    { name: 'Extra Ayam', price: 9000 },
    { name: 'Extra Telur', price: 3000 },
    { name: 'Nugget Tambahan', price: 5000 },
    { name: 'Air Mineral', price: 3000 }
  ];
  var nasiSpesialAddons = [
    { name: 'Extra Ayam', price: 10000 },
    { name: 'Extra Telur', price: 3000 },
    { name: 'Nugget Tambahan', price: 5000 },
    { name: 'Air Mineral', price: 3000 }
  ];
  var tumpengAddons = function (dekorasi, lauk, kemasan) {
    return [
      { name: 'Dekorasi Tambahan', price: dekorasi },
      { name: 'Lauk Tambahan', price: lauk },
      { name: 'Kemasan Eksklusif', price: kemasan }
    ];
  };
  var geprekAddons = [
    { name: 'Tambah Nasi', price: 4000 },
    { name: 'Topping Keju', price: 5000 },
    { name: 'Telur Ceplok', price: 3000 },
    { name: 'Es Teh', price: 4000 },
    { name: 'Air Mineral', price: 3000 }
  ];
  var geprekVariants = function (price) {
    return [1, 2, 3, 4, 5].map(function (level) {
      return { name: 'Level ' + level, price: price };
    });
  };

  var products = [
    {
      id: 1, category_id: 1, name: 'Nasi Kotak Ekonomis', slug: 'nasi-kotak-ekonomis',
      description: 'Paket hemat dengan nasi, lauk pilihan, sayuran, sambal, dan kerupuk. Pilihan tepat untuk kebutuhan makan sehari-hari dalam jumlah banyak.',
      image: 'assets/images/13k.jpg', base_price: 13000, stock: 100, is_available: 1, is_featured: 1,
      variants: [{ name: 'Reguler', price: 13000 }, { name: 'Jumbo', price: 16000 }],
      addons: nasiAddons
    },
    {
      id: 2, category_id: 1, name: 'Nasi Kotak Standar', slug: 'nasi-kotak-standar',
      description: 'Nasi kotak dengan lauk ayam, mie atau urap, sambal goreng, dan kerupuk udang. Seimbang antara harga dan porsi.',
      image: 'assets/images/15k.jpg', base_price: 15000, stock: 100, is_available: 1, is_featured: 1,
      variants: [{ name: 'Reguler', price: 15000 }, { name: 'Jumbo', price: 18000 }],
      addons: nasiAddons
    },
    {
      id: 3, category_id: 1, name: 'Nasi Kotak Premium', slug: 'nasi-kotak-premium',
      description: 'Pilihan lauk lebih lengkap dengan porsi lebih besar, nugget, dan kerupuk udang. Cocok untuk tamu undangan.',
      image: 'assets/images/18k.jpg', base_price: 18000, stock: 80, is_available: 1, is_featured: 0,
      variants: [{ name: 'Reguler', price: 18000 }, { name: 'Jumbo', price: 22000 }],
      addons: nasiPremiumAddons
    },
    {
      id: 4, category_id: 1, name: 'Nasi Kotak Spesial', slug: 'nasi-kotak-spesial',
      description: 'Paket paling lengkap dengan ayam porsi besar, telur, nugget, dan kerupuk udang. Untuk acara yang ingin lebih berkesan.',
      image: 'assets/images/20k.jpg', base_price: 20000, stock: 80, is_available: 1, is_featured: 0,
      variants: [{ name: 'Reguler', price: 20000 }, { name: 'Jumbo', price: 25000 }],
      addons: nasiSpesialAddons
    },
    {
      id: 5, category_id: 2, name: 'Tumpeng Mini', slug: 'tumpeng-mini',
      description: 'Tumpeng kecil untuk 4-5 orang dengan lauk pendamping lengkap. Pas untuk syukuran keluarga kecil di rumah.',
      image: 'assets/images/bg1.jpg', base_price: 85000, stock: 20, is_available: 1, is_featured: 1,
      variants: [{ name: 'Standar (4-5 orang)', price: 85000 }, { name: 'Premium (4-5 orang)', price: 120000 }],
      addons: tumpengAddons(25000, 30000, 15000)
    },
    {
      id: 6, category_id: 2, name: 'Tumpeng Nampan', slug: 'tumpeng-nampan',
      description: 'Tumpeng ukuran nampan untuk 6-8 orang, cocok untuk arisan dan syukuran kantor.',
      image: 'assets/images/bg2.jpg', base_price: 150000, stock: 15, is_available: 1, is_featured: 0,
      variants: [{ name: 'Standar (6-8 orang)', price: 150000 }, { name: 'Premium (6-8 orang)', price: 195000 }],
      addons: tumpengAddons(30000, 40000, 15000)
    },
    {
      id: 7, category_id: 2, name: 'Tumpeng 10 Orang', slug: 'tumpeng-10-orang',
      description: 'Tumpeng porsi 10 orang dengan aneka lauk, sambal goreng, dan dekorasi rapi.',
      image: 'assets/images/bg3.jpg', base_price: 250000, stock: 12, is_available: 1, is_featured: 1,
      variants: [{ name: 'Standar', price: 250000 }, { name: 'Premium', price: 320000 }],
      addons: tumpengAddons(40000, 50000, 20000)
    },
    {
      id: 8, category_id: 2, name: 'Tumpeng 20 Orang', slug: 'tumpeng-20-orang',
      description: 'Tumpeng besar untuk 20 orang, ideal untuk acara syukuran, ulang tahun, dan perayaan kantor.',
      image: 'assets/images/bg4.jpg', base_price: 450000, stock: 8, is_available: 1, is_featured: 0,
      variants: [{ name: 'Standar', price: 450000 }, { name: 'Premium', price: 550000 }],
      addons: tumpengAddons(50000, 75000, 25000)
    },
    {
      id: 9, category_id: 2, name: 'Tumpeng Custom', slug: 'tumpeng-custom',
      description: 'Tumpeng dengan ukuran, lauk, dan dekorasi yang dapat disesuaikan dengan tema acara Anda.',
      image: 'assets/images/bg5.jpg', base_price: 600000, stock: 5, is_available: 1, is_featured: 0,
      variants: [
        { name: '30 Orang', price: 600000 },
        { name: '50 Orang', price: 950000 },
        { name: 'Custom (konsultasi)', price: 1000000 }
      ],
      addons: tumpengAddons(60000, 100000, 30000)
    },
    {
      id: 10, category_id: 3, name: 'Ayam Geprek Original', slug: 'ayam-geprek-original',
      description: 'Ayam goreng renyah digeprek dengan sambal original khas Dapur Catering. Pedasnya bisa dipilih sesuai selera.',
      image: 'assets/images/geprek-10k.jpg', base_price: 10000, stock: 150, is_available: 1, is_featured: 1,
      variants: geprekVariants(10000), addons: geprekAddons
    },
    {
      id: 11, category_id: 3, name: 'Ayam Geprek Sambal Bawang', slug: 'ayam-geprek-sambal-bawang',
      description: 'Ayam geprek dengan sambal bawang harum dan gurih yang bikin nambah nasi.',
      image: 'assets/images/geprek-10k.jpg', base_price: 12000, stock: 150, is_available: 1, is_featured: 0,
      variants: geprekVariants(12000), addons: geprekAddons
    },
    {
      id: 12, category_id: 3, name: 'Ayam Geprek Sambal Matah', slug: 'ayam-geprek-sambal-matah',
      description: 'Ayam geprek dengan sambal matah segar khas Bali, perpaduan pedas dan wangi serai.',
      image: 'assets/images/geprek-10k.jpg', base_price: 13000, stock: 120, is_available: 1, is_featured: 0,
      variants: geprekVariants(13000), addons: geprekAddons
    },
    {
      id: 13, category_id: 3, name: 'Ayam Geprek Keju', slug: 'ayam-geprek-keju',
      description: 'Ayam geprek dengan topping keju melimpah, gurih dan pedas dalam satu gigitan.',
      image: 'assets/images/geprek-10k.jpg', base_price: 15000, stock: 100, is_available: 1, is_featured: 0,
      variants: geprekVariants(15000), addons: geprekAddons
    },
    {
      id: 14, category_id: 3, name: 'Paket Ayam Geprek Komplit', slug: 'paket-ayam-geprek-komplit',
      description: 'Ayam geprek komplit dengan nasi, telur, lalapan, dan minuman. Porsi kenyang untuk satu orang.',
      image: 'assets/images/geprek-10k.jpg', base_price: 20000, stock: 100, is_available: 1, is_featured: 1,
      variants: geprekVariants(20000), addons: geprekAddons
    }
  ];

  return {
    categories: categories,
    products: products,
    demoUser: {
      id: 1,
      name: 'Pelanggan Demo',
      email: 'pelanggan@dapurcatering.com',
      phone: '081234567890',
      password: 'pelanggan123',
      created_at: '2025-01-15 09:00:00'
    },
    demoAdmin: {
      name: 'Admin Dapur Catering',
      email: 'admin@dapurcatering.com',
      password: 'admin123'
    },
    orderTypes: ['Acara Pribadi', 'Rapat', 'Ulang Tahun', 'Pernikahan', 'Lainnya'],
    paymentMethods: ['Transfer Bank', 'QRIS', 'COD'],
    statuses: ['Menunggu', 'Diproses', 'Dikirim', 'Selesai', 'Dibatalkan'],
    paymentStatuses: ['Belum Dibayar', 'Menunggu Konfirmasi', 'Lunas', 'Gagal'],
    whatsapp: '6281234567890'
  };
})();
