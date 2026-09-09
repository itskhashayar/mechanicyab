(function () {
  'use strict';
  const root = window.MechanicYab || {};
  const api = root.api || '/wp-json/mechanicyab/v1';
  const request = (path, options) => fetch(api + path, Object.assign({ headers: { 'Accept': 'application/json' } }, options || {})).then((r) => r.json());
  const searchResults = document.querySelector('[data-search-results]');
  if (searchResults) {
    const params = new URLSearchParams(window.location.search);
    const query = new URLSearchParams({ q: params.get('q') || '', page: params.get('page') || '1', per_page: '20' });
    if (params.get('city')) query.set('location_id', params.get('city'));
    request('/search?' + query.toString()).then((payload) => {
      const items = payload.data && payload.data.items ? payload.data.items : [];
      searchResults.innerHTML = items.length ? items.map((item) => '<article class="card"><h3><a href="/mechanic/' + encodeURIComponent(item.slug) + '/">' + escapeHtml(item.name || '') + '</a></h3><p>' + escapeHtml(item.description || '') + '</p><p class="muted">امتیاز: ' + Number(item.score || item.average_rating || 0).toFixed(1) + '</p></article>').join('') : '<p class="muted">نتیجه‌ای پیدا نشد.</p>';
    }).catch(() => { searchResults.innerHTML = '<p class="muted">بارگذاری نتایج ممکن نشد.</p>'; });
  }
  const profile = document.querySelector('[data-mechanic-profile]');
  if (profile) {
    request('/mechanics/' + encodeURIComponent(profile.dataset.mechanicSlug)).then((payload) => {
      const item = payload.data || {};
      profile.querySelector('[data-profile-content]').innerHTML = '<h1>' + escapeHtml(item.name || '') + '</h1><p>' + escapeHtml(item.description || '') + '</p><p><a class="button" href="tel:' + encodeURIComponent(item.phone || '') + '">تماس با مکانیک</a></p><p class="muted">وضعیت احراز: ' + escapeHtml((item.trust || {}).verification_status || 'unverified') + '</p>';
    }).catch(() => { profile.querySelector('[data-profile-content]').innerHTML = '<p class="muted">پروفایل پیدا نشد.</p>'; });
  }
  function escapeHtml(value) { return String(value).replace(/[&<>'"]/g, (c) => ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', "'":'&#39;', '"':'&quot;' }[c])); }
}());
