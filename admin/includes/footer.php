        </main>
    </div>
</div>
<script>
(function () {
  var toggle = document.querySelector('[data-admin-toggle]');
  var sidebar = document.querySelector('[data-admin-sidebar]');
  if (toggle && sidebar) {
    toggle.addEventListener('click', function () {
      sidebar.classList.toggle('open');
    });
  }

  document.querySelectorAll('[data-repeat]').forEach(function (wrap) {
    var template = wrap.querySelector('template');
    var list = wrap.querySelector('[data-repeat-list]');
    var addBtn = wrap.querySelector('[data-repeat-add]');
    if (!template || !list || !addBtn) return;

    function bindRemove(row) {
      var remove = row.querySelector('[data-repeat-remove]');
      if (remove) {
        remove.addEventListener('click', function () {
          row.remove();
        });
      }
    }

    addBtn.addEventListener('click', function () {
      var clone = template.content.cloneNode(true);
      var row = clone.querySelector('.repeat-row');
      list.appendChild(clone);
      bindRemove(row);
    });

    list.querySelectorAll('.repeat-row').forEach(bindRemove);
  });
})();
</script>
</body>
</html>
