/* ===== School Clinic System: shared behaviour ===== */
(function () {
  'use strict';

  /* ---------- light / dark theme ---------- */
  document.addEventListener('click', function (e) {
    var btn = e.target.closest && e.target.closest('[data-theme-toggle]');
    if (!btn) return;
    var next = document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-bs-theme', next);
    try { localStorage.setItem('clinic-theme', next); } catch (err) { /* private mode: theme just isn't remembered */ }
    document.dispatchEvent(new CustomEvent('clinic:themechange', { detail: next }));
  });

  /* ---------- Appearance page: clicking a theme previews it at once; Save applies it for everyone ---------- */
  document.addEventListener('change', function (e) {
    if (e.target.hasAttribute && e.target.hasAttribute('data-theme-preview')) document.documentElement.setAttribute('data-ui-theme', e.target.value);
  });

  /* ---------- phones: tables become cards; each cell is labelled with its column name ---------- */
  function labelTable(table) {
    var heads = Array.prototype.map.call(table.querySelectorAll('thead th'), function (th) { return th.textContent.trim(); });
    table.querySelectorAll('tbody tr').forEach(function (tr) {
      Array.prototype.forEach.call(tr.children, function (td, i) { if (!td.hasAttribute('colspan')) td.setAttribute('data-label', heads[i] || ''); });
    });
  }
  /* a real table while it fits its box; cards (style.css) when it would need sideways scrolling. Phones always get cards. */
  function fitTables(root) {
    (root || document).querySelectorAll('table.table-stack').forEach(function (t) {
      if (!t.getClientRects().length) return;               /* inside a closed popup: checked when it opens */
      if (window.innerWidth < 768) { t.classList.remove('fits'); return; }
      t.classList.add('fits');                               /* measure it laid out as a table */
      var box = t.parentElement;
      while (box !== document.body && getComputedStyle(box).overflowX === 'visible') box = box.parentElement;
      if (t.offsetWidth > box.clientWidth + 1) t.classList.remove('fits');
    });
  }
  window.labelTables = function (root) { (root || document).querySelectorAll('table.table-stack').forEach(labelTable); fitTables(root); };
  window.labelTables();   /* app.js loads at the end of the page, so every table is already there */
  var refitQueued = false;
  function refit() { if (refitQueued) return; refitQueued = true; requestAnimationFrame(function () { refitQueued = false; fitTables(); }); }
  window.addEventListener('resize', refit);
  window.addEventListener('load', refit);               /* web fonts change text widths */
  document.addEventListener('shown.bs.modal', function (e) { fitTables(e.target); });

  /* ---------- strict input filters ---------- */
  document.addEventListener('input', function (e) {
    var t = e.target, n = t.getAttribute('name');
    if (t.classList && t.classList.contains('name-input')) t.value = t.value.replace(/[^\p{L}\s.'-]/gu, '');
    if (n === 'school_id') t.value = t.value.replace(/[^A-Za-z0-9-]/g, '').toUpperCase();
    if (n === 'access_code') t.value = t.value.replace(/\D/g, '').slice(0, 6);
    if (n === 'item_code') t.value = t.value.replace(/[^A-Za-z0-9-]/g, '').toUpperCase();
    if (n === 'contact_number' || n === 'guardian_contact' || n === 'emergency_contact_number') t.value = t.value.replace(/\D/g, '').slice(0, 11);
    if (n === 'blood_pressure') t.value = t.value.replace(/[^0-9\/]/g, '').slice(0, 7);
    if (n === 'unit') t.value = t.value.replace(/[^A-Za-z\s-]/g, '');
    if (t.type === 'email') { var em = t.value.replace(/\s/g, '').toLowerCase(); if (em !== t.value) t.value = em; }
    if (t.hasAttribute && t.hasAttribute('data-check')) checkField(t);
  });

  /* ---------- reject random letters such as "bsrngobwljrowbrwjl" (same rules as looks_like_gibberish() in includes/validation.php) ---------- */
  var VOWEL = 'aeiouyàáâãäåèéêëìíîïòóôõöùúûüýÿ';
  var RARE = ' ' + (window.RARE_PAIRS || '') + ' ';   /* letter pairs printed by partials/footer.php */
  function rarePairCount(w) {
    var n = 0;
    for (var i = 0; i < w.length - 1; i++) { if (RARE.indexOf(' ' + w.substr(i, 2) + ' ') !== -1) n++; }
    return n;
  }
  var GIB = {
    repeat: /(\p{L}{1,3})\1\1/u,
    keyboard: /qwer|asdf|sdfg|dfgh|fghj|ghjk|hjkl|zxcv|xcvb|cvbn|vbnm|uiop|poiu|lkjh|fdsa|rewq/,
    consonants: new RegExp('[^' + VOWEL + ']{5,}', 'u'),
    vowel: new RegExp('[' + VOWEL + ']', 'u'),
    vowels: /[aeiouàáâãäåèéêëìíîïòóôõöùúûü]{5,}/u
  };
  /* loose: short ALL-CAPS words (BSIT, HTN) and unit abbreviations (hrs, mmHg) are allowed */
  function looksLikeGibberish(text, loose) {
    return (String(text).match(/\p{L}+/gu) || []).some(function (word) {
      if (loose && word.length <= 6 && word === word.toUpperCase()) return false;
      var w = word.toLowerCase();
      return GIB.repeat.test(w) || GIB.keyboard.test(w) || GIB.consonants.test(w)
        || (w.length >= (loose ? 5 : 3) && !GIB.vowel.test(w)) || GIB.vowels.test(w) || rarePairCount(w) >= 2;
    });
  }
  /* <input data-check="name|text">: the browser blocks Save and shows the message; nothing typed is lost */
  function checkField(el) {
    var v = el.value.trim(), mode = el.getAttribute('data-check'), msg = '';
    if (el.name === 'school_id' && v && (!/[A-Za-z]/.test(v) || !/[0-9]/.test(v))) msg = 'School/Employee ID must contain both letters and numbers.';
    else if (v && looksLikeGibberish(v, mode === 'text')) msg = mode === 'name' ? 'This does not look like a real name. Please check the spelling.' : 'This looks like random letters. Please check the spelling.';
    el.setCustomValidity(msg);
  }

  /* ---------- live search: <input data-filter=".row-class"> ---------- */
  document.addEventListener('input', function (e) {
    var sel = e.target.getAttribute && e.target.getAttribute('data-filter');
    if (!sel) return;
    var term = e.target.value.toLowerCase().trim();
    document.querySelectorAll(sel).forEach(function (row) {
      row.style.display = row.textContent.toLowerCase().indexOf(term) !== -1 ? '' : 'none';
    });
  });

  /* ---------- search inside a <select>: <input data-filter-options="#id"> ---------- */
  document.addEventListener('input', function (e) {
    var sel = e.target.getAttribute && e.target.getAttribute('data-filter-options');
    if (!sel) return;
    var select = document.querySelector(sel), term = e.target.value.toLowerCase().trim();
    Array.prototype.forEach.call(select.options, function (o) {
      if (o.value) o.hidden = term !== '' && o.text.toLowerCase().indexOf(term) === -1;
    });
  });

  /* ---------- transaction type -> matching reasons ---------- */
  var REASONS = {
    'Stock In': ['Delivery / Received Supply'],
    'Stock Out': ['Damaged Item', 'Expired / Disposal', 'Lost / Missing Item'],
    'Adjustment': ['Quantity Correction']
  };
  function buildReasons(typeSelect) {
    var target = document.querySelector(typeSelect.getAttribute('data-reason-target'));
    if (!target) return;
    target.innerHTML = '';
    (REASONS[typeSelect.value] || []).forEach(function (r) {
      var o = document.createElement('option'); o.value = r; o.textContent = r; target.appendChild(o);
    });
  }
  document.addEventListener('change', function (e) {
    if (e.target.getAttribute && e.target.getAttribute('data-reason-target')) buildReasons(e.target);
  });
  document.querySelectorAll('select[data-reason-target]').forEach(buildReasons);

  /* ---------- modal forms: fill / reset ---------- */
  function fillForm(form, data) {
    form.reset();
    Array.prototype.forEach.call(form.elements, function (el) { if (el.setCustomValidity) el.setCustomValidity(''); });
    if (form.elements['id']) form.elements['id'].value = data.id || '';
    if (form.elements['status']) form.elements['status'].value = data.status || '';
    Object.keys(data).forEach(function (k) {
      if (k === 'csrf_token' || k === 'action' || k === 'return') return;
      var el = form.elements[k];
      if (el && el.tagName && el.type !== 'file') el.value = (data[k] === null || data[k] === undefined) ? '' : data[k];
    });
    form.querySelectorAll('[data-fill]').forEach(function (el) { var v = data[el.getAttribute('data-fill')]; el.value = v === undefined ? '' : v; });
    form.querySelectorAll('[data-fill-text]').forEach(function (el) { var v = data[el.getAttribute('data-fill-text')]; if (v !== undefined) el.textContent = v; });
    form.querySelectorAll('select').forEach(function (s) { s.dispatchEvent(new Event('change', { bubbles: true })); });
    form.querySelectorAll('[data-check]').forEach(checkField);
  }

  /* the saved record behind a page button (e.g. the Edit button of patient #id), or fallback */
  function recordFor(modalId, id, fallback) {
    var found = fallback;
    document.querySelectorAll('[data-bs-target="#' + modalId + '"][data-record]').forEach(function (b) {
      try { var rec = JSON.parse(b.getAttribute('data-record')); if (String(rec.id) === String(id)) found = rec; } catch (err) { /* skip bad JSON */ }
    });
    return found;
  }

  /* <button data-reset-form>: add mode clears the form, edit mode goes back to the saved record */
  document.addEventListener('click', function (e) {
    var btn = e.target.closest && e.target.closest('[data-reset-form]');
    if (!btn) return;
    var form = btn.closest('form');
    fillForm(form, form.savedRecord || {});
  });

  function toggleGroup(el, show) {
    el.classList.toggle('d-none', !show);
    el.querySelectorAll('input,select,textarea').forEach(function (i) {
      if (i.hasAttribute('data-fill')) return;
      i.disabled = !show;
    });
  }
  function applyMode(modal, isEdit) {
    modal.querySelectorAll('[data-add][data-edit]').forEach(function (el) { el.textContent = el.getAttribute(isEdit ? 'data-edit' : 'data-add'); });
    modal.querySelectorAll('.add-only').forEach(function (el) { toggleGroup(el, !isEdit); });
    modal.querySelectorAll('.edit-only').forEach(function (el) { toggleGroup(el, isEdit); });
  }

  document.addEventListener('show.bs.modal', function (e) {
    var modal = e.target, trigger = e.relatedTarget;
    var form = modal.querySelector('form[data-fillable]');
    var record = {};
    if (trigger && trigger.getAttribute('data-record')) {
      try { record = JSON.parse(trigger.getAttribute('data-record')); } catch (err) { record = {}; }
    }
    if (form && trigger) {
      /* data-keep-draft: reopening the same record (or "Add" again) keeps what was typed */
      var key = String(record.id || 'new');
      if (!modal.hasAttribute('data-keep-draft') || form.getAttribute('data-draft') !== key) {
        fillForm(form, record);
        form.setAttribute('data-draft', key);
        form.savedRecord = record;
      }
      applyMode(modal, modal.getAttribute('data-editable') === '1' && !!record.id);
    }
    var viewer = modal.getAttribute('data-viewer');
    if (viewer && trigger && window.Viewers[viewer]) window.Viewers[viewer](modal, record);
  });

  /* reopen a modal after a validation error (keeps what the user typed) */
  document.addEventListener('DOMContentLoaded', function () {
    var r = window.REOPEN;
    if (!r) return;
    var modal = document.getElementById(r.modal);
    if (!modal) return;
    var form = modal.querySelector('form[data-fillable]');
    if (form) {
      var data = r.data || {};
      fillForm(form, data);
      form.setAttribute('data-draft', String(data.id || 'new'));
      form.savedRecord = data.id ? recordFor(r.modal, data.id, data) : {};
      applyMode(modal, modal.getAttribute('data-editable') === '1' && !!data.id);
    }
    bootstrap.Modal.getOrCreateInstance(modal).show();
  });

  /* ---------- read-only viewers ---------- */
  function setView(root, key, value) {
    root.querySelectorAll('[data-view="' + key + '"]').forEach(function (el) {
      var empty = el.getAttribute('data-empty') || '\u2014';
      el.textContent = (value === null || value === undefined || value === '') ? empty : value;
    });
  }
  function cell(tr, text, cls) { var td = document.createElement('td'); td.textContent = text; if (cls) td.className = cls; tr.appendChild(td); return td; }
  function fmtDate(s) {
    if (!s) return '';
    var d = new Date(String(s).replace(' ', 'T'));
    return isNaN(d) ? s : d.toLocaleString('en-PH', { year: 'numeric', month: 'short', day: '2-digit', hour: '2-digit', minute: '2-digit' });
  }
  function showModal(id) { bootstrap.Modal.getOrCreateInstance(document.getElementById(id)).show(); }
  function swapModal(fromId, then) {
    var from = document.getElementById(fromId);
    from.addEventListener('hidden.bs.modal', function once() { from.removeEventListener('hidden.bs.modal', once); then(); });
    bootstrap.Modal.getOrCreateInstance(from).hide();
  }

  window.Viewers = {
    visit: function (modal, v) {
      var val = function (x, suffix) { return (x === null || x === undefined || x === '') ? '' : x + suffix; };
      var data = {
        visit_date_fmt: fmtDate(v.visit_date), patient_name: v.patient_name, school_id: v.school_id,
        type_grade: [v.patient_type ? v.patient_type.charAt(0).toUpperCase() + v.patient_type.slice(1) : '', v.grade_department].filter(Boolean).join(' / '),
        allergies: v.allergies, medical_conditions: v.medical_conditions, duty_staff: v.duty_staff,
        staff_role: v.staff_role ? 'Role: ' + v.staff_role : '',
        guardian_badge: 'Guardian notified: ' + v.guardian_notified, referred_badge: 'Referred: ' + v.referred_to_hospital,
        temperature_fmt: val(v.temperature, ' \u00B0C'), bp_fmt: val(v.blood_pressure, ' mmHg'), pulse_fmt: val(v.pulse_rate, ' bpm'),
        chief_complaint: v.chief_complaint, assessment: v.assessment, treatment_given: v.treatment_given,
        recommendation: v.recommendation, notes: v.notes
      };
      Object.keys(data).forEach(function (k) { setView(modal, k, data[k]); });
      var tbody = modal.querySelector('[data-view-meds]');
      tbody.innerHTML = '';
      (v.meds || []).forEach(function (m) {
        var tr = document.createElement('tr');
        cell(tr, m.item_code); cell(tr, m.item_name); cell(tr, m.category); cell(tr, m.quantity_used + ' ' + m.unit);
        tbody.appendChild(tr);
      });
      if (!(v.meds || []).length) {
        var tr = document.createElement('tr'); var td = cell(tr, 'No medicine or supply was issued for this visit.', 'text-center text-muted'); td.colSpan = 4; tbody.appendChild(tr);
      }
      window.labelTables(modal);
    },

    patient: function (modal, p) {
      var cap = function (s) { return s ? s.charAt(0).toUpperCase() + s.slice(1) : ''; };
      var data = {
        full_name: p.full_name, school_id: p.school_id, type_sex: [cap(p.patient_type), p.sex].filter(Boolean).join(' / '),
        grade_department: p.grade_department, birth_date: p.birth_date, contact_number: p.contact_number,
        guardian: [p.guardian_name, p.guardian_contact].filter(Boolean).join(' \u2014 '),
        allergies: p.allergies, medical_conditions: p.medical_conditions,
        emergency: [p.emergency_contact_name, p.emergency_contact_number].filter(Boolean).join(' \u2014 '), remarks: p.remarks,
        portal_code: p.portal_code
      };
      Object.keys(data).forEach(function (k) { setView(modal, k, data[k]); });
      var reset = modal.querySelector('#pvResetCode');
      if (reset) reset.elements.namedItem('id').value = p.id;

      var tbody = modal.querySelector('[data-view-visits]');
      var message = function (text) {
        tbody.innerHTML = '';
        var tr = document.createElement('tr'); var c = cell(tr, text, 'text-center text-muted'); c.colSpan = 5; tbody.appendChild(tr);
      };
      var renderVisit = function (v) {
        var tr = document.createElement('tr');
        cell(tr, fmtDate(v.visit_date)); cell(tr, v.chief_complaint);
        cell(tr, 'T: ' + (v.temperature || 'N/A') + ' \u00B7 BP: ' + (v.blood_pressure || 'N/A') + ' \u00B7 PR: ' + (v.pulse_rate || 'N/A'));
        cell(tr, v.duty_staff + ' (' + v.staff_role + ')');
        var td = document.createElement('td'); td.className = 'text-end';
        var b = document.createElement('button'); b.type = 'button'; b.className = 'btn btn-sm btn-outline-primary'; b.textContent = 'Details';
        b.addEventListener('click', function () {
          var full = Object.assign({ patient_name: p.full_name, school_id: p.school_id, patient_type: p.patient_type, grade_department: p.grade_department, allergies: p.allergies, medical_conditions: p.medical_conditions }, v);
          swapModal('patientViewModal', function () { window.Viewers.visit(document.getElementById('visitViewModal'), full); showModal('visitViewModal'); });
        });
        td.appendChild(b); tr.appendChild(td); tbody.appendChild(tr);
      };

      /* history is fetched on open so the Patients page stays small */
      var requested = String(p.id);
      modal.setAttribute('data-patient-id', requested);
      message('Loading clinic visit history...');
      fetch('?page=patient_visits&id=' + encodeURIComponent(requested), { credentials: 'same-origin', headers: { Accept: 'application/json' } })
        .then(function (r) { return r.json(); })
        .then(function (visits) {
          if (modal.getAttribute('data-patient-id') !== requested) return; /* another profile was opened meanwhile */
          if (!visits.length) { message('No clinic visit history yet.'); return; }
          tbody.innerHTML = '';
          visits.forEach(renderVisit);
          window.labelTables(modal);
        })
        .catch(function () {
          if (modal.getAttribute('data-patient-id') === requested) message('Could not load visit history. Your session may have ended; please reload the page.');
        });

      var rv = modal.querySelector('#pvRecordVisit');
      rv.onclick = function () {
        swapModal('patientViewModal', function () {
          var vm = document.getElementById('visitModal');
          fillForm(vm.querySelector('form[data-fillable]'), { patient_id: p.id });
          showModal('visitModal');
        });
      };
    }
  };

  /* ---------- print the body of a modal ---------- */
  window.printModal = function (btn) {
    var modal = btn.closest('.modal');
    var title = modal.querySelector('.modal-title').textContent.trim();
    var w = window.open('', '_blank');
    if (!w) { alert('Please allow pop-ups to print.'); return; }
    w.document.write('<!doctype html><html><head><title>' + title + '</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"><style>.no-print{display:none}.detail-label{font-size:.75rem;color:#64748b;text-transform:uppercase;font-weight:700}.detail-value{white-space:pre-wrap}</style></head><body class="p-4"><h4 class="mb-3">School Clinic System &mdash; ' + title + '</h4>' + modal.querySelector('.modal-body').innerHTML + '</body></html>');
    w.document.close();
    w.onload = function () { w.focus(); w.print(); };
  };
})();
