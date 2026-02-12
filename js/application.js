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

  function refreshCityFilter() {
    document.dispatchEvent(new Event('users:updated'));
  }

  function initCitySearch() {
    var input = document.getElementById('citySearch');
    var clearButton = document.getElementById('clearCitySearch');
    var tableBody = document.getElementById('userTableBody');
    var noResultsRow = document.getElementById('cityFilterNoResults');

    if (!input || !tableBody) {
      return;
    }

    function applyFilter() {
      var query = normalize(input.value);
      var visibleCount = 0;
      var rows = Array.prototype.slice.call(tableBody.querySelectorAll('tr[data-city]'));

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
    document.addEventListener('users:updated', applyFilter);

    if (clearButton) {
      clearButton.addEventListener('click', function () {
        input.value = '';
        input.focus();
        applyFilter();
      });
    }

    applyFilter();
  }

  function setAjaxFeedback(feedbackEl, variant, messages) {
    if (!feedbackEl) {
      return;
    }

    feedbackEl.className = 'alert alert-' + variant;

    while (feedbackEl.firstChild) {
      feedbackEl.removeChild(feedbackEl.firstChild);
    }

    if (!messages || messages.length === 0) {
      feedbackEl.classList.add('d-none');
      return;
    }

    if (messages.length === 1) {
      feedbackEl.textContent = messages[0];
    } else {
      var list = document.createElement('ul');
      list.className = 'mb-0';

      messages.forEach(function (message) {
        var item = document.createElement('li');
        item.textContent = message;
        list.appendChild(item);
      });

      feedbackEl.appendChild(list);
    }
  }

  function addUserToTable(user) {
    var tableBody = document.getElementById('userTableBody');
    if (!tableBody || !user) {
      return;
    }

    var noResultsRow = document.getElementById('cityFilterNoResults');
    var row = document.createElement('tr');
    row.setAttribute('data-city', user.city || '');

    ['name', 'email', 'city', 'phone'].forEach(function (field) {
      var cell = document.createElement('td');
      cell.textContent = user[field] || '';
      row.appendChild(cell);
    });

    if (noResultsRow && noResultsRow.parentNode === tableBody) {
      tableBody.insertBefore(row, noResultsRow);
    } else {
      tableBody.appendChild(row);
    }

    refreshCityFilter();
  }

  function initPhoneField() {
    var input = document.getElementById('phone_input');
    var hidden = document.getElementById('phone_e164');

    if (!input || !hidden || typeof window.intlTelInput !== 'function') {
      return {
        sync: function () {
          return false;
        }
      };
    }

    var iti = window.intlTelInput(input, {
      initialCountry: 'us',
      nationalMode: false,
      autoPlaceholder: 'aggressive',
      strictMode: true,
      loadUtils: function () {
        return import('/js/vendor/intl-tel-input/utils.js');
      }
    });

    function syncPhone() {
      var number = iti.getNumber();
      hidden.value = number || '';

      if (!number || !iti.isValidNumber()) {
        input.setCustomValidity('Please enter a valid international phone number.');
        return false;
      }

      input.setCustomValidity('');
      return true;
    }

    input.addEventListener('countrychange', syncPhone);
    input.addEventListener('input', syncPhone);
    syncPhone();

    return {
      sync: syncPhone
    };
  }

  function initCreateUserAjax() {
    var form = document.getElementById('createUserForm');
    if (!form || typeof window.fetch !== 'function') {
      return;
    }

    var phone = initPhoneField();
    var feedbackEl = document.getElementById('createUserAjaxFeedback');
    var submitButton = document.getElementById('createUserSubmit');
    var originalButtonText = submitButton ? submitButton.textContent : '';

    form.addEventListener('submit', function (event) {
      event.preventDefault();
      phone.sync();

      if (!form.checkValidity()) {
        event.stopPropagation();
        form.classList.add('was-validated');
        return;
      }

      setAjaxFeedback(feedbackEl, 'danger', []);

      if (submitButton) {
        submitButton.disabled = true;
        submitButton.textContent = 'Saving...';
      }

      fetch(form.getAttribute('action'), {
        method: 'POST',
        body: new FormData(form),
        credentials: 'same-origin',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        }
      })
        .then(function (response) {
          return response.json().then(function (data) {
            return {
              ok: response.ok,
              status: response.status,
              data: data
            };
          }).catch(function () {
            return {
              ok: false,
              status: response.status,
              data: {
                ok: false,
                errors: ['Invalid server response. Please try again.']
              }
            };
          });
        })
        .then(function (result) {
          if (!result.ok || !result.data || !result.data.ok) {
            var serverErrors = (result.data && result.data.errors) || ['Unable to save record right now. Please try again.'];
            setAjaxFeedback(feedbackEl, 'danger', serverErrors);
            return;
          }

          addUserToTable(result.data.user);
          setAjaxFeedback(feedbackEl, 'success', ['User created successfully.']);
          form.reset();
          form.classList.remove('was-validated');
          phone.sync();
        })
        .catch(function () {
          setAjaxFeedback(feedbackEl, 'danger', ['Unable to save record right now. Please try again.']);
        })
        .finally(function () {
          if (submitButton) {
            submitButton.disabled = false;
            submitButton.textContent = originalButtonText;
          }
        });
    });
  }

  function init() {
    initFormValidation();
    initCitySearch();
    initCreateUserAjax();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
