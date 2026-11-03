/* ===== Dashboard charts (data comes from window.DASH); colours follow the light/dark theme ===== */
document.addEventListener('DOMContentLoaded', function () {
  /* draw after the web font loads, otherwise Chart.js measures labels with the fallback font and clips them */
  if (document.fonts && document.fonts.ready) document.fonts.ready.then(drawCharts, drawCharts);
  else drawCharts();

  /* status panel clock */
  var clock = document.getElementById('liveClock');
  if (clock) {
    var tick = function () { clock.textContent = new Date().toLocaleTimeString('en-PH', { hour: 'numeric', minute: '2-digit' }); };
    tick(); setInterval(tick, 15000);
  }
});

function drawCharts() {
  var d = window.DASH;
  if (!d || typeof Chart === 'undefined' || drawCharts.done) return;
  drawCharts.done = true;

  function css(name) { return getComputedStyle(document.documentElement).getPropertyValue(name).trim(); }
  function themeColors() {
    return { text: css('--muted') || '#64748b', grid: css('--border') || '#e5eaf1', surface: css('--surface') || '#fff' };
  }
  /* line and bar colours follow the admin's theme (Appearance page) */
  var brand = css('--brand') || '#2563eb', brandRgb = css('--brand-rgb') || '37, 99, 235';
  var accent = css('--accent') || '#0d9488';
  function prettyDay(s) {
    var dt = new Date(s + 'T00:00:00');
    return isNaN(dt) ? s : dt.toLocaleDateString('en-PH', { weekday: 'short', month: 'short', day: 'numeric' });
  }

  Chart.defaults.font.family = "'Inter', system-ui, sans-serif";
  Chart.defaults.font.size = 12;
  Chart.defaults.plugins.tooltip.padding = 10;
  Chart.defaults.plugins.tooltip.cornerRadius = 10;
  Chart.defaults.plugins.tooltip.backgroundColor = '#0f172a';
  Chart.defaults.plugins.tooltip.titleFont = { weight: '600' };
  Chart.defaults.maintainAspectRatio = false;

  var charts = [];
  function axisOptions() {
    var c = themeColors();
    return {
      responsive: true,
      plugins: { legend: { display: false } },
      scales: {
        x: { grid: { display: false }, border: { display: false }, ticks: { color: c.text, maxRotation: 0, autoSkip: true } },
        y: { beginAtZero: true, grid: { color: c.grid }, border: { display: false }, ticks: { color: c.text, precision: 0 } }
      }
    };
  }
  function shortLabel(s) { s = String(s); return s.length > 18 ? s.slice(0, 17) + '…' : s; }

  /* visits trend: line with a soft gradient fill */
  var trendCanvas = document.getElementById('chartTrend');
  charts.push(new Chart(trendCanvas, {
    type: 'line',
    data: {
      labels: d.trendLabels.map(prettyDay),
      datasets: [{
        label: 'Visits', data: d.trendValues, borderColor: brand, borderWidth: 3, tension: .4, fill: true,
        pointRadius: 4, pointHoverRadius: 6, pointBackgroundColor: '#fff', pointBorderColor: brand, pointBorderWidth: 2,
        backgroundColor: function (ctx) {
          var area = ctx.chart.chartArea;
          if (!area) return 'rgba(' + brandRgb + ',.15)';
          var g = ctx.chart.ctx.createLinearGradient(0, area.top, 0, area.bottom);
          g.addColorStop(0, 'rgba(' + brandRgb + ',.35)'); g.addColorStop(1, 'rgba(' + brandRgb + ',0)');
          return g;
        }
      }]
    },
    options: axisOptions()
  }));

  charts.push(new Chart(document.getElementById('chartComplaints'), {
    type: 'bar',
    data: { labels: d.complaintLabels.map(shortLabel), datasets: [{ label: 'Visits', data: d.complaintValues, backgroundColor: accent, hoverBackgroundColor: brand, borderRadius: 8, borderSkipped: false, maxBarThickness: 38 }] },
    options: axisOptions()
  }));

  var usageOpts = axisOptions();
  usageOpts.indexAxis = 'y';
  usageOpts.scales = { x: usageOpts.scales.y, y: usageOpts.scales.x };
  charts.push(new Chart(document.getElementById('chartUsage'), {
    type: 'bar',
    data: { labels: d.usageLabels.map(shortLabel), datasets: [{ label: 'Quantity used', data: d.usageValues, backgroundColor: '#7c3aed', hoverBackgroundColor: '#6d28d9', borderRadius: 8, borderSkipped: false, maxBarThickness: 26 }] },
    options: usageOpts
  }));

  var c0 = themeColors();
  charts.push(new Chart(document.getElementById('chartStatus'), {
    type: 'doughnut',
    data: {
      labels: ['Available', 'Low Stock', 'Critical', 'Out of Stock', 'Near Expiry', 'Expired'],
      datasets: [{ data: d.status, backgroundColor: ['#16a34a', '#f59e0b', '#ef4444', '#475569', '#eab308', '#991b1b'], borderColor: c0.surface, borderWidth: 3, hoverOffset: 8 }]
    },
    options: {
      responsive: true, cutout: '68%',
      plugins: { legend: { position: 'bottom', labels: { color: c0.text, usePointStyle: true, pointStyle: 'circle', padding: 14, boxWidth: 8 } } }
    }
  }));

  /* re-colour axes, grid and legend when the theme is switched */
  document.addEventListener('clinic:themechange', function () {
    var c = themeColors();
    charts.forEach(function (ch) {
      var s = ch.options.scales || {};
      Object.keys(s).forEach(function (k) {
        if (s[k].ticks) s[k].ticks.color = c.text;
        if (s[k].grid && s[k].grid.display !== false) s[k].grid.color = c.grid;
      });
      if (ch.options.plugins.legend.labels) ch.options.plugins.legend.labels.color = c.text;
      if (ch.config.type === 'doughnut') ch.data.datasets[0].borderColor = c.surface;
      ch.update('none');
    });
  });
}
