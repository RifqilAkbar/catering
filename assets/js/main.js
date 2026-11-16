/* =========================================================
   Dapur Catering - Interaksi Frontend
   ========================================================= */
(function () {
  'use strict';

  function formatRupiah(value) {
    return 'Rp' + Math.round(Number(value) || 0).toLocaleString('id-ID');
  }

  function setCartCount(count) {
    count = Number(count) || 0;
    document.querySelectorAll('[data-cart-count]').forEach(function (el) {
      el.textContent = count;
      el.setAttribute('data-empty', count > 0 ? 'false' : 'true');
    });
  }

  function showToast(message, type) {
    var wrap = document.querySelector('.toast-wrap');
    if (!wrap) {
      wrap = document.createElement('div');
      wrap.className = 'toast-wrap';
      document.body.appendChild(wrap);
    }
    var toast = document.createElement('div');
    toast.className = 'toast ' + (type || 'success');
    toast.textContent = message;
    wrap.appendChild(toast);
    setTimeout(function () {
      toast.style.opacity = '0';
      toast.style.transition = 'opacity .3s ease';
      setTimeout(function () { toast.remove(); }, 320);
    }, 2800);
  }

  function initNav() {
    var toggle = document.querySelector('[data-nav-toggle]');
    var menu = document.querySelector('[data-nav-menu]');
    if (toggle && menu) {
      toggle.addEventListener('click', function () {
        menu.classList.toggle('open');
      });
    }
  }

  function initReveal() {
    var items = document.querySelectorAll('.reveal');
    if (!items.length) return;
    if ('IntersectionObserver' in window) {
      var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add('visible');
            io.unobserve(entry.target);
          }
        });
      }, { threshold: 0.12 });
      items.forEach(function (el) { io.observe(el); });
    } else {
      items.forEach(function (el) { el.classList.add('visible'); });
    }
  }

  function initProductForm(form) {
    var qtyInput = form.querySelector('[data-qty-input]');
    var totalEl = form.querySelector('[data-detail-total]');
    var variantInputs = form.querySelectorAll('[name="variant_id"]');
    var addonInputs = form.querySelectorAll('[name="addons[]"]');
    var basePrice = parseFloat(form.getAttribute('data-base-price')) || 0;

    function unitPrice() {
      var price = basePrice;
      variantInputs.forEach(function (input) {
        if (input.checked) price = parseFloat(input.getAttribute('data-price')) || 0;
      });
      addonInputs.forEach(function (input) {
        if (input.checked) price += parseFloat(input.getAttribute('data-price')) || 0;
      });
      return price;
    }

    function updateTotal() {
      var qty = Math.max(1, parseInt(qtyInput.value, 10) || 1);
      if (totalEl) totalEl.textContent = formatRupiah(unitPrice() * qty);
    }

    form.addEventListener('change', updateTotal);
    form.addEventListener('input', function (e) {
      if (e.target === qtyInput) updateTotal();
    });

    form.querySelectorAll('[data-step]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var step = parseInt(btn.getAttribute('data-step'), 10) || 0;
        var value = Math.max(1, (parseInt(qtyInput.value, 10) || 1) + step);
        qtyInput.value = value;
        updateTotal();
      });
    });

    updateTotal();

    var api = form.getAttribute('data-api');
    if (!api) return;

    form.addEventListener('submit', function (ev) {
      ev.preventDefault();
      var submitter = ev.submitter;
      var buyNow = submitter && submitter.value === '1';
      var buttons = form.querySelectorAll('button[type="submit"]');
      buttons.forEach(function (b) { b.disabled = true; });

      var data = new FormData(form);
      data.set('action', 'add');
      if (!data.get('quantity')) data.set('quantity', '1');

      fetch(api, {
        method: 'POST',
        body: data,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      })
        .then(function (res) { return res.json(); })
        .then(function (json) {
          if (!json.ok) {
            showToast(json.message || 'Gagal menambahkan produk.', 'error');
            buttons.forEach(function (b) { b.disabled = false; });
            return;
          }
          setCartCount(json.count);
          if (buyNow) {
            window.location.href = form.getAttribute('data-checkout');
          } else {
            showToast(json.message || 'Produk ditambahkan ke keranjang.', 'success');
            buttons.forEach(function (b) { b.disabled = false; });
            if (qtyInput) { qtyInput.value = 1; updateTotal(); }
          }
        })
        .catch(function () {
          /* Fallback ke submit biasa jika AJAX gagal */
          HTMLFormElement.prototype.submit.call(form);
        });
    });
  }

  function initCartForms() {
    document.querySelectorAll('[data-cart-qty]').forEach(function (form) {
      var input = form.querySelector('input[name="quantity"]');
      if (!input) return;

      var dec = form.querySelector('[data-cart-dec]');
      var inc = form.querySelector('[data-cart-inc]');

      function submitWith(value) {
        input.value = Math.max(1, value);
        form.submit();
      }

      if (dec) dec.addEventListener('click', function () {
        submitWith((parseInt(input.value, 10) || 1) - 1);
      });
      if (inc) inc.addEventListener('click', function () {
        submitWith((parseInt(input.value, 10) || 1) + 1);
      });
      input.addEventListener('change', function () {
        var value = parseInt(input.value, 10);
        if (isNaN(value) || value < 1) { value = 1; }
        submitWith(value);
      });
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    initNav();
    initReveal();
    initCartForms();

    var productForm = document.querySelector('[data-product-form]');
    if (productForm) initProductForm(productForm);
  });

  window.DapurCatering = { showToast: showToast, setCartCount: setCartCount, formatRupiah: formatRupiah };
})();
