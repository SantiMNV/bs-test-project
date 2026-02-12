(function () {
  'use strict';

  function initFormValidation() {
    var forms = document.querySelectorAll('.needs-validation');

    Array.prototype.slice.call(forms).forEach(function (form) {
      form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
          event.preventDefault();
          event.stopPropagation();
        }

        form.classList.add('was-validated');
      }, false);
    });
  }

  function normalize(value) {
    return String(value || '').trim().toLowerCase();
  }

  function initCitySearch() {
    var input = document.getElementById('citySearch');
    var clearButton = document.getElementById('clearCitySearch');
    var tableBody = document.getElementById('userTableBody');
    var noResultsRow = document.getElementById('cityFilterNoResults');

    if (!input || !tableBody) {
      return;
    }

    var rows = Array.prototype.slice.call(tableBody.querySelectorAll('tr[data-city]'));

    function applyFilter() {
      var query = normalize(input.value);
      var visibleCount = 0;

      rows.forEach(function (row) {
        var city = normalize(row.getAttribute('data-city'));
        var isVisible = query === '' || city.indexOf(query) !== -1;

        row.style.display = isVisible ? '' : 'none';
        if (isVisible) {
          visibleCount += 1;
        }
      });

      if (noResultsRow) {
        noResultsRow.style.display = query !== '' && visibleCount === 0 ? '' : 'none';
      }
    }

    input.addEventListener('input', applyFilter);

    if (clearButton) {
      clearButton.addEventListener('click', function () {
        input.value = '';
        input.focus();
        applyFilter();
      });
    }
  }

  function init() {
    initFormValidation();
    initCitySearch();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
